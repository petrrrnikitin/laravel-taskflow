<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'

const props = defineProps({
    placeholder: { type: String, default: 'Search…' },
    disabled: { type: Boolean, default: false },
    search: { type: Function, required: true },
    minLength: { type: Number, default: 2 },
})

const emit = defineEmits(['select'])

const query = ref('')
const results = ref([])
const loading = ref(false)

let controller = null

watch(query, async (q) => {
    controller?.abort()
    if (q.trim().length < props.minLength) {
        results.value = []
        return
    }
    controller = new AbortController()
    loading.value = true
    try {
        results.value = await props.search(q.trim(), controller.signal)
    } catch (e) {
        if (e.code !== 'ERR_CANCELED') results.value = []
    } finally {
        loading.value = false
    }
})

onBeforeUnmount(() => controller?.abort())

function onBlur() {
    setTimeout(() => {
        results.value = []
    }, 150)
}

function select(item) {
    query.value = ''
    results.value = []
    emit('select', item)
}
</script>

<template>
    <div class="relative">
        <input
            v-model="query"
            type="text"
            :placeholder="placeholder"
            :disabled="disabled"
            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none disabled:opacity-50"
            @blur="onBlur"
        />
        <div v-if="loading" class="absolute top-1/2 right-3 -translate-y-1/2">
            <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
        </div>
        <ul
            v-if="results.length"
            class="absolute z-10 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg"
        >
            <li v-for="(item, i) in results" :key="i" @click="select(item)">
                <slot name="result" :item="item" />
            </li>
        </ul>
    </div>
</template>
