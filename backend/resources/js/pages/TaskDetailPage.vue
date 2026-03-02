<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import TaskForm from '../components/TaskForm.vue'
import TaskComments from '../components/TaskComments.vue'
import TaskActivity from '../components/TaskActivity.vue'
import { useTasksStore } from '../stores/tasks'
import { useAuthSubmit } from '../composables/useAuthSubmit'
import client from '../http/client'

const route = useRoute()
const router = useRouter()
const projectId = Number(route.params.projectId)
const taskId = Number(route.params.taskId)

const store = useTasksStore()
const members = ref([])

const task = computed(() => store.tasks.find((t) => t.id === taskId))

const editing = ref(false)

const formInitial = computed(() =>
    task.value
        ? {
              title: task.value.title,
              description: task.value.description ?? '',
              priority: task.value.priority,
              due_date: task.value.due_date ?? '',
              assignee_id: task.value.assignee?.id ?? '',
          }
        : {},
)

const { loading: saving, error: saveError, execute: executeSave } = useAuthSubmit()
const loadError = ref(null)

async function save(payload) {
    await executeSave(async () => {
        await store.updateTask(projectId, taskId, payload)
        editing.value = false
    })
}

const STATUSES = [
    { label: 'Todo', value: 'todo' },
    { label: 'In Progress', value: 'in_progress' },
    { label: 'Done', value: 'done' },
]

async function setStatus(status) {
    await store.changeStatus(projectId, taskId, status)
}

async function remove() {
    if (!confirm('Delete this task? This cannot be undone.')) return
    await store.deleteTask(projectId, taskId)
    router.push(`/projects/${projectId}`)
}

const activeTab = ref('comments')
const commentsCount = ref(0)

const PRIORITY_BADGE = {
    high: 'bg-red-50 text-red-600 border-red-200',
    medium: 'bg-amber-50 text-amber-600 border-amber-200',
    low: 'bg-gray-100 text-gray-500 border-gray-200',
}

const today = new Date().toISOString().slice(0, 10)

function authorInitial(name) {
    return name?.charAt(0).toUpperCase() ?? '?'
}

let abortController = null

onMounted(async () => {
    abortController = new AbortController()
    const signal = abortController.signal
    try {
        const promises = [client.get(`/projects/${projectId}/members`, { signal })]
        if (!task.value) promises.push(store.fetchTask(projectId, taskId))
        const [membersRes] = await Promise.all(promises)
        if (!signal.aborted) members.value = membersRes.data.data
    } catch (e) {
        if (e.code !== 'ERR_CANCELED') loadError.value = 'Failed to load task data.'
    }
})

onBeforeUnmount(() => abortController?.abort())
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-2xl">
            <RouterLink
                :to="`/projects/${projectId}`"
                class="mb-5 inline-flex items-center gap-1 text-sm text-gray-400 transition-colors hover:text-gray-700"
                >← Project</RouterLink
            >

            <p v-if="loadError" role="alert" class="mb-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
                {{ loadError }}
            </p>

            <template v-if="!editing">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <h1 v-if="task" class="text-2xl leading-snug font-bold text-gray-900">{{ task.title }}</h1>
                    <div v-else class="h-8 w-64 animate-pulse rounded-lg bg-gray-200"></div>
                    <div v-if="task" class="flex shrink-0 items-center gap-2">
                        <button
                            class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-500 transition-colors hover:border-gray-300 hover:bg-gray-50"
                            @click="editing = true"
                        >
                            Edit
                        </button>
                        <button
                            class="rounded-lg border border-red-200 px-3 py-1.5 text-sm text-red-400 transition-colors hover:border-red-300 hover:bg-red-50 hover:text-red-600"
                            @click="remove"
                        >
                            Delete
                        </button>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="inline-flex overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <button
                            v-for="(s, i) in STATUSES"
                            :key="s.value"
                            class="px-3.5 py-2 text-sm transition-colors"
                            :class="[
                                task?.status === s.value
                                    ? 'bg-blue-600 font-medium text-white'
                                    : 'text-gray-500 hover:bg-slate-50',
                                i < STATUSES.length - 1 ? 'border-r border-gray-200' : '',
                            ]"
                            @click="setStatus(s.value)"
                        >
                            {{ s.label }}
                        </button>
                    </div>
                </div>

                <div v-if="task" class="mb-6 flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium"
                        :class="PRIORITY_BADGE[task.priority]"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ task.priority }} priority
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium"
                        :class="
                            task.due_date && task.due_date < today && task.status !== 'done'
                                ? 'border-red-200 bg-red-50 text-red-600'
                                : 'border-gray-200 bg-white text-gray-600'
                        "
                        >📅 {{ task.due_date ?? 'No due date' }}</span
                    >
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-600"
                    >
                        <template v-if="task.assignee">
                            <span
                                class="flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-600"
                            >
                                {{ authorInitial(task.assignee.name) }}
                            </span>
                            {{ task.assignee.name }}
                        </template>
                        <template v-else>Unassigned</template>
                    </span>
                </div>

                <div v-if="task" class="mb-7">
                    <p
                        v-if="task.description"
                        class="rounded-xl border border-gray-200 bg-white p-4 text-sm leading-relaxed whitespace-pre-line text-gray-700"
                    >
                        {{ task.description }}
                    </p>
                    <p v-else class="text-sm text-gray-400 italic">No description.</p>
                </div>

                <div class="mb-5 border-b border-gray-200">
                    <nav class="-mb-px flex gap-1">
                        <button
                            v-for="tab in [
                                { id: 'comments', label: 'Comments', badge: commentsCount },
                                { id: 'activity', label: 'Activity' },
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
                            <span
                                v-if="tab.badge !== undefined && tab.badge > 0"
                                class="ml-1.5 rounded-full bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500"
                                >{{ tab.badge }}</span
                            >
                        </button>
                    </nav>
                </div>

                <TaskComments
                    v-show="activeTab === 'comments'"
                    :project-id="projectId"
                    :task-id="taskId"
                    @update:count="commentsCount = $event"
                />
                <TaskActivity v-if="activeTab === 'activity'" :project-id="projectId" :task-id="taskId" />
            </template>

            <template v-else>
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Edit task</h2>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-6">
                    <TaskForm
                        :initial="formInitial"
                        :members="members"
                        :loading="saving"
                        :error="saveError"
                        submit-label="Save changes"
                        @submit="save"
                        @cancel="editing = false"
                    />
                </div>
            </template>
        </div>
    </AppLayout>
</template>
