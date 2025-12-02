<template>
  <div>
    <NuxtLayout name="dashboard">
      <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="font-display text-2xl font-bold text-gray-800">Kelola Pengguna</h1>
            <p class="text-gray-500">Manajemen akun petugas dan admin</p>
          </div>

          <button @click="openCreateModal" class="btn-primary">
            <UIcon name="i-heroicons-plus" class="w-5 h-5" />
            Tambah User
          </button>
        </div>

        <div class="card">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-100">
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Nama</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Email</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Role</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Poli</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="user in users"
                  :key="user.id"
                  class="border-b border-gray-50 hover:bg-gray-50"
                >
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="font-display font-semibold text-primary-600">
                          {{ user.name.charAt(0) }}
                        </span>
                      </div>
                      <span class="font-medium text-gray-800">{{ user.name }}</span>
                    </div>
                  </td>
                  <td class="py-3 px-4 text-gray-600">{{ user.email }}</td>
                  <td class="py-3 px-4">
                    <span :class="user.roles?.includes('admin') ? 'badge-info' : 'badge-success'">
                      {{ user.roles?.includes('admin') ? 'Admin' : 'Petugas' }}
                    </span>
                  </td>
                  <td class="py-3 px-4 text-gray-600">{{ user.poli?.nama_poli || '-' }}</td>
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-2">
                      <button
                        @click="openEditModal(user)"
                        class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-primary-600"
                      >
                        <UIcon name="i-heroicons-pencil" class="w-4 h-4" />
                      </button>
                      <button
                        @click="handleDelete(user.id)"
                        class="p-2 rounded-lg hover:bg-red-50 text-gray-600 hover:text-red-600"
                      >
                        <UIcon name="i-heroicons-trash" class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="users.length === 0">
                  <td colspan="5" class="py-8 text-center text-gray-500">
                    Belum ada data pengguna
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <UModal v-model="isModalOpen">
        <UCard>
          <template #header>
            <h3 class="font-display font-bold text-lg">
              {{ isEditing ? 'Edit Pengguna' : 'Tambah Pengguna' }}
            </h3>
          </template>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label class="label-text">Nama</label>
              <UInput v-model="form.name" placeholder="Nama lengkap" required />
            </div>

            <div>
              <label class="label-text">Email</label>
              <UInput v-model="form.email" type="email" placeholder="email@example.com" required />
            </div>

            <div v-if="!isEditing">
              <label class="label-text">Password</label>
              <UInput v-model="form.password" type="password" placeholder="Min. 8 karakter" required />
            </div>

            <div>
              <label class="label-text">Role</label>
              <USelectMenu
                v-model="form.role"
                :options="roleOptions"
                value-attribute="value"
                option-attribute="label"
              />
            </div>

            <div v-if="form.role === 'petugas'">
              <label class="label-text">Poli</label>
              <USelectMenu
                v-model="form.poli_id"
                :options="poliOptions"
                value-attribute="value"
                option-attribute="label"
                placeholder="Pilih poli"
              />
            </div>
          </form>

          <template #footer>
            <div class="flex justify-end gap-3">
              <UButton color="gray" @click="isModalOpen = false">Batal</UButton>
              <UButton color="primary" @click="handleSubmit" :loading="isLoading">
                {{ isEditing ? 'Simpan' : 'Tambah' }}
              </UButton>
            </div>
          </template>
        </UCard>
      </UModal>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
import type { Poli } from '~/types'

definePageMeta({
  layout: false,
  middleware: 'auth',
})

useHead({
  title: 'Kelola User - Puskesmas Antang',
})

const config = useRuntimeConfig()
const authStore = useAuthStore()
const toast = useToast()

interface UserData {
  id: number
  name: string
  email: string
  roles: string[]
  poli?: {
    id: number
    nama_poli: string
    kode_poli: string
  }
}

const users = ref<UserData[]>([])
const poliList = ref<Poli[]>([])
const isModalOpen = ref(false)
const isEditing = ref(false)
const isLoading = ref(false)
const editingId = ref<number | null>(null)

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'petugas' as 'admin' | 'petugas',
  poli_id: null as number | null,
})

const roleOptions = [
  { label: 'Petugas', value: 'petugas' },
  { label: 'Admin', value: 'admin' },
]

const poliOptions = computed(() =>
  poliList.value.map((p) => ({
    label: p.nama_poli || p.name || '',
    value: p.id,
  }))
)

async function fetchUsers() {
  try {
    const response = await $fetch<{ message: string; data: UserData[] }>(
      `${config.public.apiBase}/admin/users`,
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    )
    if (response.data) {
      users.value = response.data
    }
  } catch (error) {
    console.error('Error fetching users:', error)
    toast.add({
      title: 'Error',
      description: 'Gagal memuat data pengguna',
      color: 'red',
    })
  }
}

async function fetchPoli() {
  try {
    const response = await $fetch<{ message: string; data: Poli[] }>(
      `${config.public.apiBase}/poli`
    )
    if (response.data) {
      poliList.value = response.data
    }
  } catch (error) {
    console.error('Error fetching poli:', error)
  }
}

function getUserRole(user: UserData): string {
  if (user.roles?.includes('admin')) return 'admin'
  if (user.roles?.includes('petugas')) return 'petugas'
  return 'petugas'
}

function openCreateModal() {
  isEditing.value = false
  editingId.value = null
  form.value = {
    name: '',
    email: '',
    password: '',
    role: 'petugas',
    poli_id: null,
  }
  isModalOpen.value = true
}

function openEditModal(user: UserData) {
  isEditing.value = true
  editingId.value = user.id
  form.value = {
    name: user.name,
    email: user.email,
    password: '',
    role: getUserRole(user),
    poli_id: user.poli?.id || null,
  }
  isModalOpen.value = true
}

async function handleSubmit() {
  // Validasi frontend
  if (!form.value.name || !form.value.email) {
    toast.add({
      title: 'Validasi Gagal',
      description: 'Nama dan email harus diisi',
      color: 'red',
    })
    return
  }

  if (!isEditing.value && !form.value.password) {
    toast.add({
      title: 'Validasi Gagal',
      description: 'Password harus diisi untuk pengguna baru',
      color: 'red',
    })
    return
  }

  if (!isEditing.value && form.value.password.length < 8) {
    toast.add({
      title: 'Validasi Gagal',
      description: 'Password minimal 8 karakter',
      color: 'red',
    })
    return
  }

  isLoading.value = true

  try {
    const url = isEditing.value
      ? `${config.public.apiBase}/admin/users/${editingId.value}`
      : `${config.public.apiBase}/admin/users`

    const body: any = {
      name: form.value.name,
      email: form.value.email,
      role: form.value.role,
    }

    // Always include password for new users
    if (!isEditing.value) {
      body.password = form.value.password
    }

    // Include poli_id for petugas role
    if (form.value.role === 'petugas' && form.value.poli_id) {
      body.poli_id = form.value.poli_id
    }

    await $fetch(url, {
      method: isEditing.value ? 'PUT' : 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
      body,
    })

    toast.add({
      title: 'Berhasil',
      description: isEditing.value ? 'Data pengguna diperbarui' : 'Pengguna baru ditambahkan',
      color: 'green',
    })

    isModalOpen.value = false
    await fetchUsers()
  } catch (err: any) {
    console.error('Error creating user:', err)
    
    // Handle validation errors from backend
    const errorMessage = err.data?.errors 
      ? Object.values(err.data.errors).flat().join(', ')
      : err.data?.message || err.message || 'Gagal menyimpan data'
    
    toast.add({
      title: 'Gagal',
      description: errorMessage,
      color: 'red',
    })
  } finally {
    isLoading.value = false
  }
}

async function handleDelete(userId: number) {
  if (!confirm('Yakin ingin menghapus pengguna ini?')) return

  try {
    await $fetch(`${config.public.apiBase}/admin/users/${userId}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    })

    toast.add({
      title: 'Berhasil',
      description: 'Pengguna telah dihapus',
      color: 'green',
    })

    await fetchUsers()
  } catch (err: any) {
    toast.add({
      title: 'Gagal',
      description: err.data?.message || 'Gagal menghapus',
      color: 'red',
    })
  }
}

onMounted(() => {
  fetchUsers()
  fetchPoli()
})
</script>
