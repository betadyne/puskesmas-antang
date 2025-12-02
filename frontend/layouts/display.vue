<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white overflow-hidden">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-0 left-1/4 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-accent-500/10 rounded-full blur-3xl"></div>
    </div>

    <header class="relative z-10 px-8 py-4 flex items-center justify-between border-b border-white/10">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-white flex items-center justify-center shadow-glow overflow-hidden">
          <img src="/logo.png" alt="Logo Puskesmas Antang" class="w-10 h-10 object-contain" />
        </div>
        <div>
          <h1 class="font-display font-bold text-2xl">Puskesmas Antang</h1>
          <p class="text-gray-400 text-sm">Sistem Antrian Digital</p>
        </div>
      </div>

      <div class="flex items-center gap-6">
        <div class="flex items-center gap-2" v-if="connectionStatus">
          <span
            class="w-3 h-3 rounded-full"
            :class="connectionStatus === 'connected' ? 'bg-green-500 animate-pulse' : 'bg-red-500'"
          ></span>
          <span class="text-sm text-gray-400">
            {{ connectionStatus === 'connected' ? 'Terhubung' : 'Terputus' }}
          </span>
        </div>
        <div class="text-right">
          <div class="font-mono text-3xl font-bold">{{ currentTime }}</div>
          <div class="text-sm text-gray-400">{{ currentDate }}</div>
        </div>
      </div>
    </header>

    <main class="relative z-10 h-[calc(100vh-140px)]">
      <slot />
    </main>

    <footer class="relative z-10 h-16 border-t border-white/10 overflow-hidden">
      <div class="marquee-container h-full flex items-center">
        <div class="marquee-content font-display text-lg text-gray-300">
          <span class="mx-8">🏥 Selamat datang di Puskesmas Antang</span>
          <span class="mx-8">⏰ Jam Layanan: Senin-Sabtu, 08:00-14:00</span>
          <span class="mx-8">📞 Hotline: (0411) 123-4567</span>
          <span class="mx-8">💊 Jaga kesehatan Anda dan keluarga</span>
          <span class="mx-8">🩺 Layanan prima untuk masyarakat sehat</span>
          <span class="mx-8">🏥 Selamat datang di Puskesmas Antang</span>
          <span class="mx-8">⏰ Jam Layanan: Senin-Sabtu, 08:00-14:00</span>
          <span class="mx-8">📞 Hotline: (0411) 123-4567</span>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { useDateFormat, useNow, useFullscreen } from '@vueuse/core'

const now = useNow()
const currentTime = useDateFormat(now, 'HH:mm:ss')
const currentDate = useDateFormat(now, 'dddd, D MMMM YYYY', { locales: 'id-ID' })

const connectionStatus = inject<Ref<'connected' | 'disconnected'>>('connectionStatus', ref('connected'))

const { toggle: toggleFullscreen } = useFullscreen()

onMounted(() => {
  document.addEventListener('keydown', (e) => {
    if (e.key === 'f' || e.key === 'F') {
      toggleFullscreen()
    }
  })
})
</script>

<style scoped>
.marquee-container {
  width: 100%;
  overflow: hidden;
}

.marquee-content {
  display: inline-block;
  white-space: nowrap;
  animation: marquee 30s linear infinite;
}

@keyframes marquee {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}
</style>
