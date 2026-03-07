<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useAuthSubmit } from '../composables/useAuthSubmit'
import AutocompleteInput from './AutocompleteInput.vue'
import client from '../http/client'

const props = defineProps({
    projectId: { type: Number, required: true },
})

const authStore = useAuthStore()
const members = ref([])
const loadingMembers = ref(false)
const loadError = ref(null)

let mountController = null

onMounted(async () => {
    mountController = new AbortController()
    loadingMembers.value = true
    try {
        const res = await client.get(`/projects/${props.projectId}/members`, {
            signal: mountController.signal,
        })
        if (!mountController.signal.aborted) members.value = res.data.data
    } catch (e) {
        if (e.code !== 'ERR_CANCELED') loadError.value = 'Failed to load members.'
    } finally {
        loadingMembers.value = false
    }
})

onBeforeUnmount(() => mountController?.abort())

const isOwner = computed(() => {
    const me = members.value.find((m) => m.id === authStore.user?.id)
    return me?.role === 'owner'
})

const memberIds = computed(() => new Set(members.value.map((m) => m.id)))

async function searchUsers(q, signal) {
    const res = await client.get('/users/search', { params: { q }, signal })
    return res.data.data
}

// Add member
const { loading: adding, error: addError, execute: executeAdd } = useAuthSubmit()

async function selectUser(user) {
    if (memberIds.value.has(user.id)) return
    await executeAdd(async (signal) => {
        await client.post(`/projects/${props.projectId}/members`, { user_id: user.id }, { signal })
        const res = await client.get(`/projects/${props.projectId}/members`, { signal })
        members.value = res.data.data
    })
}

// Remove member
const removingId = ref(null)
const removeError = ref(null)

async function removeMember(userId) {
    if (removingId.value !== null) return
    if (!confirm('Remove this member from the project?')) return
    removingId.value = userId
    removeError.value = null
    try {
        await client.delete(`/projects/${props.projectId}/members/${userId}`)
        members.value = members.value.filter((m) => m.id !== userId)
    } catch {
        removeError.value = 'Failed to remove member.'
    } finally {
        removingId.value = null
    }
}
</script>

<template>
    <div>
        <p v-if="loadError" role="alert" class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ loadError }}
        </p>

        <div v-if="loadingMembers" class="space-y-3">
            <div v-for="i in 3" :key="i" class="h-14 animate-pulse rounded-xl bg-white"></div>
        </div>

        <div v-else class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <ul class="divide-y divide-gray-100">
                <li v-for="member in members" :key="member.id" class="flex items-center gap-3 px-4 py-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600"
                    >
                        {{ member.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-800">{{ member.name }}</p>
                        <p class="truncate text-xs text-gray-400">{{ member.email }}</p>
                    </div>
                    <span
                        class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="member.role === 'owner' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'"
                    >
                        {{ member.role }}
                    </span>
                    <button
                        v-if="isOwner && member.role !== 'owner'"
                        class="shrink-0 rounded-lg border border-red-200 px-2.5 py-1 text-xs text-red-400 transition-colors hover:border-red-300 hover:bg-red-50 hover:text-red-600 disabled:opacity-50"
                        :disabled="removingId !== null"
                        @click="removeMember(member.id)"
                    >
                        {{ removingId === member.id ? '…' : 'Remove' }}
                    </button>
                </li>
            </ul>
            <p v-if="members.length === 0" class="px-4 py-8 text-center text-sm text-gray-400 italic">
                No members yet.
            </p>
        </div>

        <p v-if="removeError" role="alert" class="mt-3 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ removeError }}
        </p>

        <div v-if="isOwner" class="mt-4 rounded-xl border border-gray-200 bg-white p-4">
            <h3 class="mb-3 text-sm font-medium text-gray-700">Add member</h3>
            <AutocompleteInput
                placeholder="Search by name or email…"
                :search="searchUsers"
                :disabled="adding"
                @select="selectUser"
            >
                <template #result="{ item }">
                    <div
                        class="flex items-center gap-3 px-3 py-2 transition-colors"
                        :class="
                            memberIds.has(item.id) ? 'cursor-default opacity-50' : 'cursor-pointer hover:bg-gray-50'
                        "
                    >
                        <div
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-600"
                        >
                            {{ item.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-800">{{ item.name }}</p>
                        </div>
                        <span v-if="memberIds.has(item.id)" class="shrink-0 text-xs text-gray-400">
                            already added
                        </span>
                    </div>
                </template>
            </AutocompleteInput>
            <p v-if="addError" role="alert" class="mt-2 text-sm text-red-600">{{ addError }}</p>
        </div>
    </div>
</template>
