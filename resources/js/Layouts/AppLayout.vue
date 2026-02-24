<script setup>
import { Toaster, toast } from 'vue-sonner'
</script>

<template>
    <div>
        <slot />
        <Toaster position="bottom-right" :rich-colors="true" :close-button="false" />
    </div>
</template>
