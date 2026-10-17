const AUTH_STORAGE_KEY = 'mycare-authenticated'

export function isAuthenticated(): boolean {
  return localStorage.getItem(AUTH_STORAGE_KEY) === 'true'
}

export function signIn(): void {
  localStorage.setItem(AUTH_STORAGE_KEY, 'true')
}

export function signOut(): void {
  localStorage.removeItem(AUTH_STORAGE_KEY)
}
