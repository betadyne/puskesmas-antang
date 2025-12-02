<template>
  <div
    class="p-4 rounded-xl transition-all duration-300"
    :class="[
      isActive ? 'bg-primary-50 border-2 border-primary-200' : 'bg-gray-50 border border-gray-100',
      hoverable ? 'hover:shadow-md hover:-translate-y-0.5 cursor-pointer' : ''
    ]"
    @click="$emit('click')"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <div
          class="w-12 h-12 rounded-xl flex items-center justify-center font-mono font-bold text-lg"
          :class="isActive ? 'bg-primary-500 text-white' : 'bg-gray-200 text-gray-600'"
        >
          {{ queue.nomor_antrian }}
        </div>
        <div>
          <p class="font-display font-semibold text-gray-800">
            {{ queue.pasien?.name || 'Pasien' }}
          </p>
          <p class="text-sm text-gray-500">
            {{ queue.poli?.name || 'Poli' }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <span :class="statusBadgeClass">
          {{ statusText }}
        </span>

        <slot name="actions" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Queue } from '~/types'

const props = defineProps<{
  queue: Queue
  isActive?: boolean
  hoverable?: boolean
}>()

defineEmits(['click'])

const statusText = computed(() => {
  const map: Record<string, string> = {
    menunggu: 'Menunggu',
    dipanggil: 'Dipanggil',
    dilayani: 'Dilayani',
    selesai: 'Selesai',
    dilewati: 'Dilewati',
  }
  return map[props.queue.status] || '-'
})

const statusBadgeClass = computed(() => {
  const classes: Record<string, string> = {
    menunggu: 'badge-warning',
    dipanggil: 'badge-success animate-pulse',
    dilayani: 'badge-info',
    selesai: 'badge bg-gray-100 text-gray-600',
    dilewati: 'badge-danger',
  }
  return classes[props.queue.status] || 'badge'
})
</script>
