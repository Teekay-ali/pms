<script setup>
import { X } from 'lucide-vue-next'
import { onMounted, onUnmounted } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md', // sm, md, lg, xl, 2xl
        validator: (v) => ['sm', 'md', 'lg', 'xl', '2xl', '3xl'].includes(v),
    },
})

const emit = defineEmits(['close'])

const close = () => emit('close')

const sizeClasses = {
    sm:    'max-w-md',
    md:    'max-w-lg',
    lg:    'max-w-2xl',
    xl:    'max-w-4xl',
    '2xl': 'max-w-5xl',
    '3xl': 'max-w-6xl',
}

const handleKeydown = (e) => {
    if (e.key === 'Escape' && props.show) close()
}

onMounted(() => document.addEventListener('keydown', handleKeydown))
onUnmounted(() => document.removeEventListener('keydown', handleKeydown))
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/60 backdrop-blur-sm"
                    @click="close"
                />

                <!-- Modal panel -->
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="show"
                        class="relative w-full bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden"
                        :class="sizeClasses[size]"
                    >
                        <!-- Header -->
                        <div
                            v-if="title"
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800"
                        >
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                {{ title }}
                            </h2>
                            <button
                                @click="close"
                                class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                            >
                                <X class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Close button (no title) -->
                        <button
                            v-else
                            @click="close"
                            class="absolute top-4 right-4 z-10 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                        >
                            <X class="w-5 h-5" />
                        </button>

                        <!-- Content slot -->
                        <div class="overflow-y-auto max-h-[80vh]">
                            <slot />
                        </div>

                        <!-- Footer slot (optional) -->
                        <div
                            v-if="$slots.footer"
                            class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50"
                        >
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
