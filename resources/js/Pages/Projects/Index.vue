<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import {
    FolderKanban,
    Plus,
    LayoutGrid,
    List,
    Search,
    SlidersHorizontal,
    CalendarDays,
    Users,
    DollarSign,
    Pencil,
    Trash2,
    Eye,
    ChevronLeft,
    ChevronRight,
    Loader2,
} from 'lucide-vue-next'

const props = defineProps({
    projects: Object,
    users: Array,
})

const page = usePage()
const permissions = computed(() => page.props.auth.permissions ?? [])
const can = (p) => permissions.value.includes(p)

// View mode
const viewMode = ref('table')
const search = ref('')
const statusFilter = ref('all')

// Modal state
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingProject = ref(null)

// ── Forms ──────────────────────────────────────────────
const createForm = useForm({
    name: '',
    description: '',
    location: '',
    start_date: '',
    end_date: '',
    budget: '',
    status: 'planning',
    project_manager_id: '',
    members: [],
})

const editForm = useForm({
    name: '',
    description: '',
    location: '',
    start_date: '',
    end_date: '',
    budget: '',
    status: 'planning',
    project_manager_id: '',
    members: [],
})

// ── Helpers ────────────────────────────────────────────
const statusConfig = {
    active:    { label: 'Active',    dot: 'bg-emerald-500', badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    on_hold:   { label: 'On Hold',   dot: 'bg-amber-500',   badge: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
    planning:  { label: 'Planning',  dot: 'bg-blue-500',    badge: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800' },
    completed: { label: 'Completed', dot: 'bg-slate-400',   badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
    cancelled: { label: 'Cancelled', dot: 'bg-rose-500',    badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
}

const filteredProjects = computed(() => {
    let items = props.projects?.data ?? []
    if (search.value)
        items = items.filter(p => p.name.toLowerCase().includes(search.value.toLowerCase()))
    if (statusFilter.value !== 'all')
        items = items.filter(p => p.status === statusFilter.value)
    return items
})

const progressPct = (p) => {
    if (!p.tasks_count) return 0
    return Math.round((p.completed_tasks_count / p.tasks_count) * 100)
}

const progressColor = (pct) => {
    if (pct >= 75) return 'bg-emerald-500'
    if (pct >= 40) return 'bg-indigo-500'
    if (pct >= 15) return 'bg-amber-500'
    return 'bg-slate-300 dark:bg-slate-600'
}

const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    : '—'

const formatBudget = (b) => b ? '₦' + Number(b).toLocaleString() : '—'

const initials = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('') ?? '?'

// ── Create ─────────────────────────────────────────────
const openCreate = () => {
    createForm.reset()
    showCreateModal.value = true
}

const submitCreate = () => {
    createForm.post(route('projects.store'), {
        onSuccess: () => {
            showCreateModal.value = false
            createForm.reset()
        },
    })
}

// ── Edit ───────────────────────────────────────────────
const openEdit = (project) => {
    editingProject.value = project
    editForm.name               = project.name
    editForm.description        = project.description ?? ''
    editForm.location           = project.location ?? ''
    editForm.start_date         = project.start_date?.substring(0, 10) ?? ''
    editForm.end_date           = project.end_date?.substring(0, 10) ?? ''
    editForm.budget             = project.budget ?? ''
    editForm.status             = project.status
    editForm.project_manager_id = project.project_manager_id ?? ''
    editForm.members            = project.members?.map(m => m.id) ?? []
    showEditModal.value = true
}

const submitEdit = () => {
    editForm.put(route('projects.update', editingProject.value.id), {
        onSuccess: () => {
            showEditModal.value = false
            editingProject.value = null
        },
    })
}

// ── Delete ─────────────────────────────────────────────
const deleteProject = (id) => {
    if (confirm('Are you sure you want to delete this project?')) {
        router.delete(route('projects.destroy', id))
    }
}

// ── Pagination ─────────────────────────────────────────
const goToPage = (url) => { if (url) router.get(url) }

// ── Input classes ──────────────────────────────────────
const inputClass = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 dark:focus:border-indigo-600 transition-all'
const errorClass = '!border-rose-400 dark:!border-rose-600'
const labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
</script>

<template>
    <Head title="Projects" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Page header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Projects</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                        {{ projects?.total ?? 0 }} projects total
                    </p>
                </div>
                <button
                    v-if="can('projects.create')"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm shadow-indigo-500/20"
                >
                    <Plus class="w-4 h-4" /> New Project
                </button>
            </div>

            <!-- Filters + view toggle -->
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search projects..."
                        class="w-full pl-9 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                    />
                </div>
                <div class="relative">
                    <SlidersHorizontal class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <select
                        v-model="statusFilter"
                        class="pl-9 pr-8 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all appearance-none cursor-pointer"
                    >
                        <option value="all">All Statuses</option>
                        <option value="planning">Planning</option>
                        <option value="active">Active</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="flex items-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-1 ml-auto">
                    <button
                        @click="viewMode = 'table'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
                        :class="viewMode === 'table' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                    >
                        <List class="w-4 h-4" /> Table
                    </button>
                    <button
                        @click="viewMode = 'grid'"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
                        :class="viewMode === 'grid' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                    >
                        <LayoutGrid class="w-4 h-4" /> Grid
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-if="filteredProjects.length === 0"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-16 text-center"
            >
                <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <FolderKanban class="w-8 h-8 text-indigo-400" />
                </div>
                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-1">No projects found</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">
                    {{ search || statusFilter !== 'all' ? 'Try adjusting your filters.' : 'Get started by creating your first project.' }}
                </p>
                <button
                    v-if="can('projects.create') && !search && statusFilter === 'all'"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    <Plus class="w-4 h-4" /> New Project
                </button>
            </div>

            <!-- TABLE VIEW -->
            <div
                v-else-if="viewMode === 'table'"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Project</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Progress</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Manager</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Budget</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        <tr
                            v-for="project in filteredProjects"
                            :key="project.id"
                            class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors group"
                        >
                            <td class="px-6 py-4">
                                <Link :href="route('projects.show', project.id)" class="block">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                        {{ project.name }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ project.description || '—' }}</p>
                                </Link>
                            </td>
                            <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border" :class="statusConfig[project.status]?.badge">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="statusConfig[project.status]?.dot"></span>
                                        {{ statusConfig[project.status]?.label }}
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 min-w-25">
                                    <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500" :class="progressColor(progressPct(project))" :style="{ width: progressPct(project) + '%' }"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 w-8">{{ progressPct(project) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="project.project_manager" class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-linear-to-br from-indigo-500 to-indigo-700 rounded-lg flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                                        {{ initials(project.project_manager.name) }}
                                    </div>
                                    <span class="text-sm text-slate-600 dark:text-slate-300 truncate max-w-[120px]">{{ project.project_manager.name }}</span>
                                </div>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ formatBudget(project.budget) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                    <span class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                        <CalendarDays class="w-3.5 h-3.5" />
                                        {{ formatDate(project.end_date) }}
                                    </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <Link
                                        :href="route('projects.show', project.id)"
                                        class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
                                    >
                                        <Eye class="w-4 h-4" />
                                    </Link>
                                    <button
                                        v-if="can('projects.update')"
                                        @click="openEdit(project)"
                                        class="p-1.5 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button
                                        v-if="can('projects.delete')"
                                        @click="deleteProject(project.id)"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors"
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

            <!-- GRID VIEW -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                <div
                    v-for="project in filteredProjects"
                    :key="project.id"
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-200 group overflow-hidden"
                >
                    <div class="h-1 w-full" :class="{
                        'bg-emerald-500': project.status === 'active',
                        'bg-amber-500':   project.status === 'on_hold',
                        'bg-blue-500':    project.status === 'planning',
                        'bg-slate-400':   project.status === 'completed',
                        'bg-rose-500':    project.status === 'cancelled',
                    }"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <Link :href="route('projects.show', project.id)" class="flex-1 min-w-0">
                                <h3 class="font-semibold text-slate-800 dark:text-slate-100 text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-2 leading-snug">
                                    {{ project.name }}
                                </h3>
                            </Link>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium border flex-shrink-0" :class="statusConfig[project.status]?.badge">
                                <span class="w-1.5 h-1.5 rounded-full" :class="statusConfig[project.status]?.dot"></span>
                                {{ statusConfig[project.status]?.label }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 dark:text-slate-500 line-clamp-2 mb-4 min-h-[32px]">
                            {{ project.description || 'No description provided.' }}
                        </p>
                        <div class="mb-4">
                            <div class="flex justify-between text-xs mb-1.5">
                                <span class="text-slate-500 dark:text-slate-400">Progress</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ progressPct(project) }}%</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" :class="progressColor(progressPct(project))" :style="{ width: progressPct(project) + '%' }"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                <DollarSign class="w-3.5 h-3.5 flex-shrink-0" />
                                <span class="truncate">{{ formatBudget(project.budget) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                <CalendarDays class="w-3.5 h-3.5 flex-shrink-0" />
                                <span class="truncate">{{ formatDate(project.end_date) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 col-span-2">
                                <Users class="w-3.5 h-3.5 flex-shrink-0" />
                                <span class="truncate">{{ project.project_manager?.name ?? 'No manager assigned' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                            <Link
                                :href="route('projects.show', project.id)"
                                class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 rounded-lg transition-colors"
                            >
                                <Eye class="w-3.5 h-3.5" /> View
                            </Link>
                            <button
                                v-if="can('projects.update')"
                                @click="openEdit(project)"
                                class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 rounded-lg transition-colors"
                            >
                                <Pencil class="w-3.5 h-3.5" /> Edit
                            </button>
                            <button
                                v-if="can('projects.delete')"
                                @click="deleteProject(project.id)"
                                class="flex-1 flex items-center justify-center gap-1.5 py-2 text-xs font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 rounded-lg transition-colors"
                            >
                                <Trash2 class="w-3.5 h-3.5" /> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="projects?.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Showing {{ projects.from }}–{{ projects.to }} of {{ projects.total }} projects
                </p>
                <div class="flex items-center gap-1">
                    <button @click="goToPage(projects.prev_page_url)" :disabled="!projects.prev_page_url" class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        <ChevronLeft class="w-4 h-4" />
                    </button>
                    <template v-for="link in projects.links.slice(1, -1)" :key="link.label">
                        <button @click="goToPage(link.url)" class="w-9 h-9 rounded-lg border text-sm font-medium transition-colors" :class="link.active ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            {{ link.label }}
                        </button>
                    </template>
                    <button @click="goToPage(projects.next_page_url)" :disabled="!projects.next_page_url" class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        <ChevronRight class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- ── CREATE MODAL ─────────────────────────────────── -->
        <Modal :show="showCreateModal" title="New Project" size="lg" @close="showCreateModal = false">
            <form @submit.prevent="submitCreate" class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <!-- Name -->
                    <div class="sm:col-span-2">
                        <label :class="labelClass">Project Name <span class="text-rose-500">*</span></label>
                        <input v-model="createForm.name" type="text" placeholder="e.g. Highway Bridge Expansion" :class="[inputClass, createForm.errors.name ? errorClass : '']" />
                        <p v-if="createForm.errors.name" :class="errorMsgClass">{{ createForm.errors.name }}</p>
                    </div>

                    <!-- Description -->
                    <div class="sm:col-span-2">
                        <label :class="labelClass">Description</label>
                        <textarea v-model="createForm.description" rows="3" placeholder="Brief project overview..." :class="[inputClass, 'resize-none', createForm.errors.description ? errorClass : '']"></textarea>
                        <p v-if="createForm.errors.description" :class="errorMsgClass">{{ createForm.errors.description }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Location / Address</label>
                        <input
                            v-model="createForm.location"
                            type="text"
                            placeholder="e.g. 123 Main St, Cape Town"
                            :class="inputClass"
                        />
                    </div>

                    <!-- Start date -->
                    <div>
                        <label :class="labelClass">Start Date</label>
                        <input v-model="createForm.start_date" type="date" :class="[inputClass, createForm.errors.start_date ? errorClass : '']" />
                        <p v-if="createForm.errors.start_date" :class="errorMsgClass">{{ createForm.errors.start_date }}</p>
                    </div>

                    <!-- End date -->
                    <div>
                        <label :class="labelClass">End Date</label>
                        <input v-model="createForm.end_date" type="date" :class="[inputClass, createForm.errors.end_date ? errorClass : '']" />
                        <p v-if="createForm.errors.end_date" :class="errorMsgClass">{{ createForm.errors.end_date }}</p>
                    </div>

                    <!-- Budget -->
                    <div>
                        <label :class="labelClass">Budget ($)</label>
                        <input v-model="createForm.budget" type="number" min="0" step="0.01" placeholder="0.00" :class="[inputClass, createForm.errors.budget ? errorClass : '']" />
                        <p v-if="createForm.errors.budget" :class="errorMsgClass">{{ createForm.errors.budget }}</p>
                    </div>

                    <!-- Status -->
                    <div>
                        <label :class="labelClass">Status <span class="text-rose-500">*</span></label>
                        <select v-model="createForm.status" :class="[inputClass, createForm.errors.status ? errorClass : '']">
                            <option value="planning">Planning</option>
                            <option value="active">Active</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <p v-if="createForm.errors.status" :class="errorMsgClass">{{ createForm.errors.status }}</p>
                    </div>

                    <!-- Project Manager -->
                    <div class="sm:col-span-2">
                        <label :class="labelClass">Project Manager</label>
                        <select v-model="createForm.project_manager_id" :class="[inputClass, createForm.errors.project_manager_id ? errorClass : '']">
                            <option value="">— Select a manager —</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <p v-if="createForm.errors.project_manager_id" :class="errorMsgClass">{{ createForm.errors.project_manager_id }}</p>
                    </div>

                </div>
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showCreateModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="submitCreate"
                        :disabled="createForm.processing"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors"
                    >
                        <Loader2 v-if="createForm.processing" class="w-4 h-4 animate-spin" />
                        {{ createForm.processing ? 'Creating...' : 'Create Project' }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- ── EDIT MODAL ───────────────────────────────────── -->
        <Modal :show="showEditModal" title="Edit Project" size="lg" @close="showEditModal = false">
            <form @submit.prevent="submitEdit" class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Project Name <span class="text-rose-500">*</span></label>
                        <input v-model="editForm.name" type="text" :class="[inputClass, editForm.errors.name ? errorClass : '']" />
                        <p v-if="editForm.errors.name" :class="errorMsgClass">{{ editForm.errors.name }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Description</label>
                        <textarea v-model="editForm.description" rows="3" :class="[inputClass, 'resize-none', editForm.errors.description ? errorClass : '']"></textarea>
                        <p v-if="editForm.errors.description" :class="errorMsgClass">{{ editForm.errors.description }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Location / Address</label>
                        <input
                            v-model="editForm.location"
                            type="text"
                            placeholder="e.g. 123 Main St, Cape Town"
                            :class="inputClass"
                        />
                    </div>

                    <div>
                        <label :class="labelClass">Start Date</label>
                        <input v-model="editForm.start_date" type="date" :class="[inputClass, editForm.errors.start_date ? errorClass : '']" />
                        <p v-if="editForm.errors.start_date" :class="errorMsgClass">{{ editForm.errors.start_date }}</p>
                    </div>

                    <div>
                        <label :class="labelClass">End Date</label>
                        <input v-model="editForm.end_date" type="date" :class="[inputClass, editForm.errors.end_date ? errorClass : '']" />
                        <p v-if="editForm.errors.end_date" :class="errorMsgClass">{{ editForm.errors.end_date }}</p>
                    </div>

                    <div>
                        <label :class="labelClass">Budget (₦)</label>
                        <input v-model="editForm.budget" type="number" min="0" step="0.01" :class="[inputClass, editForm.errors.budget ? errorClass : '']" />
                        <p v-if="editForm.errors.budget" :class="errorMsgClass">{{ editForm.errors.budget }}</p>
                    </div>

                    <div>
                        <label :class="labelClass">Status <span class="text-rose-500">*</span></label>
                        <select v-model="editForm.status" :class="[inputClass, editForm.errors.status ? errorClass : '']">
                            <option value="planning">Planning</option>
                            <option value="active">Active</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <p v-if="editForm.errors.status" :class="errorMsgClass">{{ editForm.errors.status }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Project Manager</label>
                        <select v-model="editForm.project_manager_id" :class="[inputClass, editForm.errors.project_manager_id ? errorClass : '']">
                            <option value="">— Select a manager —</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <p v-if="editForm.errors.project_manager_id" :class="errorMsgClass">{{ editForm.errors.project_manager_id }}</p>
                    </div>

                </div>
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showEditModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="submitEdit"
                        :disabled="editForm.processing"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-colors"
                    >
                        <Loader2 v-if="editForm.processing" class="w-4 h-4 animate-spin" />
                        {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </template>
        </Modal>

    </AuthenticatedLayout>
</template>
