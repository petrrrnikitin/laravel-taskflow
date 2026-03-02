<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useAuthSubmit } from '../composables/useAuthSubmit'

const router = useRouter()
const auth = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')

const { loading, error, execute } = useAuthSubmit()

async function submit() {
    await execute(async (signal) => {
        await auth.register(name.value, email.value, password.value, passwordConfirmation.value, { signal })
        router.replace('/projects')
    })
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-50 px-4">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <span class="text-3xl font-bold tracking-tight text-gray-900"
                    >Task<span class="text-blue-600">Flow</span></span
                >
                <p class="mt-1 text-sm text-gray-500">Create your account</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700" for="name">Name</label>
                        <input
                            id="name"
                            v-model="name"
                            type="text"
                            required
                            autocomplete="name"
                            placeholder="Your name"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm placeholder:text-gray-400 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700" for="email">Email</label>
                        <input
                            id="email"
                            v-model="email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm placeholder:text-gray-400 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password">Password</label>
                        <input
                            id="password"
                            v-model="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm placeholder:text-gray-400 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700" for="password_confirmation"
                            >Confirm password</label
                        >
                        <input
                            id="password_confirmation"
                            v-model="passwordConfirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="••••••••"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm placeholder:text-gray-400 focus:border-transparent focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                    </div>

                    <p
                        v-if="error"
                        role="alert"
                        aria-live="polite"
                        class="rounded-lg bg-red-50 px-3 py-2 text-sm whitespace-pre-line text-red-600"
                    >
                        {{ error }}
                    </p>

                    <button
                        type="submit"
                        :disabled="loading"
                        :aria-busy="loading"
                        :aria-disabled="loading"
                        class="mt-2 w-full rounded-lg bg-blue-600 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ loading ? 'Creating account…' : 'Create account' }}
                    </button>
                </form>

                <p class="mt-5 text-center text-sm text-gray-500">
                    Already have an account?
                    <router-link to="/login" class="font-medium text-blue-600 hover:text-blue-700">Sign in</router-link>
                </p>
            </div>
        </div>
    </div>
</template>
