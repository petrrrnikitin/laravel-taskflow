<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    initial: { type: Object, default: () => ({}) },
    members: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    error: { type: String, default: null },
    submitLabel: { type: String, default: 'Save' },
})

const emit = defineEmits(['submit', 'cancel'])

const form = ref({
    title: '',
    description: '',
    priority: 'medium',
    due_date: '',
    assignee_id: '',
})

watch(
    () => props.initial,
    (val) => {
        if (val && Object.keys(val).length) {
            form.value = {
                title: val.title ?? '',
                description: val.description ?? '',
                priority: val.priority ?? 'medium',
                due_date: val.due_date ?? '',
                assignee_id: val.assignee_id ?? '',
            }
        }
    },
    { immediate: true },
)

function submit() {
    emit('submit', {
        title: form.value.title,
        description: form.value.description || null,
        priority: form.value.priority,
        due_date: form.value.due_date || null,
        assignee_id: form.value.assignee_id ? Number(form.value.assignee_id) : null,
    })
}
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="tf-title">Title</label>
            <input
                id="tf-title"
                v-model="form.title"
                type="text"
                required
                autofocus
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
            />
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="tf-desc">
                Description <span class="font-normal text-gray-400">(optional)</span>
            </label>
            <textarea
                id="tf-desc"
                v-model="form.description"
                rows="4"
                class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
            ></textarea>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700" for="tf-priority">Priority</label>
                <select
                    id="tf-priority"
                    v-model="form.priority"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700" for="tf-due">
                    Due date <span class="font-normal text-gray-400">(optional)</span>
                </label>
                <input
                    id="tf-due"
                    v-model="form.due_date"
                    type="date"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700" for="tf-assignee">Assignee</label>
            <select
                id="tf-assignee"
                v-model="form.assignee_id"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
                <option value="">Unassigned</option>
                <option v-for="member in members" :key="member.id" :value="member.id">{{ member.name }}</option>
            </select>
        </div>

        <p v-if="error" role="alert" aria-live="polite" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">
            {{ error }}
        </p>

        <div class="flex justify-end gap-3 pt-1">
            <button
                type="button"
                class="px-4 py-2 text-sm text-gray-500 transition-colors hover:text-gray-700"
                @click="emit('cancel')"
            >
                Cancel
            </button>
            <button
                type="submit"
                :disabled="loading"
                :aria-busy="loading"
                :aria-disabled="loading"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white transition-colors hover:bg-blue-700 disabled:opacity-50"
            >
                {{ loading ? 'Saving…' : submitLabel }}
            </button>
        </div>
    </form>
</template>
