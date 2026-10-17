<script setup lang="ts">
import MenuView from '@/components/MenuView.vue'
import TopoView from '@/components/TopoView.vue'
</script>

<template>
  <div class="dashboard-layout">
    <MenuView />

    <main class="dashboard-main">
      <TopoView current-page="Pacientes" />
      <div class="patients-page"></div>
    </main>
  </div>
</template>

<style scoped>
.patients-page {
  min-height: calc(100vh - 72px);
  background: #fff;
}

@media (max-width: 700px) {
  .patients-page {
    min-height: calc(100vh - 62px);
  }
}
</style>
