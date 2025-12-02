<template>
  <div class="min-h-screen bg-gray-50">
    <aside
      class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-gray-200 transform transition-transform duration-300 lg:translate-x-0"
      :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex flex-col h-full">
        <div class="p-6 border-b border-gray-100">
          <NuxtLink to="/dashboard" class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-lg shadow-primary-500/30 overflow-hidden">
              <img src="/logo.png" alt="Logo Puskesmas Antang" class="w-9 h-9 object-contain" />
            </div>
            <div>
              <span class="font-display font-bold text-lg text-gray-800">Puskesmas</span>
              <p class="text-xs text-gray-500">Dashboard Petugas</p>
            </div>
          </NuxtLink>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
          <NuxtLink
            to="/dashboard"
            class="sidebar-link"
            active-class="sidebar-link-active"
            :class="{ 'sidebar-link-active': route.path === '/dashboard' }"
          >
            <UIcon name="i-heroicons-queue-list" class="w-5 h-5" />
            <span>Antrian</span>
          </NuxtLink>

          <NuxtLink
            to="/dashboard/reports"
            class="sidebar-link"
            active-class="sidebar-link-active"
          >
            <UIcon name="i-heroicons-chart-bar" class="w-5 h-5" />
            <span>Laporan</span>
          </NuxtLink>

          <template v-if="authStore.isAdmin">
            <div class="pt-4 pb-2">
              <p class="px-4 text-xs font-display font-semibold text-gray-400 uppercase tracking-wider">
                Administrasi
              </p>
            </div>

            <NuxtLink
              to="/admin/users"
              class="sidebar-link"
              active-class="sidebar-link-active"
            >
              <UIcon name="i-heroicons-users" class="w-5 h-5" />
              <span>Kelola User</span>
            </NuxtLink>

            <NuxtLink
              to="/admin/poli"
              class="sidebar-link"
              active-class="sidebar-link-active"
            >
              <UIcon name="i-heroicons-building-office-2" class="w-5 h-5" />
              <span>Kelola Poli</span>
            </NuxtLink>

            <NuxtLink
              to="/admin/patients"
              class="sidebar-link"
              active-class="sidebar-link-active"
            >
              <UIcon name="i-heroicons-user-group" class="w-5 h-5" />
              <span>Data Pasien</span>
            </NuxtLink>
          </template>
        </nav>

        <div class="p-4 border-t border-gray-100">
          <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50">
            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
              <UIcon name="i-heroicons-user" class="w-5 h-5 text-primary-600" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-display font-medium text-sm text-gray-800 truncate">
                {{ authStore.user?.name }}
              </p>
              <p class="text-xs text-gray-500 truncate">
                {{ authStore.userPoli?.nama_poli || (authStore.isAdmin ? 'Admin' : 'Petugas') }}
              </p>
            </div>
            <button
              @click="handleLogout"
              class="p-2 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors"
              title="Logout"
            >
              <UIcon name="i-heroicons-arrow-right-on-rectangle" class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </aside>

    <div
      v-if="isSidebarOpen"
      class="fixed inset-0 z-40 bg-black/50 lg:hidden"
      @click="isSidebarOpen = false"
    ></div>

    <div class="lg:pl-72">
      <header class="sticky top-0 z-30 h-16 bg-white/80 backdrop-blur-lg border-b border-gray-100">
        <div class="flex items-center justify-between h-full px-4 lg:px-8">
          <button
            class="lg:hidden p-2 rounded-xl hover:bg-gray-100 transition-colors"
            @click="isSidebarOpen = !isSidebarOpen"
          >
            <UIcon name="i-heroicons-bars-3" class="w-6 h-6" />
          </button>

          <div class="hidden lg:block">
            <h2 class="font-display font-semibold text-gray-800">{{ pageTitle }}</h2>
          </div>

          <div class="flex items-center gap-4">
            <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-50 text-primary-700">
              <UIcon name="i-heroicons-building-office-2" class="w-4 h-4" />
              <span class="font-display font-medium text-sm">
                {{ authStore.userPoli?.nama_poli || 'Semua Poli' }}
              </span>
            </div>

            <UDropdown
              :items="userMenuItems"
              :popper="{ placement: 'bottom-end' }"
            >
              <button class="flex items-center gap-2 p-2 rounded-xl hover:bg-gray-100 transition-colors">
                <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center">
                  <span class="text-white font-display font-semibold text-sm">
                    {{ authStore.user?.name?.charAt(0) }}
                  </span>
                </div>
                <UIcon name="i-heroicons-chevron-down" class="w-4 h-4 text-gray-500" />
              </button>
            </UDropdown>
          </div>
        </div>
      </header>

      <main class="p-4 lg:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const isSidebarOpen = ref(false)

const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    '/dashboard': 'Manajemen Antrian',
    '/dashboard/reports': 'Laporan & Statistik',
    '/admin/users': 'Kelola Pengguna',
    '/admin/poli': 'Kelola Poli',
    '/admin/patients': 'Data Pasien',
  }
  return titles[route.path] || 'Dashboard'
})

const userMenuItems = [
  [
    {
      label: 'Profil Saya',
      icon: 'i-heroicons-user-circle',
      click: () => router.push('/dashboard/profile'),
    },
  ],
  [
    {
      label: 'Logout',
      icon: 'i-heroicons-arrow-right-on-rectangle',
      click: () => handleLogout(),
    },
  ],
]

function handleLogout() {
  authStore.logout()
}

watch(() => route.path, () => {
  isSidebarOpen.value = false
})

onMounted(async () => {
  await authStore.initAuth()
  if (!authStore.isAuthenticated) {
    router.push('/login')
  }
})
</script>
