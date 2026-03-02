import { ref } from 'vue'
import { defineStore } from 'pinia'
import client from '../http/client'

export const useProjectsStore = defineStore('projects', () => {
    const projects = ref([])
    const loading = ref(false)

    async function fetchProjects() {
        loading.value = true
        try {
            const { data } = await client.get('/projects')
            projects.value = data.data
        } finally {
            loading.value = false
        }
    }

    async function createProject(payload) {
        const { data } = await client.post('/projects', payload)
        projects.value.unshift(data.data)
        return data.data
    }

    async function archiveProject(id) {
        await client.post(`/projects/${id}/archive`)
        const project = projects.value.find((p) => p.id === id)
        if (project) project.status = 'archived'
    }

    async function deleteProject(id) {
        await client.delete(`/projects/${id}`)
        projects.value = projects.value.filter((p) => p.id !== id)
    }

    async function fetchProject(id) {
        const { data } = await client.get(`/projects/${id}`)
        const existing = projects.value.find((p) => p.id === id)
        if (existing) Object.assign(existing, data.data)
        else projects.value.push(data.data)
        return data.data
    }

    function findProject(id) {
        return projects.value.find((p) => p.id === id) ?? null
    }

    return { projects, loading, fetchProjects, createProject, archiveProject, deleteProject, fetchProject, findProject }
})
