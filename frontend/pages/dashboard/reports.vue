<template>
  <div>
    <NuxtLayout name="dashboard">
      <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="font-display text-2xl font-bold text-gray-800">Laporan & Statistik</h1>
            <p class="text-gray-500">Ringkasan pelayanan antrian hari ini</p>
          </div>

          <div class="flex items-center gap-3">
            <UInput
              v-model="selectedDate"
              type="date"
              class="w-44"
            />
            <button @click="fetchReports" class="btn-primary py-2 px-4">
              <UIcon name="i-heroicons-arrow-path" class="w-4 h-4" />
              Refresh
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="card">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center">
                <UIcon name="i-heroicons-user-group" class="w-7 h-7 text-blue-600" />
              </div>
              <div>
                <p class="text-sm text-gray-500">Total Antrian</p>
                <p class="font-display text-3xl font-bold text-gray-800">{{ stats.total_queues }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center">
                <UIcon name="i-heroicons-check-badge" class="w-7 h-7 text-green-600" />
              </div>
              <div>
                <p class="text-sm text-gray-500">Terlayani</p>
                <p class="font-display text-3xl font-bold text-gray-800">{{ stats.total_served }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center">
                <UIcon name="i-heroicons-clock" class="w-7 h-7 text-amber-600" />
              </div>
              <div>
                <p class="text-sm text-gray-500">Menunggu</p>
                <p class="font-display text-3xl font-bold text-gray-800">{{ stats.total_waiting }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center">
                <UIcon name="i-heroicons-forward" class="w-7 h-7 text-red-600" />
              </div>
              <div>
                <p class="text-sm text-gray-500">Dilewati</p>
                <p class="font-display text-3xl font-bold text-gray-800">{{ stats.total_skipped }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
          <div class="card">
            <h3 class="font-display font-bold text-lg text-gray-800 mb-4">
              Statistik Per Poli
            </h3>

            <div class="space-y-4">
              <div v-if="isLoading" class="text-center py-8">
                <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 text-gray-300 animate-spin mx-auto mb-2" />
                <p class="text-gray-500">Memuat data...</p>
              </div>

              <template v-else-if="poliStats.length > 0">
                <div
                  v-for="poli in poliStats"
                  :key="poli.poli?.id"
                  class="flex items-center justify-between p-4 rounded-xl bg-gray-50"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                      <span class="font-mono font-bold text-primary-600 text-sm">{{ poli.poli?.kode_poli }}</span>
                    </div>
                    <span class="font-display font-medium text-gray-800">{{ poli.poli?.nama_poli }}</span>
                  </div>
                  <div class="flex items-center gap-4 text-sm">
                    <span class="text-gray-500">
                      {{ poli.statistics?.total || 0 }} antrian
                    </span>
                    <div class="w-32 h-2 rounded-full bg-gray-200 overflow-hidden">
                      <div
                        class="h-full bg-primary-500 rounded-full"
                        :style="{ width: `${getCompletionRate(poli)}%` }"
                      ></div>
                    </div>
                    <span class="font-medium text-primary-600">
                      {{ getCompletionRate(poli) }}%
                    </span>
                  </div>
                </div>
              </template>

              <div v-else class="text-center py-8">
                <UIcon name="i-heroicons-inbox" class="w-10 h-10 text-gray-300 mx-auto mb-2" />
                <p class="text-gray-500">Belum ada data statistik</p>
              </div>
            </div>
          </div>

          <div class="card">
            <h3 class="font-display font-bold text-lg text-gray-800 mb-4">
              Waktu Layanan Rata-rata
            </h3>

            <div class="text-center py-8">
              <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-gradient-to-br from-primary-100 to-primary-50 mb-4">
                <div class="text-center">
                  <p class="font-mono text-4xl font-bold text-primary-600">
                    {{ stats.avg_service_time || 0 }}
                  </p>
                  <p class="text-sm text-primary-500">menit</p>
                </div>
              </div>
              <p class="text-gray-500">Rata-rata waktu pelayanan per pasien</p>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-100">
              <div class="text-center">
                <p class="text-sm text-gray-500 mb-1">Tercepat</p>
                <p class="font-display font-bold text-green-600">{{ stats.min_service_time || 0 }}m</p>
              </div>
              <div class="text-center">
                <p class="text-sm text-gray-500 mb-1">Rata-rata</p>
                <p class="font-display font-bold text-primary-600">{{ stats.avg_service_time || 0 }}m</p>
              </div>
              <div class="text-center">
                <p class="text-sm text-gray-500 mb-1">Terlama</p>
                <p class="font-display font-bold text-amber-600">{{ stats.max_service_time || 0 }}m</p>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <h3 class="font-display font-bold text-lg text-gray-800">
              Riwayat Antrian Hari Ini
            </h3>
            <span class="badge-info">{{ historyList.length }} data</span>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-100">
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">No. Antrian</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Pasien</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Poli</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Status</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Waktu</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="isLoading">
                  <td colspan="5" class="py-8 text-center text-gray-500">
                    <UIcon name="i-heroicons-arrow-path" class="w-6 h-6 text-gray-300 animate-spin mx-auto mb-2" />
                    Memuat data...
                  </td>
                </tr>

                <template v-else-if="historyList.length > 0">
                  <tr
                    v-for="(q, index) in historyList"
                    :key="index"
                    class="border-b border-gray-50 hover:bg-gray-50"
                  >
                    <td class="py-3 px-4">
                      <span class="font-mono font-bold text-gray-800">{{ q.nomor_antrean }}</span>
                    </td>
                    <td class="py-3 px-4 text-gray-600">{{ q.pasien || '-' }}</td>
                    <td class="py-3 px-4 text-gray-600">{{ q.poli || '-' }}</td>
                    <td class="py-3 px-4">
                      <span :class="getStatusBadge(q.status)">{{ getStatusText(q.status) }}</span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ q.finished_at || q.created_at }}</td>
                  </tr>
                </template>

                <tr v-else>
                  <td colspan="5" class="py-8 text-center text-gray-500">
                    <UIcon name="i-heroicons-inbox" class="w-10 h-10 text-gray-300 mx-auto mb-2" />
                    Belum ada data riwayat
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: 'auth',
})

useHead({
  title: 'Laporan - Puskesmas Antang',
})

const config = useRuntimeConfig()
const authStore = useAuthStore()
const toast = useToast()

const selectedDate = ref(new Date().toISOString().split('T')[0])
const isLoading = ref(false)

const stats = ref({
  total_queues: 0,
  total_served: 0,
  total_waiting: 0,
  total_skipped: 0,
  avg_service_time: 0,
  min_service_time: 0,
  max_service_time: 0,
})

interface PoliStat {
  poli: {
    id: number
    nama_poli: string
    kode_poli: string
  }
  statistics: {
    total: number
    finished: number
    skipped: number
    avg_wait_time: number
  }
}

interface QueueHistory {
  nomor_antrean: string
  pasien: string | null
  poli: string | null
  status: string
  wait_time: number | null
  service_time: number | null
  created_at: string
  called_at: string | null
  served_at: string | null
  finished_at: string | null
}

const poliStats = ref<PoliStat[]>([])
const historyList = ref<QueueHistory[]>([])

async function fetchReports() {
  isLoading.value = true
  try {
    const response = await $fetch<{
      message: string
      data: {
        statistics: {
          total_queues: number
          total_waiting: number
          total_called: number
          total_being_served: number
          total_finished: number
          total_skipped: number
          avg_service_time: number
          min_service_time: number
          max_service_time: number
          avg_wait_time: number
          min_wait_time: number
          max_wait_time: number
        }
        by_poli: Record<string, PoliStat>
        queues: QueueHistory[]
      }
    }>(`${config.public.apiBase}/reports/daily`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
      params: {
        date: selectedDate.value,
      },
    })

    if (response.data) {
      stats.value = {
        total_queues: response.data.statistics.total_queues,
        total_served: response.data.statistics.total_finished,
        total_waiting: response.data.statistics.total_waiting,
        total_skipped: response.data.statistics.total_skipped,
        avg_service_time: response.data.statistics.avg_service_time,
        min_service_time: response.data.statistics.min_service_time,
        max_service_time: response.data.statistics.max_service_time,
      }
      poliStats.value = Object.values(response.data.by_poli || {})
      historyList.value = response.data.queues || []
    }
  } catch (error: any) {
    console.error('Error fetching reports:', error)
    toast.add({
      title: 'Error',
      description: 'Gagal memuat data laporan',
      color: 'red',
    })
  } finally {
    isLoading.value = false
  }
}

function getStatusText(status: string): string {
  const map: Record<string, string> = {
    menunggu: 'Menunggu',
    dipanggil: 'Dipanggil',
    'sedang dilayani': 'Dilayani',
    selesai: 'Selesai',
    dilewati: 'Dilewati',
  }
  return map[status] || status
}

function getStatusBadge(status: string): string {
  const map: Record<string, string> = {
    menunggu: 'badge-warning',
    dipanggil: 'badge-info',
    'sedang dilayani': 'badge-info',
    selesai: 'badge-success',
    dilewati: 'badge-danger',
  }
  return map[status] || 'badge'
}

function getCompletionRate(poli: PoliStat): number {
  const total = poli.statistics?.total || 0
  const finished = poli.statistics?.finished || 0
  if (total === 0) return 0
  return Math.round((finished / total) * 100)
}

onMounted(() => {
  fetchReports()
})

watch(selectedDate, () => {
  fetchReports()
})
</script>
