import { ref } from 'vue'

export function useApiError() {
    const error = ref(null)

    function setError(e) {
        if (e?.name === 'CanceledError' || e?.code === 'ERR_CANCELED') return
        const data = e?.response?.data
        const fieldErrors = data?.errors
        if (fieldErrors && typeof fieldErrors === 'object') {
            error.value = Object.values(fieldErrors).flat().join('\n')
            return
        }
        error.value =
            (typeof data?.message === 'string' ? data.message : null) ?? e?.message ?? 'An unexpected error occurred'
    }

    function clearError() {
        error.value = null
    }

    return { error, setError, clearError }
}
