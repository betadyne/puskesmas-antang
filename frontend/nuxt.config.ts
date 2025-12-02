export default defineNuxtConfig({
  devtools: { enabled: true },

  modules: [
    '@nuxt/ui',
    '@pinia/nuxt',
    '@vueuse/nuxt',
    '@nuxtjs/color-mode',
    '@vite-pwa/nuxt',
  ],

  css: ['~/assets/css/main.css'],

  colorMode: {
    classSuffix: '',
    preference: 'light',
  },

  ui: {
    icons: ['heroicons', 'mdi'],
  },

  pinia: {
    storesDirs: ['./stores/**'],
  },

  pwa: {
    manifest: {
      name: 'Puskesmas Antang - Sistem Antrian',
      short_name: 'Puskesmas Antang',
      theme_color: '#059669',
      background_color: '#ffffff',
      display: 'standalone',
      icons: [
        {
          src: '/icon-192.png',
          sizes: '192x192',
          type: 'image/png',
        },
        {
          src: '/icon-512.png',
          sizes: '512x512',
          type: 'image/png',
        },
      ],
    },
    workbox: {
      navigateFallback: '/',
    },
  },

  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000/api',
      pusherKey: process.env.NUXT_PUBLIC_PUSHER_KEY || '',
      pusherCluster: process.env.NUXT_PUBLIC_PUSHER_CLUSTER || 'mt1',
    },
  },

  routeRules: {
    '/': { ssr: true },
    '/register': { ssr: true },
    '/status/**': { ssr: true },
    '/display/**': { ssr: false },
    '/dashboard/**': { ssr: false },
    '/admin/**': { ssr: false },
    '/login': { ssr: false },
  },

  compatibilityDate: '2024-11-01',
})
