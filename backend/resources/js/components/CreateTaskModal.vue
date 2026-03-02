<script setup>
import { useTasksStore } from '../stores/tasks'
import { useAuthSubmit } from '../composables/useAuthSubmit'
import TaskForm from './TaskForm.vue'

const props = defineProps({
    projectId: { type: Number, required: true },
    members: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

const store = useTasksStore()
const { loading, error, execute } = useAuthSubmit()

async function handleSubmit(payload) {
    await execute(async () => {
        await store.createTask(props.projectId, payload)
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
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-lg font-semibold text-gray-900">New task</h2>
                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                            Todo
                        </span>
                    </div>
                    <button
                        class="text-gray-400 transition-colors hover:text-gray-600"
                        aria-label="Close"
                        @click="emit('close')"
                    >
                        ✕
                    </button>
                </div>
                <TaskForm
                    :members="members"
                    :loading="loading"
                    :error="error"
                    submit-label="Create task"
                    @submit="handleSubmit"
                    @cancel="emit('close')"
                />
            </div>
        </div>
    </Teleport>
</template>
