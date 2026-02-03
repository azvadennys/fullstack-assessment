<template>
    <div
        class="group relative bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 flex flex-col h-full">
        <div class="p-4 pb-2 flex justify-between items-start">
            <span :class="[
                'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                priorityColors[task.priority]
            ]">
                {{ task.priority }}
            </span>
            <button @click.stop="$emit('delete', task.id)"
                class="text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                <Trash2 class="w-4 h-4" />
            </button>
        </div>

        <div class="px-4 flex-1 cursor-pointer" @click="$emit('click')">
            <h3 class="font-semibold text-gray-900 mb-1 group-hover:text-blue-600 transition-colors line-clamp-1">
                {{ task.title }}
            </h3>
            <p class="text-sm text-gray-500 line-clamp-2 mb-3 h-10">
                {{ task.description || 'No description provided.' }}
            </p>

            <div v-if="task.attachments?.length" class="flex gap-1.5 mb-3 overflow-hidden">
                <div v-for="file in task.attachments.slice(0, 3)" :key="file.id"
                    class="w-8 h-8 rounded-lg bg-gray-50 border flex items-center justify-center text-gray-400 shrink-0">
                    <FileVideo v-if="file.mime_type.startsWith('video/')" class="w-4 h-4 text-blue-500" />
                    <FileImage v-else-if="file.mime_type.startsWith('image/')" class="w-4 h-4 text-purple-500" />
                    <FileText v-else class="w-4 h-4" />
                </div>
                <div v-if="task.attachments.length > 3"
                    class="w-8 h-8 rounded-lg bg-gray-50 border flex items-center justify-center text-[10px] font-bold text-gray-500">
                    +{{ task.attachments.length - 3 }}
                </div>
            </div>
        </div>

        <div
            class="px-4 py-3 border-t border-gray-50 bg-gray-50/50 rounded-b-xl flex justify-between items-center mt-auto">
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <Paperclip class="w-3 h-3" /> {{ task.attachments?.length || 0 }}
            </div>

            <select :value="task.status" @change="$emit('updateStatus', task.id, $event.target.value)" @click.stop
                class="text-xs bg-white border border-gray-200 rounded-md py-1 pl-2 pr-6 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>
</template>

<script setup>
import { Trash2, Paperclip, FileVideo, FileImage, FileText } from 'lucide-vue-next'

defineProps(['task'])
defineEmits(['delete', 'updateStatus', 'click'])

const priorityColors = {
    high: 'bg-red-50 text-red-600 border border-red-100',
    medium: 'bg-amber-50 text-amber-600 border border-amber-100',
    low: 'bg-emerald-50 text-emerald-600 border border-emerald-100'
}
</script>