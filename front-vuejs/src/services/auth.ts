const TOKEN_STORAGE_KEY = 'mycare-access-token'
const TOKEN_EXPIRATION_KEY = 'mycare-access-token-expires-at'
const USER_NAME_STORAGE_KEY = 'mycare-user-name'
const USER_ROLE_STORAGE_KEY = 'mycare-user-role'
const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api').replace(
  /\/$/,
  ''
)

export interface AuthUser {
  id: number
  name: string
  email: string
  role: string
}

interface LoginResponse {
  token: string
  expires_at: string
  user: AuthUser
}

export function isAuthenticated(): boolean {
  return getAccessToken() !== null
}

export function getAccessToken(): string | null {
  const token = localStorage.getItem(TOKEN_STORAGE_KEY)
  const expiration = localStorage.getItem(TOKEN_EXPIRATION_KEY)

  if (!token || !expiration || Date.parse(expiration) <= Date.now()) {
    clearStoredToken()
    return null
  }

  return token
}

export function getAuthenticatedUser(): Pick<AuthUser, 'name' | 'role'> | null {
  if (!getAccessToken()) {
    return null
  }

  const name = localStorage.getItem(USER_NAME_STORAGE_KEY)
  const role = localStorage.getItem(USER_ROLE_STORAGE_KEY)

  return name && role ? { name, role } : null
}

export async function signIn(email: string, password: string): Promise<AuthUser> {
  let response: Response

  try {
    response = await fetch(`${API_BASE_URL}/login`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email, password })
    })
  } catch {
    throw new Error('Não foi possível conectar ao servidor. Tente novamente.')
  }

  if (response.status === 401) {
    throw new Error('E-mail ou senha inválidos.')
  }

  if (response.status === 422) {
    throw new Error('Informe um e-mail válido e uma senha.')
  }

  if (!response.ok) {
    throw new Error('Não foi possível entrar. Tente novamente mais tarde.')
  }

  const data: unknown = await response.json()

  if (!isLoginResponse(data)) {
    throw new Error('O servidor retornou uma resposta de autenticação inválida.')
  }

  localStorage.setItem(TOKEN_STORAGE_KEY, data.token)
  localStorage.setItem(TOKEN_EXPIRATION_KEY, data.expires_at)
  localStorage.setItem(USER_NAME_STORAGE_KEY, data.user.name)
  localStorage.setItem(USER_ROLE_STORAGE_KEY, data.user.role)
  return data.user
}

export async function signOut(): Promise<void> {
  const token = getAccessToken()

  try {
    if (token) {
      let response: Response

      try {
        response = await fetch(`${API_BASE_URL}/logout`, {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${token}`
          }
        })
      } catch {
        throw new Error('Não foi possível conectar ao servidor para encerrar a sessão.')
      }

      if (!response.ok) {
        throw new Error('Não foi possível invalidar a sessão no servidor.')
      }
    }
  } finally {
    clearStoredToken()
  }
}

function clearStoredToken(): void {
  localStorage.removeItem(TOKEN_STORAGE_KEY)
  localStorage.removeItem(TOKEN_EXPIRATION_KEY)
  localStorage.removeItem(USER_NAME_STORAGE_KEY)
  localStorage.removeItem(USER_ROLE_STORAGE_KEY)
}

function isLoginResponse(value: unknown): value is LoginResponse {
  if (!value || typeof value !== 'object') {
    return false
  }

  const response = value as Partial<LoginResponse>
  const user = response.user

  return (
    typeof response.token === 'string' &&
    response.token.length > 0 &&
    typeof response.expires_at === 'string' &&
    !Number.isNaN(Date.parse(response.expires_at)) &&
    !!user &&
    typeof user.id === 'number' &&
    typeof user.name === 'string' &&
    typeof user.email === 'string' &&
    typeof user.role === 'string'
  )
}
