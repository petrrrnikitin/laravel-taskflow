<script setup>
import { RouterLink } from 'vue-router'

const props = defineProps({
    project: { type: Object, required: true },
})

const emit = defineEmits(['archive', 'delete'])

const isActive = props.project.status === 'active'

function handleDelete() {
    if (!confirm('Delete this project? This cannot be undone.')) return
    emit('delete')
}
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-5"
        :class="isActive ? 'transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md' : 'opacity-50'"
    >
        <div class="flex items-start justify-between gap-2">
            <h3 class="leading-snug font-semibold" :class="isActive ? 'text-gray-900' : 'text-gray-700'">
                <RouterLink
                    v-if="isActive"
                    :to="`/projects/${project.id}`"
                    class="transition-colors hover:text-blue-600"
                    >{{ project.name }}</RouterLink
                >
                <span v-else>{{ project.name }}</span>
            </h3>
            <span
                class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium"
                :class="isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                >{{ project.status }}</span
            >
        </div>

        <p v-if="project.description" class="line-clamp-2 text-sm leading-relaxed text-gray-500">
            {{ project.description }}
        </p>

        <p v-if="isActive && project.owner" class="text-xs text-gray-400">{{ project.owner.name }}</p>

        <div class="mt-auto flex gap-3 border-t border-gray-100 pt-3">
            <button
                v-if="isActive"
                class="text-xs text-gray-400 transition-colors hover:text-gray-700"
                @click="emit('archive')"
            >
                Archive
            </button>
            <button class="ml-auto text-xs text-red-400 transition-colors hover:text-red-600" @click="handleDelete">
                Delete
            </button>
        </div>
    </div>
</template>
