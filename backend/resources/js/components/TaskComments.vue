<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useAuthSubmit } from '../composables/useAuthSubmit'
import client from '../http/client'

const props = defineProps({
    projectId: { type: Number, required: true },
    taskId: { type: Number, required: true },
})

const emit = defineEmits(['update:count'])

const comments = ref([])
const fetchError = ref(null)
const newComment = ref('')
const { loading, error, execute } = useAuthSubmit()
const deletingId = ref(null)
const deleteError = ref(null)

let abortController = null

onMounted(async () => {
    abortController = new AbortController()
    try {
        const { data } = await client.get(`/projects/${props.projectId}/tasks/${props.taskId}/comments`, {
            signal: abortController.signal,
        })
        comments.value = data.data
        emit('update:count', comments.value.length)
    } catch (e) {
        if (e.code !== 'ERR_CANCELED') fetchError.value = 'Failed to load comments.'
    }
})

onBeforeUnmount(() => abortController?.abort())

async function addComment() {
    if (!newComment.value.trim()) return
    await execute(async () => {
        const { data } = await client.post(`/projects/${props.projectId}/tasks/${props.taskId}/comments`, {
            body: newComment.value.trim(),
        })
        comments.value.push(data.data)
        newComment.value = ''
        emit('update:count', comments.value.length)
    })
}

async function deleteComment(id) {
    if (deletingId.value !== null) return
    if (!confirm('Delete this comment?')) return
    deletingId.value = id
    deleteError.value = null
    try {
        await client.delete(`/projects/${props.projectId}/tasks/${props.taskId}/comments/${id}`)
        comments.value = comments.value.filter((c) => c.id !== id)
        emit('update:count', comments.value.length)
    } catch {
        deleteError.value = 'Failed to delete comment.'
    } finally {
        deletingId.value = null
    }
}

function fmtDate(iso) {
    if (!iso) return '—'
    return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function initial(name) {
    return name?.charAt(0).toUpperCase() ?? '?'
}
</script>

<template>
    <div class="space-y-4">
        <p v-if="fetchError" role="alert" class="text-sm text-red-500">{{ fetchError }}</p>
        <div v-if="comments.length" class="space-y-3">
            <div v-for="c in comments" :key="c.id" class="flex gap-3">
                <div
                    class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm font-semibold text-violet-700"
                >
                    {{ initial(c.author?.name) }}
                </div>
                <div class="flex-1 rounded-xl rounded-tl-sm border border-gray-200 bg-white px-4 py-3">
                    <div class="mb-1.5 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-700">{{ c.author?.name }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400">{{ fmtDate(c.created_at) }}</span>
                            <button
                                class="text-xs text-gray-300 transition-colors hover:text-red-400 disabled:cursor-not-allowed"
                                :disabled="deletingId !== null"
                                @click="deleteComment(c.id)"
                            >
                                {{ deletingId === c.id ? '…' : '✕' }}
                            </button>
                        </div>
                    </div>
                    <p class="text-sm leading-relaxed whitespace-pre-line text-gray-700">{{ c.body }}</p>
                </div>
            </div>
        </div>
        <p v-else-if="!fetchError" class="text-sm text-gray-400 italic">No comments yet.</p>
        <p v-if="deleteError" role="alert" class="text-xs text-red-500">{{ deleteError }}</p>

        <form class="mt-3 flex gap-3" @submit.prevent="addComment">
            <div class="flex-1">
                <textarea
                    v-model="newComment"
                    rows="2"
                    placeholder="Write a comment…"
                    class="w-full resize-none rounded-xl border border-gray-300 px-3 py-2.5 text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                ></textarea>
                <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
            </div>
            <button
                type="submit"
                :disabled="loading || !newComment.trim()"
                class="shrink-0 self-start rounded-xl bg-blue-600 px-3 py-2.5 text-sm text-white transition-colors hover:bg-blue-700 disabled:opacity-50"
            >
                Send
            </button>
        </form>
    </div>
</template>
