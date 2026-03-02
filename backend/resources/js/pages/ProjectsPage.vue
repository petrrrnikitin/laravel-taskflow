<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '../layouts/AppLayout.vue'
import ProjectCard from '../components/ProjectCard.vue'
import CreateProjectModal from '../components/CreateProjectModal.vue'
import { useProjectsStore } from '../stores/projects'

const store = useProjectsStore()
const showModal = ref(false)

onMounted(() => store.fetchProjects())

const activeProjects = computed(() => store.projects.filter((p) => p.status === 'active'))
const archivedProjects = computed(() => store.projects.filter((p) => p.status === 'archived'))
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
                <button
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                    @click="showModal = true"
                >
                    <span class="text-base leading-none">+</span> New project
                </button>
            </div>

            <div v-if="store.loading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="i in 3"
                    :key="i"
                    class="h-36 animate-pulse rounded-xl border border-gray-100 bg-white p-5"
                ></div>
            </div>

            <div
                v-else-if="store.projects.length === 0"
                class="flex flex-col items-center justify-center py-24 text-center"
            >
                <div
                    class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-400"
                >
                    📋
                </div>
                <h3 class="mb-1 text-base font-semibold text-gray-700">No projects yet</h3>
                <p class="mb-4 text-sm text-gray-400">Create your first project to get started.</p>
                <button class="text-sm font-medium text-blue-600 hover:text-blue-700" @click="showModal = true">
                    Create project →
                </button>
            </div>

            <div v-else class="space-y-8">
                <section v-if="activeProjects.length">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <ProjectCard
                            v-for="project in activeProjects"
                            :key="project.id"
                            :project="project"
                            @archive="store.archiveProject(project.id)"
                            @delete="store.deleteProject(project.id)"
                        />
                    </div>
                </section>

                <section v-if="archivedProjects.length">
                    <h2 class="mb-3 text-xs font-semibold tracking-widest text-gray-400 uppercase">Archived</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <ProjectCard
                            v-for="project in archivedProjects"
                            :key="project.id"
                            :project="project"
                            @delete="store.deleteProject(project.id)"
                        />
                    </div>
                </section>
            </div>
        </div>

        <CreateProjectModal v-if="showModal" @close="showModal = false" />
    </AppLayout>
</template>
