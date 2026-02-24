<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    CheckSquare,
    Plus,
    Search,
    SlidersHorizontal,
    CalendarDays,
    Users,
    FolderKanban,
    Pencil,
    Trash2,
    Clock,
    Loader2,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next'

const props = defineProps({
    tasks:    Object,
    projects: Array,
    users:    Array,
})

const page = usePage()
const permissions = computed(() => page.props.auth.permissions ?? [])
const can = (p) => permissions.value.includes(p)

// ── Filters ────────────────────────────────────────────
const search         = ref('')
const statusFilter   = ref('all')
const priorityFilter = ref('all')

// ── Modal state ────────────────────────────────────────
const showModal    = ref(false)
const editingTask  = ref(null)

// ── Form ───────────────────────────────────────────────
const form = useForm({
    project_id:  '',
    assigned_to: '',
    name:        '',
    description: '',
    due_date:    '',
    priority:    'medium',
    status:      'pending',
})

// ── Configs ────────────────────────────────────────────
const taskStatusConfig = {
    pending:     { label: 'Pending',     badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
    in_progress: { label: 'In Progress', badge: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800' },
    review:      { label: 'Review',      badge: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800' },
    completed:   { label: 'Completed',   badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    blocked:     { label: 'Blocked',     badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
}

const priorityConfig = {
    critical: { label: 'Critical', badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
    high:     { label: 'High',     badge: 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800' },
    medium:   { label: 'Medium',   badge: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
    low:      { label: 'Low',      badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
}

// ── Filtered tasks ─────────────────────────────────────
const filteredTasks = computed(() => {
    let items = props.tasks?.data ?? []
    if (search.value)
        items = items.filter(t => t.name.toLowerCase().includes(search.value.toLowerCase()))
    if (statusFilter.value !== 'all')
        items = items.filter(t => t.status === statusFilter.value)
    if (priorityFilter.value !== 'all')
        items = items.filter(t => t.priority === priorityFilter.value)
    return items
})

// ── Helpers ────────────────────────────────────────────
const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    : '—'

const isOverdue = (d, status) =>
    d && status !== 'completed' && new Date(d) < new Date()

const initials = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('') ?? '?'

// ── CRUD ───────────────────────────────────────────────
const openCreate = () => {
    editingTask.value = null
    form.reset()
    showModal.value = true
}

const openEdit = (task) => {
    editingTask.value    = task
    form.project_id      = task.project_id ?? ''
    form.assigned_to     = task.assigned_to ?? ''
    form.name            = task.name
    form.description     = task.description ?? ''
    form.due_date        = task.due_date?.substring(0, 10) ?? ''
    form.priority        = task.priority
    form.status          = task.status
    showModal.value      = true
}

const submit = () => {
    if (editingTask.value) {
        form.put(route('tasks.update', editingTask.value.id), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    } else {
        form.post(route('tasks.store'), {
            onSuccess: () => { showModal.value = false; form.reset() },
        })
    }
}

const deleteTask = (id) => {
    if (confirm('Delete this task?'))
        router.delete(route('tasks.destroy', id))
}

const goToPage = (url) => { if (url) router.get(url) }

// ── Input classes ──────────────────────────────────────
const inputClass    = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 dark:focus:border-indigo-600 transition-all'
const errorClass    = '!border-rose-400 dark:!border-rose-600'
const labelClass    = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
</script>

<template>
    <Head title="Tasks" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Page header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Tasks</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                        {{ tasks?.total ?? 0 }} tasks total
                    </p>
                </div>
                <button
                    v-if="can('tasks.create')"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm shadow-indigo-500/20"
                >
                    <Plus class="w-4 h-4" /> New Task
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search tasks..."
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
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="review">Review</option>
                        <option value="completed">Completed</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>
                <div class="relative">
                    <SlidersHorizontal class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <select
                        v-model="priorityFilter"
                        class="pl-9 pr-8 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all appearance-none cursor-pointer"
                    >
                        <option value="all">All Priorities</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>

            <!-- Empty state -->
            <div
                v-if="filteredTasks.length === 0"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-16 text-center"
            >
                <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <CheckSquare class="w-8 h-8 text-indigo-400" />
                </div>
                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-1">No tasks found</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">
                    {{ search || statusFilter !== 'all' || priorityFilter !== 'all' ? 'Try adjusting your filters.' : 'Create your first task to get started.' }}
                </p>
                <button
                    v-if="can('tasks.create') && !search && statusFilter === 'all' && priorityFilter === 'all'"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    <Plus class="w-4 h-4" /> New Task
                </button>
            </div>

            <!-- Table -->
            <div
                v-else
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Task</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Project</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Assignee</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Priority</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        <tr
                            v-for="task in filteredTasks"
                            :key="task.id"
                            class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors group"
                        >
                            <!-- Task name -->
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm">{{ task.name }}</p>
                                <p v-if="task.description" class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ task.description }}</p>
                            </td>

                            <!-- Project -->
                            <td class="px-6 py-4">
                                <Link
                                    v-if="task.project"
                                    :href="route('projects.show', task.project.id)"
                                    class="inline-flex items-center gap-1.5 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors"
                                >
                                    <FolderKanban class="w-3.5 h-3.5" />
                                    <span class="truncate max-w-[140px]">{{ task.project.name }}</span>
                                </Link>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>

                            <!-- Assignee -->
                            <td class="px-6 py-4">
                                <div v-if="task.assignee" class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-lg flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                                        {{ initials(task.assignee.name) }}
                                    </div>
                                    <span class="text-sm text-slate-600 dark:text-slate-300 truncate max-w-[120px]">{{ task.assignee.name }}</span>
                                </div>
                                <span v-else class="text-sm text-slate-400">Unassigned</span>
                            </td>

                            <!-- Priority -->
                            <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border" :class="priorityConfig[task.priority]?.badge">
                                        {{ priorityConfig[task.priority]?.label }}
                                    </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border" :class="taskStatusConfig[task.status]?.badge">
                                        {{ taskStatusConfig[task.status]?.label }}
                                    </span>
                            </td>

                            <!-- Due date -->
                            <td class="px-6 py-4">
                                    <span
                                        class="text-sm flex items-center gap-1.5"
                                        :class="isOverdue(task.due_date, task.status)
                                            ? 'text-rose-500 dark:text-rose-400 font-medium'
                                            : 'text-slate-500 dark:text-slate-400'"
                                    >
                                        <Clock class="w-3.5 h-3.5" />
                                        {{ formatDate(task.due_date) }}
                                        <span v-if="isOverdue(task.due_date, task.status)" class="text-[10px] font-semibold">OVERDUE</span>
                                    </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        v-if="can('tasks.update')"
                                        @click="openEdit(task)"
                                        class="p-1.5 text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button
                                        v-if="can('tasks.delete')"
                                        @click="deleteTask(task.id)"
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

            <!-- Pagination -->
            <div v-if="tasks?.last_page > 1" class="flex items-center justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Showing {{ tasks.from }}–{{ tasks.to }} of {{ tasks.total }} tasks
                </p>
                <div class="flex items-center gap-1">
                    <button @click="goToPage(tasks.prev_page_url)" :disabled="!tasks.prev_page_url" class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        <ChevronLeft class="w-4 h-4" />
                    </button>
                    <template v-for="link in tasks.links.slice(1, -1)" :key="link.label">
                        <button @click="goToPage(link.url)" class="w-9 h-9 rounded-lg border text-sm font-medium transition-colors" :class="link.active ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            {{ link.label }}
                        </button>
                    </template>
                    <button @click="goToPage(tasks.next_page_url)" :disabled="!tasks.next_page_url" class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        <ChevronRight class="w-4 h-4" />
                    </button>
                </div>
            </div>

        </div>

        <!-- ── TASK MODAL ───────────────────────────────────── -->
        <Modal :show="showModal" :title="editingTask ? 'Edit Task' : 'New Task'" size="lg" @close="showModal = false">
            <form @submit.prevent="submit" class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Task Name <span class="text-rose-500">*</span></label>
                        <input v-model="form.name" type="text" placeholder="e.g. Submit structural drawings" :class="[inputClass, form.errors.name ? errorClass : '']" />
                        <p v-if="form.errors.name" :class="errorMsgClass">{{ form.errors.name }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Description</label>
                        <textarea v-model="form.description" rows="2" placeholder="Optional details..." :class="[inputClass, 'resize-none']"></textarea>
                    </div>

                    <div>
                        <label :class="labelClass">Project <span class="text-rose-500">*</span></label>
                        <select v-model="form.project_id" :class="[inputClass, form.errors.project_id ? errorClass : '']">
                            <option value="">— Select a project —</option>
                            <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                        </select>
                        <p v-if="form.errors.project_id" :class="errorMsgClass">{{ form.errors.project_id }}</p>
                    </div>

                    <div>
                        <label :class="labelClass">Assign To</label>
                        <select v-model="form.assigned_to" :class="inputClass">
                            <option value="">— Unassigned —</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label :class="labelClass">Due Date</label>
                        <input v-model="form.due_date" type="date" :class="inputClass" />
                    </div>

                    <div>
                        <label :class="labelClass">Priority <span class="text-rose-500">*</span></label>
                        <select v-model="form.priority" :class="inputClass">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Status <span class="text-rose-500">*</span></label>
                        <select v-model="form.status" :class="inputClass">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="review">Review</option>
                            <option value="completed">Completed</option>
                            <option value="blocked">Blocked</option>
                        </select>
                    </div>

                </div>
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        {{ form.processing ? 'Saving...' : (editingTask ? 'Save Changes' : 'Create Task') }}
                    </button>
                </div>
            </template>
        </Modal>

    </AuthenticatedLayout>
</template>
