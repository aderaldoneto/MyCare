<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router/composables'
import MenuView from '@/components/MenuView.vue'
import TopoView from '@/components/TopoView.vue'
import { fetchPatient, type PatientDetails } from '@/services/patients'

const route = useRoute()
const patientId = computed(() => route.params.id || '')
const patient = ref<PatientDetails | null>(null)
const isLoading = ref(false)
const errorMessage = ref('')

async function loadPatient(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''
  patient.value = null

  try {
    patient.value = await fetchPatient(patientId.value)
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Não foi possível carregar os dados do paciente.'
  } finally {
    isLoading.value = false
  }
}

function formatBirthDate(date: string | null): string {
  if (!date) {
    return '—'
  }

  const [year, month, day] = date.split('-')
  return `${day}/${month}/${year}`
}

function sexLabel(sex: PatientDetails['sex']): string {
  if (sex === 'f') {
    return 'Feminino'
  }

  if (sex === 'm') {
    return 'Masculino'
  }

  return '—'
}

onMounted(() => {
  void loadPatient()
})

watch(patientId, () => {
  void loadPatient()
})
</script>

<template>
  <div class="dashboard-layout">
    <MenuView />

    <main class="dashboard-main">
      <TopoView
        current-page="Detalhes"
        section="Paciente"
        section-to="/patients"
        :item-name="patient ? patient.name : undefined"
      />

      <div class="patients-page">
        <div class="patients-page-heading">
          <div>
            <h1>Detalhes do paciente</h1>
          </div>
          <router-link v-if="patient" class="primary-button" :to="`/patients/${patient.id}/edit`">
            Editar paciente
          </router-link>
        </div>

        <p v-if="isLoading" class="patient-details-status" role="status">
          Carregando dados do paciente...
        </p>
        <section v-else-if="patient" class="patient-details-card" aria-label="Informações do paciente">
          <header class="patient-details-header">
            <span class="patient-details-avatar" aria-hidden="true">
              {{ patient.name.charAt(0).toUpperCase() }}
            </span>
            <div>
              <h2>{{ patient.name }}</h2>
            </div>
          </header>

          <dl class="patient-details-grid">
            <div class="patient-details-field">
              <dt>CPF</dt>
              <dd>{{ patient.cpf || '—' }}</dd>
            </div>
            <div class="patient-details-field">
              <dt>RG</dt>
              <dd>{{ patient.rg || '—' }}</dd>
            </div>
            <div class="patient-details-field">
              <dt>Data de nascimento</dt>
              <dd>{{ formatBirthDate(patient.birth_date) }}</dd>
            </div>
            <div class="patient-details-field">
              <dt>Sexo</dt>
              <dd>{{ sexLabel(patient.sex) }}</dd>
            </div>
            <div class="patient-details-field">
              <dt>Celular</dt>
              <dd>{{ patient.phone || '—' }}</dd>
            </div>
            <div class="patient-details-field">
              <dt>E-mail</dt>
              <dd>{{ patient.email || '—' }}</dd>
            </div>
            <div class="patient-details-field patient-details-field-wide">
              <dt>Descrição</dt>
              <dd class="patient-details-description">{{ patient.description || '—' }}</dd>
            </div>
          </dl>
        </section>

        <section v-else-if="errorMessage" class="patient-details-error" role="alert">
          <p>{{ errorMessage }}</p>
          <router-link class="patient-cancel-button" to="/patients">Voltar para pacientes</router-link>
        </section>
      </div>
    </main>
  </div>
</template>
