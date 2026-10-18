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

export interface PatientPage {
  data: Patient[]
  current_page: number
  last_page: number
  total: number
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

function isPatientCountResponse(value: unknown): value is PatientCountResponse {
  if (!value || typeof value !== 'object') {
    return false
  }

  const count = (value as { count?: unknown }).count
  return typeof count === 'number' && Number.isInteger(count) && count >= 0
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
