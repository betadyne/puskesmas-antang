<template>
  <div class="flex items-center justify-center" :class="containerClass">
    <div class="relative">
      <div
        class="rounded-full border-4 border-gray-200 animate-spin"
        :class="[sizeClass, `border-t-${color}-500`]"
        :style="{ borderTopColor: getColorHex }"
      ></div>
    </div>
    <span v-if="text" class="ml-3 font-display text-gray-600">{{ text }}</span>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
  size?: 'sm' | 'md' | 'lg'
  color?: 'primary' | 'accent' | 'gray'
  text?: string
  fullscreen?: boolean
}>(), {
  size: 'md',
  color: 'primary',
})

const sizeClass = computed(() => {
  const sizes: Record<string, string> = {
    sm: 'w-6 h-6',
    md: 'w-10 h-10',
    lg: 'w-16 h-16',
  }
  return sizes[props.size]
})

const containerClass = computed(() => {
  return props.fullscreen ? 'min-h-screen' : ''
})

const getColorHex = computed(() => {
  const colors: Record<string, string> = {
    primary: '#059669',
    accent: '#3b82f6',
    gray: '#6b7280',
  }
  return colors[props.color]
})
</script>
