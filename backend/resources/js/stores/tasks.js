import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import client from '../http/client'

export const useTasksStore = defineStore('tasks', () => {
    const tasks = ref([])
    const loading = ref(false)

    const byStatus = computed(() => ({
        todo: tasks.value.filter((t) => t.status === 'todo'),
        in_progress: tasks.value.filter((t) => t.status === 'in_progress'),
        done: tasks.value.filter((t) => t.status === 'done'),
    }))

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
        return data.data
    }

    async function updateTask(projectId, taskId, payload) {
        const { data } = await client.put(`/projects/${projectId}/tasks/${taskId}`, payload)
        const task = data.data
        const idx = tasks.value.findIndex((t) => t.id === taskId)
        if (idx !== -1) tasks.value[idx] = task
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
    }

    function clear() {
        tasks.value = []
    }

    return { tasks, loading, byStatus, fetchTasks, fetchTask, createTask, updateTask, changeStatus, deleteTask, clear }
})
