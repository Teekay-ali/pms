<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import {
    Plus, Pencil, Trash2, CreditCard, Send,
    ChevronLeft, ChevronRight, TrendingUp, TrendingDown,
    FileText, CheckCircle, X
} from 'lucide-vue-next'

const props = defineProps({
    invoices: Object,
    projects: Array,
})

const page = usePage()
const can  = (p) => page.props.auth.permissions?.includes(p)

// ── Filters ────────────────────────────────────────────
const filterStatus  = ref('')
const filterProject = ref('')

const filtered = computed(() => {
    return props.invoices.data.filter(inv => {
        const matchStatus  = !filterStatus.value  || inv.status === filterStatus.value
        const matchProject = !filterProject.value || inv.project_id == filterProject.value
        return matchStatus && matchProject
    })
})

// ── Status config ──────────────────────────────────────
const statusColors = {
    draft:           'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
    sent:            'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800',
    partially_paid:  'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
    paid:            'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
    overdue:         'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800',
    cancelled:       'bg-slate-100 text-slate-400 border-slate-200 dark:bg-slate-800 dark:text-slate-500 dark:border-slate-700',
}

const statusLabel = {
    draft: 'Draft', sent: 'Sent', partially_paid: 'Partial',
    paid: 'Paid', overdue: 'Overdue', cancelled: 'Cancelled',
}

// ── Form state ─────────────────────────────────────────
const showForm    = ref(false)
const editingInv  = ref(null)

const emptyItem = () => ({ description: '', quantity: 1, unit_price: '' })

const form = ref({
    project_id: '', client_name: '', client_email: '',
    client_address: '', issue_date: '', due_date: '',
    tax_rate: 0, notes: '', items: [emptyItem()],
})

const formSubtotal = computed(() =>
    form.value.items.reduce((s, i) => s + (parseFloat(i.quantity) || 0) * (parseFloat(i.unit_price) || 0), 0)
)
const formTax = computed(() => formSubtotal.value * ((parseFloat(form.value.tax_rate) || 0) / 100))
const formTotal = computed(() => formSubtotal.value + formTax.value)

function addItem()         { form.value.items.push(emptyItem()) }
function removeItem(idx)   { if (form.value.items.length > 1) form.value.items.splice(idx, 1) }

function openCreate() {
    editingInv.value = null
    form.value = { project_id: '', client_name: '', client_email: '', client_address: '', issue_date: '', due_date: '', tax_rate: 0, notes: '', items: [emptyItem()] }
    showForm.value = true
}

function openEdit(inv) {
    editingInv.value = inv
    form.value = {
        project_id:     inv.project_id,
        client_name:    inv.client_name,
        client_email:   inv.client_email ?? '',
        client_address: inv.client_address ?? '',
        issue_date:     inv.issue_date?.substring(0, 10) ?? '',
        due_date:       inv.due_date?.substring(0, 10) ?? '',
        tax_rate:       inv.tax_rate ?? 0,
        notes:          inv.notes ?? '',
        items: inv.items?.length
            ? inv.items.map(i => ({ description: i.description, quantity: i.quantity, unit_price: i.unit_price }))
            : [emptyItem()],
    }
    showForm.value = true
}

function submitForm() {
    if (editingInv.value) {
        router.patch(route('finance.invoices.update', editingInv.value.id), form.value, {
            onSuccess: () => showForm.value = false,
        })
    } else {
        router.post(route('finance.invoices.store'), form.value, {
            onSuccess: () => showForm.value = false,
        })
    }
}

// ── Mark sent ──────────────────────────────────────────
function markSent(id) {
    router.patch(route('finance.invoices.mark-sent', id))
}

// ── Delete ─────────────────────────────────────────────
function deleteInvoice(id) {
    if (!confirm('Delete this invoice?')) return
    router.delete(route('finance.invoices.destroy', id))
}

// ── Payment modal ──────────────────────────────────────
const showPayment    = ref(false)
const paymentTarget  = ref(null)

const paymentForm = ref({
    payable_type: 'invoice', payable_id: '',
    amount: '', payment_date: '', method: 'bank_transfer', reference: '', notes: '',
})

function openPayment(inv) {
    paymentTarget.value  = inv
    paymentForm.value = {
        payable_type: 'invoice',
        payable_id:   inv.id,
        amount:       inv.balance ?? (inv.total - inv.amount_paid),
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

// ── Pagination ─────────────────────────────────────────
const { current_page, last_page, prev_page_url, next_page_url } = computed(() => props.invoices).value
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Invoices" />

        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Invoices</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ invoices.total }} invoice{{ invoices.total !== 1 ? 's' : '' }}</p>
                </div>
                <button v-if="can('finance.invoices.create')" @click="openCreate"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <Plus class="w-4 h-4" /> New Invoice
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <select v-model="filterStatus" class="text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option v-for="(label, key) in statusLabel" :key="key" :value="key">{{ label }}</option>
                </select>
                <select v-model="filterProject" class="text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Projects</option>
                    <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Due</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    <tr v-if="!filtered.length">
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">No invoices found.</td>
                    </tr>
                    <tr v-for="inv in filtered" :key="inv.id"
                        class="group hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">

                        <td class="px-6 py-4 font-mono text-sm font-medium text-slate-800 dark:text-slate-200">
                            {{ inv.invoice_number }}
                        </td>

                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-800 dark:text-slate-200">{{ inv.client_name }}</p>
                            <p v-if="inv.client_email" class="text-xs text-slate-400">{{ inv.client_email }}</p>
                        </td>

                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ inv.project?.name }}</td>

                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                <span :class="inv.status === 'overdue' ? 'text-rose-600 dark:text-rose-400 font-medium' : ''">
                                    {{ fmtDate(inv.due_date) }}
                                </span>
                        </td>

                        <td class="px-6 py-4 text-right font-semibold text-slate-800 dark:text-slate-200 tabular-nums">
                            {{ fmtMoney(inv.total) }}
                        </td>

                        <td class="px-6 py-4 text-right tabular-nums">
                                <span :class="(inv.total - inv.amount_paid) > 0
                                    ? 'text-amber-600 dark:text-amber-400'
                                    : 'text-emerald-600 dark:text-emerald-400'">
                                    {{ fmtMoney(inv.total - inv.amount_paid) }}
                                </span>
                        </td>

                        <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium border capitalize" :class="statusColors[inv.status]">
                                    {{ statusLabel[inv.status] }}
                                </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <!-- Record Payment -->
                                <button v-if="can('finance.payments.manage') && !['paid','cancelled','draft'].includes(inv.status)"
                                        @click="openPayment(inv)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                                        title="Record Payment">
                                    <CreditCard class="w-4 h-4" />
                                </button>
                                <!-- Mark Sent -->
                                <button v-if="can('finance.invoices.manage') && inv.status === 'draft'"
                                        @click="markSent(inv.id)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                        title="Mark as Sent">
                                    <Send class="w-4 h-4" />
                                </button>
                                <!-- Edit -->
                                <button v-if="can('finance.invoices.manage') && !['paid','cancelled'].includes(inv.status)"
                                        @click="openEdit(inv)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                        title="Edit">
                                    <Pencil class="w-4 h-4" />
                                </button>
                                <!-- Delete -->
                                <button v-if="can('finance.invoices.manage')"
                                        @click="deleteInvoice(inv.id)"
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
                <div v-if="invoices.last_page > 1"
                     class="flex items-center justify-between px-6 py-3 border-t border-slate-100 dark:border-slate-700">
                    <p class="text-xs text-slate-500">Page {{ invoices.current_page }} of {{ invoices.last_page }}</p>
                    <div class="flex gap-2">
                        <button :disabled="!invoices.prev_page_url"
                                @click="router.get(invoices.prev_page_url)"
                                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <button :disabled="!invoices.next_page_url"
                                @click="router.get(invoices.next_page_url)"
                                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create / Edit Invoice Modal -->
        <Modal :show="showForm" @close="showForm = false" max-width="3xl">
            <div class="p-6 space-y-5 max-h-[85vh] overflow-y-auto">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white">
                    {{ editingInv ? 'Edit Invoice' : 'New Invoice' }}
                </h2>

                <!-- Client + Project -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Project</label>
                        <select v-model="form.project_id" class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select project</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Client Name</label>
                        <input v-model="form.client_name" type="text" placeholder="Company or person name"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Client Email <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input v-model="form.client_email" type="email" placeholder="client@email.com"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Client Address <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input v-model="form.client_address" type="text" placeholder="Address"
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
                </div>

                <!-- Line Items -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Line Items</label>
                        <button @click="addItem" type="button"
                                class="flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                            <Plus class="w-3.5 h-3.5" /> Add item
                        </button>
                    </div>

                    <div class="space-y-2">
                        <!-- Header row -->
                        <div class="grid grid-cols-12 gap-2 px-1">
                            <p class="col-span-6 text-xs text-slate-400">Description</p>
                            <p class="col-span-2 text-xs text-slate-400">Qty</p>
                            <p class="col-span-3 text-xs text-slate-400">Unit Price</p>
                            <p class="col-span-1"></p>
                        </div>
                        <div v-for="(item, idx) in form.items" :key="idx" class="grid grid-cols-12 gap-2 items-center">
                            <input v-model="item.description" type="text" placeholder="Description"
                                   class="col-span-6 text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <input v-model="item.quantity" type="number" min="0.01" step="0.01" placeholder="1"
                                   class="col-span-2 text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <input v-model="item.unit_price" type="number" min="0" step="0.01" placeholder="0.00"
                                   class="col-span-3 text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <button @click="removeItem(idx)" type="button"
                                    class="col-span-1 flex items-center justify-center text-slate-300 hover:text-rose-500 transition-colors">
                                <X class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="border-t border-slate-100 dark:border-slate-700 pt-4 space-y-2">
                    <div class="flex justify-between text-sm text-slate-600 dark:text-slate-300">
                        <span>Subtotal</span>
                        <span class="tabular-nums">{{ fmtMoney(formSubtotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-slate-600 dark:text-slate-300">
                        <div class="flex items-center gap-2">
                            <span>Tax</span>
                            <input v-model="form.tax_rate" type="number" min="0" max="100" step="0.5"
                                   class="w-16 text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-2 py-1 text-center focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <span>%</span>
                        </div>
                        <span class="tabular-nums">{{ fmtMoney(formTax) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold text-slate-800 dark:text-white pt-1 border-t border-slate-100 dark:border-slate-700">
                        <span>Total</span>
                        <span class="tabular-nums">{{ fmtMoney(formTotal) }}</span>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea v-model="form.notes" rows="2" placeholder="Payment terms, bank details, etc."
                              class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showForm = false" type="button"
                            class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800 transition-colors">Cancel</button>
                    <button @click="submitForm" type="button"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ editingInv ? 'Save Changes' : 'Create Invoice' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Record Payment Modal -->
        <Modal :show="showPayment" @close="showPayment = false" max-width="md">
            <div class="p-6 space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Record Payment</h2>
                    <p v-if="paymentTarget" class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ paymentTarget.invoice_number }} — Balance: {{ fmtMoney(paymentTarget.total - paymentTarget.amount_paid) }}
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
