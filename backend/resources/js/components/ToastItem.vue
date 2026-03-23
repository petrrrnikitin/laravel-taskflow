<script setup>
import { useToastStore } from '../stores/toast'

defineProps({
    toast: { type: Object, required: true },
})

const store = useToastStore()
</script>

<template>
    <div
        class="flex items-start gap-3 rounded-xl border bg-white px-4 py-3 shadow-lg"
        :class="toast.type === 'error' ? 'border-red-200' : 'border-emerald-200'"
        role="alert"
    >
        <!-- Color strip -->
        <span
            class="mt-0.5 h-4 w-1 shrink-0 rounded-full"
            :class="toast.type === 'error' ? 'bg-red-500' : 'bg-emerald-500'"
        ></span>
        <p class="flex-1 text-sm text-gray-700">{{ toast.message }}</p>
        <button
            class="text-gray-300 transition-colors hover:text-gray-500"
            aria-label="Dismiss"
            @click="store.remove(toast.id)"
        >
            ✕
        </button>
    </div>
</template>
