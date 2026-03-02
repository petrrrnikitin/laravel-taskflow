<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import client from '../http/client'

const props = defineProps({
    projectId: { type: Number, required: true },
    taskId: { type: Number, required: true },
})

const activity = ref([])
const loaded = ref(false)
const error = ref(null)

const ACTION_META = {
    created: { label: 'created this task', dot: 'bg-blue-400' },
    updated: { label: 'edited the task', dot: 'bg-gray-400' },
    status_changed: { label: '', dot: 'bg-violet-400' },
    assigned: { label: 'changed the assignee', dot: 'bg-amber-400' },
    deleted: { label: 'deleted the task', dot: 'bg-red-400' },
}

const STATUS_LABELS = { todo: 'Todo', in_progress: 'In Progress', done: 'Done' }

let abortController = null

onMounted(async () => {
    abortController = new AbortController()
    try {
        const { data } = await client.get(`/projects/${props.projectId}/tasks/${props.taskId}/activity`, {
            signal: abortController.signal,
        })
        activity.value = data.data
    } catch (e) {
        if (e.code !== 'ERR_CANCELED') error.value = 'Failed to load activity.'
    } finally {
        loaded.value = true
    }
})

onBeforeUnmount(() => abortController?.abort())

function entryLabel(entry) {
    if (entry.action === 'status_changed') {
        const from = entry.properties?.old_status
        const to = entry.properties?.new_status
        return `moved from ${STATUS_LABELS[from] ?? from} → ${STATUS_LABELS[to] ?? to}`
    }
    return ACTION_META[entry.action]?.label ?? entry.action
}

function entryDot(action) {
    return ACTION_META[action]?.dot ?? 'bg-gray-300'
}

function fmtDate(iso) {
    if (!iso) return '—'
    return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
    <div>
        <div v-if="!loaded" class="text-sm text-gray-400">Loading…</div>
        <p v-else-if="error" role="alert" class="text-sm text-red-500">{{ error }}</p>
        <div v-else-if="activity.length" class="relative">
            <div class="absolute top-2 bottom-2 left-[7px] w-px bg-gray-200"></div>
            <div v-for="entry in activity" :key="entry.id" class="relative flex gap-4 pb-5 pl-6">
                <div
                    class="absolute top-1.5 left-0 h-3.5 w-3.5 rounded-full border-2 border-white shadow-sm"
                    :class="entryDot(entry.action)"
                ></div>
                <div>
                    <div class="flex flex-wrap items-baseline gap-x-1 text-sm">
                        <span class="font-medium text-gray-700">{{ entry.actor?.name }}</span>
                        <span class="text-gray-500">{{ entryLabel(entry) }}</span>
                    </div>
                    <p class="mt-0.5 text-xs text-gray-400">{{ fmtDate(entry.created_at) }}</p>
                </div>
            </div>
        </div>
        <p v-else class="text-sm text-gray-400 italic">No activity yet.</p>
    </div>
</template>
