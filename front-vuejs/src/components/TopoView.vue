<script setup lang="ts">
defineProps<{
  currentPage: string
}>()
</script>

<template>
  <header class="topbar">
    <div class="breadcrumb">Início <span>/</span> <strong>{{ currentPage }}</strong></div>
  </header>
</template>
