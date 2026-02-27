<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router       = useRouter()
const auth         = useAuthStore()
const isLoggingOut = ref(false)

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
    <div class="min-h-screen flex flex-col">
        <nav class="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
            <span class="text-lg font-semibold text-gray-900">TaskFlow</span>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ auth.user?.name }}</span>
                <button
                    :disabled="isLoggingOut"
                    :aria-busy="isLoggingOut"
                    :aria-disabled="isLoggingOut"
                    class="text-sm text-red-600 hover:text-red-800 disabled:opacity-50 transition-colors"
                    @click="logout"
                >
                    {{ isLoggingOut ? 'Logging out…' : 'Logout' }}
                </button>
            </div>
        </nav>
        <main class="flex-1 p-6">
            <slot></slot>
        </main>
    </div>
</template>
