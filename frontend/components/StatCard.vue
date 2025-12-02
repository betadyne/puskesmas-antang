<template>
  <div class="card" :class="gradient ? `bg-gradient-to-br ${gradient} text-white` : ''">
    <div class="flex items-center gap-4">
      <div
        class="w-14 h-14 rounded-2xl flex items-center justify-center"
        :class="gradient ? 'bg-white/20' : iconBgClass"
      >
        <UIcon :name="icon" class="w-7 h-7" :class="gradient ? 'text-white' : iconClass" />
      </div>
      <div>
        <p class="text-sm" :class="gradient ? 'text-white/70' : 'text-gray-500'">
          {{ label }}
        </p>
        <p class="font-display text-3xl font-bold" :class="gradient ? 'text-white' : 'text-gray-800'">
          {{ formattedValue }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  label: string
  value: number | string
  icon: string
  color?: 'primary' | 'green' | 'amber' | 'red' | 'blue'
  gradient?: string
}>()

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString('id-ID')
  }
  return props.value
})

const iconBgClass = computed(() => {
  const colors: Record<string, string> = {
    primary: 'bg-primary-100',
    green: 'bg-green-100',
    amber: 'bg-amber-100',
    red: 'bg-red-100',
    blue: 'bg-blue-100',
  }
  return colors[props.color || 'primary']
})

const iconClass = computed(() => {
  const colors: Record<string, string> = {
    primary: 'text-primary-600',
    green: 'text-green-600',
    amber: 'text-amber-600',
    red: 'text-red-600',
    blue: 'text-blue-600',
  }
  return colors[props.color || 'primary']
})
</script>
