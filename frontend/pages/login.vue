<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 p-4">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">
      <div class="text-center mb-8">
        <NuxtLink to="/" class="inline-flex items-center gap-3 group mb-6">
          <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center shadow-xl group-hover:scale-105 transition-transform duration-300 overflow-hidden">
            <img src="/logo.png" alt="Logo Puskesmas Antang" class="w-10 h-10 object-contain" />
          </div>
        </NuxtLink>
        <h1 class="font-display text-3xl font-bold text-white mb-2">
          Selamat Datang
        </h1>
        <p class="text-primary-200">
          Login untuk mengakses dashboard petugas
        </p>
      </div>

      <div class="bg-white rounded-4xl p-8 shadow-2xl">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label class="label-text">Email</label>
            <div class="relative">
              <UIcon name="i-heroicons-envelope" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="form.email"
                type="email"
                class="input-field pl-12"
                placeholder="nama@puskesmas.id"
                required
              />
            </div>
            <p v-if="errors.email" class="mt-2 text-sm text-red-500">{{ errors.email }}</p>
          </div>

          <div>
            <label class="label-text">Password</label>
            <div class="relative">
              <UIcon name="i-heroicons-lock-closed" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="input-field pl-12 pr-12"
                placeholder="Masukkan password"
                required
              />
              <button
                type="button"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                @click="showPassword = !showPassword"
              >
                <UIcon :name="showPassword ? 'i-heroicons-eye-slash' : 'i-heroicons-eye'" class="w-5 h-5" />
              </button>
            </div>
            <p v-if="errors.password" class="mt-2 text-sm text-red-500">{{ errors.password }}</p>
          </div>

          <div v-if="loginError" class="p-4 rounded-xl bg-red-50 border border-red-200">
            <div class="flex items-center gap-3">
              <UIcon name="i-heroicons-exclamation-circle" class="w-5 h-5 text-red-500 flex-shrink-0" />
              <p class="text-sm text-red-700">{{ loginError }}</p>
            </div>
          </div>

          <button
            type="submit"
            :disabled="isLoading"
            class="btn-primary w-full justify-center text-lg py-4"
          >
            <UIcon
              v-if="isLoading"
              name="i-heroicons-arrow-path"
              class="w-5 h-5 animate-spin"
            />
            <UIcon v-else name="i-heroicons-arrow-right-on-rectangle" class="w-5 h-5" />
            {{ isLoading ? 'Memproses...' : 'Masuk' }}
          </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
          <NuxtLink to="/" class="text-primary-600 font-display font-medium hover:underline inline-flex items-center gap-2">
            <UIcon name="i-heroicons-arrow-left" class="w-4 h-4" />
            Kembali ke Beranda
          </NuxtLink>
        </div>
      </div>

      <p class="text-center mt-6 text-primary-200 text-sm">
        &copy; {{ new Date().getFullYear() }} Puskesmas Antang
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
})

useHead({
  title: 'Login - Puskesmas Antang',
})

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const form = ref({
  email: '',
  password: '',
})

const errors = ref<Record<string, string>>({})
const loginError = ref('')
const isLoading = ref(false)
const showPassword = ref(false)

function validateForm(): boolean {
  errors.value = {}

  if (!form.value.email) {
    errors.value.email = 'Email wajib diisi'
    return false
  }

  if (!form.value.password) {
    errors.value.password = 'Password wajib diisi'
    return false
  }

  return true
}

async function handleLogin() {
  if (!validateForm()) return

  isLoading.value = true
  loginError.value = ''

  try {
    const success = await authStore.login({
      email: form.value.email,
      password: form.value.password,
    })

    if (success) {
      toast.add({
        title: 'Berhasil!',
        description: 'Selamat datang kembali',
        color: 'green',
      })

      await nextTick()

      if (authStore.isAdmin) {
        await navigateTo('/admin')
      } else {
        await navigateTo('/dashboard')
      }
    }
  } catch (err: any) {
    loginError.value = err.message || 'Email atau password salah'
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await authStore.initAuth()
  if (authStore.isAuthenticated) {
    if (authStore.isAdmin) {
      await navigateTo('/admin')
    } else {
      await navigateTo('/dashboard')
    }
  }
})
</script>
