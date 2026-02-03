import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null as any,
    token: useCookie('auth_token').value || null,
  }),
  actions: {
    async login(credentials: any) {
      const config = useRuntimeConfig()
      const { $toast } = useNuxtApp()
      try {
        const data: any = await $fetch(`${config.public.apiBase}/auth/login`, {
          method: 'POST',
          body: credentials,
          headers: { 'Accept': 'application/json' }
        })
        this.token = data.access_token
        this.user = data.user
        const cookie = useCookie('auth_token')
        cookie.value = data.access_token
        $toast.success(`Welcome back, ${data.user.name}!`)
        return true
      } catch (e: any) {
        $toast.error('Invalid credentials')
        return false
      }
    },
    logout() {
      this.token = null
      this.user = null
      const cookie = useCookie('auth_token')
      cookie.value = null
      navigateTo('/login')
    }
  }
})