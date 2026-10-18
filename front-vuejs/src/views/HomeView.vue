<script setup lang="ts">
import { onMounted, ref } from 'vue'
import MenuView from '@/components/MenuView.vue'
import TopoView from '@/components/TopoView.vue'
import { getAuthenticatedUser } from '@/services/auth'
import { fetchPatientCount } from '@/services/patients'
import { fetchUserCount } from '@/services/users'

const user = getAuthenticatedUser()
const patientCount = ref<number | null>(null)
const patientCountError = ref('')
const userCount = ref<number | null>(null)
const userCountError = ref('')
const today = new Intl.DateTimeFormat('pt-BR', {
  weekday: 'long',
  day: 'numeric',
  month: 'long'
}).format(new Date())

onMounted(() => {
  void loadCounts()
})

async function loadCounts(): Promise<void> {
  try {
    patientCount.value = await fetchPatientCount()
  } catch (error) {
    patientCountError.value =
      error instanceof Error ? error.message : 'Não foi possível carregar o total de pacientes.'
  }

  try {
    userCount.value = await fetchUserCount()
  } catch (error) {
    userCountError.value =
      error instanceof Error ? error.message : 'Não foi possível carregar o total de usuários.'
  }
}
</script>

<template>
  <div class="dashboard-layout">
    <MenuView />

    <main class="dashboard-main">
      <TopoView current-page="Visão geral" />

      <div class="dashboard-content">
        <section class="welcome-row">
          <div>
            <span class="eyebrow">{{ today.toUpperCase() }}</span>
            <h1>Olá, {{ user?.name.split(' ')[0] }}</h1>
            <p>Aqui está um resumo para você.</p>
          </div>
        </section>

        <section class="summary-grid" aria-label="Resumo">
          <article class="summary-card">
            <div class="summary-card-top">
              <span class="summary-icon patients-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.5" /><path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20H3Zm13-12a3.5 3.5 0 0 1 0 6.8m2 1.2a4 4 0 0 1 3 3.9v.1h-3" /></svg>
              </span>
            </div>
            <span class="summary-label">Total de pacientes</span>
            <strong v-if="patientCount !== null" class="summary-value">{{ patientCount }}</strong>
            <span v-else-if="patientCountError" class="summary-error" role="alert">
              {{ patientCountError }}
            </span>
            <strong v-else class="summary-value" aria-label="Carregando">...</strong>
          </article>
          <article class="summary-card">
            <div class="summary-card-top">
              <span class="summary-icon patients-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.5" /><path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20H3Zm13-12a3.5 3.5 0 0 1 0 6.8m2 1.2a4 4 0 0 1 3 3.9v.1h-3" /></svg>
              </span>
            </div>
            <span class="summary-label">Total de usuários</span>
            <strong v-if="userCount !== null" class="summary-value">{{ userCount }}</strong>
            <span v-else-if="userCountError" class="summary-error" role="alert">
              {{ userCountError }}
            </span>
            <strong v-else class="summary-value" aria-label="Carregando">...</strong>
          </article>
        </section>

      </div>
    </main>
  </div>
</template>
