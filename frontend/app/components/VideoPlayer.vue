<template>
    <div class="rounded-lg overflow-hidden bg-black aspect-video relative group">
        <video ref="videoRef" :src="src" class="w-full h-full object-contain" controls></video>
    </div>
</template>

<script setup>
defineProps(['src'])
</script>