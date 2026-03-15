<script setup>
import { ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AttachmentUploader from '@/Components/AttachmentUploader.vue'
import {
    ArrowLeft, User, Building2, Phone, Mail, Calendar,
    CreditCard, AlertTriangle, FileText, Pencil, Upload,
    CheckCircle, XCircle, Clock
} from 'lucide-vue-next'

const props = defineProps({
    employee: Object,
})

const activeTab    = ref('overview')
const photoInput   = ref(null)

const tabs = [
    { key: 'overview',  label: 'Overview' },
    { key: 'documents', label: 'Documents' },
    { key: 'leave',     label: 'Leave History' },
]

const statusConfig = {
    active:     { label: 'Active',     class: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    inactive:   { label: 'Inactive',   class: 'bg-slate-50 text-slate-600 border-slate-200' },
    terminated: { label: 'Terminated', class: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
    on_leave:   { label: 'On Leave',   class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
}

const leaveStatusConfig = {
    pending:  { label: 'Pending',  icon: Clock,        class: 'text-amber-500' },
    approved: { label: 'Approved', icon: CheckCircle,  class: 'text-emerald-500' },
    rejected: { label: 'Rejected', icon: XCircle,      class: 'text-rose-500' },
}

const fmtDate  = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'
const initials = (emp) => `${emp.first_name?.[0] ?? ''}${emp.last_name?.[0] ?? ''}`.toUpperCase()

const uploadPhoto = (e) => {
    const file = e.target.files?.[0]
    if (!file) return
    const form = useForm({ photo: file })
    form.post(route('hr.employees.photo', props.employee.id), {
        preserveScroll: true,
        forceFormData: true,
    })
}

const employmentTypeLabel = {
    full_time: 'Full Time',
    part_time: 'Part Time',
    contract:  'Contract',
    intern:    'Intern',
}
</script>

<template>
    <Head :title="employee.first_name + ' ' + employee.last_name" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Back -->
            <Link :href="route('hr.employees.index')"
                  class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <ArrowLeft class="w-4 h-4" /> Back to Employees
            </Link>

            <!-- Profile header -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <div class="flex flex-col sm:flex-row gap-6 items-start">

                    <!-- Photo -->
                    <div class="relative shrink-0">
                        <div class="w-24 h-24 rounded-2xl overflow-hidden bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white text-2xl font-bold">
                            <img v-if="employee.photo" :src="'/storage/' + employee.photo" class="w-full h-full object-cover" />
                            <span v-else>{{ initials(employee) }}</span>
                        </div>
                        <button @click="photoInput?.click()"
                                class="absolute -bottom-2 -right-2 w-8 h-8 bg-indigo-600 hover:bg-indigo-500 rounded-full flex items-center justify-center text-white shadow-lg transition-colors">
                            <Upload class="w-3.5 h-3.5" />
                        </button>
                        <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="uploadPhoto" />
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 flex-wrap mb-1">
                            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ employee.first_name }} {{ employee.last_name }}
                            </h1>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium border"
                                  :class="statusConfig[employee.status]?.class">
                                {{ statusConfig[employee.status]?.label }}
                            </span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 mb-3">
                            {{ employee.job_title || 'No title' }}
                            <span v-if="employee.department"> · {{ employee.department.name }}</span>
                        </p>
                        <div class="flex flex-wrap gap-4 text-sm text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1.5"><Mail class="w-4 h-4" /> {{ employee.email }}</span>
                            <span v-if="employee.phone" class="flex items-center gap-1.5"><Phone class="w-4 h-4" /> {{ employee.phone }}</span>
                            <span v-if="employee.hire_date" class="flex items-center gap-1.5"><Calendar class="w-4 h-4" /> Hired {{ fmtDate(employee.hire_date) }}</span>
                            <span v-if="employee.employee_number" class="flex items-center gap-1.5"><FileText class="w-4 h-4" /> {{ employee.employee_number }}</span>
                        </div>
                    </div>

                    <!-- Leave balance cards -->
                    <div class="flex gap-3 shrink-0">
                        <div class="text-center p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl min-w-[80px]">
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ employee.annual_leave_balance }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Annual<br>Leave</p>
                        </div>
                        <div class="text-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl min-w-[80px]">
                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ employee.sick_leave_balance }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Sick<br>Leave</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="flex border-b border-slate-100 dark:border-slate-800">
                    <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
                            class="px-6 py-4 text-sm font-medium border-b-2 transition-all whitespace-nowrap"
                            :class="activeTab === tab.key
                                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
                                : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'">
                        {{ tab.label }}
                    </button>
                </div>

                <div class="p-6">

                    <!-- Overview -->
                    <div v-if="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <!-- Personal -->
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">Personal Information</p>
                            <div class="space-y-3">
                                <div v-for="(val, label) in {
                                    'Date of Birth': fmtDate(employee.date_of_birth),
                                    'Gender': employee.gender,
                                    'Nationality': employee.nationality,
                                    'Address': employee.address,
                                }" :key="label" class="flex gap-3">
                                    <span class="text-sm text-slate-500 dark:text-slate-400 w-36 shrink-0">{{ label }}</span>
                                    <span class="text-sm text-slate-800 dark:text-slate-100 capitalize">{{ val || '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Employment -->
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">Employment</p>
                            <div class="space-y-3">
                                <div v-for="(val, label) in {
                                    'Type': employmentTypeLabel[employee.employment_type],
                                    'Hire Date': fmtDate(employee.hire_date),
                                    'Termination': fmtDate(employee.termination_date),
                                    'Bank': employee.bank_name,
                                }" :key="label" class="flex gap-3">
                                    <span class="text-sm text-slate-500 dark:text-slate-400 w-36 shrink-0">{{ label }}</span>
                                    <span class="text-sm text-slate-800 dark:text-slate-100">{{ val || '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Emergency -->
                        <div class="lg:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">Emergency Contact</p>
                            <div class="flex flex-wrap gap-6">
                                <div class="flex gap-3">
                                    <span class="text-sm text-slate-500 w-24 shrink-0">Name</span>
                                    <span class="text-sm text-slate-800 dark:text-slate-100">{{ employee.emergency_contact_name || '—' }}</span>
                                </div>
                                <div class="flex gap-3">
                                    <span class="text-sm text-slate-500 w-24 shrink-0">Phone</span>
                                    <span class="text-sm text-slate-800 dark:text-slate-100">{{ employee.emergency_contact_phone || '—' }}</span>
                                </div>
                                <div class="flex gap-3">
                                    <span class="text-sm text-slate-500 w-24 shrink-0">Relation</span>
                                    <span class="text-sm text-slate-800 dark:text-slate-100">{{ employee.emergency_contact_relation || '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div v-else-if="activeTab === 'documents'">
                        <AttachmentUploader
                            model-type="employees"
                            :model-id="employee.id"
                            :attachments="employee.attachments ?? []"
                            :can-upload="true"
                            :can-delete="true"
                        />
                    </div>

                    <!-- Leave History -->
                    <div v-else-if="activeTab === 'leave'">
                        <div v-if="!employee.leave_requests?.length" class="text-center py-12">
                            <Calendar class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No leave requests yet.</p>
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="leave in employee.leave_requests" :key="leave.id"
                                 class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                                <component :is="leaveStatusConfig[leave.status]?.icon"
                                           class="w-5 h-5 shrink-0"
                                           :class="leaveStatusConfig[leave.status]?.class" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100 capitalize">
                                        {{ leave.type.replace('_', ' ') }} Leave
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        {{ fmtDate(leave.start_date) }} – {{ fmtDate(leave.end_date) }} · {{ leave.days }} days
                                    </p>
                                    <p v-if="leave.rejection_reason" class="text-xs text-rose-500 mt-0.5">
                                        {{ leave.rejection_reason }}
                                    </p>
                                </div>
                                <span class="text-xs font-medium capitalize px-2.5 py-1 rounded-full border"
                                      :class="{
                                          'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400': leave.status === 'pending',
                                          'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400': leave.status === 'approved',
                                          'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400': leave.status === 'rejected',
                                      }">
                                    {{ leave.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
