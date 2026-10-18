import { getAccessToken } from '@/services/auth'

const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api').replace(
  /\/$/,
  ''
)

interface PatientCountResponse {
  count: number
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

function isPatientCountResponse(value: unknown): value is PatientCountResponse {
  if (!value || typeof value !== 'object') {
    return false
  }

  const count = (value as { count?: unknown }).count
  return typeof count === 'number' && Number.isInteger(count) && count >= 0
}
