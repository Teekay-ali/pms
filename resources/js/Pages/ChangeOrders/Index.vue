<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Plus, Pencil, Trash2, CheckCircle, XCircle, TrendingUp, TrendingDown, Clock } from 'lucide-vue-next'

const props = defineProps({
    changeOrders: Object,
    projects: Array,
})

const page = usePage()
const can = (p) => page.props.auth.permissions?.includes(p)

// Filters
const filterProject = ref('')
const filterStatus  = ref('')

const filtered = computed(() => {
    return props.changeOrders.data.filter(co => {
        const matchProject = !filterProject.value || co.project_id == filterProject.value
        const matchStatus  = !filterStatus.value  || co.status === filterStatus.value
        return matchProject && matchStatus
    })
})

// Modals
const showForm       = ref(false)
const showReject     = ref(false)
const editingOrder   = ref(null)
const rejectingOrder = ref(null)
const rejectReason   = ref('')

const form = ref({
    project_id: '',
    title: '',
    description: '',
    reason: 'client_request',
    cost_impact: 0,
    timeline_impact: 0,
    status: 'draft',
})

const reasonLabels = {
    client_request: 'Client Request',
    site_condition: 'Site Condition',
    design_change: 'Design Change',
    unforeseen_work: 'Unforeseen Work',
    other: 'Other',
}

const statusColors = {
    draft:     'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
    submitted: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
    approved:  'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
    rejected:  'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800',
}

function openCreate() {
    editingOrder.value = null
    form.value = { project_id: '', title: '', description: '', reason: 'client_request', cost_impact: 0, timeline_impact: 0, status: 'draft' }
    showForm.value = true
}

function openEdit(co) {
    editingOrder.value = co
    form.value = {
        project_id:      co.project_id,
        title:           co.title,
        description:     co.description ?? '',
        reason:          co.reason,
        cost_impact:     co.cost_impact,
        timeline_impact: co.timeline_impact,
        status:          co.status,
    }
    showForm.value = true
}

function submitForm() {
    if (editingOrder.value) {
        router.patch(route('change-orders.update', editingOrder.value.id), form.value, {
            onSuccess: () => showForm.value = false,
        })
    } else {
        router.post(route('change-orders.store'), form.value, {
            onSuccess: () => showForm.value = false,
        })
    }
}

function approveOrder(id) {
    if (!confirm('Approve this change order? The project budget will be updated.')) return
    router.patch(route('change-orders.approve', id))
}

function openReject(co) {
    rejectingOrder.value = co
    rejectReason.value   = ''
    showReject.value     = true
}

function submitReject() {
    router.patch(route('change-orders.reject', rejectingOrder.value.id), {
        rejection_reason: rejectReason.value,
    }, {
        onSuccess: () => showReject.value = false,
    })
}

function deleteOrder(id) {
    if (!confirm('Delete this change order?')) return
    router.delete(route('change-orders.destroy', id))
}

function formatCurrency(val) {
    const n = parseFloat(val)
    const formatted = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Math.abs(n))
    return n >= 0 ? `+${formatted}` : `-${formatted}`
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Change Orders" />
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Change Orders</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Manage project scope and budget changes</p>
                </div>
                <button
                    v-if="can('change_orders.create')"
                    @click="openCreate"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <Plus class="w-4 h-4" />
                    New Change Order
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <select v-model="filterProject" class="text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Projects</option>
                    <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <select v-model="filterStatus" class="text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cost Impact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Timeline</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Submitted By</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    <tr v-if="filtered.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">No change orders found.</td>
                    </tr>
                    <tr
                        v-for="co in filtered"
                        :key="co.id"
                        class="group hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors"
                    >
                        <!-- Title + description -->
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800 dark:text-slate-200">{{ co.title }}</div>
                            <div v-if="co.description" class="text-xs text-slate-400 truncate max-w-[200px] mt-0.5">{{ co.description }}</div>
                        </td>

                        <!-- Project -->
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ co.project?.name }}</td>

                        <!-- Reason -->
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ reasonLabels[co.reason] }}</td>

                        <!-- Cost Impact -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1 font-medium"
                                 :class="parseFloat(co.cost_impact) >= 0
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-rose-600 dark:text-rose-400'">
                                <TrendingUp v-if="parseFloat(co.cost_impact) >= 0" class="w-3.5 h-3.5" />
                                <TrendingDown v-else class="w-3.5 h-3.5" />
                                {{ formatCurrency(co.cost_impact) }}
                            </div>
                        </td>

                        <!-- Timeline -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1 text-slate-600 dark:text-slate-300">
                                <Clock class="w-3.5 h-3.5 text-slate-400" />
                                <span>
                                        {{ co.timeline_impact > 0 ? '+' : '' }}{{ co.timeline_impact }}d
                                    </span>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium border capitalize" :class="statusColors[co.status]">
                                    {{ co.status }}
                                </span>
                            <div v-if="co.status === 'rejected' && co.rejection_reason" class="text-xs text-rose-500 mt-1 max-w-[160px] truncate" :title="co.rejection_reason">
                                {{ co.rejection_reason }}
                            </div>
                        </td>

                        <!-- Submitted By -->
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                            {{ co.creator?.name ?? '—' }}
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <!-- Approve -->
                                <button
                                    v-if="can('change_orders.approve') && co.status === 'submitted'"
                                    @click="approveOrder(co.id)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
                                    title="Approve"
                                >
                                    <CheckCircle class="w-4 h-4" />
                                </button>
                                <!-- Reject -->
                                <button
                                    v-if="can('change_orders.approve') && co.status === 'submitted'"
                                    @click="openReject(co)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                                    title="Reject"
                                >
                                    <XCircle class="w-4 h-4" />
                                </button>
                                <!-- Edit -->
                                <button
                                    v-if="can('change_orders.update') && ['draft','submitted'].includes(co.status)"
                                    @click="openEdit(co)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                    title="Edit"
                                >
                                    <Pencil class="w-4 h-4" />
                                </button>
                                <!-- Delete -->
                                <button
                                    v-if="can('change_orders.delete') && ['draft','rejected'].includes(co.status)"
                                    @click="deleteOrder(co.id)"
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
        </div>

        <!-- Create / Edit Modal -->
        <Modal :show="showForm" @close="showForm = false" :max-width="'2xl'">
            <div class="p-6 space-y-5">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white">
                    {{ editingOrder ? 'Edit Change Order' : 'New Change Order' }}
                </h2>

                <div class="grid grid-cols-1 gap-4">
                    <!-- Project -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Project</label>
                        <select v-model="form.project_id" class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select project</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title</label>
                        <input v-model="form.title" type="text" placeholder="Brief title for this change" class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea v-model="form.description" rows="3" placeholder="Detailed explanation..." class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" />
                    </div>

                    <!-- Reason -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Reason</label>
                        <select v-model="form.reason" class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="client_request">Client Request</option>
                            <option value="site_condition">Site Condition</option>
                            <option value="design_change">Design Change</option>
                            <option value="unforeseen_work">Unforeseen Work</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Cost + Timeline side by side -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Cost Impact (₦)</label>
                            <input v-model="form.cost_impact" type="number" step="0.01" placeholder="e.g. 5000 or -2000" class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p class="text-xs text-slate-400 mt-1">Positive = budget increase, negative = decrease</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Timeline Impact (days)</label>
                            <input v-model="form.timeline_impact" type="number" placeholder="e.g. 7 or -3" class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p class="text-xs text-slate-400 mt-1">Positive = more days, negative = fewer</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Save as</label>
                        <select v-model="form.status" class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="draft">Draft</option>
                            <option value="submitted">Submit for Approval</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showForm = false" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">Cancel</button>
                    <button @click="submitForm" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ editingOrder ? 'Save Changes' : 'Create' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Reject Modal -->
        <Modal :show="showReject" @close="showReject = false" max-width="md">
            <div class="p-6 space-y-4">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Reject Change Order</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Optionally provide a reason for rejection.</p>
                <textarea v-model="rejectReason" rows="3" placeholder="Rejection reason (optional)..." class="w-full text-sm bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg px-3 py-2 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" />
                <div class="flex justify-end gap-3">
                    <button @click="showReject = false" class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 hover:text-slate-800 transition-colors">Cancel</button>
                    <button @click="submitReject" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors">Reject</button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
