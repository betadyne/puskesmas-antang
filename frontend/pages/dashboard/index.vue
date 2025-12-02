<template>
  <div>
    <NuxtLayout name="dashboard">
      <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div
            class="card"
            :class="currentQueue ? 'bg-gradient-to-br from-primary-50 to-white border-primary-200' : ''"
          >
            <div class="flex items-center justify-between mb-6">
              <h3 class="font-display font-bold text-xl text-gray-800">
                Sedang Dilayani
              </h3>
              <span v-if="currentQueue" class="badge-success">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse mr-2"></span>
                Aktif
              </span>
            </div>

            <Transition
              mode="out-in"
              enter-active-class="transition duration-300 ease-out"
              enter-from-class="opacity-0 scale-95"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition duration-200 ease-in"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95"
            >
              <div v-if="currentQueue" class="text-center py-6">
                <p class="text-sm text-gray-500 mb-2">Nomor Antrian</p>
                <div class="queue-number text-primary-600 mb-4">
                  {{ currentQueue.nomor_antrean }}
                </div>

                <div class="flex items-center justify-center gap-3 mb-8">
                  <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                    <UIcon name="i-heroicons-user" class="w-6 h-6 text-primary-600" />
                  </div>
                  <div class="text-left">
                    <p class="font-display font-semibold text-gray-800">
                      {{ currentQueue.patient?.nama }}
                    </p>
                    <p class="text-sm text-gray-500">
                      NIK: {{ currentQueue.patient?.nik }}
                    </p>
                  </div>
                </div>

                <div class="flex flex-wrap justify-center gap-3">
                  <button
                    @click="handleFinish"
                    :disabled="isProcessing"
                    class="btn-primary"
                  >
                    <UIcon name="i-heroicons-check-circle" class="w-5 h-5" />
                    Selesai
                  </button>
                  <button
                    @click="handleRecall"
                    :disabled="isProcessing"
                    class="btn-secondary"
                  >
                    <UIcon name="i-heroicons-speaker-wave" class="w-5 h-5" />
                    Panggil Ulang
                  </button>
                  <button
                    @click="handleSkip"
                    :disabled="isProcessing"
                    class="inline-flex items-center gap-2 px-6 py-3 font-display font-semibold text-red-600 bg-red-50 border-2 border-red-200 rounded-2xl hover:bg-red-100 transition-all"
                  >
                    <UIcon name="i-heroicons-forward" class="w-5 h-5" />
                    Lewati
                  </button>
                </div>
              </div>

              <div v-else class="text-center py-12">
                <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                  <UIcon name="i-heroicons-user-group" class="w-10 h-10 text-gray-400" />
                </div>
                <p class="text-gray-500 mb-6">Belum ada pasien yang dilayani</p>
                <button
                  @click="handleCallNext"
                  :disabled="isProcessing || queueStore.waitingList.length === 0"
                  class="btn-primary"
                >
                  <UIcon name="i-heroicons-megaphone" class="w-5 h-5" />
                  Panggil Berikutnya
                </button>
              </div>
            </Transition>
          </div>

          <div class="card">
            <div class="flex items-center justify-between mb-6">
              <h3 class="font-display font-bold text-xl text-gray-800">
                Daftar Menunggu
              </h3>
              <span class="badge-info">
                {{ queueStore.waitingList.length }} antrian
              </span>
            </div>

            <div class="space-y-3 max-h-96 overflow-y-auto">
              <TransitionGroup name="list">
                <div
                  v-for="(q, index) in queueStore.waitingList"
                  :key="q.id"
                  class="flex items-center justify-between p-4 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors"
                >
                  <div class="flex items-center gap-4">
                    <span class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center font-display font-semibold text-gray-600">
                      {{ index + 1 }}
                    </span>
                    <div>
                      <p class="font-mono text-xl font-bold text-gray-800">
                        {{ q.nomor_antrean }}
                      </p>
                      <p class="text-sm text-gray-500">{{ q.patient?.nama }}</p>
                    </div>
                  </div>
                  <button
                    @click="handleCallSpecific(q.id)"
                    :disabled="isProcessing"
                    class="btn-secondary py-2 px-4 text-sm"
                  >
                    <UIcon name="i-heroicons-megaphone" class="w-4 h-4" />
                    Panggil
                  </button>
                </div>
              </TransitionGroup>

              <div v-if="queueStore.waitingList.length === 0" class="text-center py-8">
                <UIcon name="i-heroicons-inbox" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                <p class="text-gray-500">Tidak ada antrian menunggu</p>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <div class="card bg-gradient-to-br from-primary-500 to-primary-600 text-white">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                <UIcon name="i-heroicons-clock" class="w-6 h-6" />
              </div>
              <div>
                <p class="text-primary-100 text-sm">Sisa Antrian</p>
                <p class="font-display font-bold text-3xl">{{ queueStore.stats.total_waiting }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                <UIcon name="i-heroicons-check-badge" class="w-6 h-6 text-green-600" />
              </div>
              <div>
                <p class="text-gray-500 text-sm">Terlayani Hari Ini</p>
                <p class="font-display font-bold text-3xl text-gray-800">{{ queueStore.stats.total_served }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                <UIcon name="i-heroicons-forward" class="w-6 h-6 text-amber-600" />
              </div>
              <div>
                <p class="text-gray-500 text-sm">Dilewati</p>
                <p class="font-display font-bold text-3xl text-gray-800">{{ queueStore.stats.total_skipped }}</p>
              </div>
            </div>
          </div>

          <div class="card">
            <h4 class="font-display font-semibold text-gray-800 mb-4">Riwayat Terakhir</h4>
            <div class="space-y-2">
              <div
                v-for="h in recentHistory"
                :key="h.id"
                class="flex items-center justify-between p-3 rounded-lg bg-gray-50"
              >
                <div class="flex items-center gap-3">
                  <span class="font-mono font-bold text-gray-700">{{ h.nomor_antrean }}</span>
                  <span class="text-sm text-gray-500">{{ h.patient?.nama }}</span>
                </div>
                <span class="text-xs text-gray-400">
                  {{ formatTime(h.finished_at) }}
                </span>
              </div>

              <div v-if="recentHistory.length === 0" class="text-center py-4">
                <p class="text-sm text-gray-500">Belum ada riwayat</p>
              </div>
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
  title: 'Dashboard Antrian - Puskesmas Antang',
})

const toast = useToast()
const queueStore = useQueueStore()
const authStore = useAuthStore()
const { subscribeToQueueChannel, disconnect } = useEcho()

const isProcessing = ref(false)

const currentQueue = computed(() => queueStore.currentQueue)
const recentHistory = computed(() => queueStore.historyList.slice(0, 5))

async function handleCallNext() {
  isProcessing.value = true
  try {
    await queueStore.callNext()
    toast.add({
      title: 'Berhasil',
      description: 'Pasien berikutnya telah dipanggil',
      color: 'green',
    })
  } catch (err: any) {
    toast.add({
      title: 'Gagal',
      description: err.message,
      color: 'red',
    })
  } finally {
    isProcessing.value = false
  }
}

async function handleCallSpecific(queueId: number) {
  isProcessing.value = true
  try {
    await queueStore.callSpecific(queueId)
    toast.add({
      title: 'Berhasil',
      description: 'Pasien telah dipanggil',
      color: 'green',
    })
  } catch (err: any) {
    toast.add({
      title: 'Gagal',
      description: err.message,
      color: 'red',
    })
  } finally {
    isProcessing.value = false
  }
}

async function handleRecall() {
  if (!currentQueue.value) return

  isProcessing.value = true
  try {
    await queueStore.recall(currentQueue.value.id)
    toast.add({
      title: 'Berhasil',
      description: 'Pasien dipanggil ulang',
      color: 'green',
    })
  } catch (err: any) {
    toast.add({
      title: 'Gagal',
      description: err.message,
      color: 'red',
    })
  } finally {
    isProcessing.value = false
  }
}

async function handleFinish() {
  if (!currentQueue.value) return

  isProcessing.value = true
  try {
    await queueStore.finish(currentQueue.value.id)
    toast.add({
      title: 'Berhasil',
      description: 'Pelayanan selesai',
      color: 'green',
    })
  } catch (err: any) {
    toast.add({
      title: 'Gagal',
      description: err.message,
      color: 'red',
    })
  } finally {
    isProcessing.value = false
  }
}

async function handleSkip() {
  if (!currentQueue.value) return

  isProcessing.value = true
  try {
    await queueStore.skip(currentQueue.value.id)
    toast.add({
      title: 'Dilewati',
      description: 'Pasien dilewati',
      color: 'amber',
    })
  } catch (err: any) {
    toast.add({
      title: 'Gagal',
      description: err.message,
      color: 'red',
    })
  } finally {
    isProcessing.value = false
  }
}

function formatTime(dateStr: string | undefined): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(async () => {
  await queueStore.fetchQueues()

  if (authStore.userPoli?.id) {
    subscribeToQueueChannel(authStore.userPoli.id)
  }
})

onUnmounted(() => {
  disconnect()
})
</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}
</style>
