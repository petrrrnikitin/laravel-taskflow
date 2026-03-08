<script setup>
defineProps({
    stats: { type: Object, default: null },
    loading: { type: Boolean, default: true },
})

const statusCards = [
    { key: 'todo', label: 'Todo', color: 'text-slate-600', bg: 'bg-slate-50', border: 'border-slate-200' },
    { key: 'in_progress', label: 'In Progress', color: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-200' },
    { key: 'done', label: 'Done', color: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-200' },
]

function initial(name) {
    return name?.charAt(0).toUpperCase() ?? '?'
}
</script>

<template>
    <div v-if="loading" class="space-y-4">
        <div class="h-28 animate-pulse rounded-xl border border-gray-100 bg-white"></div>
        <div class="h-36 animate-pulse rounded-xl border border-gray-100 bg-white"></div>
    </div>

    <div v-else-if="stats" class="space-y-4">
        <!-- Completion + Status counts -->
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-semibold tracking-wider text-gray-400 uppercase">Completion</span>
                    <span class="text-sm font-bold text-gray-700">{{ Math.round(stats.completion_rate) }}%</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                    <div
                        class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                        :style="{ width: `${stats.completion_rate}%` }"
                    ></div>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-3">
                <div
                    v-for="col in statusCards"
                    :key="col.key"
                    class="flex flex-col items-center rounded-lg border py-3"
                    :class="[col.bg, col.border]"
                >
                    <span class="text-2xl font-bold" :class="col.color">
                        {{ stats.tasks.by_status[col.key] ?? 0 }}
                    </span>
                    <span class="mt-0.5 text-xs text-gray-400">{{ col.label }}</span>
                </div>
                <div
                    class="flex flex-col items-center rounded-lg border py-3"
                    :class="stats.tasks.overdue > 0 ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-gray-50'"
                >
                    <span
                        class="text-2xl font-bold"
                        :class="stats.tasks.overdue > 0 ? 'text-red-600' : 'text-gray-400'"
                    >
                        {{ stats.tasks.overdue }}
                    </span>
                    <span class="mt-0.5 text-xs" :class="stats.tasks.overdue > 0 ? 'text-red-400' : 'text-gray-400'"
                        >Overdue</span
                    >
                </div>
            </div>
        </div>

        <!-- Top Assignees -->
        <div v-if="stats.top_assignees.length" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-xs font-semibold tracking-wider text-gray-400 uppercase">Top Assignees</h3>
            <ul class="space-y-3">
                <li v-for="user in stats.top_assignees" :key="user.id" class="flex items-center gap-3">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700"
                    >
                        {{ initial(user.name) }}
                    </div>
                    <span class="flex-1 text-sm text-gray-700">{{ user.name }}</span>
                    <span class="text-xs font-medium text-gray-400">{{ user.closed_tasks_count }} done</span>
                </li>
            </ul>
        </div>
    </div>
</template>
