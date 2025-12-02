<template>
  <div>
    <NuxtLayout name="display">
      <div class="h-full grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
          <div
            class="flex-1 rounded-4xl p-8 flex flex-col items-center justify-center"
            :class="isBlinking ? 'bg-gradient-to-br from-primary-500 to-primary-600 animate-blink' : 'bg-gradient-to-br from-gray-800 to-gray-900'"
          >
            <p class="text-gray-400 text-xl mb-4 font-display">Nomor Antrian Saat Ini</p>

            <Transition
              mode="out-in"
              enter-active-class="transition duration-500 ease-out"
              enter-from-class="opacity-0 scale-90"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition duration-300 ease-in"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-90"
            >
              <div
                v-if="currentQueue"
                :key="currentQueue.nomor_antrean"
                class="text-center"
              >
                <div class="font-mono text-[12rem] font-bold leading-none text-white drop-shadow-2xl">
                  {{ currentQueue.nomor_antrean }}
                </div>
                <div class="mt-6 flex items-center justify-center gap-4">
                  <span class="px-6 py-3 rounded-2xl bg-white/20 text-white font-display font-semibold text-2xl backdrop-blur">
                    {{ currentQueue.patient?.nama || 'Pasien' }}
                  </span>
                </div>
              </div>

              <div v-else class="text-center">
                <div class="font-mono text-[10rem] font-bold leading-none text-gray-600">
                  ---
                </div>
                <p class="mt-6 text-2xl text-gray-500 font-display">Menunggu Panggilan</p>
              </div>
            </Transition>

            <div v-if="poliData" class="mt-8 text-center">
              <div class="inline-flex items-center gap-3 px-8 py-4 rounded-2xl bg-white/10 backdrop-blur">
                <UIcon name="i-heroicons-building-office-2" class="w-8 h-8 text-white" />
                <span class="font-display font-bold text-3xl text-white">
                  {{ poliData.nama_poli || poliData.name }}
                </span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-gray-800 to-gray-900 p-6 text-center">
              <p class="text-gray-400 text-sm mb-2 font-display">Menunggu</p>
              <p class="font-mono text-5xl font-bold text-amber-400">
                {{ stats.total_waiting }}
              </p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-gray-800 to-gray-900 p-6 text-center">
              <p class="text-gray-400 text-sm mb-2 font-display">Terlayani</p>
              <p class="font-mono text-5xl font-bold text-green-400">
                {{ stats.total_served }}
              </p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-gray-800 to-gray-900 p-6 text-center">
              <p class="text-gray-400 text-sm mb-2 font-display">Dilewati</p>
              <p class="font-mono text-5xl font-bold text-red-400">
                {{ stats.total_skipped }}
              </p>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-6">
          <div class="flex-1 rounded-4xl bg-gradient-to-br from-gray-800 to-gray-900 p-6 overflow-hidden">
            <div class="flex items-center gap-3 mb-6">
              <UIcon name="i-heroicons-clock" class="w-6 h-6 text-primary-400" />
              <h3 class="font-display font-bold text-xl text-white">Antrian Berikutnya</h3>
            </div>

            <div class="space-y-3 overflow-y-auto max-h-[calc(100%-80px)]">
              <TransitionGroup name="list">
                <div
                  v-for="(q, index) in nextQueue"
                  :key="q.id"
                  class="flex items-center justify-between p-4 rounded-2xl bg-gray-700/50 border border-gray-600/30"
                >
                  <div class="flex items-center gap-4">
                    <span
                      class="w-10 h-10 rounded-xl flex items-center justify-center font-display font-bold text-lg"
                      :class="index === 0 ? 'bg-primary-500 text-white' : 'bg-gray-600 text-gray-300'"
                    >
                      {{ index + 1 }}
                    </span>
                    <div>
                      <p class="font-mono text-2xl font-bold text-white">
                        {{ q.nomor_antrean }}
                      </p>
                      <p class="text-sm text-gray-400">{{ q.patient?.nama || 'Pasien' }}</p>
                    </div>
                  </div>
                  <span class="badge-warning">Menunggu</span>
                </div>
              </TransitionGroup>

              <div v-if="nextQueue.length === 0" class="text-center py-12">
                <UIcon name="i-heroicons-inbox" class="w-16 h-16 text-gray-600 mx-auto mb-4" />
                <p class="text-gray-500 font-display">Tidak ada antrian menunggu</p>
              </div>
            </div>
          </div>

          <div class="rounded-4xl bg-gradient-to-br from-gray-800 to-gray-900 p-6">
            <div class="flex items-center gap-3 mb-4">
              <UIcon name="i-heroicons-clock" class="w-5 h-5 text-gray-400" />
              <h3 class="font-display font-semibold text-gray-300">Riwayat Terakhir</h3>
            </div>

            <div class="space-y-2">
              <div
                v-for="h in recentHistory"
                :key="h.id"
                class="flex items-center justify-between p-3 rounded-xl bg-gray-700/30"
              >
                <span class="font-mono text-lg text-gray-300">{{ h.nomor_antrean }}</span>
                <span class="text-xs text-gray-500">{{ formatTime(h.finished_at) }}</span>
              </div>

              <div v-if="recentHistory.length === 0" class="text-center py-4">
                <p class="text-sm text-gray-600">Belum ada riwayat</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
import type { Queue, Poli, QueueStats, ApiResponse, WebsocketQueueEvent } from '~/types'

definePageMeta({
  layout: false,
})

const route = useRoute()
const config = useRuntimeConfig()
const { subscribeToDisplayChannel, disconnect } = useEcho()
const { speakQueueNumber, isSupported: ttsSupported } = useTTS()

const poliId = computed(() => Number(route.params.poli_id))

const poliData = ref<Poli | null>(null)
const currentQueue = ref<Queue | null>(null)
const nextQueue = ref<Queue[]>([])
const historyList = ref<Queue[]>([])
const stats = ref<QueueStats>({
  total_waiting: 0,
  total_served: 0,
  total_skipped: 0,
})

const isBlinking = ref(false)
const connectionStatus = ref<'connected' | 'disconnected'>('connected')

provide('connectionStatus', connectionStatus)

const recentHistory = computed(() => historyList.value.slice(0, 5))

useHead({
  title: computed(() => `Display Antrian - ${poliData.value?.nama_poli || poliData.value?.name || 'Poli'}`),
})

interface DisplayResponse {
  message: string
  data: {
    poli: Poli
    current_queue: any | null
    waiting_queues: any[]
    recent_queues: any[]
    statistics: {
      total_waiting: number
      total_served: number
      total_skipped: number
    }
  }
}

async function fetchDisplayData() {
  try {
    const response = await $fetch<DisplayResponse>(`${config.public.apiBase}/display/${poliId.value}`)

    if (response.data) {
      poliData.value = response.data.poli
      currentQueue.value = response.data.current_queue
      nextQueue.value = response.data.waiting_queues?.slice(0, 5) || []
      historyList.value = response.data.recent_queues || []
      stats.value = {
        total_waiting: response.data.statistics?.total_waiting || 0,
        total_served: response.data.statistics?.total_served || 0,
        total_skipped: response.data.statistics?.total_skipped || 0,
      }
    }
  } catch (error) {
    console.error('Error fetching display data:', error)
  }
}

function handleQueueCalled(event: WebsocketQueueEvent) {
  currentQueue.value = event.queue

  isBlinking.value = true
  setTimeout(() => {
    isBlinking.value = false
  }, 5000)

  playChime()

  if (ttsSupported.value && poliData.value) {
    const poliName = poliData.value.nama_poli || poliData.value.name || 'Poli'
    const queueNumber = event.queue.nomor_antrean || event.queue.nomor_antrian
    setTimeout(() => {
      speakQueueNumber(queueNumber, poliName)
    }, 1000)
  }

  nextQueue.value = nextQueue.value.filter(q => q.id !== event.queue.id)
  stats.value.total_waiting = Math.max(0, stats.value.total_waiting - 1)
}

function playChime() {
  try {
    const audio = new Audio('/sounds/chime.mp3')
    audio.volume = 0.7
    audio.play().catch(() => {})
  } catch {}
}

function formatTime(dateStr: string | undefined): string {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(async () => {
  await fetchDisplayData()

  subscribeToDisplayChannel(poliId.value, {
    onCalled: handleQueueCalled,
  })

  setInterval(fetchDisplayData, 30000)
})

onUnmounted(() => {
  disconnect()
})
</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}

.list-enter-from {
  opacity: 0;
  transform: translateX(-30px);
}

.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
