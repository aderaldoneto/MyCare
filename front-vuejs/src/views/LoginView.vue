<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router/composables'
import FooterView from '@/components/FooterView.vue'
import { signIn } from '@/services/auth'

const router = useRouter()
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const isSubmitting = ref(false)

async function handleLogin(): Promise<void> {
  if (isSubmitting.value) {
    return
  }

  errorMessage.value = ''
  isSubmitting.value = true

  try {
    await signIn(email.value.trim(), password.value)
    await router.push({ name: 'home' })
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Não foi possível entrar. Tente novamente.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <main class="login-page">
    <FooterView />

    <section class="login-brand-panel" aria-label="MyCare">
      <a class="brand brand-light" href="/" aria-label="MyCare - início">
        <span class="brand-mark" aria-hidden="true">
          <svg viewBox="0 0 40 40" fill="none">
            <path d="M20 34s-13-7.7-13-17A7.5 7.5 0 0 1 20 12.2 7.5 7.5 0 0 1 33 17c0 9.3-13 17-13 17Z" />
            <path d="M12.5 20h5l2-4 3.2 8 2-4h3" />
          </svg>
        </span>
        <span>MyCare</span>
      </a>

      <div class="brand-message">
        <span class="eyebrow">CUIDANDO DE VOCÊ!</span>
        <h1>Mais cuidado.<br />Mais perto.</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris scelerisque cursus nisl, non bibendum eros aliquam nec.</p>
      </div>

      <div class="care-illustration" aria-hidden="true">
        <div class="illustration-circle"></div>
        <div class="illustration-card">
          <span class="illustration-heart">+</span>
          <span class="illustration-line illustration-line-long"></span>
          <span class="illustration-line"></span>
          <span class="illustration-avatar"></span>
        </div>
        <span class="illustration-spark illustration-spark-one">✦</span>
        <span class="illustration-spark illustration-spark-two">✦</span>
      </div>
    </section>

    <section class="login-form-panel">
      <div class="login-form-wrap">
        <a class="brand brand-mobile" href="/" aria-label="MyCare - início">
          <span class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 40 40" fill="none">
              <path d="M20 34s-13-7.7-13-17A7.5 7.5 0 0 1 20 12.2 7.5 7.5 0 0 1 33 17c0 9.3-13 17-13 17Z" />
              <path d="M12.5 20h5l2-4 3.2 8 2-4h3" />
            </svg>
          </span>
          <span>MyCare</span>
        </a>

        <div class="form-heading">
          <span class="eyebrow">BEM-VINDO DE VOLTA!</span>
          <h2>Acesse sua conta</h2>
        </div>

        <form class="login-form" @submit.prevent="handleLogin">
          <label for="email">E-mail</label>
          <div class="input-with-icon">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <rect x="3" y="5" width="18" height="14" rx="3" />
              <path d="m4 7 8 6 8-6" />
            </svg>
            <input
              id="email"
              v-model="email"
              type="email"
              name="email"
              autocomplete="username"
              placeholder="email@exemplo.com"
              required
            />
          </div>

          <div class="password-label">
            <label for="password">Senha</label>
          </div>
          <div class="input-with-icon">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <rect x="4" y="10" width="16" height="11" rx="3" />
              <path d="M8 10V7a4 4 0 1 1 8 0v3m-4 5v2" />
            </svg>
            <input
              id="password"
              v-model="password"
              type="password"
              name="password"
              autocomplete="current-password"
              placeholder="Digite sua senha"
              required
            />
          </div>

          <p v-if="errorMessage" class="form-error" role="alert">{{ errorMessage }}</p>
          <button class="primary-button login-button" type="submit" :disabled="isSubmitting">
            {{ isSubmitting ? 'Entrando...' : 'Entrar' }}
          </button>
        </form>

      </div>
    </section>
  </main>
</template>
