import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './stores/auth'
import { setupAuthInterceptors } from './http/interceptors'

const pinia = createPinia()
const app = createApp(App)
app.use(pinia)

setupAuthInterceptors(router)

useAuthStore()
    .boot()
    .finally(() => {
        app.use(router)
        app.mount('#app')
    })
