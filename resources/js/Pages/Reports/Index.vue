<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'
import { FileText, FileSpreadsheet, Download, FolderKanban, Receipt, DollarSign, Package } from 'lucide-vue-next'

const props = defineProps({
    projects: Array,
    summary:  Object,
})

// ── Filter state per report ────────────────────────────
const projectsFilter  = ref({ status: '' })
const expensesFilter  = ref({ project_id: '', status: '', from: '', to: '' })
const financeFilter   = ref({ from: '', to: '' })
const resourcesFilter = ref({ project_id: '', type: '' })

function buildQuery(params) {
    const q = Object.entries(params).filter(([, v]) => v).map(([k, v]) => `${k}=${v}`).join('&')
    return q ? `?${q}` : ''
}

const fmtMoney = (v) => new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(v ?? 0)

const inputClass = 'text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500'
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Reports" />

        <div class="space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Reports</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Export data as PDF or Excel</p>
            </div>

            <!-- Summary cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Total Projects</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ summary.total_projects }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ summary.active_projects }} active</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Total Budget</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ fmtMoney(summary.total_budget) }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Total Spent</p>
                    <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ fmtMoney(summary.total_spent) }}</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Remaining</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ fmtMoney(summary.total_budget - summary.total_spent) }}</p>
                </div>
            </div>

            <!-- Report cards -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Projects Report -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                            <FolderKanban class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Project Summary</h2>
                            <p class="text-xs text-slate-400">Budget vs actual, progress per project</p>
                        </div>
                    </div>
                    <div class="space-y-3 mb-5">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Status</label>
                            <select v-model="projectsFilter.status" :class="inputClass" class="w-full">
                                <option value="">All Statuses</option>
                                <option value="planning">Planning</option>
                                <option value="active">Active</option>
                                <option value="on_hold">On Hold</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a :href="route('reports.projects.pdf') + buildQuery(projectsFilter)"
                           target="_blank"
                           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-sm font-medium rounded-lg transition-colors">
                            <FileText class="w-4 h-4" /> PDF
                        </a>
                        <a :href="route('reports.projects.excel') + buildQuery(projectsFilter)"
                           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-sm font-medium rounded-lg transition-colors">
                            <FileSpreadsheet class="w-4 h-4" /> Excel
                        </a>
                    </div>
                </div>

                <!-- Expenses Report -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                            <Receipt class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Expense Report</h2>
                            <p class="text-xs text-slate-400">Filter by project, status and date range</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Project</label>
                            <select v-model="expensesFilter.project_id" :class="inputClass" class="w-full">
                                <option value="">All Projects</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Status</label>
                            <select v-model="expensesFilter.status" :class="inputClass" class="w-full">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">From</label>
                            <input v-model="expensesFilter.from" type="date" :class="inputClass" class="w-full" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">To</label>
                            <input v-model="expensesFilter.to" type="date" :class="inputClass" class="w-full" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a :href="route('reports.expenses.pdf') + buildQuery(expensesFilter)"
                           target="_blank"
                           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-sm font-medium rounded-lg transition-colors">
                            <FileText class="w-4 h-4" /> PDF
                        </a>
                        <a :href="route('reports.expenses.excel') + buildQuery(expensesFilter)"
                           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-sm font-medium rounded-lg transition-colors">
                            <FileSpreadsheet class="w-4 h-4" /> Excel
                        </a>
                    </div>
                </div>

                <!-- Finance Report -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                            <DollarSign class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Finance Report</h2>
                            <p class="text-xs text-slate-400">Invoices, bills and cash flow</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">From</label>
                            <input v-model="financeFilter.from" type="date" :class="inputClass" class="w-full" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">To</label>
                            <input v-model="financeFilter.to" type="date" :class="inputClass" class="w-full" />
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a :href="route('reports.finance.pdf') + buildQuery(financeFilter)"
                           target="_blank"
                           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-sm font-medium rounded-lg transition-colors">
                            <FileText class="w-4 h-4" /> PDF
                        </a>
                        <a :href="route('reports.finance.excel') + buildQuery(financeFilter)"
                           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-sm font-medium rounded-lg transition-colors">
                            <FileSpreadsheet class="w-4 h-4" /> Excel
                        </a>
                    </div>
                </div>

                <!-- Resources Report -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-lg bg-sky-50 dark:bg-sky-900/30 flex items-center justify-center">
                            <Package class="w-4 h-4 text-sky-600 dark:text-sky-400" />
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Resource Utilization</h2>
                            <p class="text-xs text-slate-400">Inventory and resource values per project</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Project</label>
                            <select v-model="resourcesFilter.project_id" :class="inputClass" class="w-full">
                                <option value="">All Projects</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Type</label>
                            <select v-model="resourcesFilter.type" :class="inputClass" class="w-full">
                                <option value="">All Types</option>
                                <option value="material">Material</option>
                                <option value="equipment">Equipment</option>
                                <option value="labor">Labor</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a :href="route('reports.resources.pdf') + buildQuery(resourcesFilter)"
                           target="_blank"
                           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 text-rose-700 dark:text-rose-400 text-sm font-medium rounded-lg transition-colors">
                            <FileText class="w-4 h-4" /> PDF
                        </a>
                        <a :href="route('reports.resources.excel') + buildQuery(resourcesFilter)"
                           class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-sm font-medium rounded-lg transition-colors">
                            <FileSpreadsheet class="w-4 h-4" /> Excel
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
