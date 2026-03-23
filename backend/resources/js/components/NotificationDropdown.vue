<script setup>
import { useNotificationsStore } from '../stores/notifications'
import NotificationItem from './NotificationItem.vue'

const store = useNotificationsStore()
</script>

<template>
    <div
        class="absolute top-full right-0 z-50 mt-2 w-80 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg"
    >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
            <span class="text-sm font-semibold text-gray-800">Notifications</span>
            <button
                v-if="store.unreadCount > 0"
                class="text-xs text-blue-600 transition-colors hover:text-blue-800"
                @click="store.markAllAsRead"
            >
                Mark all as read
            </button>
        </div>

        <!-- Loading skeleton -->
        <div v-if="store.loading" class="space-y-px p-2">
            <div v-for="i in 3" :key="i" class="h-14 animate-pulse rounded-lg bg-gray-100"></div>
        </div>

        <!-- Empty state -->
        <p v-else-if="store.notifications.length === 0" class="px-4 py-8 text-center text-sm text-gray-400">
            No notifications yet
        </p>

        <!-- List -->
        <ul v-else class="max-h-80 divide-y divide-gray-100 overflow-y-auto">
            <NotificationItem v-for="n in store.notifications" :key="n.id" :notification="n" />
        </ul>
    </div>
</template>
