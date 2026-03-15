<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import AttachmentUploader from '@/Components/AttachmentUploader.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { GGanttChart, GGanttRow } from '@infectoone/vue-ganttastic'
import {
    ArrowLeft,
    CalendarDays,
    DollarSign,
    BarChart2,
    Users,
    CheckSquare,
    Package,
    Receipt,
    Pencil,
    Trash2,
    Plus,
    Loader2,
    Clock,
    AlertTriangle,
    Paperclip,
    ChevronRight,
    FolderKanban,
    Shield,
    TrendingUp,
} from 'lucide-vue-next'

const props = defineProps({
    project: Object,
    users: Array,
})

const page = usePage()
const permissions = computed(() => page.props.auth.permissions ?? [])
const can = (p) => permissions.value.includes(p)

const activeTab = ref('tasks')

const tabs = [
    { key: 'attachments', label: 'Files', icon: Paperclip },
    { key: 'tasks',     label: 'Tasks',     icon: CheckSquare },
    { key: 'expenses',  label: 'Expenses',  icon: Receipt },
    { key: 'gantt', label: 'Gantt', icon: BarChart2 },
    { key: 'members',   label: 'Members',   icon: Users },
    { key: 'resources', label: 'Resources', icon: Package },
]

// ── Computed ───────────────────────────────────────────
const progressPct = computed(() => {
    if (!props.project.tasks_count) return 0
    return Math.round((props.project.completed_tasks_count / props.project.tasks_count) * 100)
})

const progressColor = computed(() => {
    if (progressPct.value >= 75) return 'bg-emerald-500'
    if (progressPct.value >= 40) return 'bg-indigo-500'
    if (progressPct.value >= 15) return 'bg-amber-500'
    return 'bg-slate-300 dark:bg-slate-600'
})

const totalExpenses = computed(() =>
    props.project.expenses?.reduce((sum, e) => sum + parseFloat(e.amount), 0) ?? 0
)

const budgetUsedPct = computed(() => {
    if (!props.project.budget) return 0
    return Math.min(Math.round((totalExpenses.value / props.project.budget) * 100), 100)
})

// ── Status / Priority configs ──────────────────────────
const statusConfig = {
    active:      { label: 'Active',      dot: 'bg-emerald-500', badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    on_hold:     { label: 'On Hold',     dot: 'bg-amber-500',   badge: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
    planning:    { label: 'Planning',    dot: 'bg-blue-500',    badge: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800' },
    completed:   { label: 'Completed',   dot: 'bg-slate-400',   badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
    cancelled:   { label: 'Cancelled',   dot: 'bg-rose-500',    badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
}

const taskStatusConfig = {
    pending:     { label: 'Pending',     badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
    in_progress: { label: 'In Progress', badge: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800' },
    review:      { label: 'Review',      badge: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800' },
    completed:   { label: 'Completed',   badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    blocked:     { label: 'Blocked',     badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
}

const priorityConfig = {
    critical: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800',
    high:     'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800',
    medium:   'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
    low:      'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
}

const expenseStatusConfig = {
    pending:  { label: 'Pending',  badge: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
    approved: { label: 'Approved', badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    rejected: { label: 'Rejected', badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
}

// ── Helpers ────────────────────────────────────────────
const formatDate = (d) => d
    ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    : '—'

const formatMoney = (v) => v
    ? '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    : '—'

const initials = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('') ?? '?'

// ── Task Modal ─────────────────────────────────────────
const showTaskModal = ref(false)
const editingTask = ref(null)

const taskForm = useForm({
    project_id:  props.project.id,
    assigned_to: '',
    name:        '',
    description: '',
    due_date:    '',
    priority:    'medium',
    status:      'pending',
})

const openCreateTask = () => {
    editingTask.value = null
    taskForm.reset()
    taskForm.project_id = props.project.id
    showTaskModal.value = true
}

const openEditTask = (task) => {
    editingTask.value = task
    taskForm.project_id  = task.project_id
    taskForm.assigned_to = task.assigned_to ?? ''
    taskForm.name        = task.name
    taskForm.description = task.description ?? ''
    taskForm.due_date    = task.due_date?.substring(0, 10) ?? ''
    taskForm.priority    = task.priority
    taskForm.status      = task.status
    showTaskModal.value  = true
}

const submitTask = () => {
    if (editingTask.value) {
        taskForm.put(route('tasks.update', editingTask.value.id), {
            onSuccess: () => { showTaskModal.value = false; taskForm.reset() },
        })
    } else {
        taskForm.post(route('tasks.store'), {
            onSuccess: () => { showTaskModal.value = false; taskForm.reset() },
        })
    }
}

const deleteTask = (id) => {
    if (confirm('Delete this task?')) router.delete(route('tasks.destroy', id))
}

// ── Expense Modal ──────────────────────────────────────
const showExpenseModal = ref(false)
const editingExpense = ref(null)

const expenseForm = useForm({
    project_id:  props.project.id,
    amount:      '',
    description: '',
    date:        '',
    status:      'pending',
})

const openCreateExpense = () => {
    editingExpense.value = null
    expenseForm.reset()
    expenseForm.project_id = props.project.id
    showExpenseModal.value = true
}

const openEditExpense = (expense) => {
    editingExpense.value     = expense
    expenseForm.project_id   = expense.project_id
    expenseForm.amount       = expense.amount
    expenseForm.description  = expense.description
    expenseForm.date         = expense.date?.substring(0, 10) ?? ''
    expenseForm.status       = expense.status
    showExpenseModal.value   = true
}

const submitExpense = () => {
    if (editingExpense.value) {
        expenseForm.put(route('expenses.update', editingExpense.value.id), {
            onSuccess: () => { showExpenseModal.value = false; expenseForm.reset() },
        })
    } else {
        expenseForm.post(route('expenses.store'), {
            onSuccess: () => { showExpenseModal.value = false; expenseForm.reset() },
        })
    }
}

const deleteExpense = (id) => {
    if (confirm('Delete this expense?')) router.delete(route('expenses.destroy', id))
}

const approveExpense = (id) => {
    router.patch(route('expenses.approve', id))
}


// ── Members ────────────────────────────────────────────
const showAddMemberModal = ref(false)
const memberForm = useForm({ user_id: '' })

const existingMemberIds = computed(() =>
    props.project.members?.map(m => m.id) ?? []
)

const availableUsers = computed(() =>
    props.users.filter(u => !existingMemberIds.value.includes(u.id))
)

const addMember = () => {
    memberForm.post(route('projects.members.add', props.project.id), {
        preserveScroll: true,
        onSuccess: () => {
            showAddMemberModal.value = false
            memberForm.reset()
        },
    })
}

const removeMember = (userId) => {
    if (!confirm('Remove this member from the project?')) return
    router.delete(route('projects.members.remove', [props.project.id, userId]), {
        preserveScroll: true,
    })
}


// ── Resources ──────────────────────────────────────────
const showResourceModal = ref(false)
const editingResource   = ref(null)

const resourceForm = useForm({
    name:       '',
    type:       '',
    quantity:   '',
    unit:       '',
    cost:       '',
    project_id: '',
})

const resourceTypeSuggestions = [
    'Equipment', 'Material', 'Labour', 'Vehicle',
    'Tool', 'Safety Gear', 'Fuel', 'Electrical', 'Plumbing',
]

const openCreateResource = () => {
    editingResource.value  = null
    resourceForm.reset()
    resourceForm.project_id = props.project.id
    showResourceModal.value = true
}

const openEditResource = (resource) => {
    editingResource.value   = resource
    resourceForm.name       = resource.name
    resourceForm.type       = resource.type ?? ''
    resourceForm.quantity   = resource.quantity
    resourceForm.unit       = resource.unit ?? ''
    resourceForm.cost       = resource.cost ?? ''
    resourceForm.project_id = resource.project_id
    showResourceModal.value = true
}

const submitResource = () => {
    if (editingResource.value) {
        resourceForm.put(route('resources.update', editingResource.value.id), {
            preserveScroll: true,
            onSuccess: () => { showResourceModal.value = false; resourceForm.reset() },
        })
    } else {
        resourceForm.post(route('resources.store'), {
            preserveScroll: true,
            onSuccess: () => { showResourceModal.value = false; resourceForm.reset() },
        })
    }
}

const deleteResource = (id) => {
    if (confirm('Delete this resource?')) {
        router.delete(route('resources.destroy', id), { preserveScroll: true })
    }
}

const statusColor = (status) => ({
    completed:   '#10b981',
    in_progress: '#6366f1',
    blocked:     '#ef4444',
    review:      '#f59e0b',
    pending:     '#94a3b8',
}[status] ?? '#94a3b8')

const ganttRows = computed(() =>
    (props.project.tasks ?? [])
        .filter(t => t.start_date && t.due_date)
        .map(t => ({
            label: t.name,
            bars: [{
                myStart: t.start_date.substring(0, 10) + ' 00:00',
                myEnd:   t.due_date.substring(0, 10)   + ' 23:59',
                ganttBarConfig: {
                    id:              String(t.id),
                    label:           t.name,
                    backgroundColor: statusColor(t.status),
                    borderRadius:    '6px',
                }
            }]
        }))
)

const ganttStart = computed(() => {
    const dates = (props.project.tasks ?? [])
        .filter(t => t.start_date)
        .map(t => t.start_date.substring(0, 10))
    return dates.length ? dates.sort()[0] + ' 00:00' : new Date().toISOString().substring(0, 10) + ' 00:00'
})

const ganttEnd = computed(() => {
    const dates = (props.project.tasks ?? [])
        .filter(t => t.due_date)
        .map(t => t.due_date.substring(0, 10))
    return dates.length ? dates.sort().reverse()[0] + ' 23:59' : new Date().toISOString().substring(0, 10) + ' 23:59'
})

// ── Input classes ──────────────────────────────────────
const inputClass  = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 dark:focus:border-indigo-600 transition-all'
const errorClass  = '!border-rose-400 dark:!border-rose-600'
const labelClass  = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                <Link :href="route('projects.index')" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1">
                    <FolderKanban class="w-4 h-4" /> Projects
                </Link>
                <ChevronRight class="w-3.5 h-3.5" />
                <span class="text-slate-700 dark:text-slate-200 font-medium truncate">{{ project.name }}</span>
            </div>

            <!-- Project header card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">

                <!-- Top accent -->
                <div class="h-1 w-full" :class="{
                    'bg-emerald-500': project.status === 'active',
                    'bg-amber-500':   project.status === 'on_hold',
                    'bg-blue-500':    project.status === 'planning',
                    'bg-slate-400':   project.status === 'completed',
                    'bg-rose-500':    project.status === 'cancelled',
                }"></div>

                <div class="p-6">
                    <!-- Title row -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap mb-2">
                                <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ project.name }}</h1>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border" :class="statusConfig[project.status]?.badge">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="statusConfig[project.status]?.dot"></span>
                                    {{ statusConfig[project.status]?.label }}
                                </span>
                            </div>
                            <p v-if="project.description" class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed max-w-2xl">
                                {{ project.description }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            <Link
                                :href="route('projects.index')"
                                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors"
                            >
                                <ArrowLeft class="w-4 h-4" /> Back
                            </Link>
                            <button
                                v-if="can('projects.delete')"
                                @click="router.delete(route('projects.destroy', project.id), { onBefore: () => confirm('Delete this project?') })"
                                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 dark:hover:bg-rose-900/40 rounded-xl transition-colors"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Stats row -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-1">
                                <DollarSign class="w-4 h-4 text-emerald-600" />
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Budget</span>
                            </div>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatMoney(project.budget) }}</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-1">
                                <Receipt class="w-4 h-4 text-rose-500" />
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Spent</span>
                            </div>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatMoney(totalExpenses) }}</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-1">
                                <CalendarDays class="w-4 h-4 text-indigo-600" />
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Start Date</span>
                            </div>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatDate(project.start_date) }}</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-1">
                                <CalendarDays class="w-4 h-4 text-amber-500" />
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Due Date</span>
                            </div>
                            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatDate(project.end_date) }}</p>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <TrendingUp class="w-4 h-4 text-indigo-600" />
                                <span class="font-medium text-slate-700 dark:text-slate-200">Overall Progress</span>
                                <span class="text-slate-400 text-xs">
                                    {{ project.completed_tasks_count }}/{{ project.tasks_count }} tasks completed
                                </span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white">{{ progressPct }}%</span>
                        </div>
                        <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full transition-all duration-700"
                                :class="progressColor"
                                :style="{ width: progressPct + '%' }"
                            ></div>
                        </div>
                        <div class="flex items-center gap-4 text-xs text-slate-400 pt-1">
                            <span>{{ project.completed_tasks_count ?? 0 }} completed</span>
                            <span>{{ project.in_progress_tasks_count ?? 0 }} in progress</span>
                            <span>{{ project.pending_tasks_count ?? 0 }} pending</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">

                <!-- Tab bar -->
                <div class="flex border-b border-slate-100 dark:border-slate-800 overflow-x-auto">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        class="flex items-center gap-2 px-6 py-4 text-sm font-medium whitespace-nowrap border-b-2 transition-all"
                        :class="activeTab === tab.key
                            ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
                            : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300'"
                    >
                        <component :is="tab.icon" class="w-4 h-4" />
                        {{ tab.label }}
                        <span
                            class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold"
                            :class="activeTab === tab.key
                                ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
                        >
                            {{
                                tab.key === 'tasks'     ? project.tasks?.length :
                                    tab.key === 'expenses'  ? project.expenses?.length :
                                        tab.key === 'members'   ? project.members?.length :
                                            tab.key === 'attachments' ? project.attachments?.length :
                                                tab.key === 'gantt'       ? '' :
                                                    project.resources?.length
                            }}
                        </span>
                    </button>
                </div>

                <!-- Tab content -->
                <div class="p-6">

                    <!-- TASKS TAB -->
                    <div v-if="activeTab === 'tasks'">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Tasks</h3>
                            <button
                                v-if="can('tasks.create')"
                                @click="openCreateTask"
                                class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors"
                            >
                                <Plus class="w-3.5 h-3.5" /> Add Task
                            </button>
                        </div>

                        <div v-if="!project.tasks?.length" class="text-center py-12">
                            <CheckSquare class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No tasks yet. Add the first one.</p>
                        </div>

                        <div v-else class="space-y-2">
                            <div
                                v-for="task in project.tasks"
                                :key="task.id"
                                class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 dark:text-slate-100 text-sm truncate">{{ task.name }}</p>
                                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                                        <span v-if="task.assignee" class="text-xs text-slate-400 flex items-center gap-1">
                                            <Users class="w-3 h-3" /> {{ task.assignee.name }}
                                        </span>
                                        <span v-if="task.due_date" class="text-xs text-slate-400 flex items-center gap-1">
                                            <Clock class="w-3 h-3" /> {{ formatDate(task.due_date) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium border" :class="priorityConfig[task.priority]">
                                        {{ task.priority }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium border" :class="taskStatusConfig[task.status]?.badge">
                                        {{ taskStatusConfig[task.status]?.label }}
                                    </span>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            v-if="can('tasks.update')"
                                            @click="openEditTask(task)"
                                            class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                        >
                                            <Pencil class="w-3.5 h-3.5" />
                                        </button>
                                        <button
                                            v-if="can('tasks.delete')"
                                            @click="deleteTask(task.id)"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ATTACHMENTS TAB -->
                    <div v-else-if="activeTab === 'attachments'">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Files & Attachments</h3>
                        </div>
                        <AttachmentUploader
                            model-type="projects"
                            :model-id="project.id"
                            :attachments="project.attachments ?? []"
                            :can-upload="can('projects.update')"
                            :can-delete="can('projects.delete')"
                        />
                    </div>

                    <!-- EXPENSES TAB -->
                    <div v-else-if="activeTab === 'expenses'">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Expenses</h3>
                            <button
                                v-if="can('expenses.create')"
                                @click="openCreateExpense"
                                class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors"
                            >
                                <Plus class="w-3.5 h-3.5" /> Log Expense
                            </button>
                        </div>

                        <div v-if="!project.expenses?.length" class="text-center py-12">
                            <Receipt class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No expenses logged yet.</p>
                        </div>

                        <div v-else class="space-y-2">
                            <div
                                v-for="expense in project.expenses"
                                :key="expense.id"
                                class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 dark:text-slate-100 text-sm truncate">{{ expense.description }}</p>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-xs text-slate-400 flex items-center gap-1">
                                            <CalendarDays class="w-3 h-3" /> {{ formatDate(expense.date) }}
                                        </span>
                                        <span v-if="expense.approved_by" class="text-xs text-slate-400 flex items-center gap-1">
                                            <Shield class="w-3 h-3" /> {{ expense.approved_by.name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ formatMoney(expense.amount) }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium border" :class="expenseStatusConfig[expense.status]?.badge">
                                        {{ expenseStatusConfig[expense.status]?.label }}
                                    </span>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            v-if="can('expenses.approve') && expense.status === 'pending'"
                                            @click="approveExpense(expense.id)"
                                            class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors"
                                            title="Approve"
                                        >
                                            <CheckSquare class="w-3.5 h-3.5" />
                                        </button>
                                        <button
                                            v-if="can('expenses.update')"
                                            @click="openEditExpense(expense)"
                                            class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                        >
                                            <Pencil class="w-3.5 h-3.5" />
                                        </button>
                                        <button
                                            v-if="can('expenses.delete')"
                                            @click="deleteExpense(expense.id)"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GANTT TAB -->
                    <div v-else-if="activeTab === 'gantt'">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Gantt Chart</h3>
                            <div class="flex items-center gap-3 text-xs text-slate-400">
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-indigo-500 inline-block"></span> In Progress</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span> Completed</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-rose-500 inline-block"></span> Blocked</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-slate-400 inline-block"></span> Pending</span>
                            </div>
                        </div>

                        <div v-if="ganttRows.length === 0" class="text-center py-12">
                            <BarChart2 class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No tasks with start dates yet.</p>
                            <p class="text-xs text-slate-400 mt-1">Add start dates to tasks to see them here.</p>
                        </div>

                        <div v-else class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                            <g-gantt-chart
                                :chart-start="ganttStart"
                                :chart-end="ganttEnd"
                                bar-start="myStart"
                                bar-end="myEnd"
                                precision="month"
                                date-format="YYYY-MM-DD HH:mm"
                                width="100%"
                                :row-height="40"
                                font="14px Inter, sans-serif"
                                label-column-title="Task"
                                :label-column-width="220"
                                color-scheme="default"
                            >
                                <g-gantt-row
                                    v-for="row in ganttRows"
                                    :key="row.label"
                                    :label="row.label"
                                    :bars="row.bars"
                                />
                            </g-gantt-chart>
                        </div>
                    </div>

                    <!-- MEMBERS TAB -->
                    <div v-else-if="activeTab === 'members'">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Team Members</h3>
                            <button
                                v-if="can('projects.assign')"
                                @click="showAddMemberModal = true"
                                class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors"
                            >
                                <Plus class="w-3.5 h-3.5" /> Add Member
                            </button>
                        </div>

                        <div v-if="!project.members?.length" class="text-center py-12">
                            <Users class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No members assigned to this project.</p>
                        </div>

                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div
                                v-for="member in project.members"
                                :key="member.id"
                                class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl group"
                            >
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0">
                                    {{ initials(member.name) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm truncate">{{ member.name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ member.pivot?.role ?? 'Team Member' }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <div v-if="project.project_manager_id === member.id" class="relative group/tooltip">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800 cursor-help">
                                            PM
                                        </span>
                                        <div class="absolute bottom-full right-0 mb-2 w-48 px-3 py-2 bg-slate-800 text-white text-xs rounded-xl shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-opacity pointer-events-none z-10 text-center leading-snug">
                                            Reassign project manager before removing
                                            <div class="absolute top-full right-3 w-2 h-2 bg-slate-800 rotate-45 -translate-y-1"></div>
                                        </div>
                                    </div>
                                    <button
                                        v-if="can('projects.assign') && project.project_manager_id !== member.id"
                                        @click="removeMember(member.id)"
                                        class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all"
                                        title="Remove member"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RESOURCES TAB -->
                    <div v-else-if="activeTab === 'resources'">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-slate-900 dark:text-white">Resources</h3>
                            <button
                                v-if="can('resources.create')"
                                @click="openCreateResource"
                                class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors"
                            >
                                <Plus class="w-3.5 h-3.5" /> Add Resource
                            </button>
                        </div>

                        <div v-if="!project.resources?.length" class="text-center py-12">
                            <Package class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No resources assigned to this project.</p>
                        </div>

                        <div v-else class="space-y-2">
                            <div
                                v-for="resource in project.resources"
                                :key="resource.id"
                                class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl group hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            >
                                <div class="w-9 h-9 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg flex items-center justify-center shrink-0">
                                    <Package class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 dark:text-slate-100 text-sm truncate">{{ resource.name }}</p>
                                    <p class="text-xs text-slate-400">{{ resource.type ?? '—' }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                        {{ resource.quantity }} {{ resource.unit }}
                                    </p>
                                    <p class="text-xs text-slate-400">{{ formatMoney(resource.cost) }}</p>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                    <button
                                        v-if="can('resources.update')"
                                        @click="openEditResource(resource)"
                                        class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                        title="Edit"
                                    >
                                        <Pencil class="w-3.5 h-3.5" />
                                    </button>
                                    <button
                                        v-if="can('resources.delete')"
                                        @click="deleteResource(resource.id)"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors"
                                        title="Delete"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- ── TASK MODAL ───────────────────────────────────── -->
        <Modal :show="showTaskModal" :title="editingTask ? 'Edit Task' : 'New Task'" size="lg" @close="showTaskModal = false">
            <form @submit.prevent="submitTask" class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Task Name <span class="text-rose-500">*</span></label>
                        <input v-model="taskForm.name" type="text" placeholder="e.g. Submit structural drawings" :class="[inputClass, taskForm.errors.name ? errorClass : '']" />
                        <p v-if="taskForm.errors.name" :class="errorMsgClass">{{ taskForm.errors.name }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Description</label>
                        <textarea v-model="taskForm.description" rows="2" :class="[inputClass, 'resize-none']"></textarea>
                    </div>

                    <div>
                        <label :class="labelClass">Assign To</label>
                        <select v-model="taskForm.assigned_to" :class="inputClass">
                            <option value="">— Unassigned —</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label :class="labelClass">Due Date</label>
                        <input v-model="taskForm.due_date" type="date" :class="inputClass" />
                    </div>

                    <div>
                        <label :class="labelClass">Priority <span class="text-rose-500">*</span></label>
                        <select v-model="taskForm.priority" :class="inputClass">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div>
                        <label :class="labelClass">Status <span class="text-rose-500">*</span></label>
                        <select v-model="taskForm.status" :class="inputClass">
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
                    <button @click="showTaskModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="submitTask"
                        :disabled="taskForm.processing"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors"
                    >
                        <Loader2 v-if="taskForm.processing" class="w-4 h-4 animate-spin" />
                        {{ taskForm.processing ? 'Saving...' : (editingTask ? 'Save Changes' : 'Create Task') }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- ADD MEMBER MODAL -->
        <Modal :show="showAddMemberModal" title="Add Member" size="sm" @close="showAddMemberModal = false">
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Select User <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-model="memberForm.user_id"
                        class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                    >
                        <option value="">— Select a user —</option>
                        <option v-for="u in availableUsers" :key="u.id" :value="u.id">
                            {{ u.name }}
                        </option>
                    </select>
                    <p v-if="memberForm.errors.user_id" class="mt-1.5 text-xs text-rose-500">
                        {{ memberForm.errors.user_id }}
                    </p>
                </div>

                <div v-if="availableUsers.length === 0" class="text-sm text-slate-400 text-center py-2">
                    All users are already members of this project.
                </div>
            </div>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showAddMemberModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="addMember"
                        :disabled="memberForm.processing || !memberForm.user_id"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors"
                    >
                        <Loader2 v-if="memberForm.processing" class="w-4 h-4 animate-spin" />
                        Add Member
                    </button>
                </div>
            </template>
        </Modal>

        <!-- ── EXPENSE MODAL ────────────────────────────────── -->
        <Modal :show="showExpenseModal" :title="editingExpense ? 'Edit Expense' : 'Log Expense'" size="md" @close="showExpenseModal = false">
            <form @submit.prevent="submitExpense" class="p-6 space-y-5">

                <div>
                    <label :class="labelClass">Description <span class="text-rose-500">*</span></label>
                    <input v-model="expenseForm.description" type="text" placeholder="e.g. Concrete delivery - Phase 1" :class="[inputClass, expenseForm.errors.description ? errorClass : '']" />
                    <p v-if="expenseForm.errors.description" :class="errorMsgClass">{{ expenseForm.errors.description }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label :class="labelClass">Amount ($) <span class="text-rose-500">*</span></label>
                        <input v-model="expenseForm.amount" type="number" min="0" step="0.01" placeholder="0.00" :class="[inputClass, expenseForm.errors.amount ? errorClass : '']" />
                        <p v-if="expenseForm.errors.amount" :class="errorMsgClass">{{ expenseForm.errors.amount }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">Date <span class="text-rose-500">*</span></label>
                        <input v-model="expenseForm.date" type="date" :class="[inputClass, expenseForm.errors.date ? errorClass : '']" />
                        <p v-if="expenseForm.errors.date" :class="errorMsgClass">{{ expenseForm.errors.date }}</p>
                    </div>
                </div>

            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showExpenseModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="submitExpense"
                        :disabled="expenseForm.processing"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors"
                    >
                        <Loader2 v-if="expenseForm.processing" class="w-4 h-4 animate-spin" />
                        {{ expenseForm.processing ? 'Saving...' : (editingExpense ? 'Save Changes' : 'Log Expense') }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- RESOURCE MODAL -->
        <Modal
            :show="showResourceModal"
            :title="editingResource ? 'Edit Resource' : 'Add Resource'"
            size="md"
            @close="showResourceModal = false"
        >
            <div class="p-6 space-y-4">
                <div>
                    <label :class="labelClass">Name <span class="text-rose-500">*</span></label>
                    <input v-model="resourceForm.name" type="text" :class="[inputClass, resourceForm.errors.name ? errorClass : '']" placeholder="e.g. Steel Beams" />
                    <p v-if="resourceForm.errors.name" :class="errorMsgClass">{{ resourceForm.errors.name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label :class="labelClass">Type</label>
                        <input v-model="resourceForm.type" type="text" :class="inputClass" placeholder="e.g. Material" list="resource-types" />
                        <datalist id="resource-types">
                            <option v-for="t in resourceTypeSuggestions" :key="t" :value="t" />
                        </datalist>
                    </div>
                    <div>
                        <label :class="labelClass">Unit</label>
                        <input v-model="resourceForm.unit" type="text" :class="inputClass" placeholder="e.g. kg, pcs" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label :class="labelClass">Quantity <span class="text-rose-500">*</span></label>
                        <input v-model="resourceForm.quantity" type="number" min="0" step="0.01" :class="[inputClass, resourceForm.errors.quantity ? errorClass : '']" placeholder="0" />
                        <p v-if="resourceForm.errors.quantity" :class="errorMsgClass">{{ resourceForm.errors.quantity }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">Unit Cost</label>
                        <input v-model="resourceForm.cost" type="number" min="0" step="0.01" :class="inputClass" placeholder="0.00" />
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showResourceModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="submitResource"
                        :disabled="resourceForm.processing"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors"
                    >
                        <Loader2 v-if="resourceForm.processing" class="w-4 h-4 animate-spin" />
                        {{ editingResource ? 'Save Changes' : 'Add Resource' }}
                    </button>
                </div>
            </template>
        </Modal>

    </AuthenticatedLayout>
</template>
