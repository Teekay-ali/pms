<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import {
    Pencil, Trash2, ChevronLeft, ChevronRight,
    CreditCard, FileText, Receipt
} from 'lucide-vue-next'

const props = defineProps({
    payments: Object,
})

const page = usePage()
const can  = (p) => page.props.auth.permissions?.includes(p)

// ── Filters ────────────────────────────────────────────
const filterType   = ref('')
const filterMethod = ref('')

const filtered = computed(() => {
    return props.payments.data.filter(p => {
        const matchType   = !filterType.value   || p.payable_type === filterType.value
        const matchMethod = !filterMethod.value || p.method === filterMethod.value
        return matchType && matchMethod
    })
})

const totalIn  = computed(() =>
    filtered.value.filter(p => p.payable_type === 'Invoice')
        .reduce((s, p) => s + parseFloat(p.amount), 0)
)
const totalOut = computed(() =>
    filtered.value.filter(p => p.payable_type === 'Bill')
        .reduce((s, p) => s + parseFloat(p.amount), 0)
)

// ── Method config ──────────────────────────────────────
const methodLabel = {
    cash: 'Cash', bank_transfer: 'Bank Transfer',
    cheque: 'Cheque', card: 'Card', other: 'Other',
}

// ── Edit modal ─────────────────────────────────────────
const showEdit      = ref(false)
const editingPayment = ref(null)

const editForm = ref({
    amount: '', payment_date: '', method: 'bank_transfer', reference: '', notes: '',
})

function openEdit(payment) {
    editingPayment.value = payment
    editForm.value = {
        amount:       payment.amount,
        payment_date: payment.payment_date?.substring(0, 10) ?? '',
        method:       payment.method,
        reference:    payment.reference ?? '',
        notes:        payment.notes ?? '',
    }
    showEdit.value = true
}

function submitEdit() {
    router.patch(route('finance.payments.update', editingPayment.value.id), editForm.value, {
        onSuccess: () => showEdit.value = false,
    })
}

function deletePayment(id) {
    if (!confirm('Delete this payment? The balance will be reversed.')) return
    router.delete(route('finance.payments.destroy', id))
}

// ── Helpers ────────────────────────────────────────────
const fmtMoney = (v) => new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(v ?? 0)
const fmtDate  = (d) => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Payments" />

        <div class="space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Payments</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Full ledger of all recorded payments</p>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Money In</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ fmtMoney(totalIn) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Invoice payments received</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Money Out</p>
                    <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ fmtMoney(totalOut) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Bill payments made</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Net</p>
                    <p class="text-2xl font-bold"
                       :class="(totalIn - totalOut) >= 0
                            ? 'text-emerald-600 dark:text-emerald-400'
                            : 'text-rose-600 dark:text-rose-400'">
                        {{ fmtMoney(totalIn - totalOut) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">On this page</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <select v-model="filterType"
                        class="text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <option value="Invoice">Invoice (Money In)</option>
                    <option value="Bill">Bill (Money Out)</option>
                </select>
                <select v-model="filterMethod"
                        class="text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Methods</option>
                    <option v-for="(label, key) in methodLabel" :key="key" :value="key">{{ label }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Recorded By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Notes</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    <tr v-if="!filtered.length">
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">No payments found.</td>
                    </tr>
                    <tr v-for="payment in filtered" :key="payment.id"
                        class="group hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">

                        <!-- Type badge -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                                     :class="payment.payable_type === 'Invoice'
                                            ? 'bg-emerald-50 dark:bg-emerald-900/30'
                                            : 'bg-rose-50 dark:bg-rose-900/30'">
                                    <FileText v-if="payment.payable_type === 'Invoice'"
                                              class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                                    <Receipt v-else class="w-3.5 h-3.5 text-rose-600 dark:text-rose-400" />
                                </div>
                                <span class="text-xs font-medium"
                                      :class="payment.payable_type === 'Invoice'
                                            ? 'text-emerald-700 dark:text-emerald-400'
                                            : 'text-rose-700 dark:text-rose-400'">
                                        {{ payment.payable_type }}
                                    </span>
                            </div>
                        </td>

                        <!-- Payable number + transaction ref -->
                        <td class="px-6 py-4">
                            <p class="font-mono text-sm font-medium text-slate-800 dark:text-slate-200">
                                {{ payment.payable_number ?? '—' }}
                            </p>
                            <p v-if="payment.reference" class="text-xs text-slate-400 font-sans mt-0.5">
                                {{ payment.reference }}
                            </p>
                        </td>

                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                            {{ fmtDate(payment.payment_date) }}
                        </td>

                        <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                                    {{ methodLabel[payment.method] }}
                                </span>
                        </td>

                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300 text-sm">
                            {{ payment.created_by ?? '—' }}
                        </td>

                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs max-w-[160px] truncate">
                            {{ payment.notes ?? '—' }}
                        </td>

                        <!-- Amount -->
                        <td class="px-6 py-4 text-right font-semibold tabular-nums"
                            :class="payment.payable_type === 'Invoice'
                                    ? 'text-emerald-600 dark:text-emerald-400'
                                    : 'text-rose-600 dark:text-rose-400'">
                            {{ payment.payable_type === 'Invoice' ? '+' : '-' }}{{ fmtMoney(payment.amount) }}
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button v-if="can('finance.payments.manage')"
                                        @click="openEdit(payment)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                        title="Edit">
                                    <Pencil class="w-4 h-4" />
                                </button>
                                <button v-if="can('finance.payments.manage')"
                                        @click="deletePayment(payment.id)"
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
                <div v-if="payments.last_page > 1"
                     class="flex items-center justify-between px-6 py-3 border-t border-slate-100 dark:border-slate-700">
                    <p class="text-xs text-slate-500">Page {{ payments.current_page }} of {{ payments.last_page }}</p>
                    <div class="flex gap-2">
                        <button :disabled="!payments.prev_page_url"
                                @click="router.get(payments.prev_page_url)"
                                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <button :disabled="!payments.next_page_url"
                                @click="router.get(payments.next_page_url)"
                                class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Payment Modal -->
        <Modal :show="showEdit" @close="showEdit = false" size="md">
            <div class="p-6 space-y-4">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Edit Payment</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Amount</label>
                        <input v-model="editForm.amount" type="number" min="0.01" step="0.01"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Payment Date</label>
                        <input v-model="editForm.payment_date" type="date"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Method</label>
                        <select v-model="editForm.method"
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
                        <input v-model="editForm.reference" type="text"
                               class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea v-model="editForm.notes" rows="2"
                              class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showEdit = false" type="button"
                            class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800 transition-colors">Cancel</button>
                    <button @click="submitEdit" type="button"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Save Changes
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
