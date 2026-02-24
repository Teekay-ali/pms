<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    Truck,
    Plus,
    Search,
    Pencil,
    Trash2,
    Loader2,
    ChevronLeft,
    ChevronRight,
    Mail,
    Phone,
    MapPin,
    User,
    HardHat,
    Wrench,
} from 'lucide-vue-next'

const props = defineProps({
    vendors: Object,
})

const page  = usePage()
const perms = computed(() => page.props.auth.permissions ?? [])
const can   = (p) => perms.value.includes(p)

// ── Filters ────────────────────────────────────────────
const search     = ref('')
const typeFilter = ref('all')

// ── Type config ────────────────────────────────────────
const typeConfig = {
    vendor:         { label: 'Vendor',         icon: Truck,    badge: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800' },
    contractor:     { label: 'Contractor',     icon: HardHat,  badge: 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800' },
    subcontractor:  { label: 'Subcontractor',  icon: Wrench,   badge: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800' },
}

// ── Modal ──────────────────────────────────────────────
const showModal     = ref(false)
const editingVendor = ref(null)

// ── Form ───────────────────────────────────────────────
const form = useForm({
    name:    '',
    contact: '',
    email:   '',
    phone:   '',
    type:    'vendor',
    address: '',
})

// ── Filtered rows ──────────────────────────────────────
const filteredVendors = computed(() => {
    let items = props.vendors?.data ?? []
    if (search.value) {
        const q = search.value.toLowerCase()
        items = items.filter(v =>
            v.name.toLowerCase().includes(q) ||
            v.contact?.toLowerCase().includes(q) ||
            v.email?.toLowerCase().includes(q)
        )
    }
    if (typeFilter.value !== 'all')
        items = items.filter(v => v.type === typeFilter.value)
    return items
})

// ── Summary counts (from full current page, unfiltered) ─
const counts = computed(() => {
    const all = props.vendors?.data ?? []
    return {
        all:            all.length,
        vendor:         all.filter(v => v.type === 'vendor').length,
        contractor:     all.filter(v => v.type === 'contractor').length,
        subcontractor:  all.filter(v => v.type === 'subcontractor').length,
    }
})

// ── Helpers ────────────────────────────────────────────
const initials = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() ?? '?'

// Avatar background — stable per name length (simple deterministic pick)
const avatarColor = (name) => {
    const colors = [
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300',
        'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
        'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
        'bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300',
        'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    ]
    return colors[(name?.length ?? 0) % colors.length]
}

// ── CRUD ───────────────────────────────────────────────
const openCreate = () => {
    editingVendor.value = null
    form.reset()
    form.type = 'vendor'
    showModal.value = true
}

const openEdit = (vendor) => {
    editingVendor.value = vendor
    form.name    = vendor.name
    form.contact = vendor.contact ?? ''
    form.email   = vendor.email   ?? ''
    form.phone   = vendor.phone   ?? ''
    form.type    = vendor.type
    form.address = vendor.address ?? ''
    showModal.value = true
}

const submit = () => {
    if (editingVendor.value) {
        form.put(route('vendors.update', editingVendor.value.id), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    } else {
        form.post(route('vendors.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

const deleteVendor = (id) => {
    if (confirm('Delete this vendor?')) router.delete(route('vendors.destroy', id))
}

const goToPage = (url) => { if (url) router.get(url) }

// ── Classes ────────────────────────────────────────────
const inputClass    = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 dark:focus:border-indigo-600 transition-all'
const errorClass    = '!border-rose-400 dark:!border-rose-600'
const labelClass    = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
</script>

<template>
    <Head title="Vendors" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- ── Page header ──────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Vendors & Contractors</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                        {{ vendors?.total ?? 0 }} total in directory
                    </p>
                </div>
                <button
                    v-if="can('vendors.create')"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
                >
                    <Plus class="w-4 h-4" /> Add Vendor
                </button>
            </div>

            <!-- ── Summary cards (clickable type filters) ───── -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <button
                    v-for="(cfg, key) in { all: { label: 'All', icon: Truck }, ...typeConfig }"
                    :key="key"
                    @click="typeFilter = key"
                    class="text-left bg-white dark:bg-slate-900 rounded-2xl border p-5 transition-all"
                    :class="typeFilter === key
                        ? 'border-indigo-500 ring-2 ring-indigo-500/20 shadow-sm'
                        : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                >
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">
                        {{ cfg.label }}
                    </p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">
                        {{ key === 'all' ? counts.all : counts[key] }}
                    </p>
                </button>
            </div>

            <!-- ── Search bar ────────────────────────────────── -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="relative">
                    <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search name, contact person or email…"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                    />
                </div>
            </div>

            <!-- ── Empty state ──────────────────────────────── -->
            <div
                v-if="filteredVendors.length === 0"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-16 text-center"
            >
                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <Truck class="w-6 h-6 text-slate-400" />
                </div>
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-1">No vendors found</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">
                    {{ search || typeFilter !== 'all'
                    ? 'Try adjusting your search or filter.'
                    : 'Add your first vendor or contractor to build your directory.' }}
                </p>
                <button
                    v-if="can('vendors.create') && !search && typeFilter === 'all'"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    <Plus class="w-4 h-4" /> Add Vendor
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
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Type</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Contact Person</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Phone</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Address</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="vendor in filteredVendors"
                            :key="vendor.id"
                            class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <!-- Name + avatar -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold shrink-0"
                                        :class="avatarColor(vendor.name)"
                                    >
                                        {{ initials(vendor.name) }}
                                    </div>
                                    <span class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                            {{ vendor.name }}
                                        </span>
                                </div>
                            </td>

                            <!-- Type badge -->
                            <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border"
                                        :class="typeConfig[vendor.type]?.badge"
                                    >
                                        {{ typeConfig[vendor.type]?.label }}
                                    </span>
                            </td>

                            <!-- Contact person -->
                            <td class="px-6 py-4">
                                <div v-if="vendor.contact" class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                                    <User class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                    <span class="text-sm">{{ vendor.contact }}</span>
                                </div>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>

                            <!-- Email -->
                            <td class="px-6 py-4">
                                <a
                                    v-if="vendor.email"
                                    :href="`mailto:${vendor.email}`"
                                    class="flex items-center gap-1.5 text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                                >
                                    <Mail class="w-3.5 h-3.5 shrink-0" />
                                    {{ vendor.email }}
                                </a>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>

                            <!-- Phone -->
                            <td class="px-6 py-4">
                                <a
                                    v-if="vendor.phone"
                                    :href="`tel:${vendor.phone}`"
                                    class="flex items-center gap-1.5 text-sm text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                                >
                                    <Phone class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                    {{ vendor.phone }}
                                </a>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>

                            <!-- Address -->
                            <td class="px-6 py-4 max-w-[180px]">
                                <div v-if="vendor.address" class="flex items-start gap-1.5">
                                    <MapPin class="w-3.5 h-3.5 text-slate-400 shrink-0 mt-0.5" />
                                    <span class="text-sm text-slate-600 dark:text-slate-300 truncate">{{ vendor.address }}</span>
                                </div>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        v-if="can('vendors.update')"
                                        @click="openEdit(vendor)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                        title="Edit"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button
                                        v-if="can('vendors.delete')"
                                        @click="deleteVendor(vendor.id)"
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
                    v-if="vendors.last_page > 1"
                    class="flex items-center justify-between px-6 py-4 border-t border-slate-100 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Showing {{ vendors.from }}–{{ vendors.to }} of {{ vendors.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <button
                            @click="goToPage(vendors.prev_page_url)"
                            :disabled="!vendors.prev_page_url"
                            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <template v-for="link in vendors.links.slice(1, -1)" :key="link.label">
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
                            @click="goToPage(vendors.next_page_url)"
                            :disabled="!vendors.next_page_url"
                            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── VENDOR MODAL ──────────────────────────────────── -->
        <Modal :show="showModal" :title="editingVendor ? 'Edit Vendor' : 'Add Vendor'" size="lg" @close="showModal = false">
            <form @submit.prevent="submit" class="p-6 space-y-5">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <!-- Name -->
                    <div class="sm:col-span-2">
                        <label :class="labelClass">Company / Vendor Name <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Atlas Steel Supplies Ltd."
                            :class="[inputClass, form.errors.name ? errorClass : '']"
                        />
                        <p v-if="form.errors.name" :class="errorMsgClass">{{ form.errors.name }}</p>
                    </div>

                    <!-- Type -->
                    <div>
                        <label :class="labelClass">Type <span class="text-rose-500">*</span></label>
                        <select v-model="form.type" :class="[inputClass, form.errors.type ? errorClass : '']">
                            <option value="vendor">Vendor</option>
                            <option value="contractor">Contractor</option>
                            <option value="subcontractor">Subcontractor</option>
                        </select>
                        <p v-if="form.errors.type" :class="errorMsgClass">{{ form.errors.type }}</p>
                    </div>

                    <!-- Contact person -->
                    <div>
                        <label :class="labelClass">Contact Person</label>
                        <input
                            v-model="form.contact"
                            type="text"
                            placeholder="e.g. John Smith"
                            :class="[inputClass, form.errors.contact ? errorClass : '']"
                        />
                        <p v-if="form.errors.contact" :class="errorMsgClass">{{ form.errors.contact }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label :class="labelClass">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="contact@example.com"
                            :class="[inputClass, form.errors.email ? errorClass : '']"
                        />
                        <p v-if="form.errors.email" :class="errorMsgClass">{{ form.errors.email }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label :class="labelClass">Phone</label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            placeholder="+1 (555) 000-0000"
                            :class="[inputClass, form.errors.phone ? errorClass : '']"
                        />
                        <p v-if="form.errors.phone" :class="errorMsgClass">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Address -->
                    <div class="sm:col-span-2">
                        <label :class="labelClass">Address</label>
                        <textarea
                            v-model="form.address"
                            rows="2"
                            placeholder="Street, City, State, ZIP"
                            :class="[inputClass, 'resize-none', form.errors.address ? errorClass : '']"
                        ></textarea>
                        <p v-if="form.errors.address" :class="errorMsgClass">{{ form.errors.address }}</p>
                    </div>

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
                        {{ editingVendor ? 'Save Changes' : 'Add Vendor' }}
                    </button>
                </div>

            </form>
        </Modal>

    </AuthenticatedLayout>
</template>
