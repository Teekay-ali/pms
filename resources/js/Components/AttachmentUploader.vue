<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import {
    Paperclip, Upload, X, Download, Trash2,
    FileText, FileSpreadsheet, File, Image, Loader2
} from 'lucide-vue-next'

const props = defineProps({
    modelType:   { type: String, required: true }, // 'projects' | 'tasks' | 'expenses'
    modelId:     { type: Number, required: true },
    attachments: { type: Array,  default: () => [] },
    canUpload:   { type: Boolean, default: false },
    canDelete:   { type: Boolean, default: false },
})

const page       = ref(usePage())
const isDragging = ref(false)
const uploading  = ref(false)
const fileInput  = ref(null)

const authUserId = usePage().props.auth.user?.id

const fileIcon = (ext) => {
    if (['jpg','jpeg','png','webp','gif'].includes(ext)) return Image
    if (['xls','xlsx','csv'].includes(ext))              return FileSpreadsheet
    if (['doc','docx','pdf','txt'].includes(ext))        return FileText
    return File
}

const iconColor = (ext) => {
    if (['jpg','jpeg','png','webp','gif'].includes(ext)) return 'text-emerald-500'
    if (['pdf'].includes(ext))                           return 'text-rose-500'
    if (['xls','xlsx','csv'].includes(ext))              return 'text-emerald-600'
    if (['doc','docx'].includes(ext))                    return 'text-blue-500'
    return 'text-slate-400'
}

const canDeleteAttachment = (attachment) => {
    return props.canDelete || attachment.uploaded_by === authUserId
}

const uploadFile = (file) => {
    if (!file) return

    const formData = new FormData()
    formData.append('file', file)

    uploading.value = true

    router.post(
        route('attachments.store', { modelType: props.modelType, modelId: props.modelId }),
        formData,
        {
            preserveScroll: true,
            forceFormData:  true,
            onFinish:       () => { uploading.value = false },
        }
    )
}

const onFileChange = (e) => {
    const file = e.target.files?.[0]
    if (file) uploadFile(file)
    e.target.value = ''
}

const onDrop = (e) => {
    isDragging.value = false
    const file = e.dataTransfer.files?.[0]
    if (file) uploadFile(file)
}

const deleteAttachment = (id) => {
    if (!confirm('Delete this attachment?')) return
    router.delete(route('attachments.destroy', id), { preserveScroll: true })
}
</script>

<template>
    <div class="space-y-3">

        <!-- Upload zone -->
        <div
            v-if="canUpload"
            class="relative border-2 border-dashed rounded-xl p-4 text-center transition-colors cursor-pointer"
            :class="isDragging
                ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20'
                : 'border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
            @click="fileInput?.click()"
        >
            <input
                ref="fileInput"
                type="file"
                class="hidden"
                accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt"
                @change="onFileChange"
            />
            <div class="flex flex-col items-center gap-1.5 pointer-events-none">
                <div v-if="uploading" class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                    <Loader2 class="w-5 h-5 animate-spin" />
                    <span class="text-sm font-medium">Uploading...</span>
                </div>
                <template v-else>
                    <Upload class="w-5 h-5 text-slate-400" />
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        <span class="font-medium text-indigo-600 dark:text-indigo-400">Click to upload</span>
                        or drag and drop
                    </p>
                    <p class="text-xs text-slate-400">Images, PDF, Word, Excel — max 10MB</p>
                </template>
            </div>
        </div>

        <!-- Attachment list -->
        <div v-if="attachments.length > 0" class="space-y-1.5">
            <div
                v-for="att in attachments"
                :key="att.id"
                class="flex items-center gap-3 px-3 py-2.5 bg-slate-50 dark:bg-slate-800/50 rounded-xl group hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
            >
                <!-- Icon -->
                <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 flex items-center justify-center shrink-0">
                    <component
                        :is="fileIcon(att.extension)"
                        class="w-4 h-4"
                        :class="iconColor(att.extension)"
                    />
                </div>

                <!-- Image preview -->
                <div v-if="['jpg','jpeg','png','webp','gif'].includes(att.extension)" class="w-8 h-8 rounded-lg overflow-hidden shrink-0 -ml-10 hidden group-hover:block">
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200 truncate">{{ att.filename }}</p>
                    <p class="text-xs text-slate-400">
                        {{ att.formatted_size }}
                        <span v-if="att.uploader"> · {{ att.uploader.name }}</span>
                    </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-1 shrink-0">
                    <a
                        :href="route('attachments.download', att.id)"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                        title="Download"
                        @click.stop
                    >
                        <Download class="w-3.5 h-3.5" />
                    </a>
                    <button
                        v-if="canDeleteAttachment(att)"
                        @click.stop="deleteAttachment(att.id)"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                        title="Delete"
                    >
                        <Trash2 class="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else-if="!canUpload" class="text-center py-6">
            <Paperclip class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2" />
            <p class="text-sm text-slate-400">No attachments yet.</p>
        </div>

    </div>
</template>
