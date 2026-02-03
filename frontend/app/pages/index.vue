<template>
    <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="bg-blue-600 text-white p-1.5 rounded-lg shadow-lg shadow-blue-600/20">
                        <LayoutDashboard class="w-5 h-5" />
                    </div>
                    <span class="text-xl font-bold text-gray-900 tracking-tight">TaskFlow</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="hidden md:flex flex-col items-end mr-2">
                        <span class="text-sm font-bold text-gray-900">{{ auth.user?.name }}</span>
                        <span class="text-xs text-gray-500 uppercase font-medium">{{ auth.user?.role || 'User' }}</span>
                    </div>
                    <button @click="auth.logout"
                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <LogOut class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div
                class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
                <div class="w-full md:w-auto flex flex-1 gap-3 max-w-3xl">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" />
                        <input v-model="search" type="text" placeholder="Search tasks..."
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border-transparent focus:bg-white border focus:border-blue-500 rounded-xl text-sm transition-all focus:ring-4 focus:ring-blue-500/10" />
                    </div>

                    <select v-model="filters.status"
                        class="bg-gray-50 border-transparent rounded-xl text-sm focus:border-blue-500 focus:ring-0">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>

                    <select v-model="filters.priority"
                        class="bg-gray-50 border-transparent rounded-xl text-sm focus:border-blue-500 focus:ring-0">
                        <option value="">All Priorities</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <button @click="openModal()"
                    class="w-full md:w-auto bg-blue-600 text-white px-5 py-2 rounded-xl text-sm font-medium hover:bg-blue-700 shadow-lg shadow-blue-600/20 flex items-center justify-center gap-2 transition-all transform hover:scale-105">
                    <Plus class="w-4 h-4" /> New Task
                </button>
            </div>

            <div v-if="pending" class="flex flex-col items-center justify-center py-20">
                <Loader2 class="w-10 h-10 animate-spin text-blue-600 mb-4" />
                <p class="text-gray-500 text-sm">Loading tasks...</p>
            </div>

            <div v-else-if="!tasks?.data?.length"
                class="text-center py-24 bg-white rounded-3xl border border-dashed border-gray-200">
                <div class="mx-auto w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-4">
                    <ListTodo class="w-8 h-8 text-blue-600" />
                </div>
                <h3 class="text-lg font-bold text-gray-900">No tasks found</h3>
                <p class="text-gray-500 text-sm mt-1 max-w-sm mx-auto">It seems quiet here. Adjust your filters or
                    create a new task to get started.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <TaskCard v-for="task in tasks.data" :key="task.id" :task="task" @click="openModal(task)"
                    @delete="deleteTask" @updateStatus="updateStatus" />
            </div>

            <div v-if="tasks?.data?.length" class="mt-8 flex justify-center items-center gap-4">
                <button :disabled="!tasks.prev_page_url" @click="page--"
                    class="px-4 py-2 border border-gray-200 rounded-xl bg-white text-sm font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">Previous</button>
                <span
                    class="text-sm font-medium text-gray-600 bg-white px-3 py-1 rounded-lg border border-gray-100">Page
                    {{ page }}</span>
                <button :disabled="!tasks.next_page_url" @click="page++"
                    class="px-4 py-2 border border-gray-200 rounded-xl bg-white text-sm font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">Next</button>
            </div>
        </main>

        <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <TaskModal v-if="showModal" :task="selectedTask" @close="showModal = false" @save="handleSave"
                @upload="handleUpload" />
        </Transition>
    </div>
</template>

<script setup>
import { LayoutDashboard, LogOut, Search, Plus, Loader2, ListTodo } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { refDebounced, useIntervalFn } from '@vueuse/core'

definePageMeta({ middleware: ['auth'] })
const auth = useAuthStore()
const config = useRuntimeConfig()
const { $toast } = useNuxtApp()

// --- STATE ---
const page = ref(1)
const search = ref('')
const debouncedSearch = refDebounced(search, 500)
const filters = reactive({ status: '', priority: '' })
const showModal = ref(false)
const selectedTask = ref(null)

// --- FETCH DATA ---
const { data: tasks, pending, refresh } = await useFetch(() => `${config.public.apiBase}/tasks`, {
    headers: { Authorization: `Bearer ${auth.token}` },
    params: computed(() => ({
        page: page.value,
        status: filters.status || undefined,
        priority: filters.priority || undefined,
        search: debouncedSearch.value || undefined
    })),
    watch: [page, filters, debouncedSearch]
})

// Real-time Polling (10s)
useIntervalFn(() => refresh(), 10000)

// --- ACTIONS ---
const openModal = (task = null) => {
    selectedTask.value = task ? { ...task } : {}
    showModal.value = true
}

const handleSave = async (form) => {
    const isEdit = !!form.id
    const url = isEdit ? `/tasks/${form.id}` : '/tasks'
    const method = isEdit ? 'PUT' : 'POST'

    try {
        await $fetch(`${config.public.apiBase}${url}`, {
            method,
            body: form,
            headers: { Authorization: `Bearer ${auth.token}` }
        })
        $toast.success(isEdit ? 'Task updated successfully' : 'New task created')
        showModal.value = false
        refresh()
    } catch (e) {
        $toast.error('Failed to save task. Please try again.')
    }
}

const deleteTask = async (id) => {
    if (!confirm('Are you sure you want to delete this task?')) return
    try {
        await $fetch(`${config.public.apiBase}/tasks/${id}`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${auth.token}` }
        })
        $toast.success('Task deleted')
        refresh()
    } catch (e) { $toast.error('Delete failed') }
}

const updateStatus = async (id, status) => {
    try {
        await $fetch(`${config.public.apiBase}/tasks/${id}`, {
            method: 'PUT',
            body: { status },
            headers: { Authorization: `Bearer ${auth.token}` }
        })
        $toast.success('Status updated')
        refresh()
    } catch (e) { $toast.error('Update failed') }
}

const handleUpload = async (file, taskId) => {
    const formData = new FormData()
    formData.append('file', file)
    try {
        await $fetch(`${config.public.apiBase}/tasks/${taskId}/attachments`, {
            method: 'POST',
            body: formData,
            headers: { Authorization: `Bearer ${auth.token}` }
        })
        $toast.success('File uploaded successfully')
        refresh()
        // Kita tutup modal sementara agar data ter-refresh.
        // Idealnya fetch ulang detail task tanpa tutup modal.
        showModal.value = false
    } catch (e) { $toast.error('Upload failed: ' + (e.data?.message || 'Server Error')) }
}
</script>