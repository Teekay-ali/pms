<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import {
    Plus, Pencil, Trash2, CreditCard,
    ChevronLeft, ChevronRight, CheckCircle
} from 'lucide-vue-next'

const props = defineProps({
    bills:    Object,
    projects: Array,
    vendors:  Array,
})

const page = usePage()
const can  = (p) => page.props.auth.permissions?.includes(p)

// ── Filters ────────────────────────────────────────────
const filterStatus  = ref('')
const filterProject = ref('')

const filtered = computed(() => {
    return props.bills.data.filter(b => {
        const matchStatus  = !filterStatus.value  || b.status === filterStatus.value
        const matchProject = !filterProject.value || b.project_id == filterProject.value
        return matchStatus && matchProject
    })
})

// ── Status config ──────────────────────────────────────
const statusColors = {
    draft:          'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
    pending:        'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
    approved:       'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800',
    partially_paid: 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-900/30 dark:text-sky-400 dark:border-sky-800',
    paid:           'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
    overdue:        'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800',
    cancelled:      'bg-slate-100 text-slate-400 border-slate-200 dark:bg-slate-800 dark:text-slate-500 dark:border-slate-700',
}

const statusLabel = {
    draft: 'Draft', pending: 'Pending', approved: 'Approved',
    partially_paid: 'Partial', paid: 'Paid',
    overdue: 'Overdue', cancelled: 'Cancelled',
}

// ── Form ───────────────────────────────────────────────
const showForm   = ref(false)
const editingBill = ref(null)

const form = ref({
    project_id: '', vendor_id: '', description: '',
    reference: '', amount: '', issue_date: '', due_date: '', notes: '',
})

function openCreate() {
    editingBill.value = null
    form.value = { project_id: '', vendor_id: '', description: '', reference: '', amount: '', issue_date: '', due_date: '', notes: '' }
    showForm.value = true
}

function openEdit(bill) {
    editingBill.value = bill
    form.value = {
        project_id:  bill.project_id,
        vendor_id:   bill.vendor_id ?? '',
        description: bill.description,
        reference:   bill.reference ?? '',
        amount:      bill.amount,
        issue_date:  bill.issue_date?.substring(0, 10) ?? '',
        due_date:    bill.due_date?.substring(0, 10) ?? '',
        notes:       bill.notes ?? '',
    }
    showForm.value = true
}

function submitForm() {
    if (editingBill.value) {
        router.patch(route('finance.bills.update', editingBill.value.id), form.value, {
            onSuccess: () => showForm.value = false,
        })
    } else {
        router.post(route('finance.bills.store'), form.value, {
            onSuccess: () => showForm.value = false,
        })
    }
}

// ── Approve ────────────────────────────────────────────
function approveBill(id) {
    if (!confirm('Approve this bill?')) return
    router.patch(route('finance.bills.approve', id))
}

// ── Delete ─────────────────────────────────────────────
function deleteBill(id) {
    if (!confirm('Delete this bill?')) return
    router.delete(route('finance.bills.destroy', id))
}

// ── Payment modal ──────────────────────────────────────
const showPayment   = ref(false)
const paymentTarget = ref(null)

const paymentForm = ref({
    payable_type: 'bill', payable_id: '',
    amount: '', payment_date: '', method: 'bank_transfer', reference: '', notes: '',
})

function openPayment(bill) {
    paymentTarget.value = bill
    paymentForm.value = {
        payable_type: 'bill',
        payable_id:   bill.id,
        amount:       parseFloat(bill.amount) - parseFloat(bill.amount_paid),
        payment_date: new Date().toISOString().substring(0, 10),
        method:       'bank_transfer',
        reference:    '',
        notes:        '',
    }
    showPayment.value = true
}

function submitPayment() {
    router.post(route('finance.payments.store'), paymentForm.value, {
        onSuccess: () => showPayment.value = false,
    })
}

// ── Helpers ────────────────────────────────────────────
const fmtMoney = (v) => new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(v ?? 0)
const fmtDate  = (d) => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Bills" />

        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Bills</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ bills.total }} bill{{ bills.total !== 1 ? 's' : '' }}</p>
                </div>
                <button v-if="can('finance.bills.create')" @click="openCreate"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <Plus class="w-4 h-4" /> New Bill
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <select v-model="filterStatus"
                        class="text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option v-for="(label, key) in statusLabel" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="filterProject"
                        class="text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Projects</option>
                    <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Bill #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Vendor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Due</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    <tr v-if="!filtered.length">
                        <td colspan="9" class="px-6 py-12 text-center text-slate-400">No bills found.</td>
                    </tr>
                    <tr v-for="bill in filtered" :key="bill.id"
                        class="group hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">

                        <td class="px-6 py-4 font-mono text-sm font-medium text-slate-800 dark:text-slate-200">
                            {{ bill.bill_number }}
                            <p v-if="bill.reference" class="text-xs text-slate-400 font-sans mt-0.5">Ref: {{ bill.reference }}</p>
                        </td>

                        <td class="px-6 py-4">
                            <p class="text-slate-800 dark:text-slate-200 max-w-[200px] truncate">{{ bill.description }}</p>
                        </td>

                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                            {{ bill.vendor?.name ?? '—' }}
                        </td>

                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                            {{ bill.project?.name }}
                        </td>

                        <td class="px-6 py-4">
                                <span :class="bill.status === 'overdue' ? 'text-rose-600 dark:text-rose-400 font-medium' : 'text-slate-600 dark:text-slate-300'">
                                    {{ fmtDate(bill.due_date) }}
                                </span>
                        </td>

                        <td class="px-6 py-4 text-right font-semibold text-slate-800 dark:text-slate-200 tabular-nums">
                            {{ fmtMoney(bill.amount) }}
                        </td>

                        <td class="px-6 py-4 text-right tabular-nums">
                                <span :class="(parseFloat(bill.amount) - parseFloat(bill.amount_paid)) > 0
                                    ? 'text-amber-600 dark:text-amber-400'
                                    : 'text-emerald-600 dark:text-emerald-400'">
                                    {{ fmtMoney(parseFloat(bill.amount) - parseFloat(bill.amount_paid)) }}
                                </span>
                        </td>

                        <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium border capitalize"
                                      :class="statusColors[bill.status]">
                                    {{ statusLabel[bill.status] }}
                                </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <!-- Record Payment -->
                                <button v-if="can('finance.payments.manage') && ['approved','partially_paid','overdue'].includes(bill.status)"
                                        @click="openPayment(bill)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                                        title="Record Payment">
                                    <CreditCard class="w-4 h-4" />
                                </button>
                                <!-- Approve -->
                                <button v-if="can('finance.bills.manage') && bill.status === 'pending'"
                                        @click="approveBill(bill.id)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                        title="Approve">
                                    <CheckCircle class="w-4 h-4" />
                                </button>
                                <!-- Edit -->
                                <button v-if="can('finance.bills.manage') && !['paid','cancelled'].includes(bill.status)"
                                        @click="openEdit(bill)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                        title="Edit">
                                    <Pencil class="w-4 h-4" />
                                </button>
                                <!-- Delete -->
                                <button v-if="can('finance.bills.manage')"
                                        @click="deleteBill(bill.id)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                                        title="Delete">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="bills.last_page > 1"
                     class="flex items-center justify-between px-6 py-3 border-t border-slate-100 dark:border-slate-700">
                    <p class="text-xs text-slate-500">Page {{ bills.current_page }} of {{ bills.last_page }}</p>
                    <div class="flex gap-2">
                        <button :disabled="!bills.prev_page_url"
                                @click="router.get(bills.prev_page_url)"
                                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <button :disabled="!bills.next_page_url"
                                @click="router.get(bills.next_page_url)"
                                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create / Edit Bill Modal -->
        <Modal :show="showForm" @close="showForm = false" size="2xl">
            <div class="p-6 space-y-5">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white">
                    {{ editingBill ? 'Edit Bill' : 'New Bill' }}
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Project</label>
                        <select v-model="form.project_id"
                                class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select project</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor <span class="text-slate-400 font-normal">(optional)</span></label>
                        <select v-model="form.vendor_id"
                                class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">No vendor</option>
                            <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                        <textarea v-model="form.description" rows="2" placeholder="What is this bill for?"
                                  class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Amount</label>
                        <input v-model="form.amount" type="number" min="0.01" step="0.01" placeholder="0.00"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vendor Ref # <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input v-model="form.reference" type="text" placeholder="Vendor's invoice number"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Issue Date</label>
                        <input v-model="form.issue_date" type="date"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Due Date</label>
                        <input v-model="form.due_date" type="date"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea v-model="form.notes" rows="2"
                                  class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showForm = false" type="button"
                            class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800 transition-colors">Cancel</button>
                    <button @click="submitForm" type="button"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ editingBill ? 'Save Changes' : 'Create Bill' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Record Payment Modal -->
        <Modal :show="showPayment" @close="showPayment = false" size="md">
            <div class="p-6 space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Record Payment</h2>
                    <p v-if="paymentTarget" class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ paymentTarget.bill_number }} — Balance: {{ fmtMoney(parseFloat(paymentTarget.amount) - parseFloat(paymentTarget.amount_paid)) }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Amount</label>
                        <input v-model="paymentForm.amount" type="number" min="0.01" step="0.01"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Date</label>
                        <input v-model="paymentForm.payment_date" type="date"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Method</label>
                        <select v-model="paymentForm.method"
                                class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="card">Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Reference <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input v-model="paymentForm.reference" type="text" placeholder="Transaction ref"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea v-model="paymentForm.notes" rows="2"
                              class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showPayment = false" type="button"
                            class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800 transition-colors">Cancel</button>
                    <button @click="submitPayment" type="button"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Record Payment
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
