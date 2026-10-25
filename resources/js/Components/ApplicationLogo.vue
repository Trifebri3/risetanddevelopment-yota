<script setup>
defineProps({
    variant: {
        type: String,
        default: 'landscape', // 'landscape' or 'icon'
    },
});
</script>

<template>
    <div class="inline-flex items-center">
        <img
            v-if="variant === 'icon'"
            src="/logo.svg"
            alt="R&D Workspace Logo"
            class="h-9 w-auto object-contain"
        />
        <img
            v-else
            src="/logolandscape.svg"
            alt="R&D Workspace Logo"
            class="h-9 w-auto max-h-10 object-contain"
        />
    </div>
</template>
