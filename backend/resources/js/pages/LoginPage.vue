<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore }   from '../stores/auth'
import { useAuthSubmit }  from '../composables/useAuthSubmit'

const router = useRouter()
const auth   = useAuthStore()

const email    = ref('')
const password = ref('')

const { loading, error, execute } = useAuthSubmit()

async function submit() {
    await execute(async (signal) => {
        await auth.login(email.value, password.value, { signal })
        router.push('/dashboard')
    })
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Sign in to TaskFlow</h1>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="email">Email</label>
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        required
                        autocomplete="email"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="password">Password</label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <p v-if="error" role="alert" aria-live="polite" class="text-sm text-red-600 whitespace-pre-line">{{ error }}</p>

                <button
                    type="submit"
                    :disabled="loading"
                    :aria-busy="loading"
                    :aria-disabled="loading"
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-medium py-2 rounded-lg text-sm transition-colors"
                >
                    {{ loading ? 'Signing in…' : 'Sign in' }}
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-gray-600">
                Don't have an account?
                <router-link to="/register" class="text-blue-600 hover:underline">Register</router-link>
            </p>
        </div>
    </div>
</template>
