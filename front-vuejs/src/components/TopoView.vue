<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  currentPage: string
  patientName?: string
  section?: string
  sectionTo?: string
  itemName?: string
}>()

const isPatientForm = computed(() =>
  ['Cadastrar', 'Editar'].includes(props.currentPage)
)
const parentSection = computed(() => props.section || (isPatientForm.value ? 'Paciente' : ''))
const parentPath = computed(() => props.sectionTo || (isPatientForm.value ? '/patients' : ''))
const breadcrumbItem = computed(() => props.itemName || props.patientName)
</script>

<template>
  <header class="topbar">
    <nav class="breadcrumb" aria-label="Caminho de navegação">
      <router-link to="/home">MyCare</router-link>
      <span aria-hidden="true">&gt;</span>
      <template v-if="parentSection">
        <router-link :to="parentPath">{{ parentSection }}</router-link>
        <template v-if="breadcrumbItem">
          <span aria-hidden="true">&gt;</span>
          <span>{{ breadcrumbItem }}</span>
        </template>
        <span aria-hidden="true">&gt;</span>
      </template>
      <strong>{{ currentPage }}</strong>
    </nav>
  </header>
</template>
