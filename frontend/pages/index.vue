<template>
  <div>
    <NuxtLayout name="public">
      <section class="relative overflow-hidden">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
          <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="relative z-10">
              <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-100 text-primary-700 mb-6">
                <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                <span class="font-display font-medium text-sm">Sistem Antrian Digital</span>
              </div>

              <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                Layanan Kesehatan
                <span class="text-gradient">Lebih Cepat</span>
                & Teratur
              </h1>

              <p class="text-lg text-gray-600 leading-relaxed mb-8 max-w-xl">
                Daftar antrian secara online, pantau status real-time, dan nikmati pelayanan kesehatan tanpa perlu menunggu lama di Puskesmas Antang.
              </p>

              <div class="flex flex-wrap gap-4 mb-12">
                <NuxtLink to="/register" class="btn-primary text-lg px-8 py-4">
                  <UIcon name="i-heroicons-clipboard-document-list" class="w-5 h-5" />
                  Daftar Antrian
                </NuxtLink>
                <NuxtLink to="/status" class="btn-secondary text-lg px-8 py-4">
                  <UIcon name="i-heroicons-magnifying-glass" class="w-5 h-5" />
                  Cek Status
                </NuxtLink>
              </div>

              <p class="text-sm text-gray-500">
                Tidak perlu registrasi akun. Cukup isi data diri dan dapatkan nomor antrian instan.
              </p>
            </div>

            <div class="relative hidden lg:block">
              <div class="absolute -top-10 -right-10 w-72 h-72 bg-primary-200 rounded-full blur-3xl opacity-50"></div>
              <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-accent-200 rounded-full blur-3xl opacity-50"></div>

              <div class="relative bg-white rounded-4xl p-8 shadow-2xl border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                  <div>
                    <p class="text-sm text-gray-500 mb-1">Jumlah Antrian Hari Ini</p>
                    <p class="font-display font-bold text-2xl text-gray-800">Per Poli</p>
                  </div>
                  <div class="w-16 h-16 rounded-2xl bg-primary-500 flex items-center justify-center shadow-glow">
                    <UIcon name="i-heroicons-queue-list" class="w-8 h-8 text-white" />
                  </div>
                </div>

                <div class="space-y-3 max-h-80 overflow-y-auto">
                  <div v-if="poliStats.length === 0" class="text-center py-8 text-gray-500">
                    <UIcon name="i-heroicons-inbox" class="w-10 h-10 mx-auto mb-2 text-gray-300" />
                    <p class="text-sm">Memuat data...</p>
                  </div>
                  
                  <div
                    v-for="poli in poliStats"
                    :key="poli.id"
                    class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 hover:bg-gray-100 transition-colors"
                  >
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center">
                        <span class="font-mono font-bold text-primary-600 text-sm">{{ poli.kode_poli }}</span>
                      </div>
                      <div>
                        <span class="font-display font-medium text-gray-800 block">{{ poli.nama_poli }}</span>
                        <span v-if="poli.current_queue" class="text-xs text-green-600">
                          Sedang dilayani: {{ poli.current_queue }}
                        </span>
                      </div>
                    </div>
                    <div class="text-right">
                      <span class="font-mono font-bold text-2xl text-primary-600">{{ poli.waiting_count }}</span>
                      <p class="text-xs text-gray-500">menunggu</p>
                    </div>
                  </div>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-gradient-to-r from-primary-50 to-accent-50 border border-primary-100">
                  <div class="flex items-center gap-3">
                    <UIcon name="i-heroicons-information-circle" class="w-5 h-5 text-primary-600" />
                    <p class="text-sm text-gray-700">
                      <span class="font-semibold">Total:</span> {{ totalWaiting }} pasien menunggu
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="py-16 bg-white border-y border-gray-100">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div class="stat-card">
              <span class="stat-number">{{ stats.cities }}+</span>
              <span class="stat-label">Kecamatan Terlayani</span>
            </div>
            <div class="stat-card">
              <span class="stat-number">{{ stats.patients }}+</span>
              <span class="stat-label">Pasien Terdaftar</span>
            </div>
            <div class="stat-card">
              <span class="stat-number">{{ stats.poli }}+</span>
              <span class="stat-label">Poli Tersedia</span>
            </div>
            <div class="stat-card">
              <span class="stat-number">{{ stats.satisfaction }}%</span>
              <span class="stat-label">Tingkat Kepuasan</span>
            </div>
          </div>
        </div>
      </section>

      <section class="py-20">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center mb-16">
            <span class="inline-block px-4 py-2 rounded-full bg-accent-100 text-accent-700 font-display font-medium text-sm mb-4">
              Fitur Unggulan
            </span>
            <h2 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
              Kenapa Memilih Sistem Antrian Kami?
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
              Dirancang khusus untuk memberikan pengalaman pelayanan kesehatan yang lebih baik bagi masyarakat.
            </p>
          </div>

          <div class="grid md:grid-cols-3 gap-8">
            <div class="card-hover group">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                <UIcon name="i-heroicons-device-phone-mobile" class="w-7 h-7 text-white" />
              </div>
              <h3 class="font-display font-bold text-xl text-gray-900 mb-3">Daftar Online</h3>
              <p class="text-gray-600 leading-relaxed">
                Daftar antrian dari mana saja menggunakan smartphone. Tidak perlu datang pagi-pagi ke puskesmas.
              </p>
            </div>

            <div class="card-hover group">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-accent-500 to-accent-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                <UIcon name="i-heroicons-signal" class="w-7 h-7 text-white" />
              </div>
              <h3 class="font-display font-bold text-xl text-gray-900 mb-3">Real-time Update</h3>
              <p class="text-gray-600 leading-relaxed">
                Pantau posisi antrian secara real-time. Dapatkan notifikasi saat giliran Anda hampir tiba.
              </p>
            </div>

            <div class="card-hover group">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-healthcare-coral to-red-400 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                <UIcon name="i-heroicons-clock" class="w-7 h-7 text-white" />
              </div>
              <h3 class="font-display font-bold text-xl text-gray-900 mb-3">Hemat Waktu</h3>
              <p class="text-gray-600 leading-relaxed">
                Tidak perlu menunggu berjam-jam. Datang sesuai estimasi waktu yang diberikan sistem.
              </p>
            </div>
          </div>
        </div>
      </section>

      <section class="py-20 bg-gradient-to-br from-primary-600 to-primary-700">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-6">
            Siap Mendaftar Antrian?
          </h2>
          <p class="text-primary-100 text-lg mb-8 max-w-2xl mx-auto">
            Proses pendaftaran hanya membutuhkan waktu kurang dari 1 menit. Dapatkan nomor antrian Anda sekarang!
          </p>
          <NuxtLink to="/register" class="inline-flex items-center gap-3 px-8 py-4 bg-white text-primary-700 font-display font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
            <UIcon name="i-heroicons-rocket-launch" class="w-6 h-6" />
            Mulai Daftar Sekarang
          </NuxtLink>
        </div>
      </section>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
})

useHead({
  title: 'Puskesmas Antang - Sistem Antrian Online',
})

const config = useRuntimeConfig()

interface PoliStat {
  id: number
  kode_poli: string
  nama_poli: string
  waiting_count: number
  current_queue: string | null
}

const stats = ref({
  cities: 5,
  patients: 1500,
  poli: 8,
  satisfaction: 98,
})

const poliStats = ref<PoliStat[]>([])

const totalWaiting = computed(() => {
  return poliStats.value.reduce((sum, poli) => sum + poli.waiting_count, 0)
})

async function fetchPoliStats() {
  try {
    const response = await $fetch<{ message: string; data: PoliStat[] }>(
      `${config.public.apiBase}/poli/queue-stats`
    )
    if (response.data) {
      poliStats.value = response.data
      stats.value.poli = response.data.length
    }
  } catch (error) {
    console.error('Error fetching poli stats:', error)
  }
}

onMounted(() => {
  fetchPoliStats()
  
  // Refresh every 30 seconds
  const interval = setInterval(fetchPoliStats, 30000)
  onUnmounted(() => clearInterval(interval))
})
</script>
