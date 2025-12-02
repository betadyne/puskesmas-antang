<template>
  <div class="min-h-screen bg-gradient-to-br from-white via-healthcare-mint/20 to-primary-50/30">
    <header class="sticky top-0 z-50 glass border-b border-gray-100/50">
      <nav class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
          <NuxtLink to="/" class="flex items-center gap-3 group">
            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-lg shadow-primary-500/30 group-hover:shadow-xl group-hover:shadow-primary-500/40 transition-all duration-300 overflow-hidden">
              <img src="/logo.png" alt="Logo Puskesmas Antang" class="w-9 h-9 object-contain" />
            </div>
            <div>
              <span class="font-display font-bold text-xl text-gray-800">Puskesmas</span>
              <span class="font-display font-bold text-xl text-primary-600"> Antang</span>
            </div>
          </NuxtLink>

          <div class="hidden md:flex items-center gap-8">
            <NuxtLink to="/" class="nav-link" active-class="nav-link-active">
              Beranda
            </NuxtLink>
            <NuxtLink to="/register" class="nav-link" active-class="nav-link-active">
              Daftar Antrian
            </NuxtLink>
            <NuxtLink to="/status" class="nav-link" active-class="nav-link-active">
              Cek Status
            </NuxtLink>
          </div>

          <div class="flex items-center gap-4">
            <NuxtLink to="/login" class="btn-primary">
              <UIcon name="i-heroicons-user-circle" class="w-5 h-5" />
              <span class="hidden sm:inline">Login Petugas</span>
            </NuxtLink>

            <button
              class="md:hidden p-2 rounded-xl hover:bg-gray-100 transition-colors"
              @click="isMenuOpen = !isMenuOpen"
            >
              <UIcon :name="isMenuOpen ? 'i-heroicons-x-mark' : 'i-heroicons-bars-3'" class="w-6 h-6" />
            </button>
          </div>
        </div>

        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 -translate-y-2"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-2"
        >
          <div v-if="isMenuOpen" class="md:hidden py-4 border-t border-gray-100">
            <div class="flex flex-col gap-2">
              <NuxtLink
                to="/"
                class="px-4 py-3 rounded-xl hover:bg-primary-50 font-display font-medium text-gray-700"
                @click="isMenuOpen = false"
              >
                Beranda
              </NuxtLink>
              <NuxtLink
                to="/register"
                class="px-4 py-3 rounded-xl hover:bg-primary-50 font-display font-medium text-gray-700"
                @click="isMenuOpen = false"
              >
                Daftar Antrian
              </NuxtLink>
              <NuxtLink
                to="/status"
                class="px-4 py-3 rounded-xl hover:bg-primary-50 font-display font-medium text-gray-700"
                @click="isMenuOpen = false"
              >
                Cek Status
              </NuxtLink>
            </div>
          </div>
        </Transition>
      </nav>
    </header>

    <main>
      <slot />
    </main>

    <footer class="bg-gray-900 text-white mt-20">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div>
            <div class="flex items-center gap-3 mb-4">
              <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center overflow-hidden">
                <img src="/logo.png" alt="Logo Puskesmas Antang" class="w-8 h-8 object-contain" />
              </div>
              <span class="font-display font-bold text-lg">Puskesmas Antang</span>
            </div>
            <p class="text-gray-400 text-sm leading-relaxed">
              Melayani kesehatan masyarakat dengan sepenuh hati. Sistem antrian modern untuk pelayanan yang lebih baik.
            </p>
          </div>

          <div>
            <h4 class="font-display font-semibold mb-4">Jam Operasional</h4>
            <div class="space-y-2 text-gray-400 text-sm">
              <p>Senin - Kamis: 08:00 - 14:00</p>
              <p>Jumat: 08:00 - 11:00</p>
              <p>Sabtu: 08:00 - 12:00</p>
              <p>Minggu & Libur: Tutup</p>
            </div>
          </div>

          <div>
            <h4 class="font-display font-semibold mb-4">Kontak</h4>
            <div class="space-y-2 text-gray-400 text-sm">
              <p class="flex items-center gap-2">
                <UIcon name="i-heroicons-map-pin" class="w-4 h-4" />
                Jl. Antang Raya, Makassar
              </p>
              <p class="flex items-center gap-2">
                <UIcon name="i-heroicons-phone" class="w-4 h-4" />
                (0411) 123-4567
              </p>
            </div>
          </div>
        </div>

        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500 text-sm">
          <p>&copy; {{ new Date().getFullYear() }} Puskesmas Antang. Sistem Informasi Antrian Pasien.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
const isMenuOpen = ref(false)

const route = useRoute()
watch(() => route.path, () => {
  isMenuOpen.value = false
})
</script>
