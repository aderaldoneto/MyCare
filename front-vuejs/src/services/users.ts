import { getAccessToken } from '@/services/auth'

const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api').replace(
  /\/$/,
  ''
)

export interface User {
  id: number
  name: string
  email: string
  role: string
}

export interface UserPage {
  data: User[]
  current_page: number
  last_page: number
  total: number
}

export interface UserFormData {
  name: string
  email: string
  role: string
  password: string
}

export const USER_ROLES = [
  { value: 'admin', label: 'Administrador' },
  { value: 'manager', label: 'Gerente' },
  { value: 'receptionist', label: 'Recepcionista' },
  { value: 'doctor', label: 'Médico' },
  { value: 'nurse', label: 'Enfermagem' },
  { value: 'dentist', label: 'Dentista' },
  { value: 'psychologist', label: 'Psicologia' },
  { value: 'nutritionist', label: 'Nutrição' },
  { value: 'physiotherapist', label: 'Fisioterapia' }
] as const

export async function fetchUserCount(): Promise<number> {
  const token = getAccessToken()
  if (!token) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  let response: Response
  try {
    response = await fetch(`${API_BASE_URL}/users/count`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` }
    })
  } catch {
    throw new Error('Não foi possível carregar o total de usuários.')
  }

  if (response.status === 401) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }
  if (!response.ok) {
    throw new Error('Não foi possível carregar o total de usuários.')
  }

  const data: unknown = await response.json()
  if (!isUserCountResponse(data)) {
    throw new Error('O servidor retornou um total de usuários inválido.')
  }

  return Number(data.count)
}

function isUserCountResponse(value: unknown): value is { count: number | string } {
  if (!value || typeof value !== 'object') {
    return false
  }

  const count = (value as { count?: unknown }).count
  return (
    (typeof count === 'number' && Number.isSafeInteger(count) && count >= 0) ||
    (typeof count === 'string' && /^\d+$/.test(count) && Number.isSafeInteger(Number(count)))
  )
}

export async function fetchUsers(search: string, page: number): Promise<UserPage> {
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
    response = await fetch(`${API_BASE_URL}/users?${query.toString()}`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` }
    })
  } catch {
    throw new Error('Não foi possível carregar os usuários.')
  }

  if (response.status === 401) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }
  if (!response.ok) {
    throw new Error('Não foi possível carregar os usuários.')
  }

  const data: unknown = await response.json()
  if (!isUserPage(data)) {
    throw new Error('O servidor retornou uma lista de usuários inválida.')
  }
  return data
}

export async function fetchUser(id: string): Promise<User> {
  const token = getAccessToken()
  if (!token) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  let response: Response
  try {
    response = await fetch(`${API_BASE_URL}/users/${encodeURIComponent(id)}`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` }
    })
  } catch {
    throw new Error('Não foi possível carregar os dados do usuário.')
  }

  if (response.status === 401) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }
  if (response.status === 404) {
    throw new Error('Usuário não encontrado.')
  }
  if (!response.ok) {
    throw new Error('Não foi possível carregar os dados do usuário.')
  }

  const data: unknown = await response.json()
  if (!isUser(data)) {
    throw new Error('O servidor retornou dados de usuário inválidos.')
  }
  return data
}

export async function saveUser(user: UserFormData, id?: string): Promise<void> {
  const token = getAccessToken()
  if (!token) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  let response: Response
  try {
    response = await fetch(
      id ? `${API_BASE_URL}/users/${encodeURIComponent(id)}` : `${API_BASE_URL}/users`,
      {
        method: id ? 'PUT' : 'POST',
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          name: user.name,
          email: user.email,
          role: user.role,
          password: user.password || null
        })
      }
    )
  } catch {
    throw new Error('Não foi possível conectar ao servidor. Tente novamente.')
  }

  if (response.status === 401) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }
  if (response.status === 404) {
    throw new Error('Usuário não encontrado.')
  }
  if (response.status === 422) {
    const data: unknown = await response.json()
    throw new Error(getApiErrorMessage(data) || 'Confira os dados informados.')
  }
  if (!response.ok) {
    throw new Error('Não foi possível salvar o usuário. Tente novamente.')
  }
}

export async function deleteUser(id: string): Promise<void> {
  const token = getAccessToken()
  if (!token) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }

  let response: Response
  try {
    response = await fetch(`${API_BASE_URL}/users/${encodeURIComponent(id)}`, {
      method: 'DELETE',
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` }
    })
  } catch {
    throw new Error('Não foi possível conectar ao servidor. Tente novamente.')
  }

  if (response.status === 401) {
    throw new Error('Sua sessão expirou. Entre novamente.')
  }
  if (response.status === 404) {
    throw new Error('Usuário não encontrado.')
  }
  if (response.status === 422) {
    const data: unknown = await response.json()
    const message = data && typeof data === 'object' ? (data as { message?: unknown }).message : null
    throw new Error(typeof message === 'string' ? message : 'Não foi possível excluir o usuário.')
  }
  if (!response.ok) {
    throw new Error('Não foi possível excluir o usuário. Tente novamente.')
  }
}

function isUser(value: unknown): value is User {
  if (!value || typeof value !== 'object') {
    return false
  }
  const user = value as Partial<User>
  return (
    typeof user.id === 'number' &&
    typeof user.name === 'string' &&
    typeof user.email === 'string' &&
    typeof user.role === 'string'
  )
}

function isUserPage(value: unknown): value is UserPage {
  if (!value || typeof value !== 'object') {
    return false
  }
  const page = value as Partial<UserPage>
  return (
    Array.isArray(page.data) &&
    page.data.every(isUser) &&
    typeof page.current_page === 'number' &&
    typeof page.last_page === 'number' &&
    typeof page.total === 'number'
  )
}

function getApiErrorMessage(value: unknown): string | null {
  if (!value || typeof value !== 'object') {
    return null
  }

  const body = value as { message?: unknown; errors?: Record<string, unknown> }
  if (typeof body.message === 'string') {
    return body.message
  }

  for (const key of Object.keys(body.errors || {})) {
    const messages = body.errors?.[key]
    if (Array.isArray(messages) && typeof messages[0] === 'string') {
      return messages[0]
    }
  }

  return null
}
