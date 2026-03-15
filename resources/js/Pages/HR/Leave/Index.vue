<script setup>
import { ref } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import {
    CalendarClock, Plus, CheckCircle, XCircle,
    Loader2, ChevronLeft, ChevronRight, Clock
} from 'lucide-vue-next'

const props = defineProps({
    leaves:    Object,
    employees: Array,
    filters:   Object,
    stats:     Object,
})

const showModal         = ref(false)
const showRejectModal   = ref(false)
const rejectingLeave    = ref(null)
const statusFilter      = ref(props.filters?.status ?? '')
const typeFilter        = ref(props.filters?.type ?? '')

const form = useForm({
    employee_id: '',
    type:        'annual',
    start_date:  '',
    end_date:    '',
    reason:      '',
})

const rejectForm = useForm({
    rejection_reason: '',
})

const submit = () => {
    form.post(route('hr.leave.store'), {
        onSuccess: () => { showModal.value = false; form.reset() },
    })
}

const approve = (id) => {
    router.post(route('hr.leave.approve', id), {}, { preserveScroll: true })
}

const openReject = (leave) => {
    rejectingLeave.value = leave
    rejectForm.reset()
    showRejectModal.value = true
}

const submitReject = () => {
    rejectForm.post(route('hr.leave.reject', rejectingLeave.value.id), {
        preserveScroll: true,
        onSuccess: () => { showRejectModal.value = false; rejectForm.reset() },
    })
}

const deleteLeave = (id) => {
    if (confirm('Delete this request?'))
        router.delete(route('hr.leave.destroy', id), { preserveScroll: true })
}

const applyFilters = () => {
    router.get(route('hr.leave.index'), {
        status: statusFilter.value || undefined,
        type:   typeFilter.value || undefined,
    }, { preserveState: true, replace: true })
}

const goToPage = (url) => { if (url) router.get(url) }

const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'

const typeLabel = {
    annual:    'Annual',
    sick:      'Sick',
    unpaid:    'Unpaid',
    maternity: 'Maternity',
    paternity: 'Paternity',
    other:     'Other',
}

const inputClass    = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all'
const labelClass    = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
</script>

<template>
    <Head title="Leave Management" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Leave Management</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Manage employee leave requests</p>
                </div>
                <button @click="showModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors">
                    <Plus class="w-4 h-4" /> New Request
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-200 dark:border-amber-800 p-4">
                    <p class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ stats.pending }}</p>
                    <p class="text-sm text-amber-600 dark:text-amber-500 mt-0.5">Pending</p>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-200 dark:border-emerald-800 p-4">
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ stats.approved }}</p>
                    <p class="text-sm text-emerald-600 dark:text-emerald-500 mt-0.5">Approved</p>
                </div>
                <div class="bg-rose-50 dark:bg-rose-900/20 rounded-2xl border border-rose-200 dark:border-rose-800 p-4">
                    <p class="text-2xl font-bold text-rose-700 dark:text-rose-400">{{ stats.rejected }}</p>
                    <p class="text-sm text-rose-600 dark:text-rose-500 mt-0.5">Rejected</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex gap-3 flex-wrap">
                <select v-model="statusFilter" @change="applyFilters"
                        class="px-4 py-2.5 bg-white dark:bg-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select v-model="typeFilter" @change="applyFilters"
                        class="px-4 py-2.5 bg-white dark:bg-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    <option value="">All Types</option>
                    <option value="annual">Annual</option>
                    <option value="sick">Sick</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="maternity">Maternity</option>
                    <option value="paternity">Paternity</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div v-if="!leaves?.data?.length" class="text-center py-16">
                    <CalendarClock class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                    <p class="text-slate-500 dark:text-slate-400">No leave requests found</p>
                </div>

                <table v-else class="w-full">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Employee</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Period</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Days</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <tr v-for="leave in leaves.data" :key="leave.id"
                        class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors group">
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                {{ leave.employee?.first_name }} {{ leave.employee?.last_name }}
                            </p>
                            <p class="text-xs text-slate-400">{{ leave.employee?.department?.name || '—' }}</p>
                        </td>
                        <td class="px-6 py-4">
                                <span class="text-sm text-slate-600 dark:text-slate-300 capitalize">
                                    {{ typeLabel[leave.type] }}
                                </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                {{ fmtDate(leave.start_date) }} – {{ fmtDate(leave.end_date) }}
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ leave.days }}</span>
                        </td>
                        <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium border capitalize"
                                      :class="{
                                          'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800': leave.status === 'pending',
                                          'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800': leave.status === 'approved',
                                          'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800': leave.status === 'rejected',
                                      }">
                                    {{ leave.status }}
                                </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <template v-if="leave.status === 'pending'">
                                    <button @click="approve(leave.id)"
                                            class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                                            title="Approve">
                                        <CheckCircle class="w-4 h-4" />
                                    </button>
                                    <button @click="openReject(leave)"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors"
                                            title="Reject">
                                        <XCircle class="w-4 h-4" />
                                    </button>
                                </template>
                                <button @click="deleteLeave(leave.id)"
                                        v-if="leave.status === 'pending'"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="leaves?.last_page > 1"
                     class="flex items-center justify-between px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-sm text-slate-500">Showing {{ leaves.from }}–{{ leaves.to }} of {{ leaves.total }}</p>
                    <div class="flex gap-1">
                        <button @click="goToPage(leaves.prev_page_url)" :disabled="!leaves.prev_page_url"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 disabled:opacity-40 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <button @click="goToPage(leaves.next_page_url)" :disabled="!leaves.next_page_url"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 disabled:opacity-40 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Leave Modal -->
        <Modal :show="showModal" title="New Leave Request" size="md" @close="showModal = false">
            <div class="p-6 space-y-4">
                <div>
                    <label :class="labelClass">Employee <span class="text-rose-500">*</span></label>
                    <select v-model="form.employee_id" :class="[inputClass, form.errors.employee_id ? '!border-rose-400' : '']">
                        <option value="">— Select employee —</option>
                        <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name }}</option>
                    </select>
                    <p v-if="form.errors.employee_id" :class="errorMsgClass">{{ form.errors.employee_id }}</p>
                </div>

                <div>
                    <label :class="labelClass">Leave Type <span class="text-rose-500">*</span></label>
                    <select v-model="form.type" :class="inputClass">
                        <option value="annual">Annual</option>
                        <option value="sick">Sick</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="maternity">Maternity</option>
                        <option value="paternity">Paternity</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label :class="labelClass">Start Date <span class="text-rose-500">*</span></label>
                        <input v-model="form.start_date" type="date" :class="[inputClass, form.errors.start_date ? '!border-rose-400' : '']" />
                        <p v-if="form.errors.start_date" :class="errorMsgClass">{{ form.errors.start_date }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">End Date <span class="text-rose-500">*</span></label>
                        <input v-model="form.end_date" type="date" :class="[inputClass, form.errors.end_date ? '!border-rose-400' : '']" />
                        <p v-if="form.errors.end_date" :class="errorMsgClass">{{ form.errors.end_date }}</p>
                    </div>
                </div>

                <div>
                    <label :class="labelClass">Reason</label>
                    <textarea v-model="form.reason" rows="3" :class="[inputClass, 'resize-none']" placeholder="Optional reason..."></textarea>
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
                        Submit Request
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Reject Modal -->
        <Modal :show="showRejectModal" title="Reject Leave Request" size="sm" @close="showRejectModal = false">
            <div class="p-6">
                <label :class="labelClass">Reason for Rejection <span class="text-rose-500">*</span></label>
                <textarea v-model="rejectForm.rejection_reason" rows="3"
                          :class="[inputClass, 'resize-none', rejectForm.errors.rejection_reason ? '!border-rose-400' : '']"
                          placeholder="Explain why the request is being rejected..."></textarea>
                <p v-if="rejectForm.errors.rejection_reason" :class="errorMsgClass">{{ rejectForm.errors.rejection_reason }}</p>
            </div>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showRejectModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button @click="submitReject" :disabled="rejectForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="rejectForm.processing" class="w-4 h-4 animate-spin" />
                        Reject
                    </button>
                </div>
            </template>
        </Modal>

    </AuthenticatedLayout>
</template>
