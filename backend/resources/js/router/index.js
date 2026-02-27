import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: '/dashboard' },
        { path: '/login',     component: () => import('../pages/LoginPage.vue'),    meta: { guest: true } },
        { path: '/register',  component: () => import('../pages/RegisterPage.vue'), meta: { guest: true } },
        { path: '/dashboard', component: () => import('../pages/DashboardPage.vue'), meta: { requiresAuth: true } },
        { path: '/:pathMatch(.*)*', component: () => import('../pages/NotFoundPage.vue') },
    ],
})

router.beforeEach((to) => {
    const auth = useAuthStore()
    if (to.meta.requiresAuth && !auth.isAuthenticated) return '/login'
    if (to.meta.guest        && auth.isAuthenticated)  return '/dashboard'
})

export default router
