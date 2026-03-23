import { ref, reactive, watch } from 'vue'
import { defineStore } from 'pinia'
import client from '../http/client'
import { useToastStore } from './toast'

export const useTasksStore = defineStore('tasks', () => {
    const tasks = ref([])
    const loading = ref(false)

    // Stable reactive arrays — references never change, contents are spliced in-place.
    // This allows vuedraggable to hold reliable references for `:list`.
    const byStatus = reactive({
        todo: [],
        in_progress: [],
        done: [],
    })

    function syncByStatus() {
        for (const status of ['todo', 'in_progress', 'done']) {
            const filtered = tasks.value.filter((t) => t.status === status)
            byStatus[status].splice(0, byStatus[status].length, ...filtered)
        }
    }

    watch(tasks, syncByStatus, { deep: true, immediate: true })

    async function fetchTasks(projectId) {
        loading.value = true
        try {
            const { data } = await client.get(`/projects/${projectId}/tasks`)
            tasks.value = data.data
        } finally {
            loading.value = false
        }
    }

    async function fetchTask(projectId, taskId) {
        const { data } = await client.get(`/projects/${projectId}/tasks/${taskId}`)
        const task = data.data
        const idx = tasks.value.findIndex((t) => t.id === taskId)
        if (idx !== -1) tasks.value[idx] = task
        else tasks.value.push(task)
        return task
    }

    async function createTask(projectId, payload) {
        const { data } = await client.post(`/projects/${projectId}/tasks`, payload)
        tasks.value.push(data.data)
        useToastStore().add('Task created')
        return data.data
    }

    async function updateTask(projectId, taskId, payload) {
        const { data } = await client.put(`/projects/${projectId}/tasks/${taskId}`, payload)
        const task = data.data
        const idx = tasks.value.findIndex((t) => t.id === taskId)
        if (idx !== -1) tasks.value[idx] = task
        useToastStore().add('Changes saved')
        return task
    }

    async function changeStatus(projectId, taskId, status) {
        const { data } = await client.patch(`/projects/${projectId}/tasks/${taskId}/status`, { status })
        const task = tasks.value.find((t) => t.id === taskId)
        if (task) task.status = data.data.status
    }

    async function deleteTask(projectId, taskId) {
        await client.delete(`/projects/${projectId}/tasks/${taskId}`)
        tasks.value = tasks.value.filter((t) => t.id !== taskId)
        useToastStore().add('Task deleted')
    }

    function clear() {
        tasks.value = []
    }

    return { tasks, loading, byStatus, fetchTasks, fetchTask, createTask, updateTask, changeStatus, deleteTask, clear }
})
