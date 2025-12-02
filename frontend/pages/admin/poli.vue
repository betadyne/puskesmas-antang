<template>
  <div>
    <NuxtLayout name="dashboard">
      <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="font-display text-2xl font-bold text-gray-800">Kelola Poli</h1>
            <p class="text-gray-500">Manajemen unit pelayanan puskesmas</p>
          </div>

          <button @click="openCreateModal" class="btn-primary">
            <UIcon name="i-heroicons-plus" class="w-5 h-5" />
            Tambah Poli
          </button>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="poli in poliList"
            :key="poli.id"
            class="card-hover"
          >
            <div class="flex items-start justify-between mb-4">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-50 flex items-center justify-center">
                <UIcon name="i-heroicons-building-office-2" class="w-7 h-7 text-primary-600" />
              </div>
              <span class="badge-success">Aktif</span>
            </div>

            <h3 class="font-display font-bold text-lg text-gray-800 mb-1">{{ poli.nama_poli }}</h3>
            <p class="text-sm text-gray-500 mb-4">Kode: {{ poli.kode_poli }}</p>
            <p v-if="poli.slug" class="text-sm text-gray-600 mb-4">Slug: {{ poli.slug }}</p>

            <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
              <button
                @click="openEditModal(poli)"
                class="flex-1 py-2 px-4 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-600 font-display font-medium text-sm transition-colors"
              >
                <UIcon name="i-heroicons-pencil" class="w-4 h-4 inline mr-1" />
                Edit
              </button>
              <button
                @click="handleDelete(poli.id)"
                class="py-2 px-4 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 font-display font-medium text-sm transition-colors"
              >
                <UIcon name="i-heroicons-trash" class="w-4 h-4" />
              </button>
            </div>
          </div>

          <div v-if="poliList.length === 0" class="col-span-full text-center py-12">
            <UIcon name="i-heroicons-building-office-2" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">Belum ada data poli</p>
          </div>
        </div>
      </div>

      <UModal v-model="isModalOpen">
        <UCard>
          <template #header>
            <h3 class="font-display font-bold text-lg">
              {{ isEditing ? 'Edit Poli' : 'Tambah Poli' }}
            </h3>
          </template>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label class="label-text">Nama Poli</label>
              <UInput v-model="form.nama_poli" placeholder="Contoh: Poli Umum" required />
            </div>

            <div>
              <label class="label-text">Kode Poli</label>
              <UInput v-model="form.kode_poli" placeholder="Contoh: PU" required />
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
  title: 'Kelola Poli - Puskesmas Antang',
})

const config = useRuntimeConfig()
const authStore = useAuthStore()
const toast = useToast()

const poliList = ref<Poli[]>([])
const isModalOpen = ref(false)
const isEditing = ref(false)
const isLoading = ref(false)
const editingId = ref<number | null>(null)

const form = ref({
  nama_poli: '',
  kode_poli: '',
})

async function fetchPoli() {
  try {
    const response = await $fetch<{ message: string; data: Poli[] }>(
      `${config.public.apiBase}/admin/polis`,
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    )
    if (response.data) {
      poliList.value = response.data
    }
  } catch (error) {
    console.error('Error fetching poli:', error)
    toast.add({
      title: 'Error',
      description: 'Gagal memuat data poli',
      color: 'red',
    })
  }
}

function openCreateModal() {
  isEditing.value = false
  editingId.value = null
  form.value = {
    nama_poli: '',
    kode_poli: '',
  }
  isModalOpen.value = true
}

function openEditModal(poli: Poli) {
  isEditing.value = true
  editingId.value = poli.id
  form.value = {
    nama_poli: poli.nama_poli || '',
    kode_poli: poli.kode_poli || '',
  }
  isModalOpen.value = true
}

async function handleSubmit() {
  isLoading.value = true

  try {
    const url = isEditing.value
      ? `${config.public.apiBase}/admin/polis/${editingId.value}`
      : `${config.public.apiBase}/admin/polis`

    await $fetch(url, {
      method: isEditing.value ? 'PUT' : 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
      body: form.value,
    })

    toast.add({
      title: 'Berhasil',
      description: isEditing.value ? 'Data poli diperbarui' : 'Poli baru ditambahkan',
      color: 'green',
    })

    isModalOpen.value = false
    await fetchPoli()
  } catch (err: any) {
    toast.add({
      title: 'Gagal',
      description: err.data?.message || err.message || 'Gagal menyimpan data',
      color: 'red',
    })
  } finally {
    isLoading.value = false
  }
}

async function handleDelete(poliId: number) {
  if (!confirm('Yakin ingin menghapus poli ini?')) return

  try {
    await $fetch(`${config.public.apiBase}/admin/polis/${poliId}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    })

    toast.add({
      title: 'Berhasil',
      description: 'Poli telah dihapus',
      color: 'green',
    })

    await fetchPoli()
  } catch (err: any) {
    toast.add({
      title: 'Gagal',
      description: err.data?.message || 'Gagal menghapus',
      color: 'red',
    })
  }
}

onMounted(() => {
  fetchPoli()
})
</script>
