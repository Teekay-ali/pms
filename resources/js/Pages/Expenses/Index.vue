<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    Receipt,
    Plus,
    Search,
    SlidersHorizontal,
    Pencil,
    Trash2,
    CheckCircle,
    XCircle,
    Loader2,
    ChevronLeft,
    ChevronRight,
    CalendarDays,
    FolderKanban,
    UserCircle,
} from 'lucide-vue-next'
import AttachmentUploader from "@/Components/AttachmentUploader.vue";

const props = defineProps({
    expenses: Object,
    projects: Array,
})

const page     = usePage()
const perms    = computed(() => page.props.auth.permissions ?? [])
const can      = (p) => perms.value.includes(p)

// ── Filters ────────────────────────────────────────────
const search        = ref('')
const statusFilter  = ref('all')
const projectFilter = ref('all')

// ── Modal ──────────────────────────────────────────────
const showModal     = ref(false)
const editingExpense = ref(null)

// ── Form ───────────────────────────────────────────────
const form = useForm({
    project_id:  '',
    amount:      '',
    description: '',
    date:        '',
})

// ── Status config ──────────────────────────────────────
const statusConfig = {
    pending:  { label: 'Pending',  badge: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
    approved: { label: 'Approved', badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    rejected: { label: 'Rejected', badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
}

// ── Filtered rows (client-side on current page) ────────
const filteredExpenses = computed(() => {
    let items = props.expenses?.data ?? []
    if (search.value)
        items = items.filter(e =>
            e.description.toLowerCase().includes(search.value.toLowerCase()) ||
            e.project?.name.toLowerCase().includes(search.value.toLowerCase())
        )
    if (statusFilter.value !== 'all')
        items = items.filter(e => e.status === statusFilter.value)
    if (projectFilter.value !== 'all')
        items = items.filter(e => e.project_id == projectFilter.value)
    return items
})

// Summary totals from current page (filtered)
const totals = computed(() => ({
    all:      filteredExpenses.value.reduce((s, e) => s + parseFloat(e.amount), 0),
    pending:  filteredExpenses.value.filter(e => e.status === 'pending').reduce((s, e) => s + parseFloat(e.amount), 0),
    approved: filteredExpenses.value.filter(e => e.status === 'approved').reduce((s, e) => s + parseFloat(e.amount), 0),
}))

// ── Helpers ────────────────────────────────────────────
const fmtMoney = (v) => new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(v)
const fmtDate  = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'
const initials = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('') ?? '?'

// ── CRUD ───────────────────────────────────────────────
const openCreate = () => {
    editingExpense.value = null
    form.reset()
    showModal.value = true
}

const openEdit = (expense) => {
    editingExpense.value  = expense
    form.project_id       = expense.project_id ?? ''
    form.amount           = expense.amount
    form.description      = expense.description
    form.date             = expense.date?.substring(0, 10) ?? ''
    showModal.value       = true
}

const submit = () => {
    if (editingExpense.value) {
        form.put(route('expenses.update', editingExpense.value.id), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    } else {
        form.post(route('expenses.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

const approveExpense = (id) => router.patch(route('expenses.approve', id))
const rejectExpense  = (id) => router.patch(route('expenses.reject', id))
const deleteExpense  = (id) => { if (confirm('Delete this expense?')) router.delete(route('expenses.destroy', id)) }
const goToPage       = (url) => { if (url) router.get(url) }

// ── Classes ────────────────────────────────────────────
const inputClass    = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 dark:focus:border-indigo-600 transition-all'
const errorClass    = '!border-rose-400 dark:!border-rose-600'
const labelClass    = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
</script>

<template>
    <Head title="Expenses" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- ── Page header ──────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Expenses</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                        {{ expenses?.total ?? 0 }} total expense{{ expenses?.total !== 1 ? 's' : '' }}
                    </p>
                </div>
                <button
                    v-if="can('expenses.create')"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
                >
                    <Plus class="w-4 h-4" /> New Expense
                </button>
            </div>

            <!-- ── Summary cards ────────────────────────────── -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Total</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ fmtMoney(totals.all) }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pending approval</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ fmtMoney(totals.pending) }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Approved</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ fmtMoney(totals.approved) }}</p>
                </div>
            </div>

            <!-- ── Filters ──────────────────────────────────── -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Search -->
                    <div class="relative flex-1">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search description or project…"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                        />
                    </div>
                    <!-- Status filter -->
                    <div class="relative">
                        <SlidersHorizontal class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                        <select v-model="statusFilter" class="pl-10 pr-8 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all appearance-none cursor-pointer">
                            <option value="all">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <!-- Project filter -->
                    <div class="relative">
                        <FolderKanban class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                        <select v-model="projectFilter" class="pl-10 pr-8 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all appearance-none cursor-pointer">
                            <option value="all">All Projects</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ── Empty state ──────────────────────────────── -->
            <div
                v-if="filteredExpenses.length === 0"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-16 text-center"
            >
                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <Receipt class="w-6 h-6 text-slate-400" />
                </div>
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-1">No expenses found</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">
                    {{ search || statusFilter !== 'all' || projectFilter !== 'all' ? 'Try adjusting your filters.' : 'Record your first expense to get started.' }}
                </p>
                <button
                    v-if="can('expenses.create') && !search && statusFilter === 'all' && projectFilter === 'all'"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    <Plus class="w-4 h-4" /> New Expense
                </button>
            </div>

            <!-- ── Table ────────────────────────────────────── -->
            <div
                v-else
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Project</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Amount</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Approved By</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="expense in filteredExpenses"
                            :key="expense.id"
                            class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <!-- Description -->
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-100 max-w-xs truncate">
                                    {{ expense.description }}
                                </p>
                            </td>

                            <!-- Project -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <FolderKanban class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                    <span class="text-sm text-slate-600 dark:text-slate-300 truncate max-w-[140px]">
                                            {{ expense.project?.name ?? '—' }}
                                        </span>
                                </div>
                            </td>

                            <!-- Amount -->
                            <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 tabular-nums">
                                        {{ fmtMoney(expense.amount) }}
                                    </span>
                            </td>

                            <!-- Date -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                                    <CalendarDays class="w-3.5 h-3.5 shrink-0" />
                                    <span class="text-sm">{{ fmtDate(expense.date) }}</span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border"
                                        :class="statusConfig[expense.status]?.badge"
                                    >
                                        {{ statusConfig[expense.status]?.label }}
                                    </span>
                            </td>

                            <!-- Approved by -->
                            <td class="px-6 py-4">
                                <div v-if="expense.approved_by" class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold flex items-center justify-center shrink-0">
                                        {{ initials(expense.approved_by?.name) }}
                                    </div>
                                    <span class="text-sm text-slate-600 dark:text-slate-300 truncate max-w-[100px]">
                                            {{ expense.approved_by?.name }}
                                        </span>
                                </div>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <!-- Approve -->
                                    <button
                                        v-if="can('expenses.approve') && expense.status === 'pending'"
                                        @click="approveExpense(expense.id)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                                        title="Approve"
                                    >
                                        <CheckCircle class="w-4 h-4" />
                                    </button>
                                    <!-- Reject -->
                                    <button
                                        v-if="can('expenses.approve') && expense.status === 'pending'"
                                        @click="rejectExpense(expense.id)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                                        title="Reject"
                                    >
                                        <XCircle class="w-4 h-4" />
                                    </button>
                                    <!-- Edit (only pending) -->
                                    <button
                                        v-if="can('expenses.update') && expense.status === 'pending'"
                                        @click="openEdit(expense)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                        title="Edit"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <!-- Delete -->
                                    <button
                                        v-if="can('expenses.delete')"
                                        @click="deleteExpense(expense.id)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                                        title="Delete"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ── Pagination ──────────────────────────── -->
                <div
                    v-if="expenses.last_page > 1"
                    class="flex items-center justify-between px-6 py-4 border-t border-slate-100 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Showing {{ expenses.from }}–{{ expenses.to }} of {{ expenses.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <button
                            @click="goToPage(expenses.prev_page_url)"
                            :disabled="!expenses.prev_page_url"
                            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <template v-for="link in expenses.links.slice(1, -1)" :key="link.label">
                            <button
                                @click="goToPage(link.url)"
                                :disabled="!link.url"
                                class="min-w-[36px] h-9 px-2 rounded-lg border text-sm font-medium transition-colors"
                                :class="link.active
                                    ? 'bg-indigo-600 border-indigo-600 text-white'
                                    : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'"
                            >
                                {{ link.label }}
                            </button>
                        </template>
                        <button
                            @click="goToPage(expenses.next_page_url)"
                            :disabled="!expenses.next_page_url"
                            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── EXPENSE MODAL ─────────────────────────────────── -->
        <Modal :show="showModal" :title="editingExpense ? 'Edit Expense' : 'New Expense'" size="md" @close="showModal = false">
            <form @submit.prevent="submit" class="p-6 space-y-5">

                <div>
                    <label :class="labelClass">Description <span class="text-rose-500">*</span></label>
                    <textarea
                        v-model="form.description"
                        rows="2"
                        placeholder="e.g. Steel reinforcement bars for Block A"
                        :class="[inputClass, 'resize-none', form.errors.description ? errorClass : '']"
                    ></textarea>
                    <p v-if="form.errors.description" :class="errorMsgClass">{{ form.errors.description }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label :class="labelClass">Project <span class="text-rose-500">*</span></label>
                        <select v-model="form.project_id" :class="[inputClass, form.errors.project_id ? errorClass : '']">
                            <option value="" disabled>Select project…</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <p v-if="form.errors.project_id" :class="errorMsgClass">{{ form.errors.project_id }}</p>
                    </div>

                    <div>
                        <label :class="labelClass">Amount (NGN) <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            placeholder="0.00"
                            :class="[inputClass, form.errors.amount ? errorClass : '']"
                        />
                        <p v-if="form.errors.amount" :class="errorMsgClass">{{ form.errors.amount }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Date <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.date"
                            type="date"
                            :class="[inputClass, form.errors.date ? errorClass : '']"
                        />
                        <p v-if="form.errors.date" :class="errorMsgClass">{{ form.errors.date }}</p>
                    </div>
                </div>

                <!-- Attachments (receipts) — edit mode only -->
                <div v-if="editingExpense">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Receipts & Documents
                    </label>
                    <AttachmentUploader
                        model-type="expenses"
                        :model-id="editingExpense.id"
                        :attachments="editingExpense.attachments ?? []"
                        :can-upload="can('expenses.update')"
                        :can-delete="false"
                    />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="showModal = false"
                        class="px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-colors"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        {{ editingExpense ? 'Save Changes' : 'Create Expense' }}
                    </button>
                </div>
            </form>
        </Modal>

    </AuthenticatedLayout>
</template>
