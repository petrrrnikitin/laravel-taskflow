<script setup>
import { useToastStore } from '../stores/toast'
import ToastItem from './ToastItem.vue'

const store = useToastStore()
</script>

<template>
    <Teleport to="body">
        <div class="fixed right-6 bottom-6 z-[100] flex flex-col gap-2">
            <TransitionGroup name="toast">
                <ToastItem v-for="t in store.toasts" :key="t.id" :toast="t" />
            </TransitionGroup>
        </div>
    </Teleport>
</template>
