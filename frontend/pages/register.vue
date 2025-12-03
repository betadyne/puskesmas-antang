<template>
  <div>
    <NuxtLayout name="public">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
          <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-glow mb-6">
              <UIcon name="i-heroicons-clipboard-document-list" class="w-8 h-8 text-white" />
            </div>
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 mb-3">
              Registrasi Antrian
            </h1>
            <p class="text-gray-600">
              Isi data diri Anda untuk mendapatkan nomor antrian
            </p>
          </div>

          <Transition
            mode="out-in"
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-4"
          >
            <div v-if="!ticket" class="card">
              <form @submit.prevent="handleSubmit" class="space-y-6">
                <div>
                  <label class="label-text">Nomor Induk Kependudukan (NIK)</label>
                  <input
                    v-model="form.nik"
                    type="text"
                    class="input-field font-mono"
                    placeholder="Masukkan 16 digit NIK"
                    maxlength="16"
                    pattern="[0-9]{16}"
                    required
                  />
                  <p v-if="errors.nik" class="mt-2 text-sm text-red-500">{{ errors.nik }}</p>
                </div>

                <div>
                  <label class="label-text">Nama Lengkap</label>
                  <input
                    v-model="form.name"
                    type="text"
                    class="input-field"
                    placeholder="Masukkan nama sesuai KTP"
                    required
                  />
                  <p v-if="errors.name" class="mt-2 text-sm text-red-500">{{ errors.name }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="label-text">Tanggal Lahir</label>
                    <input
                      v-model="form.tgl_lahir"
                      type="date"
                      class="input-field"
                      required
                    />
                    <p v-if="errors.tgl_lahir" class="mt-2 text-sm text-red-500">{{ errors.tgl_lahir }}</p>
                  </div>

                  <div>
                    <label class="label-text">Jenis Kelamin</label>
                    <USelectMenu
                      v-model="form.jenis_kelamin"
                      :options="[
                        { label: 'Laki-laki', value: 'L' },
                        { label: 'Perempuan', value: 'P' }
                      ]"
                      placeholder="Pilih jenis kelamin"
                      value-attribute="value"
                      option-attribute="label"
                      class="w-full"
                      size="lg"
                    />
                    <p v-if="errors.jenis_kelamin" class="mt-2 text-sm text-red-500">{{ errors.jenis_kelamin }}</p>
                  </div>
                </div>

                <div>
                  <label class="label-text">Nomor HP</label>
                  <input
                    v-model="form.no_hp"
                    type="tel"
                    class="input-field"
                    placeholder="Contoh: 08123456789"
                    required
                  />
                  <p v-if="errors.no_hp" class="mt-2 text-sm text-red-500">{{ errors.no_hp }}</p>
                </div>

                <div>
                  <label class="label-text">Pilih Poli</label>
                  <USelectMenu
                    v-model="form.poli_id"
                    :options="poliOptions"
                    placeholder="Pilih poli tujuan"
                    value-attribute="value"
                    option-attribute="label"
                    class="w-full"
                    size="lg"
                  >
                    <template #leading>
                      <UIcon name="i-heroicons-building-office-2" class="w-5 h-5 text-gray-400" />
                    </template>
                  </USelectMenu>
                  <p v-if="errors.poli_id" class="mt-2 text-sm text-red-500">{{ errors.poli_id }}</p>
                </div>

                <div>
                  <label class="label-text">Alamat Lengkap</label>
                  <textarea
                    v-model="form.alamat"
                    rows="3"
                    class="input-field resize-none"
                    placeholder="Masukkan alamat lengkap"
                    required
                  ></textarea>
                  <p v-if="errors.alamat" class="mt-2 text-sm text-red-500">{{ errors.alamat }}</p>
                </div>

                <div class="pt-4">
                  <button
                    type="submit"
                    :disabled="isLoading"
                    class="btn-primary w-full justify-center text-lg py-4"
                  >
                    <UIcon
                      v-if="isLoading"
                      name="i-heroicons-arrow-path"
                      class="w-5 h-5 animate-spin"
                    />
                    <UIcon v-else name="i-heroicons-check-circle" class="w-5 h-5" />
                    {{ isLoading ? 'Memproses...' : 'Dapatkan Nomor Antrian' }}
                  </button>
                </div>
              </form>
            </div>

            <div v-else class="space-y-6">
              <div class="card text-center bg-gradient-to-br from-primary-50 to-white border-2 border-primary-200 no-print">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-6">
                  <UIcon name="i-heroicons-check-circle" class="w-12 h-12 text-green-500" />
                </div>

                <h2 class="font-display text-2xl font-bold text-gray-900 mb-2">
                  Pendaftaran Berhasil!
                </h2>
                <p class="text-gray-600 mb-8">
                  Simpan tiket ini untuk menunjukkan ke petugas
                </p>

                <!-- Ticket Card for Save/Print -->
                <div ref="ticketRef" id="ticket-content" class="bg-white rounded-3xl p-8 shadow-soft border border-gray-100 mb-8">
                  <div class="text-center mb-4">
                    <img src="/logo.png" alt="Logo" class="w-12 h-12 mx-auto mb-2" />
                    <h3 class="font-display font-bold text-lg text-gray-800">Puskesmas Antang</h3>
                    <p class="text-xs text-gray-500">Sistem Antrian Digital</p>
                  </div>
                  
                  <div class="border-t border-dashed border-gray-300 my-4"></div>
                  
                  <p class="text-sm text-gray-500 mb-2">Nomor Antrian Anda</p>
                  <div class="queue-number-display mb-4">
                    {{ ticket.nomor_antrian }}
                  </div>

                  <div class="flex items-center justify-center gap-2 mb-6">
                    <div class="px-4 py-2 rounded-xl bg-primary-100 text-primary-700 font-display font-medium">
                      {{ ticket.poli_name }}
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4 text-left">
                    <div class="p-4 rounded-xl bg-gray-50">
                      <p class="text-xs text-gray-500 mb-1">Waktu Daftar</p>
                      <p class="font-display font-medium text-gray-800 text-sm">
                        {{ formatDate(ticket.created_at) }}
                      </p>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50">
                      <p class="text-xs text-gray-500 mb-1">Estimasi Waktu</p>
                      <p class="font-display font-medium text-gray-800 text-sm">
                        ~{{ ticket.estimasi_waktu || 15 }} menit
                      </p>
                    </div>
                  </div>
                  
                  <div class="border-t border-dashed border-gray-300 my-4"></div>
                  
                  <p class="text-xs text-gray-500 text-center">
                    Harap datang 10 menit sebelum giliran Anda
                  </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 no-print">
                  <button
                    @click="saveAsImage"
                    :disabled="isSaving"
                    class="btn-primary flex-1 justify-center"
                  >
                    <UIcon v-if="isSaving" name="i-heroicons-arrow-path" class="w-5 h-5 animate-spin" />
                    <UIcon v-else name="i-heroicons-arrow-down-tray" class="w-5 h-5" />
                    {{ isSaving ? 'Menyimpan...' : 'Simpan Gambar' }}
                  </button>
                  <button
                    @click="printTicket"
                    class="btn-secondary flex-1 justify-center"
                  >
                    <UIcon name="i-heroicons-printer" class="w-5 h-5" />
                    Cetak Tiket
                  </button>
                </div>
              </div>
              
              <!-- Print-only ticket -->
              <div class="print-ticket hidden print:block">
                <div class="ticket-print-container">
                  <div class="text-center mb-4">
                    <h2 class="font-bold text-xl">PUSKESMAS ANTANG</h2>
                    <p class="text-sm">Sistem Antrian Digital</p>
                  </div>
                  
                  <div class="border-t border-dashed border-gray-400 my-3"></div>
                  
                  <div class="text-center my-6">
                    <p class="text-sm mb-1">Nomor Antrian</p>
                    <p class="text-5xl font-bold my-2">{{ ticket.nomor_antrian }}</p>
                    <p class="text-lg font-semibold mt-2">{{ ticket.poli_name }}</p>
                  </div>
                  
                  <div class="border-t border-dashed border-gray-400 my-3"></div>
                  
                  <div class="text-sm space-y-1">
                    <p><strong>Waktu:</strong> {{ formatDate(ticket.created_at) }}</p>
                    <p><strong>Estimasi:</strong> ~{{ ticket.estimasi_waktu || 15 }} menit</p>
                  </div>
                  
                  <div class="border-t border-dashed border-gray-400 my-3"></div>
                  
                  <p class="text-xs text-center">
                    Harap datang 10 menit sebelum giliran
                  </p>
                </div>
              </div>

              <div class="card bg-amber-50 border-amber-200">
                <div class="flex items-start gap-4">
                  <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 text-amber-600" />
                  </div>
                  <div>
                    <h4 class="font-display font-semibold text-amber-800 mb-1">Penting!</h4>
                    <p class="text-sm text-amber-700 leading-relaxed">
                      Harap datang 10 menit sebelum estimasi waktu. Nomor antrian yang tidak hadir saat dipanggil akan dilewati.
                    </p>
                  </div>
                </div>
              </div>

              <div class="text-center">
                <NuxtLink to="/" class="text-primary-600 font-display font-medium hover:underline">
                  &larr; Kembali ke Beranda
                </NuxtLink>
              </div>
            </div>
          </Transition>
        </div>
      </div>
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
import type { Poli, QueueTicket } from '~/types'

definePageMeta({
  layout: false,
})

useHead({
  title: 'Registrasi Antrian - Puskesmas Antang',
})

const config = useRuntimeConfig()
const toast = useToast()

const ticketRef = ref<HTMLElement | null>(null)
const isSaving = ref(false)

const form = ref({
  nik: '',
  name: '',
  tgl_lahir: '',
  jenis_kelamin: '' as 'L' | 'P' | '',
  no_hp: '',
  poli_id: null as number | null,
  alamat: '',
})

const errors = ref<Record<string, string>>({})
const isLoading = ref(false)
const ticket = ref<QueueTicket | null>(null)
const poliList = ref<Poli[]>([])

const poliOptions = computed(() =>
  poliList.value.map((p) => ({
    label: p.nama_poli || p.name || '',
    value: p.id,
  }))
)

interface PoliResponse {
  message: string
  data: Poli[]
}

async function fetchPoli() {
  try {
    const response = await $fetch<PoliResponse>(`${config.public.apiBase}/poli`)
    if (response.data) {
      poliList.value = response.data
    }
  } catch (error) {
    console.error('Error fetching poli:', error)
  }
}

function validateForm(): boolean {
  errors.value = {}
  let isValid = true

  if (!form.value.nik || form.value.nik.length !== 16) {
    errors.value.nik = 'NIK harus 16 digit'
    isValid = false
  }

  if (!form.value.name || form.value.name.length < 3) {
    errors.value.name = 'Nama minimal 3 karakter'
    isValid = false
  }

  if (!form.value.tgl_lahir) {
    errors.value.tgl_lahir = 'Tanggal lahir wajib diisi'
    isValid = false
  }

  if (!form.value.jenis_kelamin) {
    errors.value.jenis_kelamin = 'Pilih jenis kelamin'
    isValid = false
  }

  if (!form.value.no_hp) {
    errors.value.no_hp = 'Nomor HP wajib diisi'
    isValid = false
  }

  if (!form.value.poli_id) {
    errors.value.poli_id = 'Pilih poli tujuan'
    isValid = false
  }

  if (!form.value.alamat) {
    errors.value.alamat = 'Alamat wajib diisi'
    isValid = false
  }

  return isValid
}

interface RegisterResponse {
  message: string
  data: {
    registration: any
    queue: {
      nomor_antrean: string
      poli: {
        nama_poli: string
      }
    }
  }
}

async function handleSubmit() {
  if (!validateForm()) return

  isLoading.value = true

  try {
    const response = await $fetch<RegisterResponse>(
      `${config.public.apiBase}/register`,
      {
        method: 'POST',
        body: {
          nik: form.value.nik,
          nama: form.value.name,
          tgl_lahir: form.value.tgl_lahir,
          jenis_kelamin: form.value.jenis_kelamin,
          no_hp: form.value.no_hp,
          poli_tujuan: form.value.poli_id,
          alamat: form.value.alamat,
          cara_daftar: 'online',
        },
      }
    )

    if (response.data) {
      const selectedPoli = poliList.value.find(p => p.id === form.value.poli_id)
      ticket.value = {
        nomor_antrian: response.data.queue.nomor_antrean,
        poli_name: response.data.queue.poli?.nama_poli || selectedPoli?.nama_poli || '',
        created_at: new Date().toISOString(),
        estimasi_waktu: 15,
      }
      toast.add({
        title: 'Berhasil!',
        description: `Nomor antrian Anda: ${response.data.queue.nomor_antrean}`,
        color: 'green',
      })
    }
  } catch (err: any) {
    const message = err.data?.message || err.message || 'Terjadi kesalahan saat mendaftar'
    toast.add({
      title: 'Gagal',
      description: message,
      color: 'red',
    })
  } finally {
    isLoading.value = false
  }
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

async function saveAsImage() {
  if (!ticketRef.value) {
    toast.add({
      title: 'Error',
      description: 'Tidak dapat menemukan elemen tiket',
      color: 'red',
    })
    return
  }

  isSaving.value = true

  try {
    const html2canvas = (await import('html2canvas')).default
    
    const canvas = await html2canvas(ticketRef.value, {
      backgroundColor: '#ffffff',
      scale: 2,
      useCORS: true,
      allowTaint: true,
      logging: false,
    })

    const link = document.createElement('a')
    link.download = `tiket-antrian-${ticket.value?.nomor_antrian || 'puskesmas'}.png`
    link.href = canvas.toDataURL('image/png')
    link.click()

    toast.add({
      title: 'Berhasil',
      description: 'Tiket berhasil disimpan sebagai gambar',
      color: 'green',
    })
  } catch (error) {
    console.error('Error saving image:', error)
    toast.add({
      title: 'Gagal',
      description: 'Gagal menyimpan gambar. Coba lagi.',
      color: 'red',
    })
  } finally {
    isSaving.value = false
  }
}

function printTicket() {
  window.print()
}

onMounted(() => {
  fetchPoli()
})
</script>
