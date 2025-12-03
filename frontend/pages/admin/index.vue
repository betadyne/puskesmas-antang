<template>
  <div>
    <NuxtLayout name="dashboard">
      <div class="space-y-6">
        <div>
          <h1 class="font-display text-2xl font-bold text-gray-800">Dashboard Admin</h1>
          <p class="text-gray-500">Ringkasan sistem antrian Puskesmas Antang</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="card bg-gradient-to-br from-primary-500 to-primary-600 text-white">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center">
                <UIcon name="i-heroicons-user-group" class="w-7 h-7" />
              </div>
              <div>
                <p class="text-primary-100 text-sm">Total Pasien</p>
                <p class="font-display text-3xl font-bold">{{ stats.total_patients }}</p>
              </div>
            </div>
          </div>

          <div class="card bg-gradient-to-br from-accent-500 to-accent-600 text-white">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center">
                <UIcon name="i-heroicons-building-office-2" class="w-7 h-7" />
              </div>
              <div>
                <p class="text-accent-100 text-sm">Total Poli</p>
                <p class="font-display text-3xl font-bold">{{ stats.total_poli }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center">
                <UIcon name="i-heroicons-check-badge" class="w-7 h-7 text-green-600" />
              </div>
              <div>
                <p class="text-gray-500 text-sm">Terlayani Hari Ini</p>
                <p class="font-display text-3xl font-bold text-gray-800">{{ stats.today_served }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center">
                <UIcon name="i-heroicons-users" class="w-7 h-7 text-amber-600" />
              </div>
              <div>
                <p class="text-gray-500 text-sm">Total Petugas</p>
                <p class="font-display text-3xl font-bold text-gray-800">{{ stats.total_users }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
          <div class="card">
            <h3 class="font-display font-bold text-lg text-gray-800 mb-4">Aktivitas Hari Ini</h3>

            <div class="space-y-3">
              <template v-if="!isLoading && recentActivities.length > 0">
                <div
                  v-for="activity in recentActivities"
                  :key="activity.id"
                  class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50"
                >
                  <div
                    class="w-10 h-10 rounded-xl flex items-center justify-center"
                    :class="getActivityColor(activity.type)"
                  >
                    <UIcon :name="getActivityIcon(activity.type)" class="w-5 h-5" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-800 truncate">{{ activity.message }}</p>
                    <p class="text-sm text-gray-500">{{ activity.time }}</p>
                  </div>
                </div>
              </template>

              <div v-if="isLoading" class="text-center py-8">
                <UIcon name="i-heroicons-arrow-path" class="w-8 h-8 text-gray-300 animate-spin mx-auto mb-2" />
                <p class="text-gray-500">Memuat data...</p>
              </div>
              
              <div v-else-if="recentActivities.length === 0" class="text-center py-8">
                <UIcon name="i-heroicons-inbox" class="w-10 h-10 text-gray-300 mx-auto mb-2" />
                <p class="text-gray-500">Belum ada aktivitas hari ini</p>
              </div>
            </div>
          </div>

          <div class="card">
            <h3 class="font-display font-bold text-lg text-gray-800 mb-4">Shortcut Menu</h3>

            <div class="grid grid-cols-2 gap-4">
              <NuxtLink
                to="/admin/users"
                class="p-4 rounded-xl bg-gray-50 hover:bg-primary-50 hover:border-primary-200 border-2 border-transparent transition-all group"
              >
                <UIcon name="i-heroicons-users" class="w-8 h-8 text-gray-400 group-hover:text-primary-600 mb-2" />
                <p class="font-display font-medium text-gray-700 group-hover:text-primary-700">Kelola User</p>
              </NuxtLink>

              <NuxtLink
                to="/admin/poli"
                class="p-4 rounded-xl bg-gray-50 hover:bg-primary-50 hover:border-primary-200 border-2 border-transparent transition-all group"
              >
                <UIcon name="i-heroicons-building-office-2" class="w-8 h-8 text-gray-400 group-hover:text-primary-600 mb-2" />
                <p class="font-display font-medium text-gray-700 group-hover:text-primary-700">Kelola Poli</p>
              </NuxtLink>

              <NuxtLink
                to="/admin/patients"
                class="p-4 rounded-xl bg-gray-50 hover:bg-primary-50 hover:border-primary-200 border-2 border-transparent transition-all group"
              >
                <UIcon name="i-heroicons-user-group" class="w-8 h-8 text-gray-400 group-hover:text-primary-600 mb-2" />
                <p class="font-display font-medium text-gray-700 group-hover:text-primary-700">Data Pasien</p>
              </NuxtLink>

              <NuxtLink
                to="/dashboard/reports"
                class="p-4 rounded-xl bg-gray-50 hover:bg-primary-50 hover:border-primary-200 border-2 border-transparent transition-all group"
              >
                <UIcon name="i-heroicons-chart-bar" class="w-8 h-8 text-gray-400 group-hover:text-primary-600 mb-2" />
                <p class="font-display font-medium text-gray-700 group-hover:text-primary-700">Laporan</p>
              </NuxtLink>
            </div>
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
  title: 'Dashboard Admin - Puskesmas Antang',
})

const config = useRuntimeConfig()
const authStore = useAuthStore()
const toast = useToast()

interface StatsData {
  total_patients: number
  total_poli: number
  total_users: number
  today_served: number
  today_waiting: number
  today_total: number
  recent_activities: Activity[]
}

interface Activity {
  id: number
  type: 'queue' | 'user' | 'poli'
  message: string
  time: string
}

const stats = ref({
  total_patients: 0,
  total_poli: 0,
  total_users: 0,
  today_served: 0,
})

const recentActivities = ref<Activity[]>([])
const isLoading = ref(true)

async function fetchStats() {
  isLoading.value = true
  try {
    const response = await $fetch<{ message: string; data: StatsData }>(
      `${config.public.apiBase}/admin/stats`,
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    )

    if (response.data) {
      stats.value = {
        total_patients: response.data.total_patients,
        total_poli: response.data.total_poli,
        total_users: response.data.total_users,
        today_served: response.data.today_served,
      }
      recentActivities.value = response.data.recent_activities || []
    }
  } catch (error: any) {
    console.error('Error fetching stats:', error)
    toast.add({
      title: 'Error',
      description: 'Gagal memuat data dashboard',
      color: 'red',
    })
  } finally {
    isLoading.value = false
  }
}

function getActivityColor(type: string): string {
  const colors: Record<string, string> = {
    queue: 'bg-primary-100 text-primary-600',
    user: 'bg-accent-100 text-accent-600',
    poli: 'bg-amber-100 text-amber-600',
  }
  return colors[type] || 'bg-gray-100 text-gray-600'
}

function getActivityIcon(type: string): string {
  const icons: Record<string, string> = {
    queue: 'i-heroicons-ticket',
    user: 'i-heroicons-user',
    poli: 'i-heroicons-building-office-2',
  }
  return icons[type] || 'i-heroicons-bell'
}

onMounted(() => {
  fetchStats()
  
  // Refresh setiap 30 detik
  const interval = setInterval(fetchStats, 30000)
  onUnmounted(() => clearInterval(interval))
})
</script>
