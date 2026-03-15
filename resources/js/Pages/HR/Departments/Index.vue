<script setup>
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Building2, Plus, Pencil, Trash2, Users, Loader2 } from 'lucide-vue-next'

const props = defineProps({
    departments: Array,
    employees:   Array,
})

const showModal   = ref(false)
const editingDept = ref(null)

const form = useForm({
    name:        '',
    description: '',
    manager_id:  '',
})

const openCreate = () => {
    editingDept.value = null
    form.reset()
    showModal.value = true
}

const openEdit = (dept) => {
    editingDept.value   = dept
    form.name           = dept.name
    form.description    = dept.description ?? ''
    form.manager_id     = dept.manager_id ?? ''
    showModal.value     = true
}

const submit = () => {
    if (editingDept.value) {
        form.put(route('hr.departments.update', editingDept.value.id), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    } else {
        form.post(route('hr.departments.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

const deleteDept = (id) => {
    if (confirm('Delete this department?'))
        router.delete(route('hr.departments.destroy', id), { preserveScroll: true })
}

const inputClass    = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all'
const labelClass    = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
</script>

<template>
    <Head title="Departments" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Departments</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">{{ departments.length }} departments</p>
                </div>
                <button @click="openCreate"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors">
                    <Plus class="w-4 h-4" /> Add Department
                </button>
            </div>

            <!-- Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="dept in departments" :key="dept.id"
                     class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 group hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl flex items-center justify-center">
                            <Building2 class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openEdit(dept)"
                                    class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors">
                                <Pencil class="w-4 h-4" />
                            </button>
                            <button @click="deleteDept(dept.id)"
                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <h3 class="font-semibold text-slate-800 dark:text-slate-100 mb-1">{{ dept.name }}</h3>
                    <p class="text-xs text-slate-400 mb-3 min-h-[32px]">{{ dept.description || 'No description' }}</p>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                            <Users class="w-4 h-4" />
                            {{ dept.employees_count }} employees
                        </div>
                        <span v-if="dept.manager" class="text-xs text-slate-400">
                            Mgr: {{ dept.manager.first_name }} {{ dept.manager.last_name }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Modal -->
        <Modal :show="showModal" :title="editingDept ? 'Edit Department' : 'Add Department'" size="sm" @close="showModal = false">
            <div class="p-6 space-y-4">
                <div>
                    <label :class="labelClass">Name <span class="text-rose-500">*</span></label>
                    <input v-model="form.name" type="text" :class="[inputClass, form.errors.name ? '!border-rose-400' : '']" />
                    <p v-if="form.errors.name" :class="errorMsgClass">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label :class="labelClass">Description</label>
                    <input v-model="form.description" type="text" :class="inputClass" />
                </div>
                <div>
                    <label :class="labelClass">Manager</label>
                    <select v-model="form.manager_id" :class="inputClass">
                        <option value="">— None —</option>
                        <option v-for="e in employees" :key="e.id" :value="e.id">
                            {{ e.name }} {{ e.job_title ? `(${e.job_title})` : '' }}
                        </option>
                    </select>
                </div>
            </div>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button @click="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        {{ editingDept ? 'Save' : 'Add' }}
                    </button>
                </div>
            </template>
        </Modal>

    </AuthenticatedLayout>
</template>
