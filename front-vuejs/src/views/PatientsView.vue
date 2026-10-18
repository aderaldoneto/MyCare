<script setup lang="ts">
import { onMounted, ref } from 'vue'
import MenuView from '@/components/MenuView.vue'
import TopoView from '@/components/TopoView.vue'
import AlertToast from '@/components/AlertToast.vue'
import { fetchPatients, type Patient } from '@/services/patients'

const patients = ref<Patient[]>([])
const searchInput = ref('')
const search = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref(typeof window !== 'undefined' ? String(window.location.search ? new URLSearchParams(window.location.search).get('success') || '' : '') : '')

async function loadPatients(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const result = await fetchPatients(search.value, currentPage.value)
    patients.value = result.data
    currentPage.value = result.current_page
    lastPage.value = result.last_page
    total.value = result.total
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Não foi possível carregar os pacientes.'
  } finally {
    isLoading.value = false
  }
}

function handleSearch(): void {
  search.value = searchInput.value.trim()
  currentPage.value = 1
  void loadPatients()
}

function changePage(page: number): void {
  if (page < 1 || page > lastPage.value || page === currentPage.value) {
    return
  }

  currentPage.value = page
  void loadPatients()
}

function formatBirthDate(date: string | null): string {
  if (!date) {
    return '—'
  }

  const [year, month, day] = date.split('-')
  return `${day}/${month}/${year}`
}

onMounted(() => {
  void loadPatients()
})
</script>

<template>
  <div class="dashboard-layout">
    <MenuView />

    <main class="dashboard-main">
      <TopoView current-page="Pacientes" />
      <AlertToast v-if="successMessage" type="success" :message="successMessage" @close="successMessage = ''" />

      <div class="patients-page">
        <div class="patients-page-heading">
          <div>
            <h1>Pacientes</h1>
            <p>Consulte e encontre seus pacientes.</p>
          </div>
          <router-link class="primary-button" to="/patients/create">
            <span aria-hidden="true">+</span> Cadastrar paciente
          </router-link>
        </div>

        <section class="patient-table-card" aria-label="Lista de pacientes">
          <form class="patient-search" role="search" @submit.prevent="handleSearch">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="10.8" cy="10.8" r="6.8" />
              <path d="m16 16 4.5 4.5" />
            </svg>
            <input
              v-model="searchInput"
              type="search"
              aria-label="Buscar paciente"
              placeholder="Buscar por nome do paciente ou CPF"
            />
            <button type="submit">Buscar</button>
          </form>

          <p v-if="errorMessage" class="patients-error" role="alert">{{ errorMessage }}</p>

          <div class="patient-table-scroll">
            <table class="patient-table">
              <thead>
                <tr>
                  <th scope="col">Nome do paciente</th>
                  <th scope="col">CPF</th>
                  <th scope="col">Celular</th>
                  <th scope="col">Data de nascimento</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="patient in patients" :key="patient.id">
                  <td>{{ patient.name }}</td>
                  <td>{{ patient.cpf }}</td>
                  <td>{{ patient.phone || '—' }}</td>
                  <td>{{ formatBirthDate(patient.birth_date) }}</td>
                  <td>
                    <router-link :to="`/patients/${patient.id}`" aria-label="Ver detalhes do paciente">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye preview-icon">
                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                    </router-link>
                  </td>
                </tr>
                <tr v-if="isLoading">
                  <td class="patient-table-message" colspan="4">Carregando pacientes...</td>
                </tr>
                <tr v-else-if="!errorMessage && patients.length === 0">
                  <td class="patient-table-message" colspan="4">
                    {{ search ? 'Nenhum paciente encontrado.' : 'Nenhum paciente cadastrado.' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <footer v-if="total > 0" class="patient-pagination">
            <span>{{ total }} {{ total === 1 ? 'paciente' : 'pacientes' }}</span>
            <div class="patient-pagination-controls">
              <button
                type="button"
                aria-label="Página anterior"
                :disabled="currentPage <= 1 || isLoading"
                @click="changePage(currentPage - 1)"
              >
                ‹
              </button>
              <span>Página {{ currentPage }} de {{ lastPage }}</span>
              <button
                type="button"
                aria-label="Próxima página"
                :disabled="currentPage >= lastPage || isLoading"
                @click="changePage(currentPage + 1)"
              >
                ›
              </button>
            </div>
          </footer>
        </section>
      </div>
    </main>
  </div>
</template>
