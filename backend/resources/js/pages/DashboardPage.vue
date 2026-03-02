<script setup>
import AppLayout from '../layouts/AppLayout.vue'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ auth.user?.name }}!</h1>
            <p class="mt-2 text-gray-500">Dashboard content coming soon.</p>
        </div>
    </AppLayout>
</template>
