<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    Package,
    Plus,
    Search,
    SlidersHorizontal,
    FolderKanban,
    Pencil,
    Trash2,
    Loader2,
    ChevronLeft,
    ChevronRight,
    Boxes,
    DollarSign,
    Unlink,
} from 'lucide-vue-next'

const props = defineProps({
    resources: Object,
    projects:  Array,
})

const page  = usePage()
const perms = computed(() => page.props.auth.permissions ?? [])
const can   = (p) => perms.value.includes(p)

// ── Filters ────────────────────────────────────────────
const search        = ref('')
const typeFilter    = ref('all')
const projectFilter = ref('all')

// ── Derived type list from current page data ───────────
const availableTypes = computed(() => {
    const types = props.resources?.data
        ?.map(r => r.type)
        .filter(Boolean)
    return [...new Set(types)].sort()
})

// ── Modal ──────────────────────────────────────────────
const showModal       = ref(false)
const editingResource = ref(null)

// ── Form ───────────────────────────────────────────────
const form = useForm({
    name:       '',
    type:       '',
    quantity:   '',
    unit:       '',
    cost:       '',
    project_id: '',
})

// ── Filtered rows ──────────────────────────────────────
const filteredResources = computed(() => {
    let items = props.resources?.data ?? []
    if (search.value)
        items = items.filter(r =>
            r.name.toLowerCase().includes(search.value.toLowerCase()) ||
            r.type?.toLowerCase().includes(search.value.toLowerCase())
        )
    if (typeFilter.value !== 'all')
        items = items.filter(r => r.type === typeFilter.value)
    if (projectFilter.value !== 'all')
        items = items.filter(r =>
            projectFilter.value === 'unassigned' ? !r.project_id : r.project_id == projectFilter.value
        )
    return items
})

// ── Summary cards ──────────────────────────────────────
const totals = computed(() => ({
    count:      filteredResources.value.length,
    value:      filteredResources.value.reduce((s, r) => s + parseFloat(r.cost ?? 0) * parseFloat(r.quantity ?? 0), 0),
    unassigned: filteredResources.value.filter(r => !r.project_id).length,
}))

// ── Helpers ────────────────────────────────────────────
const fmtMoney = (v) => new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 0 }).format(v)
const fmtQty   = (q, u) => `${parseFloat(q).toLocaleString()}${u ? ' ' + u : ''}`

// ── CRUD ───────────────────────────────────────────────
const openCreate = () => {
    editingResource.value = null
    form.reset()
    showModal.value = true
}

const openEdit = (resource) => {
    editingResource.value = resource
    form.name       = resource.name
    form.type       = resource.type ?? ''
    form.quantity   = resource.quantity
    form.unit       = resource.unit ?? ''
    form.cost       = resource.cost ?? ''
    form.project_id = resource.project_id ?? ''
    showModal.value = true
}

const submit = () => {
    if (editingResource.value) {
        form.put(route('resources.update', editingResource.value.id), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    } else {
        form.post(route('resources.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

const deleteResource = (id) => {
    if (confirm('Delete this resource?')) router.delete(route('resources.destroy', id))
}

const goToPage = (url) => { if (url) router.get(url) }

// ── Classes ────────────────────────────────────────────
const inputClass    = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 dark:focus:border-indigo-600 transition-all'
const errorClass    = '!border-rose-400 dark:!border-rose-600'
const labelClass    = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'

// ── Common resource types for datalist suggestions ─────
const resourceTypeSuggestions = [
    'Equipment', 'Material', 'Labour', 'Vehicle',
    'Tool', 'Safety Gear', 'Fuel', 'Chemical', 'Electrical', 'Plumbing',
]
</script>

<template>
    <Head title="Resources" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- ── Page header ──────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Resources</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                        {{ resources?.total ?? 0 }} total resource{{ resources?.total !== 1 ? 's' : '' }}
                    </p>
                </div>
                <button
                    v-if="can('resources.create')"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
                >
                    <Plus class="w-4 h-4" /> New Resource
                </button>
            </div>

            <!-- ── Summary cards ────────────────────────────── -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 flex items-center gap-4">
                    <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center shrink-0">
                        <Boxes class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Items</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ totals.count }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 flex items-center gap-4">
                    <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center shrink-0">
                        <DollarSign class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Value</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ fmtMoney(totals.value) }}</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 flex items-center gap-4">
                    <div class="w-10 h-10 bg-amber-50 dark:bg-amber-900/30 rounded-xl flex items-center justify-center shrink-0">
                        <Unlink class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Unassigned</p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ totals.unassigned }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Filters ──────────────────────────────────── -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search name or type…"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                        />
                    </div>
                    <!-- Type filter — only shown when types exist -->
                    <div v-if="availableTypes.length" class="relative">
                        <SlidersHorizontal class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                        <select v-model="typeFilter" class="pl-10 pr-8 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all appearance-none cursor-pointer">
                            <option value="all">All Types</option>
                            <option v-for="t in availableTypes" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>
                    <!-- Project filter -->
                    <div class="relative">
                        <FolderKanban class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                        <select v-model="projectFilter" class="pl-10 pr-8 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all appearance-none cursor-pointer">
                            <option value="all">All Projects</option>
                            <option value="unassigned">Unassigned</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ── Empty state ──────────────────────────────── -->
            <div
                v-if="filteredResources.length === 0"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-16 text-center"
            >
                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <Package class="w-6 h-6 text-slate-400" />
                </div>
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-1">No resources found</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">
                    {{ search || typeFilter !== 'all' || projectFilter !== 'all'
                    ? 'Try adjusting your filters.'
                    : 'Add your first resource to start tracking inventory.' }}
                </p>
                <button
                    v-if="can('resources.create') && !search && typeFilter === 'all' && projectFilter === 'all'"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    <Plus class="w-4 h-4" /> New Resource
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
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Resource</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Type</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Quantity</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Unit Cost</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Value</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Project</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="resource in filteredResources"
                            :key="resource.id"
                            class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <!-- Name -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center shrink-0">
                                        <Package class="w-4 h-4 text-slate-400" />
                                    </div>
                                    <span class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                            {{ resource.name }}
                                        </span>
                                </div>
                            </td>

                            <!-- Type -->
                            <td class="px-6 py-4">
                                    <span
                                        v-if="resource.type"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700"
                                    >
                                        {{ resource.type }}
                                    </span>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>

                            <!-- Quantity -->
                            <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-200 tabular-nums">
                                        {{ fmtQty(resource.quantity, resource.unit) }}
                                    </span>
                            </td>

                            <!-- Unit cost -->
                            <td class="px-6 py-4 text-right">
                                    <span class="text-sm text-slate-600 dark:text-slate-300 tabular-nums">
                                        {{ resource.cost > 0 ? fmtMoney(resource.cost) : '—' }}
                                    </span>
                            </td>

                            <!-- Total value -->
                            <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-100 tabular-nums">
                                        {{ resource.cost > 0 ? fmtMoney(resource.cost * resource.quantity) : '—' }}
                                    </span>
                            </td>

                            <!-- Project -->
                            <td class="px-6 py-4">
                                <div v-if="resource.project" class="flex items-center gap-2">
                                    <FolderKanban class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                    <span class="text-sm text-slate-600 dark:text-slate-300 truncate max-w-[140px]">
                                            {{ resource.project.name }}
                                        </span>
                                </div>
                                <span v-else class="inline-flex items-center gap-1 text-xs text-amber-600 dark:text-amber-400">
                                        <Unlink class="w-3 h-3" /> Unassigned
                                    </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        v-if="can('resources.update')"
                                        @click="openEdit(resource)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                        title="Edit"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button
                                        v-if="can('resources.delete')"
                                        @click="deleteResource(resource.id)"
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
                    v-if="resources.last_page > 1"
                    class="flex items-center justify-between px-6 py-4 border-t border-slate-100 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Showing {{ resources.from }}–{{ resources.to }} of {{ resources.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <button
                            @click="goToPage(resources.prev_page_url)"
                            :disabled="!resources.prev_page_url"
                            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <template v-for="link in resources.links.slice(1, -1)" :key="link.label">
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
                            @click="goToPage(resources.next_page_url)"
                            :disabled="!resources.next_page_url"
                            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── RESOURCE MODAL ────────────────────────────────── -->
        <Modal :show="showModal" :title="editingResource ? 'Edit Resource' : 'New Resource'" size="md" @close="showModal = false">
            <form @submit.prevent="submit" class="p-6 space-y-5">

                <!-- Name -->
                <div>
                    <label :class="labelClass">Name <span class="text-rose-500">*</span></label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="e.g. Steel Rebar 12mm"
                        :class="[inputClass, form.errors.name ? errorClass : '']"
                    />
                    <p v-if="form.errors.name" :class="errorMsgClass">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <!-- Type (with suggestions via datalist) -->
                    <div>
                        <label :class="labelClass">Type</label>
                        <input
                            v-model="form.type"
                            type="text"
                            list="resource-types"
                            placeholder="e.g. Material"
                            :class="[inputClass, form.errors.type ? errorClass : '']"
                        />
                        <datalist id="resource-types">
                            <option v-for="t in resourceTypeSuggestions" :key="t" :value="t" />
                        </datalist>
                        <p v-if="form.errors.type" :class="errorMsgClass">{{ form.errors.type }}</p>
                    </div>

                    <!-- Project -->
                    <div>
                        <label :class="labelClass">Project</label>
                        <select v-model="form.project_id" :class="[inputClass, form.errors.project_id ? errorClass : '']">
                            <option value="">Unassigned</option>
                            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <p v-if="form.errors.project_id" :class="errorMsgClass">{{ form.errors.project_id }}</p>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label :class="labelClass">Quantity <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.quantity"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0"
                            :class="[inputClass, form.errors.quantity ? errorClass : '']"
                        />
                        <p v-if="form.errors.quantity" :class="errorMsgClass">{{ form.errors.quantity }}</p>
                    </div>

                    <!-- Unit -->
                    <div>
                        <label :class="labelClass">Unit</label>
                        <input
                            v-model="form.unit"
                            type="text"
                            placeholder="e.g. kg, m, pcs"
                            :class="[inputClass, form.errors.unit ? errorClass : '']"
                        />
                        <p v-if="form.errors.unit" :class="errorMsgClass">{{ form.errors.unit }}</p>
                    </div>

                    <!-- Unit cost -->
                    <div class="sm:col-span-2">
                        <label :class="labelClass">Unit Cost (NGN)</label>
                        <input
                            v-model="form.cost"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            :class="[inputClass, form.errors.cost ? errorClass : '']"
                        />
                        <p v-if="form.errors.cost" :class="errorMsgClass">{{ form.errors.cost }}</p>
                    </div>

                </div>

                <!-- Total value preview -->
                <div
                    v-if="form.quantity > 0 && form.cost > 0"
                    class="flex items-center justify-between px-4 py-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-100 dark:border-indigo-800"
                >
                    <span class="text-sm text-indigo-700 dark:text-indigo-300 font-medium">Total value</span>
                    <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300">
                        {{ fmtMoney(form.quantity * form.cost) }}
                    </span>
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
                        {{ editingResource ? 'Save Changes' : 'Create Resource' }}
                    </button>
                </div>
            </form>
        </Modal>

    </AuthenticatedLayout>
</template>
