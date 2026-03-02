import client from './client'

let isRefreshing = false
let queue = []

const flushQueue = (err, token) => queue.splice(0).forEach((p) => (err ? p.reject(err) : p.resolve(token)))

export function setupAuthInterceptors(router) {
    client.interceptors.response.use(null, async (error) => {
        const status = error.response?.status
        let pathname
        try {
            pathname = new URL(String(error.config?.url ?? ''), location.origin).pathname
        } catch {
            pathname = String(error.config?.url ?? '')
        }
        const skipRefresh = ['/auth/login', '/auth/register', '/auth/refresh'].some((path) => pathname.includes(path))
        if (status !== 401 || error.config?._retry || skipRefresh) {
            return Promise.reject(error)
        }
        if (isRefreshing) {
            return new Promise((resolve, reject) => queue.push({ resolve, reject })).then((token) => {
                error.config.headers = error.config.headers ?? {}
                error.config.headers.Authorization = `Bearer ${token}`
                return client(error.config)
            })
        }
        isRefreshing = true
        error.config._retry = true
        try {
            const { useAuthStore } = await import('../stores/auth')
            const auth = useAuthStore()
            await auth.refresh()
            flushQueue(null, auth.accessToken.value)
            error.config.headers = error.config.headers ?? {}
            error.config.headers.Authorization = `Bearer ${auth.accessToken.value}`
            return client(error.config)
        } catch (e) {
            flushQueue(e, null)
            const { useAuthStore } = await import('../stores/auth')
            useAuthStore().clearAuth()
            router.push('/login')
            return Promise.reject(e)
        } finally {
            isRefreshing = false
        }
    })
}
