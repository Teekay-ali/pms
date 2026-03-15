<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
    TrendingUp, TrendingDown, AlertCircle, FileText,
    Receipt, CreditCard, ArrowRight, DollarSign
} from 'lucide-vue-next'

const props = defineProps({
    invoiceStats:   Object,
    billStats:      Object,
    recentPayments: Array,
    projectSummary: Array,
})

const fmtMoney = (v) => new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(v ?? 0)
const fmtDate  = (d) => d ? new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'

const methodLabels = {
    cash: 'Cash', bank_transfer: 'Bank Transfer',
    cheque: 'Cheque', card: 'Card', other: 'Other',
}

const netCashFlow = computed(() =>
    (props.invoiceStats.total_collected ?? 0) - (props.billStats.total_paid ?? 0)
)
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Finance Dashboard" />

        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Finance</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Overview of invoices, bills and cash flow</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('finance.invoices.index')"
                          class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        <FileText class="w-4 h-4" /> Invoices
                    </Link>
                    <Link :href="route('finance.bills.index')"
                          class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        <Receipt class="w-4 h-4" /> Bills
                    </Link>
                </div>
            </div>

            <!-- Top stat cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- Total Invoiced -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Invoiced</p>
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                            <FileText class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ fmtMoney(invoiceStats.total_invoiced) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Collected: {{ fmtMoney(invoiceStats.total_collected) }}</p>
                </div>

                <!-- Outstanding Invoices -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Receivables</p>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <AlertCircle class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ fmtMoney(invoiceStats.outstanding) }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ invoiceStats.overdue_count }} overdue invoice{{ invoiceStats.overdue_count !== 1 ? 's' : '' }}</p>
                </div>

                <!-- Total Bills -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Payables</p>
                        <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-900/30 flex items-center justify-center">
                            <Receipt class="w-4 h-4 text-rose-600 dark:text-rose-400" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ fmtMoney(billStats.total_billed) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Paid: {{ fmtMoney(billStats.total_paid) }}</p>
                </div>

                <!-- Net Cash Flow -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Net Cash Flow</p>
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                             :class="netCashFlow >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/30' : 'bg-rose-50 dark:bg-rose-900/30'">
                            <TrendingUp v-if="netCashFlow >= 0" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                            <TrendingDown v-else class="w-4 h-4 text-rose-600 dark:text-rose-400" />
                        </div>
                    </div>
                    <p class="text-2xl font-bold"
                       :class="netCashFlow >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                        {{ fmtMoney(netCashFlow) }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">Collected minus paid out</p>
                </div>
            </div>

            <!-- Project Summary + Recent Payments -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                <!-- Project Finance Summary -->
                <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Project Finance Summary</h2>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div v-if="!projectSummary.length" class="px-6 py-10 text-center text-sm text-slate-400">No active projects.</div>
                        <div v-for="p in projectSummary" :key="p.id" class="px-6 py-4">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ p.name }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full capitalize"
                                      :class="p.status === 'active'
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'">
                                    {{ p.status }}
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-3 text-xs">
                                <div>
                                    <p class="text-slate-400 mb-0.5">Invoiced</p>
                                    <p class="font-semibold text-slate-700 dark:text-slate-300">{{ fmtMoney(p.total_invoiced) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 mb-0.5">Billed</p>
                                    <p class="font-semibold text-slate-700 dark:text-slate-300">{{ fmtMoney(p.total_billed) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 mb-0.5">Budget</p>
                                    <p class="font-semibold text-slate-700 dark:text-slate-300">{{ fmtMoney(p.budget) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Recent Payments</h2>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div v-if="!recentPayments.length" class="px-6 py-10 text-center text-sm text-slate-400">No payments recorded.</div>
                        <div v-for="p in recentPayments" :key="p.id" class="px-6 py-3.5 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                     :class="p.type === 'Invoice'
                                        ? 'bg-indigo-50 dark:bg-indigo-900/30'
                                        : 'bg-rose-50 dark:bg-rose-900/30'">
                                    <CreditCard class="w-4 h-4"
                                                :class="p.type === 'Invoice'
                                                    ? 'text-indigo-600 dark:text-indigo-400'
                                                    : 'text-rose-600 dark:text-rose-400'" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-200 truncate">{{ p.payable_number }}</p>
                                    <p class="text-xs text-slate-400">{{ methodLabels[p.method] }} · {{ fmtDate(p.payment_date) }}</p>
                                </div>
                            </div>
                            <p class="text-sm font-semibold shrink-0"
                               :class="p.type === 'Invoice'
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-rose-600 dark:text-rose-400'">
                                {{ p.type === 'Invoice' ? '+' : '-' }}{{ fmtMoney(p.amount) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
