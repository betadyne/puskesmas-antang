<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center p-8">
    <div class="max-w-2xl w-full">
      <div class="text-center mb-12">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-glow mb-6">
          <UIcon name="i-heroicons-tv" class="w-10 h-10 text-white" />
        </div>
        <h1 class="font-display text-4xl font-bold text-white mb-3">
          Display Antrian
        </h1>
        <p class="text-gray-400 text-lg">
          Pilih poli untuk menampilkan layar antrian
        </p>
      </div>

      <div class="grid sm:grid-cols-2 gap-6">
        <NuxtLink
          v-for="poli in poliList"
          :key="poli.id"
          :to="`/display/${poli.id}`"
          class="group p-8 rounded-3xl bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-primary-500 transition-all duration-300 hover:-translate-y-1"
        >
          <div class="w-14 h-14 rounded-2xl bg-primary-500/20 flex items-center justify-center mb-4 group-hover:bg-primary-500/30 transition-colors">
            <UIcon name="i-heroicons-building-office-2" class="w-7 h-7 text-primary-400" />
          </div>
          <h3 class="font-display font-bold text-xl text-white mb-2">{{ poli.name }}</h3>
          <p class="text-gray-500 text-sm">Kode: {{ poli.code }}</p>
        </NuxtLink>

        <div v-if="poliList.length === 0" class="col-span-full text-center py-12">
          <UIcon name="i-heroicons-inbox" class="w-16 h-16 text-gray-600 mx-auto mb-4" />
          <p class="text-gray-500">Belum ada poli tersedia</p>
        </div>
      </div>

      <div class="text-center mt-12">
        <NuxtLink to="/" class="text-primary-400 font-display font-medium hover:underline inline-flex items-center gap-2">
          <UIcon name="i-heroicons-arrow-left" class="w-4 h-4" />
          Kembali ke Beranda
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Poli, ApiResponse } from '~/types'

definePageMeta({
  layout: false,
})

useHead({
  title: 'Pilih Poli - Display Antrian',
})

const config = useRuntimeConfig()
const poliList = ref<Poli[]>([])

async function fetchPoli() {
  try {
    const { data } = await useFetch<ApiResponse<Poli[]>>(`${config.public.apiBase}/poli`)
    if (data.value?.success) {
      poliList.value = data.value.data.filter((p) => p.is_active)
    }
  } catch (error) {
    console.error('Error fetching poli:', error)
  }
}

onMounted(() => {
  fetchPoli()
})
</script>
