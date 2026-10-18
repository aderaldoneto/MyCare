<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router/composables'
import { getAuthenticatedUser, isAdmin, signOut } from '@/services/auth'

const router = useRouter()
const logoutError = ref('')
const user = getAuthenticatedUser()
const userInitials = computed(() =>
  (user?.name.trim().split(/\s+/).map((part) => part.charAt(0)) || [])
    .join('')
    .slice(0, 2)
    .toUpperCase()
)
const roleLabels: Record<string, string> = {
  admin: 'Administrador',
  manager: 'Gerente',
  receptionist: 'Recepcionista',
  doctor: 'Médico',
  nurse: 'Enfermagem',
  dentist: 'Dentista',
  psychologist: 'Psicologia',
  nutritionist: 'Nutrição',
  physiotherapist: 'Fisioterapia'
}
const userRole = user ? roleLabels[user.role] || user.role : ''
const canManageUsers = isAdmin()

async function handleLogout(): Promise<void> {
  logoutError.value = ''

  try {
    await signOut()
  } catch (error) {
    logoutError.value =
      error instanceof Error ? error.message : 'Não foi possível encerrar a sessão no servidor.'
  }

  await router.push({ name: 'login' })

  if (logoutError.value) {
    window.alert(logoutError.value)
  }
}
</script>

<template>
  <aside class="sidebar">
    <router-link class="brand brand-light sidebar-brand" to="/home" aria-label="MyCare - início">
      <span class="brand-mark" aria-hidden="true">
        <svg viewBox="0 0 40 40" fill="none">
          <path d="M20 34s-13-7.7-13-17A7.5 7.5 0 0 1 20 12.2 7.5 7.5 0 0 1 33 17c0 9.3-13 17-13 17Z" />
          <path d="M12.5 20h5l2-4 3.2 8 2-4h3" />
        </svg>
      </span>
      <span>MyCare</span>
    </router-link>

    <div class="sidebar-section-label">MENU PRINCIPAL</div>
    <nav class="sidebar-nav" aria-label="Navegação principal">
      <router-link
        class="sidebar-link"
        to="/home"
        active-class="active"
        aria-current-value="page"
      >
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <rect x="3" y="3" width="7" height="8" rx="2" />
          <rect x="14" y="3" width="7" height="5" rx="2" />
          <rect x="14" y="12" width="7" height="9" rx="2" />
          <rect x="3" y="15" width="7" height="6" rx="2" />
        </svg>
        Visão geral
      </router-link>
      <router-link
        class="sidebar-link"
        to="/patients"
        active-class="active"
        aria-current-value="page"
      >
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="9" cy="8" r="3.5" />
          <path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20H3Zm13-12a3.5 3.5 0 0 1 0 6.8m2 1.2a4 4 0 0 1 3 3.9v.1h-3" />
        </svg>
        Pacientes
      </router-link>
      <router-link
        v-if="canManageUsers"
        class="sidebar-link"
        to="/users"
        active-class="active"
        aria-current-value="page"
      >
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="9" cy="8" r="3.5" />
          <path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20H3Zm13-12a3.5 3.5 0 0 1 0 6.8m2 1.2a4 4 0 0 1 3 3.9v.1h-3" />
        </svg>
        Usuários
      </router-link>
    </nav>

    <div class="sidebar-bottom">
      <button
        class="sidebar-link logout-button"
        type="button"
        aria-label="Sair da conta"
        @click="handleLogout"
      >
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5m5-4 4-4-4-4m4 4H9" />
        </svg>
        Sair
      </button>
      <p v-if="logoutError" class="form-error" role="alert">{{ logoutError }}</p>

      <div class="profile">
        <span class="profile-avatar">{{ userInitials }}</span>
        <span><strong>{{ user?.name }}</strong><small>{{ userRole }}</small></span>
        <span class="profile-menu" aria-hidden="true">···</span>
      </div>
    </div>
  </aside>
</template>
