import { useLocalStorage } from '@vueuse/core'
import type { User, LoginCredentials } from '~/types'

interface LoginResponse {
  message: string
  data: {
    token: string
    user: User
    permissions: string[]
    roles: string[]
  }
}

interface UserResponse {
  message: string
  data: {
    user: User
    permissions: string[]
    roles: string[]
  }
}

export const useAuthStore = defineStore('auth', () => {
  const config = useRuntimeConfig()
  const router = useRouter()

  const user = ref<User | null>(null)
  const userPermissions = ref<string[]>([])
  const userRoles = ref<string[]>([])
  const token = useCookie<string | null>('auth_token', {
    maxAge: 60 * 60 * 24 * 7,
    sameSite: 'lax',
  })
  const storedUser = useLocalStorage<User | null>('auth_user', null)
  const storedRoles = useLocalStorage<string[]>('auth_roles', [])
  const storedPermissions = useLocalStorage<string[]>('auth_permissions', [])
  const isLoading = ref(false)

  const isAuthenticated = computed(() => {
    return !!token.value && !!user.value
  })
  const isAdmin = computed(() => userRoles.value.includes('admin') || user.value?.roles?.includes('admin'))
  const isPetugas = computed(() => userRoles.value.includes('petugas') || user.value?.roles?.includes('petugas'))
  const userPoli = computed(() => user.value?.poli)
  const permissions = computed(() => userPermissions.value)

  function checkPermission(permission: string): boolean {
    return permissions.value.includes(permission) || isAdmin.value
  }

  async function login(credentials: LoginCredentials): Promise<boolean> {
    isLoading.value = true
    try {
      const response = await $fetch<LoginResponse>(
        `${config.public.apiBase}/login`,
        {
          method: 'POST',
          body: credentials,
        }
      )

      if (!response.data?.token) {
        throw new Error(response.message || 'Login gagal')
      }

      token.value = response.data.token
      user.value = response.data.user
      userPermissions.value = response.data.permissions || []
      userRoles.value = response.data.roles || []

      // Persist to localStorage
      storedUser.value = response.data.user
      storedRoles.value = response.data.roles || []
      storedPermissions.value = response.data.permissions || []

      return true
    } catch (err: any) {
      console.error('Login error:', err)
      const message = err.data?.message || err.message || 'Login gagal'
      throw new Error(message)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchUser(): Promise<void> {
    if (!token.value) return

    try {
      const response = await $fetch<UserResponse>(
        `${config.public.apiBase}/user`,
        {
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        }
      )

      if (response.data?.user) {
        user.value = response.data.user
        userPermissions.value = response.data.permissions || []
        userRoles.value = response.data.roles || []
        // Update localStorage
        storedUser.value = response.data.user
        storedRoles.value = response.data.roles || []
        storedPermissions.value = response.data.permissions || []
      } else {
        logout()
      }
    } catch {
      logout()
    }
  }

  function logout(): void {
    token.value = null
    user.value = null
    userPermissions.value = []
    userRoles.value = []
    storedUser.value = null
    storedRoles.value = []
    storedPermissions.value = []
    router.push('/login')
  }

  async function initAuth(): Promise<void> {
    if (token.value && !user.value) {
      // Try to restore from localStorage first
      if (storedUser.value) {
        user.value = storedUser.value
        userRoles.value = storedRoles.value
        userPermissions.value = storedPermissions.value
      }
      // Then verify with server
      await fetchUser()
    }
  }

  return {
    user,
    token,
    isLoading,
    isAuthenticated,
    isAdmin,
    isPetugas,
    userPoli,
    permissions,
    userRoles,
    checkPermission,
    login,
    logout,
    fetchUser,
    initAuth,
  }
})
