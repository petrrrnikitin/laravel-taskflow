import { ref, onBeforeUnmount } from 'vue'
import { useApiError } from './useApiError'

export function useAuthSubmit() {
    const loading = ref(false)
    const { error, setError, clearError } = useApiError()
    let controller = null

    onBeforeUnmount(() => controller?.abort())

    async function execute(fn) {
        if (loading.value) return
        clearError()
        loading.value = true
        controller = new AbortController()
        try {
            await fn(controller.signal)
        } catch (e) {
            setError(e)
        } finally {
            loading.value = false
        }
    }

    return { loading, error, execute }
}
