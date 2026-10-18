<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router/composables'
import MenuView from '@/components/MenuView.vue'
import TopoView from '@/components/TopoView.vue'
import AlertToast from '@/components/AlertToast.vue'
import { createPatient, type NewPatient } from '@/services/patients'

const router = useRouter()
const isSubmitting = ref(false)
const errorMessage = ref('')
const patient = reactive<NewPatient>({
  name: '',
  cpf: '',
  rg: '',
  birth_date: '',
  sex: '',
  phone: '',
  email: '',
  description: ''
})

function handleMaskedInput(
  event: Event,
  maxDigits: number,
  formatDigits: (digits: string) => string
): void {
  const input = event.target as HTMLInputElement
  const digitsBeforeCursor = input.value
    .slice(0, input.selectionStart ?? input.value.length)
    .replace(/\D/g, '').length
  const digits = input.value.replace(/\D/g, '').slice(0, maxDigits)
  const formatted = formatDigits(digits)

  input.value = formatted

  let cursor = 0
  let digitsAtCursor = 0
  while (cursor < formatted.length && digitsAtCursor < digitsBeforeCursor) {
    if (/\d/.test(formatted[cursor])) {
      digitsAtCursor += 1
    }
    cursor += 1
  }

  input.setSelectionRange(cursor, cursor)
}

function handleCpfInput(event: Event): void {
  handleMaskedInput(event, 11, (digits) =>
    digits
      .replace(/^(\d{3})(\d)/, '$1.$2')
      .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
      .replace(/\.(\d{3})(\d)/, '.$1-$2')
  )
  patient.cpf = (event.target as HTMLInputElement).value
}

function handlePhoneInput(event: Event): void {
  handleMaskedInput(event, 11, (digits) => {
    if (digits.length <= 2) {
      return digits
    }

    let formatted = `(${digits.slice(0, 2)}) ${digits[2]}`
    if (digits.length > 3) {
      formatted += ` ${digits.slice(3, 7)}`
    }
    if (digits.length > 7) {
      formatted += `-${digits.slice(7, 11)}`
    }
    return formatted
  })
  patient.phone = (event.target as HTMLInputElement).value
}

function handleRgInput(event: Event): void {
  handleMaskedInput(event, 20, (digits) => digits)
  patient.rg = (event.target as HTMLInputElement).value
}

function today(): string {
  const date = new Date()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${date.getFullYear()}-${month}-${day}`
}

async function handleSubmit(): Promise<void> {
  const form = document.querySelector<HTMLFormElement>('.patient-form-card')
  const cpfDigits = patient.cpf.replace(/\D/g, '')
  const rgDigits = patient.rg.replace(/\D/g, '')
  const phoneDigits = patient.phone.replace(/\D/g, '')
  const emailIsValid = !patient.email || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(patient.email)
  let validationError = ''
  let invalidField = ''

  if (patient.name.trim().length < 5) {
    validationError = 'O nome deve ter no mínimo 5 caracteres.'
    invalidField = 'name'
  } else if (cpfDigits.length !== 11) {
    validationError = 'O CPF deve conter 11 números.'
    invalidField = 'cpf'
  } else if (rgDigits.length < 7 || rgDigits.length !== patient.rg.length) {
    validationError = 'O RG deve conter ao menos 7 números.'
    invalidField = 'rg'
  } else if (!patient.birth_date) {
    validationError = 'Preencha a data de nascimento.'
    invalidField = 'birth_date'
  } else if (patient.birth_date > today()) {
    validationError = 'A data de nascimento não pode ser futura.'
    invalidField = 'birth_date'
  } else if (!patient.sex) {
    validationError = 'Selecione o sexo.'
    invalidField = 'sex'
  } else if (phoneDigits.length !== 11) {
    validationError = 'O celular deve conter 11 números.'
    invalidField = 'phone'
  } else if (!emailIsValid) {
    validationError = 'Digite um e-mail válido!'
    invalidField = 'email'
  }

  if (validationError) {
    form?.querySelector<HTMLElement>(`[data-field="${invalidField}"]`)?.focus()
    errorMessage.value = validationError
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    await createPatient(patient)
    await router.push({ name: 'patients', query: { success: 'Paciente cadastrado com sucesso!' } })
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Não foi possível cadastrar o paciente.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="dashboard-layout">
    <MenuView />

    <main class="dashboard-main">
      <TopoView current-page="Cadastrar paciente" />
      <AlertToast v-if="errorMessage" :message="errorMessage" @close="errorMessage = ''" />

      <div class="patients-page">
        <div class="patients-page-heading">
          <div>
            <h1>Cadastrar paciente</h1>
            <p>Preencha os dados para adicionar um paciente.</p>
          </div>
        </div>

        <form class="patient-form-card" novalidate @submit.prevent="handleSubmit">
          <div class="patient-form-grid">
            <label class="patient-form-field patient-form-field-two-thirds">
              <span>Nome completo <strong>*</strong></span>
              <input v-model.trim="patient.name" data-field="name" type="text" maxlength="255" required />
            </label>

            <label class="patient-form-field">
              <span>CPF <strong>*</strong></span>
              <input
                :value="patient.cpf"
                @input="handleCpfInput"
                data-field="cpf"
                type="text"
                maxlength="14"
                autocomplete="off"
                inputmode="numeric"
                placeholder="000.000.000-00"
                required
              />
            </label>

            <label class="patient-form-field">
              <span>RG <strong>*</strong></span>
                <input
                :value="patient.rg"
                @input="handleRgInput"
                data-field="rg"
                type="text"
                maxlength="20"
                inputmode="numeric"
                placeholder="Somente números"
                required
              />
            </label>

            <label class="patient-form-field">
              <span>Data de nascimento <strong>*</strong></span>
              <input
                v-model="patient.birth_date"
                data-field="birth_date"
                type="date"
                :max="today()"
                required
              />
            </label>

            <label class="patient-form-field">
              <span>Sexo <strong>*</strong></span>
              <select v-model="patient.sex" data-field="sex" required>
                <option value="">Selecionar</option>
                <option value="f">Feminino</option>
                <option value="m">Masculino</option>
              </select>
            </label>

            <label class="patient-form-field">
              <span>Celular <strong>*</strong></span>
              <input
                :value="patient.phone"
                @input="handlePhoneInput"
                data-field="phone"
                type="tel"
                maxlength="16"
                inputmode="numeric"
                autocomplete="tel"
                placeholder="(00) 0 0000-0000"
                required
              />
            </label>

            <label class="patient-form-field patient-form-field-two-fifths">
              <span>E-mail (Opcional)</span>
              <input v-model.trim="patient.email" data-field="email" type="email" maxlength="255" placeholder="Descrever" />
            </label>

            <label class="patient-form-field patient-form-field-wide">
              <span>Descrição (Opcional)</span>
              <textarea v-model.trim="patient.description" rows="5" />
            </label>
          </div>

          <div class="patient-form-actions">
            <router-link class="patient-cancel-button" to="/patients">Cancelar</router-link>
            <button class="primary-button" type="submit" :disabled="isSubmitting">
              {{ isSubmitting ? 'Cadastrando...' : 'Cadastrar paciente' }}
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>
