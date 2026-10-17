<script setup lang="ts">
import { useRouter } from 'vue-router/composables'
import { signOut } from '@/services/auth'

const router = useRouter()
const today = new Intl.DateTimeFormat('pt-BR', {
  weekday: 'long',
  day: 'numeric',
  month: 'long'
}).format(new Date())


function handleLogout(): void {
  signOut()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <a class="brand brand-light sidebar-brand" href="/home" aria-label="MyCare - início">
        <span class="brand-mark" aria-hidden="true">
          <svg viewBox="0 0 40 40" fill="none">
            <path d="M20 34s-13-7.7-13-17A7.5 7.5 0 0 1 20 12.2 7.5 7.5 0 0 1 33 17c0 9.3-13 17-13 17Z" />
            <path d="M12.5 20h5l2-4 3.2 8 2-4h3" />
          </svg>
        </span>
        <span>MyCare</span>
      </a>

      <div class="sidebar-section-label">MENU PRINCIPAL</div>
      <nav class="sidebar-nav" aria-label="Navegação principal">
        <a class="sidebar-link active" href="/home" aria-current="page">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3" y="3" width="7" height="8" rx="2" />
            <rect x="14" y="3" width="7" height="5" rx="2" />
            <rect x="14" y="12" width="7" height="9" rx="2" />
            <rect x="3" y="15" width="7" height="6" rx="2" />
          </svg>
          Visão geral
        </a>
        <a class="sidebar-link" href="/patients">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="9" cy="8" r="3.5" />
            <path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20H3Zm13-12a3.5 3.5 0 0 1 0 6.8m2 1.2a4 4 0 0 1 3 3.9v.1h-3" />
          </svg>
          Pacientes
        </a>
      </nav>

      <div class="sidebar-bottom">
        <button class="sidebar-link logout-button" type="button" aria-label="Sair da conta" @click="handleLogout">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5m5-4 4-4-4-4m4 4H9" />
          </svg>
          Sair
        </button>

        <div class="profile">
          <span class="profile-avatar">AN</span>
          <span><strong>Aderaldo Neto</strong><small>Meu cargo</small></span>
          <span class="profile-menu" aria-hidden="true">···</span>
          <!--Criar um menuzinho para o perfil do user-->
        </div>
      </div>
    </aside>

    <main class="dashboard-main">
      <header class="topbar">
        <div class="breadcrumb">Início <span>/</span> <strong>Visão geral</strong></div>
      </header>

      <div class="dashboard-content">
        <section class="welcome-row">
          <div>
            <span class="eyebrow">{{ today.toUpperCase() }}</span>
            <h1>Olá, Aderaldo</h1>
            <p>Aqui está um resumo para você.</p>
          </div>
          <a class="primary-button new-patient-button" href="#pacientes">
            <span aria-hidden="true">+</span> Novo paciente
          </a>
        </section>

        <section class="summary-grid" aria-label="Resumo">
          <article class="summary-card">
            <div class="summary-card-top">
              <span class="summary-icon patients-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.5" /><path d="M3 20v-1.5A4.5 4.5 0 0 1 7.5 14h3a4.5 4.5 0 0 1 4.5 4.5V20H3Zm13-12a3.5 3.5 0 0 1 0 6.8m2 1.2a4 4 0 0 1 3 3.9v.1h-3" /></svg>
              </span>
            </div>
            <span class="summary-label">Total de pacientes</span>
            <strong class="summary-value">99</strong>
          </article>
        </section>

        <section id="pacientes" class="patients-section">
          <div class="section-heading">
            <div>
              <h2>Talvez um gráfico</h2>
              <p>Alguma descrição.</p>
            </div>
          </div>
        </section>

      </div>
    </main>
  </div>
</template>
