<script setup>
import { ref } from 'vue'
import { useProjectsStore } from '../stores/projects'
import { useAuthSubmit } from '../composables/useAuthSubmit'

const emit = defineEmits(['close'])

const store = useProjectsStore()
const name = ref('')
const description = ref('')

const { loading, error, execute } = useAuthSubmit()

async function submit() {
    await execute(async () => {
        await store.createProject({
            name: name.value,
            description: description.value || null,
        })
        emit('close')
    })
}
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" @click="emit('close')"></div>

            <div class="relative mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">New project</h2>
                    <button
                        class="text-gray-400 transition-colors hover:text-gray-600"
                        aria-label="Close"
                        @click="emit('close')"
                    >
                        ✕
                    </button>
                </div>

                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700" for="proj-name">Name</label>
                        <input
                            id="proj-name"
                            v-model="name"
                            type="text"
                            required
                            autofocus
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700" for="proj-desc">
                            Description
                            <span class="text-gray-400">(optional)</span>
                        </label>
                        <textarea
                            id="proj-desc"
                            v-model="description"
                            rows="3"
                            class="w-full resize-none rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <p v-if="error" role="alert" aria-live="polite" class="text-sm text-red-600">{{ error }}</p>

                    <div class="flex justify-end gap-3 pt-1">
                        <button
                            type="button"
                            class="px-4 py-2 text-sm text-gray-600 transition-colors hover:text-gray-900"
                            @click="emit('close')"
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
                            {{ loading ? 'Creating…' : 'Create project' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
