<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import {
    UserCog, Plus, Search, Pencil, Trash2, Loader2,
    ChevronLeft, ChevronRight, Mail, Phone, Building2, Eye
} from 'lucide-vue-next'

const props = defineProps({
    employees:   Object,
    departments: Array,
    filters:     Object,
    stats:       Object,
})

const showModal    = ref(false)
const editingEmp   = ref(null)
const search       = ref(props.filters?.search ?? '')
const deptFilter   = ref(props.filters?.department ?? '')
const statusFilter = ref(props.filters?.status ?? '')

const form = useForm({
    first_name:                 '',
    last_name:                  '',
    email:                      '',
    phone:                      '',
    date_of_birth:              '',
    gender:                     '',
    nationality:                '',
    id_number:                  '',
    address:                    '',
    emergency_contact_name:     '',
    emergency_contact_phone:    '',
    emergency_contact_relation: '',
    employee_number:            '',
    job_title:                  '',
    department_id:              '',
    employment_type:            'full_time',
    status:                     'active',
    hire_date:                  '',
    salary:                     '',
    bank_name:                  '',
    bank_account:               '',
    annual_leave_balance:       21,
    sick_leave_balance:         10,
    user_id:                    '',
})

const openCreate = () => {
    editingEmp.value = null
    form.reset()
    form.employment_type        = 'full_time'
    form.status                 = 'active'
    form.annual_leave_balance   = 21
    form.sick_leave_balance     = 10
    showModal.value = true
}

const openEdit = (emp) => {
    editingEmp.value = emp
    Object.keys(form.data()).forEach(k => {
        form[k] = emp[k] ?? (typeof form[k] === 'number' ? 0 : '')
    })
    form.date_of_birth  = emp.date_of_birth?.substring(0, 10) ?? ''
    form.hire_date      = emp.hire_date?.substring(0, 10) ?? ''
    showModal.value = true
}

const submit = () => {
    if (editingEmp.value) {
        form.put(route('hr.employees.update', editingEmp.value.id), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    } else {
        form.post(route('hr.employees.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

const deleteEmp = (id) => {
    if (confirm('Remove this employee?'))
        router.delete(route('hr.employees.destroy', id), { preserveScroll: true })
}

const applyFilters = () => {
    router.get(route('hr.employees.index'), {
        search:     search.value || undefined,
        department: deptFilter.value || undefined,
        status:     statusFilter.value || undefined,
    }, { preserveState: true, replace: true })
}

const goToPage = (url) => { if (url) router.get(url) }

const statusConfig = {
    active:     { label: 'Active',     badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    inactive:   { label: 'Inactive',   badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
    terminated: { label: 'Terminated', badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
    on_leave:   { label: 'On Leave',   badge: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
}

const employmentTypeLabel = {
    full_time: 'Full Time',
    part_time: 'Part Time',
    contract:  'Contract',
    intern:    'Intern',
}

const initials = (emp) => `${emp.first_name?.[0] ?? ''}${emp.last_name?.[0] ?? ''}`.toUpperCase()

const inputClass    = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all'
const labelClass    = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
const errorClass    = '!border-rose-400 dark:!border-rose-500'
</script>

<template>
    <Head title="Employees" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Employees</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">{{ stats.total }} total employees</p>
                </div>
                <button @click="openCreate"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors">
                    <Plus class="w-4 h-4" /> Add Employee
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div v-for="(val, key) in { 'Total': stats.total, 'Active': stats.active, 'On Leave': stats.on_leave, 'Inactive': stats.inactive }"
                     :key="key"
                     class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ val }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ key }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <div class="relative flex-1 min-w-48">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input v-model="search" @keyup.enter="applyFilters" type="text" placeholder="Search employees..."
                           class="w-full pl-9 pr-4 py-2.5 bg-white dark:bg-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all" />
                </div>
                <select v-model="deptFilter" @change="applyFilters"
                        class="px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    <option value="">All Departments</option>
                    <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
                <select v-model="statusFilter" @change="applyFilters"
                        class="px-4 py-2.5 bg-white dark:bg-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="on_leave">On Leave</option>
                    <option value="inactive">Inactive</option>
                    <option value="terminated">Terminated</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div v-if="!employees?.data?.length" class="text-center py-16">
                    <UserCog class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                    <p class="text-slate-500 dark:text-slate-400">No employees found</p>
                </div>
                <table v-else class="w-full">
                    <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Employee</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Department</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3.5"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    <tr v-for="emp in employees.data" :key="emp.id"
                        class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div v-if="emp.photo"
                                     class="w-9 h-9 rounded-xl overflow-hidden shrink-0">
                                    <img :src="'/storage/' + emp.photo" class="w-full h-full object-cover" />
                                </div>
                                <div v-else
                                     class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                    {{ initials(emp) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm">
                                        {{ emp.first_name }} {{ emp.last_name }}
                                    </p>
                                    <p class="text-xs text-slate-400">{{ emp.job_title || '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300">
                                <Building2 class="w-3.5 h-3.5 text-slate-400" />
                                {{ emp.department?.name || '—' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                                <span class="text-sm text-slate-600 dark:text-slate-300">
                                    {{ employmentTypeLabel[emp.employment_type] }}
                                </span>
                        </td>
                        <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium border"
                                      :class="statusConfig[emp.status]?.badge">
                                    {{ statusConfig[emp.status]?.label }}
                                </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <Mail class="w-3 h-3" /> {{ emp.email }}
                                </div>
                                <div v-if="emp.phone" class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <Phone class="w-3 h-3" /> {{ emp.phone }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Link :href="route('hr.employees.show', emp.id)"
                                      class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors">
                                    <Eye class="w-4 h-4" />
                                </Link>
                                <button @click="openEdit(emp)"
                                        class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors">
                                    <Pencil class="w-4 h-4" />
                                </button>
                                <button @click="deleteEmp(emp.id)"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="employees?.last_page > 1"
                     class="flex items-center justify-between px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-sm text-slate-500">Showing {{ employees.from }}–{{ employees.to }} of {{ employees.total }}</p>
                    <div class="flex gap-1">
                        <button @click="goToPage(employees.prev_page_url)" :disabled="!employees.prev_page_url"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 disabled:opacity-40 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <button @click="goToPage(employees.next_page_url)" :disabled="!employees.next_page_url"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 disabled:opacity-40 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Modal -->
        <Modal :show="showModal" :title="editingEmp ? 'Edit Employee' : 'Add Employee'" size="lg" @close="showModal = false">
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">

                <!-- Personal Info -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">Personal Information</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label :class="labelClass">First Name <span class="text-rose-500">*</span></label>
                            <input v-model="form.first_name" type="text" :class="[inputClass, form.errors.first_name ? errorClass : '']" />
                            <p v-if="form.errors.first_name" :class="errorMsgClass">{{ form.errors.first_name }}</p>
                        </div>
                        <div>
                            <label :class="labelClass">Last Name <span class="text-rose-500">*</span></label>
                            <input v-model="form.last_name" type="text" :class="[inputClass, form.errors.last_name ? errorClass : '']" />
                            <p v-if="form.errors.last_name" :class="errorMsgClass">{{ form.errors.last_name }}</p>
                        </div>
                        <div>
                            <label :class="labelClass">Email <span class="text-rose-500">*</span></label>
                            <input v-model="form.email" type="email" :class="[inputClass, form.errors.email ? errorClass : '']" />
                            <p v-if="form.errors.email" :class="errorMsgClass">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label :class="labelClass">Phone</label>
                            <input v-model="form.phone" type="text" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">Date of Birth</label>
                            <input v-model="form.date_of_birth" type="date" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">Gender</label>
                            <select v-model="form.gender" :class="inputClass">
                                <option value="">— Select —</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label :class="labelClass">Nationality</label>
                            <input v-model="form.nationality" type="text" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">ID Number</label>
                            <input v-model="form.id_number" type="text" :class="inputClass" />
                        </div>
                        <div class="sm:col-span-2">
                            <label :class="labelClass">Address</label>
                            <textarea v-model="form.address" rows="2" :class="[inputClass, 'resize-none']"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">Emergency Contact</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label :class="labelClass">Name</label>
                            <input v-model="form.emergency_contact_name" type="text" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">Phone</label>
                            <input v-model="form.emergency_contact_phone" type="text" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">Relation</label>
                            <input v-model="form.emergency_contact_relation" type="text" placeholder="e.g. Spouse" :class="inputClass" />
                        </div>
                    </div>
                </div>

                <!-- Employment -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">Employment Details</p>

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Linked System Account</label>
                        <select v-model="form.user_id" :class="inputClass">
                            <option value="">— No system account —</option>
                            <option v-for="u in $page.props.users" :key="u.id" :value="u.id">
                                {{ u.name }} ({{ u.email }})
                            </option>
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Link this employee to a SitePro login account</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label :class="labelClass">Employee Number</label>
                            <input v-model="form.employee_number" type="text" placeholder="e.g. EMP-001" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">Job Title</label>
                            <input v-model="form.job_title" type="text" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">Department</label>
                            <select v-model="form.department_id" :class="inputClass">
                                <option value="">— None —</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label :class="labelClass">Employment Type <span class="text-rose-500">*</span></label>
                            <select v-model="form.employment_type" :class="inputClass">
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                        <div>
                            <label :class="labelClass">Status <span class="text-rose-500">*</span></label>
                            <select v-model="form.status" :class="inputClass">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="on_leave">On Leave</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                        <div>
                            <label :class="labelClass">Hire Date</label>
                            <input v-model="form.hire_date" type="date" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">Salary</label>
                            <input v-model="form.salary" type="number" step="0.01" min="0" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">Annual Leave Balance (days)</label>
                            <input v-model="form.annual_leave_balance" type="number" min="0" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">Sick Leave Balance (days)</label>
                            <input v-model="form.sick_leave_balance" type="number" min="0" :class="inputClass" />
                        </div>
                    </div>
                </div>

                <!-- Bank -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">Banking Details</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label :class="labelClass">Bank Name</label>
                            <input v-model="form.bank_name" type="text" :class="inputClass" />
                        </div>
                        <div>
                            <label :class="labelClass">Account Number</label>
                            <input v-model="form.bank_account" type="text" :class="inputClass" />
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button @click="submit" :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        {{ editingEmp ? 'Save Changes' : 'Add Employee' }}
                    </button>
                </div>
            </template>
        </Modal>

    </AuthenticatedLayout>
</template>
