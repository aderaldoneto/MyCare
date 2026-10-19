<script setup lang="ts">
import { onMounted, ref } from 'vue'
import MenuView from '@/components/MenuView.vue'
import TopoView from '@/components/TopoView.vue'
import AlertToast from '@/components/AlertToast.vue'
import { fetchUsers, type User, USER_ROLES } from '@/services/users'

const users = ref<User[]>([])
const searchInput = ref('')
const search = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref(
  typeof window !== 'undefined' ? new URLSearchParams(window.location.search).get('success') || '' : ''
)

async function loadUsers(): Promise<void> {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const result = await fetchUsers(search.value, currentPage.value)
    users.value = result.data
    currentPage.value = result.current_page
    lastPage.value = result.last_page
    total.value = result.total
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Não foi possível carregar os usuários.'
  } finally {
    isLoading.value = false
  }
}

function handleSearch(): void {
  search.value = searchInput.value.trim()
  currentPage.value = 1
  void loadUsers()
}

function changePage(page: number): void {
  if (page < 1 || page > lastPage.value || page === currentPage.value) {
    return
  }
  currentPage.value = page
  void loadUsers()
}

function roleLabel(role: string): string {
  return USER_ROLES.find((item) => item.value === role)?.label || role
}

onMounted(() => {
  void loadUsers()
})
</script>

<template>
  <div class="dashboard-layout">
    <MenuView />

    <main class="dashboard-main">
      <TopoView current-page="Usuários" />
      <AlertToast
        v-if="successMessage"
        type="success"
        :message="successMessage"
        @close="successMessage = ''"
      />

      <div class="patients-page">
        <div class="patients-page-heading">
          <div><h1>Usuários</h1></div>
          <router-link class="primary-button" to="/users/create">
            <span aria-hidden="true">+</span> Cadastrar usuário
          </router-link>
        </div>

        <section class="patient-table-card" aria-label="Lista de usuários">
          <form class="patient-search" role="search" @submit.prevent="handleSearch">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="10.8" cy="10.8" r="6.8" />
              <path d="m16 16 4.5 4.5" />
            </svg>
            <input
              v-model="searchInput"
              type="search"
              aria-label="Buscar usuário"
              placeholder="Buscar por nome ou e-mail"
            />
            <button type="submit">Buscar</button>
          </form>

          <AlertToast
            v-if="errorMessage"
            :message="errorMessage"
            @close="errorMessage = ''"
          />

          <div class="patient-table-scroll">
            <table class="patient-table">
              <thead>
                <tr>
                  <th scope="col">Nome</th>
                  <th scope="col">E-mail</th>
                  <th scope="col">Perfil</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id">
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>{{ roleLabel(user.role) }}</td>
                  <td>
                    <router-link :to="`/users/${user.id}`" aria-label="Editar usuário">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pen preview-icon">
                          <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a.5.5 0 0 0 .83-.497z"/>
                        </svg>
                    </router-link>
                  </td>
                </tr>
                <tr v-if="isLoading">
                  <td class="patient-table-message" colspan="4">Carregando usuários...</td>
                </tr>
                <tr v-else-if="!errorMessage && users.length === 0">
                  <td class="patient-table-message" colspan="4">
                    {{ search ? 'Nenhum usuário encontrado.' : 'Nenhum usuário cadastrado.' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <footer v-if="total > 0" class="patient-pagination">
            <span>{{ total }} {{ total === 1 ? 'usuário' : 'usuários' }}</span>
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
