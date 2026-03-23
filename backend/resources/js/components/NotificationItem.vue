<script setup>
import { useRouter } from 'vue-router'
import { useNotificationsStore } from '../stores/notifications'

const props = defineProps({
    notification: { type: Object, required: true },
})

const router = useRouter()
const store = useNotificationsStore()

const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' })

function relativeTime(iso) {
    if (!iso) return ''
    const diff = (new Date(iso) - Date.now()) / 1000
    const abs = Math.abs(diff)
    if (abs < 60) return rtf.format(Math.round(diff), 'second')
    if (abs < 3600) return rtf.format(Math.round(diff / 60), 'minute')
    if (abs < 86400) return rtf.format(Math.round(diff / 3600), 'hour')
    return rtf.format(Math.round(diff / 86400), 'day')
}

async function handleClick() {
    if (!props.notification.read_at) {
        await store.markAsRead(props.notification.id)
    }
    const { task_id, project_id } = props.notification.data ?? {}
    if (project_id && task_id) {
        router.push(`/projects/${project_id}/tasks/${task_id}`)
    }
}
</script>

<template>
    <li
        class="relative flex cursor-pointer gap-3 px-4 py-3 transition-colors hover:bg-gray-50"
        :class="notification.read_at ? 'bg-white' : 'bg-blue-50'"
        @click="handleClick"
    >
        <!-- Unread indicator -->
        <span v-if="!notification.read_at" class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
        <span v-else class="mt-1.5 h-2 w-2 shrink-0"></span>

        <div class="min-w-0 flex-1">
            <p class="text-sm leading-snug text-gray-700">
                {{ notification.data?.message ?? notification.type }}
            </p>
            <p class="mt-0.5 text-xs text-gray-400">
                {{ relativeTime(notification.created_at) }}
            </p>
        </div>
    </li>
</template>
