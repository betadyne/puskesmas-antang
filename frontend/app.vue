<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
useHead({
  title: 'Puskesmas Antang - Sistem Antrian',
  meta: [
    { name: 'description', content: 'Sistem Informasi Antrian Pasien Puskesmas Antang' },
    { name: 'theme-color', content: '#059669' },
  ],
  link: [
    { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg' },
  ],
})
</script>
