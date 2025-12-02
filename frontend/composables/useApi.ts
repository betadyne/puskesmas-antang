import type { ApiResponse } from '~/types'

export function useApi() {
  const config = useRuntimeConfig()
  const authStore = useAuthStore()
  const toast = useToast()
  const router = useRouter()

  async function $api<T>(
    endpoint: string,
    options: {
      method?: 'GET' | 'POST' | 'PUT' | 'DELETE'
      body?: any
      requiresAuth?: boolean
    } = {}
  ): Promise<T> {
    const { method = 'GET', body, requiresAuth = true } = options

    const headers: Record<string, string> = {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    }

    if (requiresAuth && authStore.token) {
      headers['Authorization'] = `Bearer ${authStore.token}`
    }

    try {
      const response = await $fetch<ApiResponse<T>>(`${config.public.apiBase}${endpoint}`, {
        method,
        headers,
        body: body ? JSON.stringify(body) : undefined,
        onResponseError({ response }) {
          const status = response.status

          if (status === 401) {
            authStore.logout()
            toast.add({
              title: 'Sesi Berakhir',
              description: 'Silakan login kembali',
              color: 'red',
            })
            router.push('/login')
          } else if (status === 403) {
            toast.add({
              title: 'Akses Ditolak',
              description: 'Anda tidak memiliki izin untuk mengakses fitur ini',
              color: 'red',
            })
          } else if (status === 422) {
            const errors = response._data?.errors
            if (errors) {
              const firstError = Object.values(errors)[0]
              toast.add({
                title: 'Validasi Gagal',
                description: Array.isArray(firstError) ? firstError[0] : String(firstError),
                color: 'red',
              })
            }
          }
        },
      })

      if (!response.success) {
        throw new Error(response.message || 'Terjadi kesalahan')
      }

      return response.data
    } catch (error: any) {
      console.error('API Error:', error)
      throw error
    }
  }

  return { $api }
}
