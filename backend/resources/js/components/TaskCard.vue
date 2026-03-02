<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({
    task: { type: Object, required: true },
    projectId: { type: Number, required: true },
})

const today = new Date().toISOString().slice(0, 10)

const isOverdue = computed(() => props.task.due_date && props.task.due_date < today && props.task.status !== 'done')

const priorityBorder = { high: 'border-l-red-400', medium: 'border-l-amber-400', low: 'border-l-gray-300' }
const priorityBadge = {
    high: 'bg-red-50 text-red-600',
    medium: 'bg-amber-50 text-amber-600',
    low: 'bg-gray-100 text-gray-400',
}

const initial = computed(() => props.task.assignee?.name?.charAt(0).toUpperCase() ?? null)
</script>

<template>
    <div
        class="flex flex-col gap-2.5 rounded-lg border-l-2 bg-white p-3 shadow-sm transition-shadow hover:shadow-md"
        :class="priorityBorder[task.priority]"
    >
        <div class="flex items-start justify-between gap-2">
            <RouterLink
                :to="`/projects/${projectId}/tasks/${task.id}`"
                class="text-sm leading-snug font-medium text-gray-800 transition-colors hover:text-blue-600"
                >{{ task.title }}</RouterLink
            >
            <span class="shrink-0 rounded px-1.5 py-0.5 text-xs font-medium" :class="priorityBadge[task.priority]">{{
                task.priority
            }}</span>
        </div>

        <div v-if="task.assignee || task.due_date" class="flex items-center justify-between gap-2">
            <div v-if="task.assignee" class="flex items-center gap-1.5">
                <div
                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600"
                >
                    {{ initial }}
                </div>
                <span class="truncate text-xs text-gray-400">{{ task.assignee.name }}</span>
            </div>
            <span v-else class="flex-1"></span>
            <span
                v-if="task.due_date"
                class="shrink-0 text-xs font-medium"
                :class="isOverdue ? 'text-red-500' : 'text-gray-400'"
                >{{ task.due_date }}</span
            >
        </div>
    </div>
</template>
