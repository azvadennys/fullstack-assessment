<template>
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
        <div
            class="hidden lg:flex flex-col justify-center items-center bg-blue-600 text-white p-12 relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10">
            </div>
            <div class="relative z-10 text-center">
                <h1 class="text-5xl font-extrabold mb-6 tracking-tight">TaskFlow.</h1>
                <p class="text-blue-100 text-xl max-w-md">Manage projects, track progress, and collaborate in real-time.
                    The assessment platform you need.</p>
            </div>
        </div>

        <div class="flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md space-y-8">
                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-gray-900">Welcome back</h2>
                    <p class="mt-2 text-gray-600">Please sign in to your account</p>
                </div>

                <form @submit.prevent="handleLogin" class="mt-8 space-y-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email address</label>
                            <input v-model="form.email" type="email" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="admin@example.com">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input v-model="form.password" type="password" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" :disabled="loading"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-colors">
                        <span v-if="loading">Signing in...</span>
                        <span v-else>Sign in</span>
                    </button>

                    <div class="text-center text-xs text-gray-400 mt-4">
                        Default: admin@example.com / password
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'
const auth = useAuthStore()
const router = useRouter()
const loading = ref(false)
const form = reactive({ email: '', password: '' })

const handleLogin = async () => {
    loading.value = true
    if (await auth.login(form)) router.push('/')
    loading.value = false
}
</script>