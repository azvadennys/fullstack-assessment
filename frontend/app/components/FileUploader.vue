<template>
    <div @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop.prevent="handleDrop"
        class="border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer"
        :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'"
        @click="$refs.fileInput.click()">
        <input ref="fileInput" type="file" class="hidden" @change="handleFileSelect" multiple />

        <div v-if="uploading" class="space-y-2">
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 100%"></div>
            </div>
            <p class="text-xs text-blue-600 font-medium animate-pulse">Processing upload...</p>
        </div>

        <div v-else>
            <div class="mx-auto h-12 w-12 text-gray-400 flex items-center justify-center">
                <slot name="icon"></slot>
            </div>
            <p class="mt-2 text-sm text-gray-600">
                <span class="font-semibold text-blue-600">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-gray-500 mt-1">Images, Docs, Video (Max 50MB)</p>
        </div>
    </div>
</template>

<script setup>
const emit = defineEmits(['upload'])
const isDragging = ref(false)
const uploading = ref(false)

const handleFileSelect = (e) => processFiles(e.target.files)
const handleDrop = (e) => {
    isDragging.value = false
    processFiles(e.dataTransfer.files)
}

const processFiles = async (files) => {
    if (!files.length) return
    uploading.value = true
    emit('upload', files[0])
    setTimeout(() => uploading.value = false, 1000) // Reset UI after emit
}
</script>