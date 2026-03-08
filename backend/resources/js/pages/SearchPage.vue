<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import TaskCard from '../components/TaskCard.vue'
import client from '../http/client'

const q = ref('')
const status = ref('')
const priority = ref('')

const results = ref([])
const meta = ref(null)
const loading = ref(false)
const error = ref(null)

let controller = null
let debounceTimer = null

const hasQuery = computed(() => q.value.trim().length > 0 || status.value || priority.value)
const hasMore = computed(() => meta.value && meta.value.current_page < meta.value.last_page)

function abort() {
    controller?.abort()
    controller = null
}

onBeforeUnmount(() => {
    abort()
    clearTimeout(debounceTimer)
})

async function fetchPage(page = 1, append = false) {
    abort()
    controller = new AbortController()
    loading.value = true
    error.value = null
    try {
        const params = { page }
        if (q.value.trim()) params.q = q.value.trim()
        if (status.value) params.status = status.value
        if (priority.value) params.priority = priority.value

        const { data } = await client.get('/tasks/search', { params, signal: controller.signal })

        if (append) {
            results.value.push(...data.data)
        } else {
            results.value = data.data
        }
        meta.value = data.meta
    } catch (e) {
        if (e?.code !== 'ERR_CANCELED') error.value = 'Failed to load tasks.'
    } finally {
        loading.value = false
    }
}

watch([q, status, priority], () => {
    clearTimeout(debounceTimer)
    if (!hasQuery.value) {
        abort()
        results.value = []
        meta.value = null
        loading.value = false
        return
    }
    debounceTimer = setTimeout(() => fetchPage(1, false), 300)
})

function loadMore() {
    if (!hasMore.value || loading.value) return
    fetchPage(meta.value.current_page + 1, true)
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="mb-6 text-2xl font-bold text-gray-900">Search Tasks</h1>

            <!-- Search input -->
            <div class="relative mb-3">
                <svg
                    class="pointer-events-none absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"
                    />
                </svg>
                <input
                    v-model="q"
                    type="text"
                    placeholder="Search tasks…"
                    class="w-full rounded-xl border border-gray-200 bg-white py-3.5 pr-10 pl-11 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-400 focus:ring-2 focus:ring-blue-500/20 focus:outline-none"
                    autofocus
                />
                <button
                    v-if="q"
                    class="absolute top-1/2 right-3 -translate-y-1/2 rounded p-0.5 text-gray-400 transition-colors hover:text-gray-600"
                    @click="q = ''"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 shadow-sm">
                <span class="shrink-0 text-xs font-medium text-gray-400">Filters</span>
                <div class="mx-1 h-4 w-px bg-gray-200"></div>
                <div class="relative">
                    <select
                        v-model="status"
                        class="cursor-pointer appearance-none rounded-lg bg-gray-50 py-1.5 pr-7 pl-3 text-xs text-gray-600 focus:outline-none"
                    >
                        <option value="">All statuses</option>
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                    <svg
                        class="pointer-events-none absolute top-1/2 right-2 h-3 w-3 -translate-y-1/2 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <div class="relative">
                    <select
                        v-model="priority"
                        class="cursor-pointer appearance-none rounded-lg bg-gray-50 py-1.5 pr-7 pl-3 text-xs text-gray-600 focus:outline-none"
                    >
                        <option value="">All priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                    <svg
                        class="pointer-events-none absolute top-1/2 right-2 h-3 w-3 -translate-y-1/2 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
                <button
                    v-if="status || priority"
                    class="ml-auto text-xs text-gray-400 transition-colors hover:text-gray-600"
                    @click="
                        status = ''
                        priority = ''
                    "
                >
                    Clear
                </button>
            </div>

            <!-- Loading skeleton (initial load) -->
            <div v-if="loading && results.length === 0" class="space-y-3">
                <div
                    v-for="i in 4"
                    :key="i"
                    class="h-20 animate-pulse rounded-xl border border-gray-100 bg-white"
                ></div>
            </div>

            <!-- Error -->
            <p v-else-if="error" class="text-sm text-red-500">{{ error }}</p>

            <!-- Empty: start typing -->
            <div v-else-if="!hasQuery" class="flex flex-col items-center py-16 text-center">
                <svg class="mb-3 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"
                    />
                </svg>
                <p class="text-sm text-gray-400">Start typing to search tasks</p>
            </div>

            <!-- Empty: no results -->
            <div v-else-if="!loading && results.length === 0" class="flex flex-col items-center py-16 text-center">
                <p class="text-sm font-medium text-gray-500">No tasks found</p>
                <p class="mt-1 text-xs text-gray-400">Try a different query or remove filters</p>
            </div>

            <!-- Results -->
            <div v-else class="space-y-2">
                <div v-for="task in results" :key="task.id" class="flex flex-col gap-1">
                    <span class="px-0.5 text-xs text-gray-400">
                        {{ task.project?.name ?? `Project #${task.project_id}` }}
                    </span>
                    <TaskCard :task="task" :project-id="task.project_id" />
                </div>

                <!-- Skeleton for "load more" in progress -->
                <div v-if="loading" class="space-y-2 pt-1">
                    <div
                        v-for="i in 2"
                        :key="i"
                        class="h-20 animate-pulse rounded-xl border border-gray-100 bg-white"
                    ></div>
                </div>

                <!-- Load more -->
                <div v-if="hasMore && !loading" class="pt-2 text-center">
                    <button
                        class="rounded-lg bg-gray-100 px-5 py-2 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-200"
                        @click="loadMore"
                    >
                        Load more
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
