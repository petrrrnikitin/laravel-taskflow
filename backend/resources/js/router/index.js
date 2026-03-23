import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: '/projects' },
        { path: '/login', component: () => import('../pages/LoginPage.vue'), meta: { guest: true } },
        { path: '/register', component: () => import('../pages/RegisterPage.vue'), meta: { guest: true } },
        { path: '/projects', component: () => import('../pages/ProjectsPage.vue'), meta: { requiresAuth: true } },
        { path: '/my-tasks', component: () => import('../pages/MyTasksPage.vue'), meta: { requiresAuth: true } },
        { path: '/search', component: () => import('../pages/SearchPage.vue'), meta: { requiresAuth: true } },
        {
            path: '/projects/:projectId',
            component: () => import('../pages/ProjectDetailPage.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/projects/:projectId/tasks/:taskId',
            component: () => import('../pages/TaskDetailPage.vue'),
            meta: { requiresAuth: true },
        },
        { path: '/:pathMatch(.*)*', component: () => import('../pages/NotFoundPage.vue') },
    ],
})

router.beforeEach((to) => {
    const auth = useAuthStore()
    if (to.meta.requiresAuth && !auth.isAuthenticated) return '/login'
    if (to.meta.guest && auth.isAuthenticated) return '/projects'
})

export default router
