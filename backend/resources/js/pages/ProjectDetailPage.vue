<script setup>
import { ref, computed, onMounted, onUnmounted, onBeforeUnmount } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import draggable from 'vuedraggable'
import AppLayout from '../layouts/AppLayout.vue'
import TaskCard from '../components/TaskCard.vue'
import CreateTaskModal from '../components/CreateTaskModal.vue'
import ProjectMembersPanel from '../components/ProjectMembersPanel.vue'
import ProjectStatsPanel from '../components/ProjectStatsPanel.vue'
import { useProjectsStore } from '../stores/projects'
import { useTasksStore } from '../stores/tasks'
import client from '../http/client'

const route = useRoute()
const projectId = Number(route.params.projectId)

const projectsStore = useProjectsStore()
const tasksStore = useTasksStore()

const project = computed(() => projectsStore.findProject(projectId))
const members = ref([])
const stats = ref(null)
const statsLoading = ref(true)
const showModal = ref(false)
const loadError = ref(null)
const activeTab = ref('board')

// PDF report state
const reportState = ref('idle') // idle | generating | timeout | error
const reportError = ref(null)
let reportId = null
let pollTimer = null
let pollTimeout = null

const columns = [
    {
        label: 'Todo',
        status: 'todo',
        accent: 'border-slate-300',
        bg: 'bg-slate-100/70',
        dot: 'bg-slate-400',
        text: 'text-slate-600',
    },
    {
        label: 'In Progress',
        status: 'in_progress',
        accent: 'border-blue-400',
        bg: 'bg-blue-50/70',
        dot: 'bg-blue-500',
        text: 'text-blue-700',
    },
    {
        label: 'Done',
        status: 'done',
        accent: 'border-emerald-400',
        bg: 'bg-emerald-50/70',
        dot: 'bg-emerald-500',
        text: 'text-emerald-700',
    },
]

let abortController = null

onMounted(async () => {
    abortController = new AbortController()
    const signal = abortController.signal
    try {
        if (!project.value) await projectsStore.fetchProject(projectId)
        const [, res, statsRes] = await Promise.all([
            tasksStore.fetchTasks(projectId),
            client.get(`/projects/${projectId}/members`, { signal }),
            client.get(`/projects/${projectId}/stats`, { signal }).catch(() => null),
        ])
        if (!signal.aborted) {
            members.value = res.data.data
            if (statsRes) stats.value = statsRes.data.data
            statsLoading.value = false
        }
    } catch (e) {
        if (e.code !== 'ERR_CANCELED') {
            loadError.value = 'Failed to load project data.'
            statsLoading.value = false
        }
    }
})

onBeforeUnmount(() => {
    abortController?.abort()
    stopPolling()
})
onUnmounted(() => tasksStore.clear())

// --- PDF report ---

function stopPolling() {
    clearInterval(pollTimer)
    clearTimeout(pollTimeout)
    pollTimer = null
    pollTimeout = null
}

function startPolling() {
    pollTimeout = setTimeout(() => {
        stopPolling()
        reportState.value = 'timeout'
    }, 60_000)

    let inFlight = false
    pollTimer = setInterval(async () => {
        if (inFlight) return
        inFlight = true
        try {
            const response = await client.get(`/projects/${projectId}/reports/${reportId}/download`, {
                responseType: 'blob',
            })
            stopPolling()
            reportState.value = 'idle'
            const url = URL.createObjectURL(response.data)
            const a = document.createElement('a')
            a.href = url
            a.download = `project-${projectId}-report.pdf`
            a.click()
            URL.revokeObjectURL(url)
        } catch (e) {
            if (e.response?.status === 409) return // not ready yet
            stopPolling()
            reportState.value = 'error'
            reportError.value = 'Report generation failed.'
        } finally {
            inFlight = false
        }
    }, 3_000)
}

const dragging = ref(false)

async function onDrop(event, targetStatus) {
    if (!event.added) return
    const task = event.added.element
    if (task.status === targetStatus) return
    if (dragging.value) return
    dragging.value = true
    const prevStatus = task.status
    task.status = targetStatus
    try {
        await tasksStore.changeStatus(projectId, task.id, targetStatus)
    } catch {
        task.status = prevStatus
    } finally {
        dragging.value = false
    }
}

async function exportPdf() {
    if (reportState.value === 'generating') return
    reportState.value = 'generating'
    reportError.value = null
    try {
        const { data } = await client.post(`/projects/${projectId}/reports`)
        reportId = data.data.id
        startPolling()
    } catch {
        reportState.value = 'error'
        reportError.value = 'Failed to start report generation.'
    }
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-6xl">
            <RouterLink
                to="/projects"
                class="mb-5 inline-flex items-center gap-1 text-sm text-gray-400 transition-colors hover:text-gray-700"
                >← Projects
            </RouterLink>

            <p v-if="loadError" role="alert" class="mb-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
                {{ loadError }}
            </p>

            <div class="mb-7 flex flex-wrap items-start justify-between gap-4">
                <template v-if="project">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ project.name }}</h1>
                        <p v-if="project.description" class="mt-1 text-sm leading-relaxed text-gray-500">
                            {{ project.description }}
                        </p>
                    </div>
                </template>
                <template v-else>
                    <div>
                        <div class="mb-2 h-8 w-56 animate-pulse rounded-lg bg-gray-200"></div>
                        <div class="h-4 w-80 animate-pulse rounded bg-gray-100"></div>
                    </div>
                </template>

                <div class="flex shrink-0 flex-col items-end gap-1.5">
                    <div class="flex items-center gap-2">
                        <!-- Export PDF -->
                        <button
                            :disabled="reportState === 'generating'"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-50 disabled:opacity-60"
                            @click="exportPdf"
                        >
                            <!-- Spinner -->
                            <svg
                                v-if="reportState === 'generating'"
                                class="h-4 w-4 animate-spin text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"
                                />
                            </svg>
                            <!-- PDF icon -->
                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 13h4M10 17h4M10 9h1"
                                />
                            </svg>
                            <span>{{ reportState === 'generating' ? 'Generating…' : 'Export PDF' }}</span>
                        </button>

                        <!-- New task -->
                        <button
                            class="inline-flex min-w-fit items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                            @click="showModal = true"
                        >
                            <span class="text-base leading-none">+</span> New task
                        </button>
                    </div>

                    <!-- Report status messages -->
                    <p v-if="reportState === 'timeout'" class="text-xs text-amber-600">
                        Report timed out.
                        <button class="underline hover:text-amber-800" @click="exportPdf">Retry</button>
                    </p>
                    <p v-else-if="reportState === 'error'" class="text-xs text-red-500">
                        {{ reportError }}
                        <button class="underline hover:text-red-700" @click="exportPdf">Retry</button>
                    </p>
                </div>
            </div>

            <div class="mb-5 border-b border-gray-200">
                <nav class="-mb-px flex gap-1">
                    <button
                        v-for="tab in [
                            { id: 'board', label: 'Board' },
                            { id: 'members', label: 'Members' },
                            { id: 'stats', label: 'Stats' },
                        ]"
                        :key="tab.id"
                        class="border-b-2 px-4 py-2.5 text-sm font-medium transition-colors"
                        :class="
                            activeTab === tab.id
                                ? 'border-blue-600 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        "
                        @click="activeTab = tab.id"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <div v-show="activeTab === 'board'">
                <div v-if="tasksStore.loading" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div
                        v-for="i in 3"
                        :key="i"
                        class="h-48 animate-pulse rounded-xl border border-gray-100 bg-white p-4"
                    ></div>
                </div>

                <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div
                        v-for="col in columns"
                        :key="col.status"
                        class="flex min-h-48 flex-col gap-2 rounded-xl border-t-[3px] p-4"
                        :class="[col.bg, col.accent]"
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 shrink-0 rounded-full" :class="col.dot"></span>
                                <h2 class="text-xs font-semibold tracking-widest uppercase" :class="col.text">
                                    {{ col.label }}
                                </h2>
                            </div>
                            <span class="rounded-md bg-white/70 px-1.5 py-0.5 text-xs font-medium text-gray-500">{{
                                tasksStore.byStatus[col.status].length
                            }}</span>
                        </div>

                        <draggable
                            :list="tasksStore.byStatus[col.status]"
                            group="tasks"
                            item-key="id"
                            ghost-class="opacity-50"
                            class="flex flex-col gap-2"
                            @change="onDrop($event, col.status)"
                        >
                            <template #item="{ element }">
                                <TaskCard :task="element" :project-id="projectId" />
                            </template>
                            <template #footer>
                                <p
                                    v-if="tasksStore.byStatus[col.status].length === 0"
                                    class="py-6 text-center text-xs text-gray-400 italic"
                                >
                                    No tasks
                                </p>
                            </template>
                        </draggable>
                    </div>
                </div>
            </div>

            <ProjectMembersPanel v-if="activeTab === 'members'" :project-id="projectId" />

            <ProjectStatsPanel v-if="activeTab === 'stats'" :stats="stats" :loading="statsLoading" />
        </div>

        <CreateTaskModal v-if="showModal" :project-id="projectId" :members="members" @close="showModal = false" />
    </AppLayout>
</template>
