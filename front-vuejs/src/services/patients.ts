import { getAccessToken } from '@/services/auth'

const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api').replace(
  /\/$/,
  ''
)

interface PatientCountResponse {
  count: number
}

export interface Patient {
  id: number
  name: string
  cpf: string
  phone: string | null
  birth_date: string | null
}

export interface PatientDetails extends Patient {
  rg: string | null
  sex: 'm' | 'f' | null
  email: string | null
  description: string | null
}

export interface PatientPage {
  data: Patient[]
  current_page: number
  last_page: number
  total: number
}

export interface NewPatient {
  name: string
  cpf: string
  rg: string
  birth_date: string
  sex: 'm' | 'f' | ''
  phone: string
  email: string
  description: string
}

export async function fetchPatientCount(): Promise<number> {
  const token = getAccessToken()

  if (!token) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  let response: Response

  try {
    response = await fetch(`${API_BASE_URL}/patients/count`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      }
    })
  } catch {
    throw new Error('Não foi possível carregar o total de pacientes.')
  }

  if (response.status === 401) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  if (!response.ok) {
    throw new Error('Não foi possível carregar o total de pacientes.')
  }

  const data: unknown = await response.json()

  if (!isPatientCountResponse(data)) {
    throw new Error('O servidor retornou um total de pacientes inválido.')
  }

  return data.count
}

export async function fetchPatients(search: string, page: number): Promise<PatientPage> {
  const token = getAccessToken()

  if (!token) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  const query = new URLSearchParams({ page: String(page) })
  if (search.trim()) {
    query.set('search', search.trim())
  }

  let response: Response

  try {
    response = await fetch(`${API_BASE_URL}/patients?${query.toString()}`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      }
    })
  } catch {
    throw new Error('Não foi possível carregar os pacientes.')
  }

  if (response.status === 401) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  if (response.status === 422) {
    throw new Error('O filtro de busca não é válido.')
  }

  if (!response.ok) {
    throw new Error('Não foi possível carregar os pacientes.')
  }

  const data: unknown = await response.json()

  if (!isPatientPage(data)) {
    throw new Error('O servidor retornou uma lista de pacientes inválida.')
  }

  return data
}

export async function createPatient(patient: NewPatient): Promise<void> {
  const token = getAccessToken()

  if (!token) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  let response: Response

  try {
    response = await fetch(`${API_BASE_URL}/patients`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(patientPayload(patient))
    })
  } catch {
    throw new Error('Não foi possível conectar ao servidor. Tente novamente.')
  }

  if (response.status === 401) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  if (response.status === 422) {
    const data: unknown = await response.json()
    throw new Error(getValidationMessage(data) || 'Confira os dados informados.')
  }

  if (!response.ok) {
    throw new Error('Não foi possível cadastrar o paciente. Tente novamente.')
  }
}

export async function fetchPatient(id: string): Promise<PatientDetails> {
  const token = getAccessToken()

  if (!token) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  let response: Response

  try {
    response = await fetch(`${API_BASE_URL}/patients/${encodeURIComponent(id)}`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
      }
    })
  } catch {
    throw new Error('Não foi possível carregar os dados do paciente.')
  }

  if (response.status === 401) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  if (response.status === 404) {
    throw new Error('Paciente não encontrado.')
  }

  if (!response.ok) {
    throw new Error('Não foi possível carregar os dados do paciente.')
  }

  const data: unknown = await response.json()
  if (!isPatientDetails(data)) {
    throw new Error('O servidor retornou dados de paciente inválidos.')
  }

  return data
}

export async function updatePatient(id: string, patient: NewPatient): Promise<void> {
  const token = getAccessToken()

  if (!token) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  let response: Response

  try {
    response = await fetch(`${API_BASE_URL}/patients/${encodeURIComponent(id)}`, {
      method: 'PUT',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(patientPayload(patient))
    })
  } catch {
    throw new Error('Não foi possível conectar ao servidor. Tente novamente.')
  }

  if (response.status === 401) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  if (response.status === 404) {
    throw new Error('Paciente não encontrado.')
  }

  if (response.status === 422) {
    const data: unknown = await response.json()
    throw new Error(getValidationMessage(data) || 'Confira os dados informados.')
  }

  if (!response.ok) {
    throw new Error('Não foi possível atualizar o paciente. Tente novamente.')
  }
}

function patientPayload(patient: NewPatient): Record<string, string | null> {
  return {
    ...patient,
    rg: patient.rg || null,
    birth_date: patient.birth_date || null,
    sex: patient.sex || null,
    phone: patient.phone || null,
    email: patient.email || null,
    description: patient.description || null
  }
}

function isPatientCountResponse(value: unknown): value is PatientCountResponse {
  if (!value || typeof value !== 'object') {
    return false
  }

  const count = (value as { count?: unknown }).count
  return typeof count === 'number' && Number.isInteger(count) && count >= 0
}

function isPatientDetails(value: unknown): value is PatientDetails {
  if (!value || typeof value !== 'object') {
    return false
  }

  const patient = value as Partial<PatientDetails>
  return (
    typeof patient.id === 'number' &&
    typeof patient.name === 'string' &&
    typeof patient.cpf === 'string' &&
    (typeof patient.rg === 'string' || patient.rg === null) &&
    (typeof patient.birth_date === 'string' || patient.birth_date === null) &&
    (patient.sex === 'm' || patient.sex === 'f' || patient.sex === null) &&
    (typeof patient.phone === 'string' || patient.phone === null) &&
    (typeof patient.email === 'string' || patient.email === null) &&
    (typeof patient.description === 'string' || patient.description === null)
  )
}

function getValidationMessage(value: unknown): string | null {
  if (!value || typeof value !== 'object') {
    return null
  }

  const errors = (value as { errors?: Record<string, unknown> }).errors
  if (!errors || typeof errors !== 'object') {
    return null
  }

  for (const key of Object.keys(errors)) {
    const messages = errors[key]
    if (Array.isArray(messages) && typeof messages[0] === 'string') {
      return messages[0]
    }
  }

  return null
}

function isPatientPage(value: unknown): value is PatientPage {
  if (!value || typeof value !== 'object') {
    return false
  }

  const page = value as Partial<PatientPage>

  return (
    Array.isArray(page.data) &&
    page.data.every(
      (patient) =>
        typeof patient.id === 'number' &&
        typeof patient.name === 'string' &&
        typeof patient.cpf === 'string' &&
        (typeof patient.phone === 'string' || patient.phone === null) &&
        (typeof patient.birth_date === 'string' || patient.birth_date === null)
    ) &&
    typeof page.current_page === 'number' &&
    typeof page.last_page === 'number' &&
    typeof page.total === 'number'
  )
}
