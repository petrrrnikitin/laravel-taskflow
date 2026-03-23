<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import TaskCard from '../components/TaskCard.vue'
import { useAuthStore } from '../stores/auth'
import client from '../http/client'

const auth = useAuthStore()

const tasks = ref([])
const loading = ref(true)
const error = ref(null)
const hideDone = ref(false)

let controller = null

const inProgress = computed(() => tasks.value.filter((t) => t.status === 'in_progress'))
const todo = computed(() => tasks.value.filter((t) => t.status === 'todo'))
const done = computed(() => tasks.value.filter((t) => t.status === 'done'))
const activeCount = computed(() => inProgress.value.length + todo.value.length)

onMounted(async () => {
    controller = new AbortController()
    try {
        const { data } = await client.get('/tasks/search', {
            params: { assignee_id: auth.user.id, per_page: 100 },
            signal: controller.signal,
        })
        tasks.value = data.data
    } catch (e) {
        if (e.code !== 'ERR_CANCELED') error.value = 'Failed to load tasks.'
    } finally {
        loading.value = false
    }
})

onBeforeUnmount(() => controller?.abort())
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-2xl">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Tasks</h1>
                    <p v-if="!loading && !error" class="mt-0.5 text-sm text-gray-400">
                        {{ activeCount }} active{{ done.length ? `, ${done.length} done` : '' }}
                    </p>
                </div>
                <button
                    v-if="done.length > 0"
                    class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-500 transition-colors hover:bg-gray-50"
                    @click="hideDone = !hideDone"
                >
                    {{ hideDone ? 'Show completed' : 'Hide completed' }}
                </button>
            </div>

            <!-- Error -->
            <p v-if="error" role="alert" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">{{ error }}</p>

            <!-- Loading skeleton -->
            <div v-else-if="loading" class="space-y-2">
                <div
                    v-for="i in 5"
                    :key="i"
                    class="h-16 animate-pulse rounded-lg border border-gray-100 bg-white"
                ></div>
            </div>

            <!-- Empty state -->
            <div v-else-if="tasks.length === 0" class="flex flex-col items-center py-20 text-center">
                <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                    <svg class="h-7 w-7 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="font-medium text-gray-700">All clear!</p>
                <p class="mt-1 text-sm text-gray-400">No tasks assigned to you.</p>
            </div>

            <!-- Task sections -->
            <div v-else class="space-y-6">
                <!-- In Progress -->
                <section v-if="inProgress.length">
                    <h2
                        class="mb-2 flex items-center gap-2 text-xs font-semibold tracking-widest text-blue-600 uppercase"
                    >
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        In Progress
                        <span class="rounded-full bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-600">{{
                            inProgress.length
                        }}</span>
                    </h2>
                    <div class="space-y-2">
                        <div v-for="task in inProgress" :key="task.id">
                            <p class="mb-0.5 px-0.5 text-xs text-gray-400">
                                {{ task.project?.name ?? `Project #${task.project_id}` }}
                            </p>
                            <TaskCard :task="task" :project-id="task.project_id" />
                        </div>
                    </div>
                </section>

                <!-- Todo -->
                <section v-if="todo.length">
                    <h2
                        class="mb-2 flex items-center gap-2 text-xs font-semibold tracking-widest text-slate-500 uppercase"
                    >
                        <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                        Todo
                        <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-500">{{
                            todo.length
                        }}</span>
                    </h2>
                    <div class="space-y-2">
                        <div v-for="task in todo" :key="task.id">
                            <p class="mb-0.5 px-0.5 text-xs text-gray-400">
                                {{ task.project?.name ?? `Project #${task.project_id}` }}
                            </p>
                            <TaskCard :task="task" :project-id="task.project_id" />
                        </div>
                    </div>
                </section>

                <!-- Done -->
                <section v-if="done.length && !hideDone">
                    <h2
                        class="mb-2 flex items-center gap-2 text-xs font-semibold tracking-widest text-emerald-600 uppercase"
                    >
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Done
                        <span class="rounded-full bg-emerald-50 px-1.5 py-0.5 text-xs font-medium text-emerald-600">{{
                            done.length
                        }}</span>
                    </h2>
                    <div class="space-y-2">
                        <div v-for="task in done" :key="task.id">
                            <p class="mb-0.5 px-0.5 text-xs text-gray-400">
                                {{ task.project?.name ?? `Project #${task.project_id}` }}
                            </p>
                            <TaskCard :task="task" :project-id="task.project_id" />
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
