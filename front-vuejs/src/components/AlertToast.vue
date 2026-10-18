<script setup lang="ts">
import { onBeforeUnmount, onMounted } from 'vue'

const props = withDefaults(
  defineProps<{
    message: string
    type?: 'error' | 'success'
  }>(),
  { type: 'error' }
)

const emit = defineEmits<{ (event: 'close'): void }>()
let timeout: ReturnType<typeof setTimeout>

onMounted(() => {
  timeout = setTimeout(() => emit('close'), 5000)
})

onBeforeUnmount(() => clearTimeout(timeout))
</script>

<template>
  <div class="alert-toast" :class="`alert-toast-${props.type}`" role="alert" aria-live="assertive">
    <span class="alert-toast-icon" aria-hidden="true">{{ props.type === 'success' ? '✓' : '×' }}</span>
    <p>{{ props.message }}</p>
    <button type="button" aria-label="Fechar aviso" @click="emit('close')">×</button>
    <span class="alert-toast-progress" aria-hidden="true"></span>
  </div>
</template>
