<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore }   from '../stores/auth'
import { useAuthSubmit }  from '../composables/useAuthSubmit'

const router = useRouter()
const auth   = useAuthStore()

const name                 = ref('')
const email                = ref('')
const password             = ref('')
const passwordConfirmation = ref('')

const { loading, error, execute } = useAuthSubmit()

async function submit() {
    await execute(async (signal) => {
        await auth.register(name.value, email.value, password.value, passwordConfirmation.value, { signal })
        router.push('/dashboard')
    })
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Create your account</h1>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Name</label>
                    <input
                        id="name"
                        v-model="name"
                        type="text"
                        required
                        autocomplete="name"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

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
                        autocomplete="new-password"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="password_confirmation">Confirm password</label>
                    <input
                        id="password_confirmation"
                        v-model="passwordConfirmation"
                        type="password"
                        required
                        autocomplete="new-password"
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
                    {{ loading ? 'Creating account…' : 'Create account' }}
                </button>
            </form>

            <p class="mt-4 text-center text-sm text-gray-600">
                Already have an account?
                <router-link to="/login" class="text-blue-600 hover:underline">Sign in</router-link>
            </p>
        </div>
    </div>
</template>
