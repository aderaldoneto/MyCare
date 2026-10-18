<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  currentPage: string
  patientName?: string
}>()

const isPatientForm = computed(() =>
  ['Cadastrar', 'Editar'].includes(props.currentPage)
)
</script>

<template>
  <header class="topbar">
    <nav class="breadcrumb" aria-label="Caminho de navegação">
      <router-link to="/home">MyCare</router-link>
      <span aria-hidden="true">&gt;</span>
      <template v-if="isPatientForm">
        <router-link to="/patients">Paciente</router-link>
        <template v-if="patientName">
          <span aria-hidden="true">&gt;</span>
          <span>{{ patientName }}</span>
        </template>
        <span aria-hidden="true">&gt;</span>
      </template>
      <strong>{{ currentPage }}</strong>
    </nav>
  </header>
</template>
