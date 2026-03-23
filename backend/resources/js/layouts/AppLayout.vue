<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { onClickOutside } from '@vueuse/core'
import { useAuthStore } from '../stores/auth'
import { useNotificationsStore } from '../stores/notifications'
import NotificationDropdown from '../components/NotificationDropdown.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const notificationsStore = useNotificationsStore()
const isLoggingOut = ref(false)

const initial = computed(() => auth.user?.name?.charAt(0).toUpperCase() ?? '?')

// Notification dropdown
const showNotifications = ref(false)
const bellRef = ref(null)

onClickOutside(bellRef, () => {
    showNotifications.value = false
})

watch(route, () => {
    showNotifications.value = false
})

async function toggleNotifications() {
    showNotifications.value = !showNotifications.value
    if (showNotifications.value) {
        await notificationsStore.fetchNotifications()
    }
}

onMounted(() => {
    notificationsStore.fetchUnreadCount().catch(() => {})
})

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
        <nav class="sticky top-0 z-10 flex items-center gap-4 border-b border-gray-200 bg-white px-6 py-3">
            <!-- Logo -->
            <RouterLink to="/projects" class="shrink-0 text-lg font-bold tracking-tight text-gray-900 select-none">
                Task<span class="text-blue-600">Flow</span>
            </RouterLink>

            <!-- Nav links -->
            <div class="hidden flex-1 items-center gap-1 sm:flex">
                <RouterLink
                    v-for="link in [
                        { to: '/projects', label: 'Projects', match: '/projects' },
                        { to: '/my-tasks', label: 'My Tasks', match: '/my-tasks' },
                    ]"
                    :key="link.to"
                    :to="link.to"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                    :class="
                        route.path === link.match || route.path.startsWith(link.match + '/')
                            ? 'bg-blue-50 text-blue-600'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                    "
                >
                    {{ link.label }}
                </RouterLink>
            </div>

            <!-- Right side -->
            <div class="ml-auto flex items-center gap-3">
                <!-- Search -->
                <RouterLink
                    to="/search"
                    class="transition-colors"
                    :class="route.path === '/search' ? 'text-blue-600' : 'text-gray-400 hover:text-gray-700'"
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

                <div ref="bellRef" class="relative">
                    <button
                        class="relative transition-colors"
                        :class="showNotifications ? 'text-blue-600' : 'text-gray-400 hover:text-gray-700'"
                        aria-label="Notifications"
                        @click="toggleNotifications"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                        <span
                            v-if="notificationsStore.unreadCount > 0"
                            class="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-blue-600 text-[10px] font-bold text-white"
                        >
                            {{ notificationsStore.unreadCount > 9 ? '9+' : notificationsStore.unreadCount }}
                        </span>
                    </button>
                    <NotificationDropdown v-if="showNotifications" />
                </div>

                <div class="h-4 w-px bg-gray-200"></div>

                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-700 select-none"
                    :title="auth.user?.name"
                >
                    {{ initial }}
                </div>
                <span class="hidden min-w-0 truncate text-sm text-gray-600 sm:inline">{{ auth.user?.name }}</span>
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
