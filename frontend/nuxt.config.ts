// frontend/nuxt.config.ts
export default defineNuxtConfig({
  modules: ['@nuxtjs/tailwindcss', '@pinia/nuxt', '@vueuse/nuxt'],
  
  runtimeConfig: {
    public: {
      apiBase: 'http://127.0.0.1:8000/api' // URL Backend Laravel
    }
  },

  ssr: false, // Client-side only untuk memudahkan auth logic

  app: {
    head: {
      title: 'TaskFlow - Management Platform',
      link: [{ rel: 'stylesheet', href: 'https://rsms.me/inter/inter.css' }]
    }
  },
  
  compatibilityDate: '2024-07-04'
})