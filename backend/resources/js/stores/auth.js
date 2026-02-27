import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import client from '../http/client'

export const useAuthStore = defineStore('auth', () => {
    const user        = ref(null)
    const accessToken = ref(null)
    const isAuthenticated = computed(() => !!accessToken.value)

    function setAuth({ token, user: u }) {
        accessToken.value = token
        user.value = u
        client.defaults.headers.common['Authorization'] = `Bearer ${token}`
    }

    function clearAuth() {
        accessToken.value = null
        user.value = null
        delete client.defaults.headers.common['Authorization']
    }

    async function refresh() {
        const csrf = document.cookie.match(/(?:^|;\s*)refresh_csrf=([^;]+)/)?.[1]
        const { data } = await client.post('/auth/refresh', null, {
            headers: { 'X-Refresh-CSRF': decodeURIComponent(csrf ?? '') },
        })
        setAuth(data)
    }

    async function boot() {
        try { await refresh() } catch { clearAuth() }
    }

    async function login(email, password, { signal } = {}) {
        const { data } = await client.post('/auth/login', { email, password }, { signal })
        setAuth(data)
    }

    async function register(name, email, password, passwordConfirmation, { signal } = {}) {
        const { data } = await client.post('/auth/register', {
            name, email, password, password_confirmation: passwordConfirmation,
        }, { signal })
        setAuth(data)
    }

    async function logout() {
        try { await client.post('/auth/logout') } catch {}
        clearAuth()
    }

    return { user, accessToken, isAuthenticated, boot, login, register, logout, refresh, clearAuth }
})
