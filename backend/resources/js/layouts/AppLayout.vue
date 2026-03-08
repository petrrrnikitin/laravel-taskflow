<script setup>
import { ref, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth = useAuthStore()
const isLoggingOut = ref(false)

const initial = computed(() => auth.user?.name?.charAt(0).toUpperCase() ?? '?')

async function logout() {
    if (isLoggingOut.value) return
    isLoggingOut.value = true
    try {
        await auth.logout()
        router.push('/login')
    } finally {
        isLoggingOut.value = false
    }
}
</script>

<template>
    <div class="flex min-h-screen flex-col bg-slate-50">
        <nav class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-3">
            <RouterLink to="/projects" class="text-lg font-bold tracking-tight text-gray-900 select-none">
                Task<span class="text-blue-600">Flow</span>
            </RouterLink>
            <div class="flex items-center gap-3">
                <RouterLink
                    to="/search"
                    class="text-gray-400 transition-colors hover:text-gray-700"
                    aria-label="Search tasks"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"
                        />
                    </svg>
                </RouterLink>
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700 select-none"
                    :title="auth.user?.name"
                >
                    {{ initial }}
                </div>
                <span class="hidden text-sm text-gray-600 sm:inline">{{ auth.user?.name }}</span>
                <button
                    :disabled="isLoggingOut"
                    :aria-busy="isLoggingOut"
                    :aria-disabled="isLoggingOut"
                    class="text-sm text-gray-400 transition-colors hover:text-gray-700 disabled:opacity-50"
                    @click="logout"
                >
                    {{ isLoggingOut ? 'Logging out…' : 'Log out' }}
                </button>
            </div>
        </nav>
        <main class="flex-1 p-6">
            <slot></slot>
        </main>
    </div>
</template>
