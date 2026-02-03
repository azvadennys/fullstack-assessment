<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

        <div
            class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in duration-200">

            <div
                class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ isEdit ? 'Task Details' : 'Create New Task' }}</h2>
                    <p class="text-sm text-gray-500" v-if="isEdit">Created on {{ formatDate(task.created_at) }}</p>
                </div>
                <button @click="$emit('close')"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                    <X class="w-6 h-6" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <input v-model="form.title" placeholder="Enter task title"
                            class="w-full text-2xl font-bold border-none p-0 focus:ring-0 placeholder-gray-300 text-gray-900" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Status</label>
                            <select v-model="form.status"
                                class="mt-1 block w-full bg-transparent border-none p-0 text-sm font-medium text-gray-900 focus:ring-0">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <label class="text-xs font-semibold text-gray-500 uppercase">Priority</label>
                            <select v-model="form.priority"
                                class="mt-1 block w-full bg-transparent border-none p-0 text-sm font-medium text-gray-900 focus:ring-0">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea v-model="form.description" rows="4" placeholder="Add a more detailed description..."
                            class="w-full border-gray-200 rounded-xl text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div v-if="isEdit" class="pt-6 border-t border-gray-100">
                        <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <Paperclip class="w-4 h-4 text-blue-500" /> Attachments
                        </h3>

                        <div v-if="task?.attachments?.length" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div v-for="file in task.attachments" :key="file.id"
                                class="group relative border border-gray-200 rounded-xl overflow-hidden bg-gray-50">

                                <div v-if="file.mime_type.startsWith('video/')">
                                    <VideoPlayer :src="getFileUrl(file.file_path)" />
                                </div>

                                <div v-else-if="file.mime_type.startsWith('image/')" class="aspect-video relative">
                                    <img :src="getFileUrl(file.file_path)" class="w-full h-full object-cover" />
                                </div>

                                <div v-else class="p-4 flex items-center gap-3">
                                    <div class="p-2 bg-white rounded-lg border">
                                        <FileText class="w-6 h-6 text-gray-400" />
                                    </div>
                                    <span class="text-sm font-medium truncate flex-1">{{ file.file_name }}</span>
                                </div>

                                <a :href="getFileUrl(file.file_path)" target="_blank"
                                    class="absolute top-2 right-2 p-1.5 bg-white/90 backdrop-blur rounded-lg shadow-sm hover:text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Download class="w-4 h-4" />
                                </a>
                            </div>
                        </div>

                        <FileUploader @upload="(f) => $emit('upload', f, task.id)">
                            <template #icon>
                                <UploadCloud class="w-6 h-6" />
                            </template>
                        </FileUploader>
                    </div>
                </div>

                <div class="bg-gray-50 -my-6 -mr-6 p-6 border-l border-gray-100 flex flex-col">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <MessageSquare class="w-4 h-4" /> Comments
                    </h3>

                    <div class="flex-1 overflow-y-auto space-y-4 mb-4 pr-2 custom-scrollbar">
                        <div v-if="!tempComments.length" class="text-center py-10 text-gray-400 text-sm">
                            No comments yet.
                        </div>
                        <div v-for="c in tempComments" :key="c.id" class="flex gap-3">
                            <div
                                class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700 shrink-0">
                                {{ c.user.charAt(0) }}
                            </div>
                            <div
                                class="bg-white p-3 rounded-2xl rounded-tl-none shadow-sm border border-gray-100 text-sm">
                                <span class="font-bold text-gray-900 block text-xs mb-1">{{ c.user }}</span>
                                <p class="text-gray-600">{{ c.text }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <input v-model="newComment" @keyup.enter="addComment" placeholder="Write a comment..."
                            class="w-full pl-4 pr-10 py-2.5 rounded-xl border-gray-200 text-sm focus:border-blue-500 focus:ring-blue-500 shadow-sm" />
                        <button @click="addComment"
                            class="absolute right-2 top-2 p-1 text-blue-600 hover:bg-blue-50 rounded-lg">
                            <Send class="w-4 h-4" />
                        </button>
                    </div>
                </div>

            </div>

            <div class="p-6 border-t border-gray-100 bg-white flex justify-between items-center">
                <button v-if="isEdit" class="text-red-500 text-sm font-medium hover:underline">Delete Task</button>
                <div class="flex gap-3 ml-auto">
                    <button @click="$emit('close')"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                    <button @click="save"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg shadow-blue-600/20 transition-all">
                        {{ isEdit ? 'Save Changes' : 'Create Task' }}
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { X, Paperclip, UploadCloud, FileText, Download, MessageSquare, Send } from 'lucide-vue-next'
import VideoPlayer from './VideoPlayer.vue'
import FileUploader from './FileUploader.vue'

const props = defineProps(['task'])
const emit = defineEmits(['close', 'save', 'upload'])

const isEdit = computed(() => !!props.task?.id)
const form = reactive({ ...props.task } || { title: '', status: 'pending', priority: 'medium', description: '' })
const newComment = ref('')

// Dummy Real-time comments simulation
const tempComments = ref([
    { id: 1, user: 'System', text: 'Task created.' }
])

const save = () => emit('save', form)

const addComment = () => {
    if (!newComment.value) return
    tempComments.value.push({ id: Date.now(), user: 'You', text: newComment.value })
    newComment.value = ''
}

const getFileUrl = (path) => `http://localhost:8000/storage/${path}`
const formatDate = (date) => new Date(date).toLocaleDateString()
</script>

<style scoped>
/* Custom Scrollbar for comments */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
</style>