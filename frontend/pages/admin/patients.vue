<template>
  <div>
    <NuxtLayout name="dashboard">
      <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="font-display text-2xl font-bold text-gray-800">Data Pasien</h1>
            <p class="text-gray-500">Database pasien terdaftar</p>
          </div>

          <div class="flex items-center gap-3">
            <UInput
              v-model="searchQuery"
              placeholder="Cari NIK atau nama..."
              icon="i-heroicons-magnifying-glass"
              class="w-64"
            />
          </div>
        </div>

        <div class="card">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-100">
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">NIK</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Nama</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Alamat</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">No. HP</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Terdaftar</th>
                  <th class="text-left py-3 px-4 font-display font-semibold text-gray-600">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="patient in filteredPatients"
                  :key="patient.id"
                  class="border-b border-gray-50 hover:bg-gray-50"
                >
                  <td class="py-3 px-4">
                    <span class="font-mono text-gray-800">{{ patient.nik }}</span>
                  </td>
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="font-display font-semibold text-primary-600 text-sm">
                          {{ patient.nama?.charAt(0) || '?' }}
                        </span>
                      </div>
                      <span class="font-medium text-gray-800">{{ patient.nama }}</span>
                    </div>
                  </td>
                  <td class="py-3 px-4 text-gray-600 max-w-xs truncate">
                    {{ patient.alamat || '-' }}
                  </td>
                  <td class="py-3 px-4 text-gray-600">{{ patient.no_hp || '-' }}</td>
                  <td class="py-3 px-4 text-sm text-gray-500">
                    {{ formatDate(patient.created_at) }}
                  </td>
                  <td class="py-3 px-4">
                    <div class="flex items-center gap-2">
                      <button
                        @click="viewPatient(patient)"
                        class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-primary-600"
                      >
                        <UIcon name="i-heroicons-eye" class="w-4 h-4" />
                      </button>
                      <button
                        @click="openEditModal(patient)"
                        class="p-2 rounded-lg hover:bg-gray-100 text-gray-600 hover:text-primary-600"
                      >
                        <UIcon name="i-heroicons-pencil" class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="filteredPatients.length === 0">
                  <td colspan="6" class="py-8 text-center text-gray-500">
                    {{ searchQuery ? 'Tidak ditemukan hasil pencarian' : 'Belum ada data pasien' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="totalPages > 1" class="flex items-center justify-between pt-4 border-t border-gray-100 mt-4">
            <p class="text-sm text-gray-500">
              Menampilkan {{ patients.length }} dari {{ totalPatients }} pasien
            </p>
            <div class="flex items-center gap-2">
              <UButton
                color="gray"
                size="sm"
                :disabled="currentPage === 1"
                @click="currentPage--"
              >
                Sebelumnya
              </UButton>
              <span class="px-3 py-1 text-sm text-gray-600">
                {{ currentPage }} / {{ totalPages }}
              </span>
              <UButton
                color="gray"
                size="sm"
                :disabled="currentPage === totalPages"
                @click="currentPage++"
              >
                Selanjutnya
              </UButton>
            </div>
          </div>
        </div>
      </div>

      <USlideover v-model="isDetailOpen">
        <UCard class="h-full">
          <template #header>
            <div class="flex items-center justify-between">
              <h3 class="font-display font-bold text-lg">Detail Pasien</h3>
              <UButton
                color="gray"
                variant="ghost"
                icon="i-heroicons-x-mark"
                @click="isDetailOpen = false"
              />
            </div>
          </template>

          <div v-if="selectedPatient" class="space-y-6">
            <div class="text-center">
              <div class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center mx-auto mb-4">
                <span class="font-display font-bold text-3xl text-primary-600">
                  {{ selectedPatient.nama?.charAt(0) || '?' }}
                </span>
              </div>
              <h4 class="font-display font-bold text-xl text-gray-800">
                {{ selectedPatient.nama }}
              </h4>
            </div>

            <div class="space-y-4">
              <div class="p-4 rounded-xl bg-gray-50">
                <p class="text-xs text-gray-500 mb-1">NIK</p>
                <p class="font-mono font-medium text-gray-800">{{ selectedPatient.nik }}</p>
              </div>

              <div class="p-4 rounded-xl bg-gray-50">
                <p class="text-xs text-gray-500 mb-1">Alamat</p>
                <p class="text-gray-800">{{ selectedPatient.alamat || '-' }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-gray-50">
                  <p class="text-xs text-gray-500 mb-1">Tanggal Lahir</p>
                  <p class="text-gray-800">{{ selectedPatient.tgl_lahir || '-' }}</p>
                </div>
                <div class="p-4 rounded-xl bg-gray-50">
                  <p class="text-xs text-gray-500 mb-1">Jenis Kelamin</p>
                  <p class="text-gray-800">
                    {{ selectedPatient.jenis_kelamin === 'L' ? 'Laki-laki' : selectedPatient.jenis_kelamin === 'P' ? 'Perempuan' : '-' }}
                  </p>
                </div>
              </div>

              <div class="p-4 rounded-xl bg-gray-50">
                <p class="text-xs text-gray-500 mb-1">No. HP</p>
                <p class="text-gray-800">{{ selectedPatient.no_hp || '-' }}</p>
              </div>

              <div class="p-4 rounded-xl bg-gray-50">
                <p class="text-xs text-gray-500 mb-1">Terdaftar Sejak</p>
                <p class="text-gray-800">{{ formatDate(selectedPatient.created_at) }}</p>
              </div>
            </div>
          </div>
        </UCard>
      </USlideover>

      <UModal v-model="isModalOpen">
        <UCard>
          <template #header>
            <h3 class="font-display font-bold text-lg">Edit Data Pasien</h3>
          </template>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
              <label class="label-text">NIK</label>
              <UInput v-model="form.nik" disabled />
            </div>

            <div>
              <label class="label-text">Nama</label>
              <UInput v-model="form.nama" required />
            </div>

            <div>
              <label class="label-text">Alamat</label>
              <UTextarea v-model="form.alamat" rows="2" />
            </div>

            <div>
              <label class="label-text">No. HP</label>
              <UInput v-model="form.no_hp" type="tel" />
            </div>
          </form>

          <template #footer>
            <div class="flex justify-end gap-3">
              <UButton color="gray" @click="isModalOpen = false">Batal</UButton>
              <UButton color="primary" @click="handleSubmit" :loading="isLoading">
                Simpan
              </UButton>
            </div>
          </template>
        </UCard>
      </UModal>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
  middleware: 'auth',
})

useHead({
  title: 'Data Pasien - Puskesmas Antang',
})

const config = useRuntimeConfig()
const authStore = useAuthStore()
const toast = useToast()

interface PatientData {
  id: number
  nik: string
  nama: string
  no_bpjs?: string
  tgl_lahir?: string
  jenis_kelamin?: string
  no_hp?: string
  alamat?: string
  created_at: string
}

interface PaginatedData {
  data: PatientData[]
  total: number
  last_page: number
  current_page: number
}

const patients = ref<PatientData[]>([])
const searchQuery = ref('')
const currentPage = ref(1)
const totalPatients = ref(0)
const totalPages = ref(1)

const isDetailOpen = ref(false)
const isModalOpen = ref(false)
const isLoading = ref(false)
const selectedPatient = ref<PatientData | null>(null)
const editingId = ref<number | null>(null)

const form = ref({
  nik: '',
  nama: '',
  alamat: '',
  no_hp: '',
})

const filteredPatients = computed(() => {
  if (!searchQuery.value) return patients.value

  const query = searchQuery.value.toLowerCase()
  return patients.value.filter(
    (p) =>
      p.nik.includes(query) ||
      p.nama?.toLowerCase().includes(query)
  )
})

async function fetchPatients() {
  try {
    const response = await $fetch<{ message: string; data: PaginatedData }>(
      `${config.public.apiBase}/admin/patients`,
      {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
        },
        params: {
          page: currentPage.value,
          search: searchQuery.value || undefined,
        },
      }
    )

    if (response.data) {
      patients.value = response.data.data
      totalPatients.value = response.data.total
      totalPages.value = response.data.last_page
    }
  } catch (error) {
    console.error('Error fetching patients:', error)
    toast.add({
      title: 'Error',
      description: 'Gagal memuat data pasien',
      color: 'red',
    })
  }
}

function viewPatient(patient: PatientData) {
  selectedPatient.value = patient
  isDetailOpen.value = true
}

function openEditModal(patient: PatientData) {
  editingId.value = patient.id
  form.value = {
    nik: patient.nik,
    nama: patient.nama,
    alamat: patient.alamat || '',
    no_hp: patient.no_hp || '',
  }
  isModalOpen.value = true
}

async function handleSubmit() {
  isLoading.value = true

  try {
    await $fetch(`${config.public.apiBase}/admin/patients/${editingId.value}`, {
      method: 'PUT',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
      body: {
        nama: form.value.nama,
        alamat: form.value.alamat || undefined,
        no_hp: form.value.no_hp || undefined,
      },
    })

    toast.add({
      title: 'Berhasil',
      description: 'Data pasien diperbarui',
      color: 'green',
    })

    isModalOpen.value = false
    await fetchPatients()
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

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

watch(currentPage, () => {
  fetchPatients()
})

onMounted(() => {
  fetchPatients()
})
</script>
