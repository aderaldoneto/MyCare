<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router/composables'
import MenuView from '@/components/MenuView.vue'
import TopoView from '@/components/TopoView.vue'
import AlertToast from '@/components/AlertToast.vue'
import {
  deleteUser,
  fetchUser,
  saveUser,
  USER_ROLES,
  type UserFormData
} from '@/services/users'

const route = useRoute()
const router = useRouter()
const userId = computed(() => route.params.id || '')
const isEditing = computed(() => Boolean(userId.value))
const isLoading = ref(false)
const isLoaded = ref(false)
const isSubmitting = ref(false)
const isDeleting = ref(false)
const isDeleteDialogOpen = ref(false)
const errorMessage = ref('')
const user = ref({ id: 0, name: '', email: '', role: '' })
const form = reactive<UserFormData>({
  name: '',
  email: '',
  role: '',
  password: ''
})

async function loadUser(): Promise<void> {
  if (!isEditing.value) {
    isLoaded.value = true
    return
  }

  isLoading.value = true
  isLoaded.value = false
  errorMessage.value = ''

  try {
    const data = await fetchUser(userId.value)
    user.value = data
    form.name = data.name
    form.email = data.email
    form.role = data.role
    form.password = ''
    isLoaded.value = true
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Não foi possível carregar o usuário.'
  } finally {
    isLoading.value = false
  }
}

async function handleSubmit(): Promise<void> {
  errorMessage.value = ''

  if (form.name.trim().length < 5) {
    errorMessage.value = 'O nome deve ter no mínimo 5 caracteres.'
    return
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errorMessage.value = 'Digite um e-mail válido.'
    return
  }
  if (!form.role) {
    errorMessage.value = 'Selecione o perfil do usuário.'
    return
  }
  if ((!isEditing.value || form.password) && form.password.length < 8) {
    errorMessage.value = 'A senha deve ter no mínimo 8 caracteres.'
    return
  }

  isSubmitting.value = true
  try {
    await saveUser(form, isEditing.value ? userId.value : undefined)
    await router.push({
      name: 'users',
      query: {
        success: isEditing.value
          ? 'Usuário atualizado com sucesso!'
          : 'Usuário cadastrado com sucesso!'
      }
    })
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Não foi possível salvar o usuário.'
  } finally {
    isSubmitting.value = false
  }
}

async function confirmDelete(): Promise<void> {
  isDeleting.value = true
  errorMessage.value = ''
  try {
    await deleteUser(userId.value)
    await router.push({
      name: 'users',
      query: { success: 'Usuário excluído com sucesso!' }
    })
  } catch (error) {
    isDeleteDialogOpen.value = false
    errorMessage.value =
      error instanceof Error ? error.message : 'Não foi possível excluir o usuário.'
  } finally {
    isDeleting.value = false
  }
}

onMounted(() => {
  void loadUser()
})

watch(userId, () => {
  void loadUser()
})
</script>

<template>
  <div class="dashboard-layout">
    <MenuView />

    <main class="dashboard-main">
      <TopoView
        section="Usuários"
        section-to="/users"
        :item-name="isEditing && isLoaded ? user.name : undefined"
        :current-page="isEditing ? 'Editar' : 'Cadastrar'"
      />
      <AlertToast v-if="errorMessage" :message="errorMessage" @close="errorMessage = ''" />

      <div class="patients-page">
        <div class="patients-page-heading">
          <div><h1>{{ isEditing ? 'Editar usuário' : 'Cadastrar usuário' }}</h1></div>
        </div>

        <p v-if="isLoading" role="status">Carregando dados do usuário...</p>
        <router-link v-else-if="isEditing && !isLoaded" class="patient-cancel-button" to="/users">
          Voltar para usuários
        </router-link>

        <form
          v-if="isLoaded"
          class="patient-form-card"
          novalidate
          @submit.prevent="handleSubmit"
        >
          <div class="patient-form-grid user-form-grid">
            <label class="patient-form-field patient-form-field-wide">
              <span>Nome completo <strong>*</strong></span>
              <input v-model.trim="form.name" type="text" maxlength="255" required />
            </label>

            <label class="patient-form-field">
              <span>E-mail <strong>*</strong></span>
              <input v-model.trim="form.email" type="email" maxlength="255" required />
            </label>

            <label class="patient-form-field">
              <span>Perfil de acesso <strong>*</strong></span>
              <select v-model="form.role" required>
                <option value="">Selecione um perfil</option>
                <option v-for="role in USER_ROLES" :key="role.value" :value="role.value">
                  {{ role.label }}
                </option>
              </select>
            </label>

            <label class="patient-form-field">
              <span>Senha <strong v-if="!isEditing">*</strong></span>
              <input
                v-model="form.password"
                type="password"
                minlength="8"
                maxlength="255"
                :required="!isEditing"
                :placeholder="isEditing ? 'Deixe em branco para manter a senha atual' : ''"
                autocomplete="new-password"
              />
            </label>
          </div>

          <div class="patient-form-actions">
            <button
              v-if="isEditing"
              class="patient-delete-button"
              type="button"
              @click="isDeleteDialogOpen = true"
            >
              Excluir usuário
            </button>
            <router-link v-else class="patient-cancel-button" to="/users">Cancelar</router-link>
            <button class="primary-button" type="submit" :disabled="isSubmitting">
              {{
                isSubmitting
                  ? 'Salvando...'
                  : isEditing
                    ? 'Salvar alterações'
                    : 'Cadastrar usuário'
              }}
            </button>
          </div>
        </form>
      </div>

      <div
        v-if="isDeleteDialogOpen"
        class="delete-dialog-backdrop"
        @click.self="isDeleteDialogOpen = false"
      >
        <section
          class="delete-dialog"
          role="alertdialog"
          aria-modal="true"
          aria-labelledby="delete-user-dialog-title"
        >
          <header class="delete-dialog-header">
            <h2 id="delete-user-dialog-title">Excluir usuário</h2>
            <button
              type="button"
              aria-label="Fechar confirmação"
              :disabled="isDeleting"
              @click="isDeleteDialogOpen = false"
            >
              ×
            </button>
          </header>
          <p>
            Atenção! Ao excluir este usuário, <strong>essa ação não poderá ser desfeita.</strong>
            Deseja realmente continuar?
          </p>
          <footer class="delete-dialog-actions">
            <button
              type="button"
              class="patient-delete-cancel"
              :disabled="isDeleting"
              @click="isDeleteDialogOpen = false"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="primary-button"
              :disabled="isDeleting"
              @click="confirmDelete"
            >
              {{ isDeleting ? 'Excluindo...' : 'Confirmar' }}
            </button>
          </footer>
        </section>
      </div>
    </main>
  </div>
</template>
