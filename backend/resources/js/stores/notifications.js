import { ref } from 'vue'
import { defineStore } from 'pinia'
import client from '../http/client'

export const useNotificationsStore = defineStore('notifications', () => {
    const notifications = ref([])
    const unreadCount = ref(0)
    const loading = ref(false)

    async function fetchNotifications() {
        loading.value = true
        try {
            const { data } = await client.get('/notifications')
            notifications.value = data.data
        } finally {
            loading.value = false
        }
    }

    async function fetchUnreadCount() {
        const { data } = await client.get('/notifications/unread-count')
        unreadCount.value = data.count
    }

    async function markAsRead(id) {
        await client.patch(`/notifications/${id}/read`)
        const n = notifications.value.find((n) => n.id === id)
        if (n && !n.read_at) {
            n.read_at = new Date().toISOString()
        }
        if (unreadCount.value > 0) unreadCount.value--
    }

    async function markAllAsRead() {
        await client.post('/notifications/read-all')
        const now = new Date().toISOString()
        notifications.value.forEach((n) => {
            if (!n.read_at) n.read_at = now
        })
        unreadCount.value = 0
    }

    return { notifications, unreadCount, loading, fetchNotifications, fetchUnreadCount, markAsRead, markAllAsRead }
})
