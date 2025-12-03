<template>
  <div>
    <NuxtLayout name="public">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
          <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-gradient-to-br from-accent-500 to-accent-600 shadow-glow-blue mb-6">
              <UIcon name="i-heroicons-magnifying-glass" class="w-8 h-8 text-white" />
            </div>
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 mb-3">
              Cek Status Antrian
            </h1>
            <p class="text-gray-600">
              Masukkan nomor antrian untuk melihat status terkini
            </p>
          </div>

          <div class="card mb-8">
            <form @submit.prevent="handleSearch" class="flex gap-4">
              <div class="flex-1">
                <input
                  v-model="searchQuery"
                  type="text"
                  class="input-field font-mono text-xl text-center uppercase tracking-widest"
                  placeholder="Contoh: A001"
                  maxlength="10"
                  required
                />
              </div>
              <button
                type="submit"
                :disabled="isLoading"
                class="btn-accent px-8"
              >
                <UIcon
                  v-if="isLoading"
                  name="i-heroicons-arrow-path"
                  class="w-5 h-5 animate-spin"
                />
                <UIcon v-else name="i-heroicons-magnifying-glass" class="w-5 h-5" />
              </button>
            </form>
          </div>

          <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-4"
          >
            <div v-if="queue" class="space-y-6">
              <div class="card">
                <div class="flex items-start justify-between mb-6">
                  <div>
                    <p class="text-sm text-gray-500 mb-1">Nomor Antrian</p>
                    <div class="queue-number text-primary-600">
                      {{ queue.nomor_antrean }}
                    </div>
                  </div>
                  <span :class="statusBadgeClass">
                    {{ statusText }}
                  </span>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                  <div class="p-4 rounded-xl bg-gray-50">
                    <div class="flex items-center gap-2 mb-2">
                      <UIcon name="i-heroicons-user" class="w-4 h-4 text-gray-400" />
                      <span class="text-xs text-gray-500">Nama Pasien</span>
                    </div>
                    <p class="font-display font-semibold text-gray-800">
                      {{ queue.patient?.nama || '-' }}
                    </p>
                  </div>
                  <div class="p-4 rounded-xl bg-gray-50">
                    <div class="flex items-center gap-2 mb-2">
                      <UIcon name="i-heroicons-building-office-2" class="w-4 h-4 text-gray-400" />
                      <span class="text-xs text-gray-500">Poli Tujuan</span>
                    </div>
                    <p class="font-display font-semibold text-gray-800">
                      {{ queue.poli?.nama_poli || '-' }}
                    </p>
                  </div>
                </div>

                <div v-if="queue.petugas" class="p-4 rounded-xl bg-blue-50 border border-blue-100 mb-6">
                  <div class="flex items-center gap-2 mb-2">
                    <UIcon name="i-heroicons-user-circle" class="w-4 h-4 text-blue-500" />
                    <span class="text-xs text-blue-600">Dokter</span>
                  </div>
                  <p class="font-display font-semibold text-blue-800">
                    {{ queue.petugas.name }}
                  </p>
                </div>

                <div v-if="queue.status === 'menunggu'" class="p-4 rounded-xl bg-primary-50 border border-primary-100">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                      <UIcon name="i-heroicons-users" class="w-5 h-5 text-primary-600" />
                    </div>
                    <div>
                      <p class="font-display font-semibold text-primary-800">
                        {{ queuePosition }} antrian di depan Anda
                      </p>
                      <p class="text-sm text-primary-600">
                        Estimasi waktu tunggu: ~{{ estimatedTime }} menit
                      </p>
                    </div>
                  </div>
                </div>

                <div v-else-if="queue.status === 'dipanggil'" class="p-4 rounded-xl bg-green-50 border border-green-200 animate-pulse-slow">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                      <UIcon name="i-heroicons-bell-alert" class="w-5 h-5 text-green-600" />
                    </div>
                    <div>
                      <p class="font-display font-bold text-green-800 text-lg">
                        GILIRAN ANDA!
                      </p>
                      <p class="text-sm text-green-600">
                        Silakan menuju ke {{ queue.poli?.nama_poli }}
                      </p>
                    </div>
                  </div>
                </div>

                <div v-else-if="queue.status === 'selesai'" class="p-4 rounded-xl bg-gray-100">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gray-200 flex items-center justify-center">
                      <UIcon name="i-heroicons-check-circle" class="w-5 h-5 text-gray-600" />
                    </div>
                    <div>
                      <p class="font-display font-semibold text-gray-700">
                        Pelayanan Selesai
                      </p>
                      <p class="text-sm text-gray-500">
                        Terima kasih telah berkunjung
                      </p>
                    </div>
                  </div>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100">
                  <div class="flex items-center justify-between text-sm text-gray-500">
                    <span>Waktu Daftar:</span>
                    <span class="font-mono">{{ formatDate(queue.created_at) }}</span>
                  </div>
                </div>
              </div>

              <div class="text-center">
                <button
                  @click="handleSearch"
                  class="text-primary-600 font-display font-medium hover:underline inline-flex items-center gap-2"
                >
                  <UIcon name="i-heroicons-arrow-path" class="w-4 h-4" />
                  Refresh Status
                </button>
              </div>
            </div>

            <div v-else-if="notFound" class="card text-center">
              <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                <UIcon name="i-heroicons-magnifying-glass" class="w-8 h-8 text-gray-400" />
              </div>
              <h3 class="font-display font-semibold text-gray-800 mb-2">
                Antrian Tidak Ditemukan
              </h3>
              <p class="text-gray-600 mb-6">
                Pastikan nomor antrian yang Anda masukkan sudah benar
              </p>
              <NuxtLink to="/register" class="btn-primary inline-flex">
                <UIcon name="i-heroicons-plus" class="w-5 h-5" />
                Daftar Antrian Baru
              </NuxtLink>
            </div>
          </Transition>
        </div>
      </div>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
import type { Queue, ApiResponse } from '~/types'

definePageMeta({
  layout: false,
})

useHead({
  title: 'Cek Status Antrian - Puskesmas Antang',
})

const config = useRuntimeConfig()
const route = useRoute()
const router = useRouter()

const searchQuery = ref((route.query.nomor as string) || '')
const queue = ref<Queue | null>(null)
const notFound = ref(false)
const isLoading = ref(false)
const queuePosition = ref(0)
const estimatedTime = ref(15)

const statusText = computed(() => {
  const statusMap: Record<string, string> = {
    menunggu: 'Menunggu',
    dipanggil: 'Dipanggil',
    dilayani: 'Sedang Dilayani',
    selesai: 'Selesai',
    dilewati: 'Dilewati',
  }
  return statusMap[queue.value?.status || ''] || '-'
})

const statusBadgeClass = computed(() => {
  const classes: Record<string, string> = {
    menunggu: 'badge-warning',
    dipanggil: 'badge-success animate-pulse',
    dilayani: 'badge-info',
    selesai: 'badge bg-gray-100 text-gray-600',
    dilewati: 'badge-danger',
  }
  return classes[queue.value?.status || ''] || 'badge'
})

interface StatusResponse {
  message: string
  data: {
    queue: any
    status_text: string
    position_in_queue: number | null
    estimated_wait_time: number | null
  } | null
}

async function handleSearch() {
  if (!searchQuery.value.trim()) return

  isLoading.value = true
  notFound.value = false
  queue.value = null

  router.replace({ query: { nomor: searchQuery.value.toUpperCase() } })

  try {
    const response = await $fetch<StatusResponse>(
      `${config.public.apiBase}/queue/status/${searchQuery.value.toUpperCase()}`
    )

    if (!response.data) {
      notFound.value = true
      return
    }

    queue.value = response.data.queue
    queuePosition.value = response.data.position_in_queue || 0
    estimatedTime.value = response.data.estimated_wait_time || (queuePosition.value * 5)
  } catch {
    notFound.value = true
  } finally {
    isLoading.value = false
  }
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  if (searchQuery.value) {
    handleSearch()
  }
})
</script>
