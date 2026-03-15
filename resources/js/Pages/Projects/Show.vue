<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import AttachmentUploader from '@/Components/AttachmentUploader.vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { GGanttChart, GGanttRow } from '@infectoone/vue-ganttastic'
import {
    Activity, ArrowLeft, BarChart2, BookOpen, CalendarDays,
    CheckCircle, CheckSquare, ChevronRight, ClipboardList,
    DollarSign, FolderKanban, Loader2, MapPin, Menu, MessageSquare,
    Package, Paperclip, Pencil, Plus, Receipt, Shield,
    Trash2, TrendingUp, Users, X, XCircle,
} from 'lucide-vue-next'

const props = defineProps({
    project: Object,
    users:   Array,
})

const page        = usePage()
const permissions = computed(() => page.props.auth.permissions ?? [])
const roles       = computed(() => page.props.auth.roles ?? [])
const can         = (p) => permissions.value.includes(p)
const hasRole     = (r) => roles.value.includes(r)

// ── Sidebar navigation ─────────────────────────────────
const activeSection     = ref('overview')
const mobileSidebarOpen = ref(false)

const navigate = (key) => {
    activeSection.value     = key
    mobileSidebarOpen.value = false
}

const sidebarGroups = computed(() => [
    {
        items: [
            { key: 'overview', label: 'Overview', icon: FolderKanban },
        ],
    },
    {
        label: 'Project Work',
        items: [
            { key: 'tasks',      label: 'Tasks',      icon: CheckSquare,   count: props.project.tasks?.length },
            { key: 'gantt',      label: 'Gantt',       icon: BarChart2 },
            { key: 'daily_log',  label: 'Daily Log',   icon: BookOpen,      count: props.project.daily_logs?.length },
            { key: 'punch_list', label: 'Punch List',  icon: ClipboardList, count: props.project.punch_list_items?.length },
        ],
    },
    {
        label: 'Finance',
        items: [
            { key: 'expenses', label: 'Expenses', icon: Receipt, count: props.project.expenses?.length },
        ],
    },
    {
        label: 'Resources',
        items: [
            { key: 'resources', label: 'Resources', icon: Package, count: props.project.resources?.length },
            { key: 'members',   label: 'Members',   icon: Users,   count: props.project.members?.length },
        ],
    },
    {
        label: 'Communication',
        items: [
            { key: 'discussions', label: 'Discussions', icon: MessageSquare, count: props.project.discussions?.length },
            { key: 'files',       label: 'Files',       icon: Paperclip,    count: props.project.attachments?.length },
        ],
    },
    {
        label: 'Audit',
        items: [
            { key: 'activity', label: 'Activity', icon: Activity, count: props.project.activities?.length },
        ],
    },
])

// ── Computed stats ─────────────────────────────────────
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
    props.project.expenses?.reduce((s, e) => s + parseFloat(e.amount), 0) ?? 0
)

const budgetUsedPct = computed(() => {
    if (!props.project.budget) return 0
    return Math.min(Math.round((totalExpenses.value / props.project.budget) * 100), 100)
})

const punchStats = computed(() => {
    const items = props.project.punch_list_items ?? []
    return {
        total:     items.length,
        open:      items.filter(i => i.status === 'open').length,
        completed: items.filter(i => i.status === 'completed').length,
    }
})

// ── Configs ────────────────────────────────────────────
const statusConfig = {
    active:    { label: 'Active',    dot: 'bg-emerald-500', badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    on_hold:   { label: 'On Hold',   dot: 'bg-amber-500',   badge: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
    planning:  { label: 'Planning',  dot: 'bg-blue-500',    badge: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800' },
    completed: { label: 'Completed', dot: 'bg-slate-400',   badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
    cancelled: { label: 'Cancelled', dot: 'bg-rose-500',    badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
}

const priorityConfig = {
    critical: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800',
    high:     'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800',
    medium:   'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
    low:      'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
}

const taskStatusConfig = {
    pending:     { label: 'Pending',     badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
    in_progress: { label: 'In Progress', badge: 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800' },
    review:      { label: 'Review',      badge: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800' },
    completed:   { label: 'Completed',   badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    blocked:     { label: 'Blocked',     badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
}

const expenseStatusConfig = {
    pending:  { label: 'Pending',  badge: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
    approved: { label: 'Approved', badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    rejected: { label: 'Rejected', badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
}

const punchPriorityConfig = {
    high:   'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800',
    medium: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
    low:    'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
}

const punchStatusConfig = {
    open:        { label: 'Open',        class: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
    in_progress: { label: 'In Progress', class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
    completed:   { label: 'Completed',   class: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
}

// ── Helpers ────────────────────────────────────────────
const formatDate  = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'
const formatMoney = (v) => v ? '₦' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'
const initials    = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('') ?? '?'
const isOverdue   = (d, status) => d && status !== 'completed' && new Date(d) < new Date()
const fmtDueDate  = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) : '—'

// ── Task Modal ─────────────────────────────────────────
const showTaskModal = ref(false)
const editingTask   = ref(null)

const taskForm = useForm({
    project_id: props.project.id, assigned_to: '', name: '',
    description: '', start_date: '', due_date: '', priority: 'medium', status: 'pending',
})

const openCreateTask = () => {
    editingTask.value = null; taskForm.reset(); taskForm.project_id = props.project.id; showTaskModal.value = true
}

const openEditTask = (task) => {
    editingTask.value    = task
    taskForm.project_id  = task.project_id
    taskForm.assigned_to = task.assigned_to ?? ''
    taskForm.name        = task.name
    taskForm.description = task.description ?? ''
    taskForm.start_date  = task.start_date?.substring(0, 10) ?? ''
    taskForm.due_date    = task.due_date?.substring(0, 10) ?? ''
    taskForm.priority    = task.priority
    taskForm.status      = task.status
    showTaskModal.value  = true
}

const submitTask = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showTaskModal.value = false; taskForm.reset() } }
    editingTask.value ? taskForm.put(route('tasks.update', editingTask.value.id), opts) : taskForm.post(route('tasks.store'), opts)
}

const deleteTask = (id) => { if (confirm('Delete this task?')) router.delete(route('tasks.destroy', id), { preserveScroll: true }) }

// ── Expense Modal ──────────────────────────────────────
const showExpenseModal = ref(false)
const editingExpense   = ref(null)

const expenseForm = useForm({ project_id: props.project.id, amount: '', description: '', date: '', status: 'pending' })

const openCreateExpense = () => {
    editingExpense.value = null; expenseForm.reset(); expenseForm.project_id = props.project.id; showExpenseModal.value = true
}

const openEditExpense = (e) => {
    editingExpense.value    = e
    expenseForm.project_id  = e.project_id
    expenseForm.amount      = e.amount
    expenseForm.description = e.description
    expenseForm.date        = e.date?.substring(0, 10) ?? ''
    expenseForm.status      = e.status
    showExpenseModal.value  = true
}

const submitExpense = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showExpenseModal.value = false; expenseForm.reset() } }
    editingExpense.value ? expenseForm.put(route('expenses.update', editingExpense.value.id), opts) : expenseForm.post(route('expenses.store'), opts)
}

const deleteExpense  = (id) => { if (confirm('Delete this expense?')) router.delete(route('expenses.destroy', id), { preserveScroll: true }) }
const approveExpense = (id) => router.patch(route('expenses.approve', id), {}, { preserveScroll: true })
const rejectExpense  = (id) => router.patch(route('expenses.reject', id), {}, { preserveScroll: true })

// ── Members ────────────────────────────────────────────
const showAddMemberModal = ref(false)
const memberForm         = useForm({ user_id: '' })
const existingMemberIds  = computed(() => props.project.members?.map(m => m.id) ?? [])
const availableUsers     = computed(() => props.users.filter(u => !existingMemberIds.value.includes(u.id)))

const addMember = () => {
    memberForm.post(route('projects.members.add', props.project.id), {
        preserveScroll: true,
        onSuccess: () => { showAddMemberModal.value = false; memberForm.reset() },
    })
}

const removeMember = (userId) => {
    if (!confirm('Remove this member?')) return
    router.delete(route('projects.members.remove', [props.project.id, userId]), { preserveScroll: true })
}

// ── Resources ──────────────────────────────────────────
const showResourceModal = ref(false)
const editingResource   = ref(null)
const resourceForm      = useForm({ name: '', type: '', quantity: '', unit: '', cost: '', project_id: '' })
const resourceTypeSuggestions = ['Equipment', 'Material', 'Labour', 'Vehicle', 'Tool', 'Safety Gear', 'Fuel', 'Electrical', 'Plumbing']

const openCreateResource = () => {
    editingResource.value = null; resourceForm.reset(); resourceForm.project_id = props.project.id; showResourceModal.value = true
}

const openEditResource = (r) => {
    editingResource.value = r
    resourceForm.name = r.name; resourceForm.type = r.type ?? ''; resourceForm.quantity = r.quantity
    resourceForm.unit = r.unit ?? ''; resourceForm.cost = r.cost ?? ''; resourceForm.project_id = r.project_id
    showResourceModal.value = true
}

const submitResource = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showResourceModal.value = false; resourceForm.reset() } }
    editingResource.value ? resourceForm.put(route('resources.update', editingResource.value.id), opts) : resourceForm.post(route('resources.store'), opts)
}

const deleteResource = (id) => { if (confirm('Delete this resource?')) router.delete(route('resources.destroy', id), { preserveScroll: true }) }

// ── Gantt ──────────────────────────────────────────────
const statusColor = (s) => ({ completed: '#10b981', in_progress: '#6366f1', blocked: '#ef4444', review: '#f59e0b', pending: '#94a3b8' }[s] ?? '#94a3b8')

const ganttRows = computed(() =>
    (props.project.tasks ?? []).filter(t => t.start_date && t.due_date).map(t => ({
        label: t.name,
        bars: [{ myStart: t.start_date.substring(0, 10) + ' 00:00', myEnd: t.due_date.substring(0, 10) + ' 23:59',
            ganttBarConfig: { id: String(t.id), label: t.name, backgroundColor: statusColor(t.status), borderRadius: '6px' } }],
    }))
)

const ganttStart = computed(() => {
    const d = (props.project.tasks ?? []).filter(t => t.start_date).map(t => t.start_date.substring(0, 10))
    return d.length ? d.sort()[0] + ' 00:00' : new Date().toISOString().substring(0, 10) + ' 00:00'
})

const ganttEnd = computed(() => {
    const d = (props.project.tasks ?? []).filter(t => t.due_date).map(t => t.due_date.substring(0, 10))
    return d.length ? d.sort().reverse()[0] + ' 23:59' : new Date().toISOString().substring(0, 10) + ' 23:59'
})

// ── Daily Log ──────────────────────────────────────────
const showLogModal    = ref(false)
const editingLog      = ref(null)
const fetchingWeather = ref(false)
const logForm         = useForm({ date: new Date().toISOString().substring(0, 10), work_performed: '', workers_on_site: '', equipment_used: '', delays_issues: '', weather: '', temperature: '' })

const openCreateLog = () => {
    editingLog.value = null; logForm.reset(); logForm.date = new Date().toISOString().substring(0, 10); showLogModal.value = true; fetchWeather()
}

const openEditLog = (log) => {
    editingLog.value = log
    logForm.date = log.date?.substring(0, 10) ?? ''; logForm.work_performed = log.work_performed
    logForm.workers_on_site = log.workers_on_site; logForm.equipment_used = log.equipment_used ?? ''
    logForm.delays_issues = log.delays_issues ?? ''; logForm.weather = log.weather ?? ''; logForm.temperature = log.temperature ?? ''
    showLogModal.value = true
}

const fetchWeather = async () => {
    if (!props.project.location) return
    fetchingWeather.value = true
    try {
        const res = await fetch(route('projects.weather', props.project.id))
        const d   = await res.json()
        if (d.weather) { logForm.weather = d.weather; logForm.temperature = d.temperature }
    } catch {}
    fetchingWeather.value = false
}

const submitLog = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showLogModal.value = false; logForm.reset() } }
    editingLog.value
        ? logForm.put(route('daily-logs.update', [props.project.id, editingLog.value.id]), opts)
        : logForm.post(route('daily-logs.store', props.project.id), opts)
}

const deleteLog = (id) => { if (confirm('Delete this log?')) router.delete(route('daily-logs.destroy', [props.project.id, id]), { preserveScroll: true }) }

const weatherEmoji = (w) => {
    if (!w) return '🌤️'
    const l = w.toLowerCase()
    if (l.includes('rain')) return '🌧️'
    if (l.includes('storm')) return '⛈️'
    if (l.includes('snow')) return '❄️'
    if (l.includes('cloud')) return '☁️'
    if (l.includes('clear')) return '☀️'
    if (l.includes('wind')) return '💨'
    return '🌤️'
}

// ── Punch List ─────────────────────────────────────────
const showPunchModal = ref(false)
const editingItem    = ref(null)
const punchForm      = useForm({ description: '', location_on_site: '', assigned_to: '', priority: 'medium', status: 'open', due_date: '' })

const openCreatePunch = () => {
    editingItem.value = null; punchForm.reset(); punchForm.priority = 'medium'; punchForm.status = 'open'; showPunchModal.value = true
}

const openEditPunch = (item) => {
    editingItem.value          = item
    punchForm.description      = item.description
    punchForm.location_on_site = item.location_on_site ?? ''
    punchForm.assigned_to      = item.assigned_to ?? ''
    punchForm.priority         = item.priority
    punchForm.status           = item.status
    punchForm.due_date         = item.due_date?.substring(0, 10) ?? ''
    showPunchModal.value       = true
}

const submitPunch = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showPunchModal.value = false; punchForm.reset() } }
    editingItem.value
        ? punchForm.put(route('punch-list.update', [props.project.id, editingItem.value.id]), opts)
        : punchForm.post(route('punch-list.store', props.project.id), opts)
}

const deletePunchItem = (id) => { if (confirm('Delete this item?')) router.delete(route('punch-list.destroy', [props.project.id, id]), { preserveScroll: true }) }

// ── Input classes ──────────────────────────────────────
const inputClass    = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 dark:focus:border-indigo-600 transition-all'
const errorClass    = '!border-rose-400 dark:!border-rose-600'
const labelClass    = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <div class="space-y-4">

            <!-- Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                <Link :href="route('projects.index')" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-1">
                    <FolderKanban class="w-4 h-4" /> Projects
                </Link>
                <ChevronRight class="w-3.5 h-3.5" />
                <span class="text-slate-700 dark:text-slate-200 font-medium truncate">{{ project.name }}</span>
            </div>

            <!-- Project header -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="h-1 w-full" :class="{
                    'bg-emerald-500': project.status === 'active',
                    'bg-amber-500':   project.status === 'on_hold',
                    'bg-blue-500':    project.status === 'planning',
                    'bg-slate-400':   project.status === 'completed',
                    'bg-rose-500':    project.status === 'cancelled',
                }"></div>
                <div class="p-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 flex-wrap mb-1">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ project.name }}</h1>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border" :class="statusConfig[project.status]?.badge">
                                <span class="w-1.5 h-1.5 rounded-full" :class="statusConfig[project.status]?.dot"></span>
                                {{ statusConfig[project.status]?.label }}
                            </span>
                        </div>
                        <p v-if="project.description" class="text-sm text-slate-500 dark:text-slate-400 max-w-2xl">{{ project.description }}</p>
                        <div v-if="project.location" class="flex items-center gap-1.5 mt-1 text-sm text-slate-400 dark:text-slate-500">
                            <MapPin class="w-3.5 h-3.5 shrink-0" /> {{ project.location }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <Link :href="route('projects.index')"
                              class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
                            <ArrowLeft class="w-4 h-4" /> Back
                        </Link>
                        <button v-if="can('projects.delete')"
                                @click="router.delete(route('projects.destroy', project.id), { onBefore: () => confirm('Delete this project?') })"
                                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-100 rounded-xl transition-colors">
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar + Content layout -->
            <div class="flex gap-5 items-start">

                <!-- Mobile toggle -->
                <button @click="mobileSidebarOpen = true"
                        class="lg:hidden shrink-0 flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-600 dark:text-slate-300 shadow-sm">
                    <Menu class="w-4 h-4" />
                </button>

                <!-- Mobile overlay -->
                <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
                            leave-active-class="transition-opacity duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-if="mobileSidebarOpen" class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="mobileSidebarOpen = false" />
                </Transition>

                <!-- Sidebar -->
                <aside :class="[
                    'shrink-0 z-50 transition-transform duration-300',
                    'fixed top-0 left-0 h-full w-64 lg:static lg:w-52 lg:h-auto lg:translate-x-0',
                    mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
                ]">
                    <div class="h-full lg:h-auto bg-white dark:bg-slate-900 lg:rounded-2xl border-r lg:border border-slate-200 dark:border-slate-800 shadow-sm lg:sticky lg:top-6 overflow-y-auto">

                        <!-- Mobile header -->
                        <div class="flex items-center justify-between p-4 border-b border-slate-100 dark:border-slate-800 lg:hidden">
                            <span class="font-semibold text-slate-800 dark:text-white text-sm">{{ project.name }}</span>
                            <button @click="mobileSidebarOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                                <X class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Progress mini-stats -->
                        <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="text-slate-500 dark:text-slate-400">Progress</span>
                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ progressPct }}%</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden mb-3">
                                <div class="h-full rounded-full transition-all duration-700" :class="progressColor" :style="{ width: progressPct + '%' }"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-2">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ project.tasks_count ?? 0 }}</p>
                                    <p class="text-[10px] text-slate-400">Tasks</p>
                                </div>
                                <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-2">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ budgetUsedPct }}%</p>
                                    <p class="text-[10px] text-slate-400">Budget</p>
                                </div>
                            </div>
                        </div>

                        <!-- Nav groups -->
                        <nav class="p-2 space-y-4">
                            <div v-for="group in sidebarGroups" :key="group.label">
                                <p v-if="group.label" class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 px-3 mb-1">
                                    {{ group.label }}
                                </p>
                                <div class="space-y-0.5">
                                    <button
                                        v-for="item in group.items"
                                        :key="item.key"
                                        @click="navigate(item.key)"
                                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all"
                                        :class="activeSection === item.key
                                            ? 'bg-indigo-600/10 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400'
                                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200'"
                                    >
                                        <component :is="item.icon" class="w-4 h-4 shrink-0" />
                                        <span class="flex-1 text-left">{{ item.label }}</span>
                                        <span v-if="item.count !== undefined && item.count > 0"
                                              class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full"
                                              :class="activeSection === item.key
                                                  ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                                                  : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'">
                                            {{ item.count }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </nav>
                    </div>
                </aside>

                <!-- Main content -->
                <div class="flex-1 min-w-0 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">

                    <!-- ══ OVERVIEW ═══════════════════════════════════ -->
                    <div v-if="activeSection === 'overview'" class="p-6 space-y-6">
                        <h2 class="font-semibold text-slate-900 dark:text-white">Project Overview</h2>

                        <!-- Stats grid -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <DollarSign class="w-4 h-4 text-emerald-600" />
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Budget</span>
                                </div>
                                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatMoney(project.budget) }}</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <DollarSign class="w-4 h-4 text-rose-500" />
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Spent</span>
                                </div>
                                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatMoney(totalExpenses) }}</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <CalendarDays class="w-4 h-4 text-indigo-600" />
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Start</span>
                                </div>
                                <p class="text-lg font-bold text-slate-900 dark:text-white">{{ formatDate(project.start_date) }}</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <CalendarDays class="w-4 h-4 text-amber-500" />
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Due</span>
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
                                    <span class="text-slate-400 text-xs">{{ project.completed_tasks_count }}/{{ project.tasks_count }} tasks</span>
                                </div>
                                <span class="font-bold text-slate-900 dark:text-white">{{ progressPct }}%</span>
                            </div>
                            <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700" :class="progressColor" :style="{ width: progressPct + '%' }"></div>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-slate-400 pt-1">
                                <span>{{ project.completed_tasks_count ?? 0 }} completed</span>
                                <span>{{ project.in_progress_tasks_count ?? 0 }} in progress</span>
                                <span>{{ project.pending_tasks_count ?? 0 }} pending</span>
                            </div>
                        </div>

                        <!-- Project Manager -->
                        <div v-if="project.projectManager" class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl flex items-center justify-center text-white text-xs font-bold shrink-0">
                                {{ initials(project.projectManager.name) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ project.projectManager.name }}</p>
                                <p class="text-xs text-slate-400 flex items-center gap-1"><Shield class="w-3 h-3" /> Project Manager</p>
                            </div>
                        </div>
                    </div>

                    <!-- ══ TASKS ══════════════════════════════════════ -->
                    <div v-else-if="activeSection === 'tasks'" class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-slate-900 dark:text-white">Tasks</h2>
                            <button v-if="can('tasks.create')" @click="openCreateTask"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors">
                                <Plus class="w-3.5 h-3.5" /> Add Task
                            </button>
                        </div>
                        <div v-if="!project.tasks?.length" class="text-center py-12">
                            <CheckSquare class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No tasks yet.</p>
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="task in project.tasks" :key="task.id"
                                 class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <p class="font-medium text-slate-800 dark:text-slate-100 text-sm truncate">{{ task.name }}</p>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-medium border" :class="taskStatusConfig[task.status]?.badge">
                                            {{ taskStatusConfig[task.status]?.label }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-medium border capitalize" :class="priorityConfig[task.priority]">
                                            {{ task.priority }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-slate-400">
                                        <span v-if="task.assignee" class="flex items-center gap-1">
                                            <div class="w-4 h-4 rounded bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white text-[8px] font-bold">
                                                {{ initials(task.assignee.name) }}
                                            </div>
                                            {{ task.assignee.name }}
                                        </span>
                                        <span v-if="task.due_date" class="flex items-center gap-1"
                                              :class="isOverdue(task.due_date, task.status) ? 'text-rose-500' : ''">
                                            📅 {{ fmtDueDate(task.due_date) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                    <button v-if="can('tasks.update')" @click="openEditTask(task)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                        <Pencil class="w-3.5 h-3.5" />
                                    </button>
                                    <button v-if="can('tasks.delete')" @click="deleteTask(task.id)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ GANTT ══════════════════════════════════════ -->
                    <div v-else-if="activeSection === 'gantt'" class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-slate-900 dark:text-white">Gantt Chart</h2>
                            <div class="flex items-center gap-3 text-xs text-slate-400">
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-indigo-500 inline-block"></span> In Progress</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-500 inline-block"></span> Completed</span>
                                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-rose-500 inline-block"></span> Blocked</span>
                            </div>
                        </div>
                        <div v-if="!ganttRows.length" class="text-center py-12">
                            <BarChart2 class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No tasks with start dates yet.</p>
                        </div>
                        <div v-else class="rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700" style="height:500px">
                            <g-gantt-chart :chart-start="ganttStart" :chart-end="ganttEnd" bar-start="myStart" bar-end="myEnd"
                                           precision="month" date-format="YYYY-MM-DD HH:mm" width="100%" :row-height="40"
                                           :label-column-width="220" color-scheme="default">
                                <g-gantt-row v-for="row in ganttRows" :key="row.label" :label="row.label" :bars="row.bars" />
                            </g-gantt-chart>
                        </div>
                    </div>

                    <!-- ══ DAILY LOG ══════════════════════════════════ -->
                    <div v-else-if="activeSection === 'daily_log'" class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-slate-900 dark:text-white">Daily Log</h2>
                            <button v-if="can('projects.update')" @click="openCreateLog"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors">
                                <Plus class="w-3.5 h-3.5" /> New Entry
                            </button>
                        </div>
                        <div v-if="!project.daily_logs?.length" class="text-center py-12">
                            <BookOpen class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No daily logs yet.</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="log in project.daily_logs" :key="log.id"
                                 class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 group">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="text-2xl">{{ weatherEmoji(log.weather) }}</div>
                                        <div>
                                            <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm">
                                                {{ new Date(log.date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }) }}
                                            </p>
                                            <div class="flex items-center gap-3 mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                                <span v-if="log.weather">{{ log.weather }}{{ log.temperature ? ' · ' + log.temperature + '°C' : '' }}</span>
                                                <span>👷 {{ log.workers_on_site }} workers</span>
                                                <span>by {{ log.logger?.name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                        <button v-if="can('projects.update')" @click="openEditLog(log)"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                            <Pencil class="w-3.5 h-3.5" />
                                        </button>
                                        <button v-if="can('projects.update')" @click="deleteLog(log.id)"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                                <p class="text-sm text-slate-700 dark:text-slate-200 mb-2">{{ log.work_performed }}</p>
                                <div class="flex flex-wrap gap-3">
                                    <div v-if="log.equipment_used" class="text-xs text-slate-500 dark:text-slate-400">🔧 {{ log.equipment_used }}</div>
                                    <div v-if="log.delays_issues" class="text-xs text-amber-600 dark:text-amber-400">⚠️ {{ log.delays_issues }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ PUNCH LIST ═════════════════════════════════ -->
                    <div v-else-if="activeSection === 'punch_list'" class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="font-semibold text-slate-900 dark:text-white">Punch List</h2>
                                <p v-if="punchStats.total > 0" class="text-xs text-slate-400 mt-0.5">
                                    {{ punchStats.completed }}/{{ punchStats.total }} completed · {{ punchStats.open }} open
                                </p>
                            </div>
                            <button v-if="can('projects.update')" @click="openCreatePunch"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors">
                                <Plus class="w-3.5 h-3.5" /> Add Item
                            </button>
                        </div>
                        <div v-if="punchStats.total > 0" class="mb-4">
                            <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                     :style="{ width: (punchStats.completed / punchStats.total * 100) + '%' }"></div>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">{{ Math.round(punchStats.completed / punchStats.total * 100) }}% complete</p>
                        </div>
                        <div v-if="!project.punch_list_items?.length" class="text-center py-12">
                            <ClipboardList class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No punch list items yet.</p>
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="item in project.punch_list_items" :key="item.id"
                                 class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 group"
                                 :class="{ 'opacity-60': item.status === 'completed' }">
                                <div class="mt-0.5 shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center cursor-pointer transition-colors"
                                     :class="item.status === 'completed' ? 'bg-emerald-500 border-emerald-500' : 'border-slate-300 dark:border-slate-600 hover:border-emerald-400'"
                                     @click="() => { openEditPunch(item); punchForm.status = item.status === 'completed' ? 'open' : 'completed'; submitPunch() }">
                                    <svg v-if="item.status === 'completed'" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100" :class="{ 'line-through text-slate-400': item.status === 'completed' }">
                                        {{ item.description }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                        <span v-if="item.location_on_site" class="text-xs text-slate-500 dark:text-slate-400">📍 {{ item.location_on_site }}</span>
                                        <span v-if="item.assignee" class="text-xs text-slate-500 dark:text-slate-400">👷 {{ item.assignee.name }}</span>
                                        <span v-if="item.due_date" class="text-xs text-slate-500 dark:text-slate-400">📅 {{ formatDate(item.due_date) }}</span>
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold border capitalize" :class="punchPriorityConfig[item.priority]">{{ item.priority }}</span>
                                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-semibold border" :class="punchStatusConfig[item.status]?.class">{{ punchStatusConfig[item.status]?.label }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                    <button v-if="can('projects.update')" @click="openEditPunch(item)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                        <Pencil class="w-3.5 h-3.5" />
                                    </button>
                                    <button v-if="can('projects.update')" @click="deletePunchItem(item.id)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ EXPENSES ═══════════════════════════════════ -->
                    <div v-else-if="activeSection === 'expenses'" class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-slate-900 dark:text-white">Expenses</h2>
                            <button v-if="can('expenses.create')" @click="openCreateExpense"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors">
                                <Plus class="w-3.5 h-3.5" /> Log Expense
                            </button>
                        </div>
                        <div v-if="!project.expenses?.length" class="text-center py-12">
                            <Receipt class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No expenses logged yet.</p>
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="expense in project.expenses" :key="expense.id"
                                 class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 dark:text-slate-100 text-sm truncate">{{ expense.description }}</p>
                                    <div class="flex items-center gap-3 mt-1 text-xs text-slate-400">
                                        <span>📅 {{ formatDate(expense.date) }}</span>
                                        <span v-if="expense.approved_by"><Shield class="w-3 h-3 inline" /> {{ expense.approved_by.name }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ formatMoney(expense.amount) }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium border" :class="expenseStatusConfig[expense.status]?.badge">
                                        {{ expenseStatusConfig[expense.status]?.label }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                    <button v-if="can('expenses.approve') && expense.status === 'pending'" @click="approveExpense(expense.id)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors">
                                        <CheckCircle class="w-3.5 h-3.5" />
                                    </button>
                                    <button v-if="can('expenses.approve') && expense.status === 'pending'" @click="rejectExpense(expense.id)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                                        <XCircle class="w-3.5 h-3.5" />
                                    </button>
                                    <button v-if="can('expenses.update') && expense.status === 'pending'" @click="openEditExpense(expense)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                        <Pencil class="w-3.5 h-3.5" />
                                    </button>
                                    <button v-if="can('expenses.delete')" @click="deleteExpense(expense.id)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ RESOURCES ══════════════════════════════════ -->
                    <div v-else-if="activeSection === 'resources'" class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-slate-900 dark:text-white">Resources</h2>
                            <button v-if="can('resources.create')" @click="openCreateResource"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors">
                                <Plus class="w-3.5 h-3.5" /> Add Resource
                            </button>
                        </div>
                        <div v-if="!project.resources?.length" class="text-center py-12">
                            <Package class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No resources assigned.</p>
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="resource in project.resources" :key="resource.id"
                                 class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl group hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                <div class="w-9 h-9 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg flex items-center justify-center shrink-0">
                                    <Package class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 dark:text-slate-100 text-sm truncate">{{ resource.name }}</p>
                                    <p class="text-xs text-slate-400">{{ resource.type ?? '—' }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ resource.quantity }} {{ resource.unit }}</p>
                                    <p class="text-xs text-slate-400">{{ formatMoney(resource.cost) }}</p>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                    <button v-if="can('resources.update')" @click="openEditResource(resource)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                                        <Pencil class="w-3.5 h-3.5" />
                                    </button>
                                    <button v-if="can('resources.delete')" @click="deleteResource(resource.id)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ MEMBERS ════════════════════════════════════ -->
                    <div v-else-if="activeSection === 'members'" class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-slate-900 dark:text-white">Team Members</h2>
                            <button v-if="can('projects.assign')" @click="showAddMemberModal = true"
                                    class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl transition-colors">
                                <Plus class="w-3.5 h-3.5" /> Add Member
                            </button>
                        </div>
                        <div v-if="!project.members?.length" class="text-center py-12">
                            <Users class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No members assigned.</p>
                        </div>
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div v-for="member in project.members" :key="member.id"
                                 class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl group">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0">
                                    {{ initials(member.name) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm truncate">{{ member.name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ member.pivot?.role ?? 'Team Member' }}</p>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <div v-if="project.project_manager_id === member.id" class="relative group/tooltip">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800 cursor-help">PM</span>
                                        <div class="absolute bottom-full right-0 mb-2 w-48 px-3 py-2 bg-slate-800 text-white text-xs rounded-xl shadow-xl opacity-0 group-hover/tooltip:opacity-100 transition-opacity pointer-events-none z-10 text-center leading-snug">
                                            Reassign project manager before removing
                                            <div class="absolute top-full right-3 w-2 h-2 bg-slate-800 rotate-45 -translate-y-1"></div>
                                        </div>
                                    </div>
                                    <button v-if="can('projects.assign') && project.project_manager_id !== member.id"
                                            @click="removeMember(member.id)"
                                            class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all">
                                        <Trash2 class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══ DISCUSSIONS ════════════════════════════════ -->
                    <div v-else-if="activeSection === 'discussions'" class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-slate-900 dark:text-white">Discussions</h2>
                            <Link :href="route('discussions.index')" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                View all →
                            </Link>
                        </div>
                        <div v-if="!project.discussions?.length" class="text-center py-12">
                            <MessageSquare class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No discussions for this project yet.</p>
                        </div>
                        <div v-else class="space-y-2">
                            <Link v-for="d in project.discussions" :key="d.id" :href="route('discussions.show', d.id)"
                                  class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group">
                                <MessageSquare class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">{{ d.title }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ d.author?.name }} · {{ d.replies_count }} replies</p>
                                </div>
                                <span v-if="d.category" class="text-[10px] font-semibold text-white px-2 py-0.5 rounded-full shrink-0" :style="{ background: d.category.color }">
                                    {{ d.category.name }}
                                </span>
                            </Link>
                        </div>
                    </div>

                    <!-- ══ FILES ══════════════════════════════════════ -->
                    <div v-else-if="activeSection === 'files'" class="p-6">
                        <h2 class="font-semibold text-slate-900 dark:text-white mb-4">Files & Attachments</h2>
                        <AttachmentUploader
                            model-type="projects"
                            :model-id="project.id"
                            :attachments="project.attachments ?? []"
                            :can-upload="can('projects.update')"
                            :can-delete="can('projects.delete')"
                        />
                    </div>

                    <!-- ══ ACTIVITY ════════════════════════════════════ -->
                    <div v-else-if="activeSection === 'activity'" class="p-6">
                        <h2 class="font-semibold text-slate-900 dark:text-white mb-4">Activity</h2>
                        <div v-if="!project.activities?.length" class="text-center py-12">
                            <Activity class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                            <p class="text-slate-500 dark:text-slate-400 text-sm">No activity recorded yet.</p>
                        </div>
                        <div v-else class="space-y-1">
                            <div v-for="log in project.activities" :key="log.id"
                                 class="flex gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <div class="w-7 h-7 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                    <Activity class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-slate-700 dark:text-slate-200">
                                        <span class="font-semibold">{{ log.causer?.name ?? 'System' }}</span>
                                        <span class="text-slate-500 dark:text-slate-400"> {{ log.description }}</span>
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ new Date(log.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ══ MODALS ══════════════════════════════════════════════ -->

        <!-- Task Modal -->
        <Modal :show="showTaskModal" :title="editingTask ? 'Edit Task' : 'New Task'" size="lg" @close="showTaskModal = false">
            <form @submit.prevent="submitTask" class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label :class="labelClass">Task Name <span class="text-rose-500">*</span></label>
                        <input v-model="taskForm.name" type="text" :class="[inputClass, taskForm.errors.name ? errorClass : '']" />
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
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
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
                        <label :class="labelClass">Start Date</label>
                        <input v-model="taskForm.start_date" type="date" :class="inputClass" />
                    </div>
                    <div>
                        <label :class="labelClass">Due Date</label>
                        <input v-model="taskForm.due_date" type="date" :class="inputClass" />
                    </div>
                    <div class="sm:col-span-2">
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
                    <button @click="showTaskModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">Cancel</button>
                    <button @click="submitTask" :disabled="taskForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="taskForm.processing" class="w-4 h-4 animate-spin" />
                        {{ taskForm.processing ? 'Saving...' : (editingTask ? 'Save Changes' : 'Create Task') }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Expense Modal -->
        <Modal :show="showExpenseModal" :title="editingExpense ? 'Edit Expense' : 'Log Expense'" size="md" @close="showExpenseModal = false">
            <form @submit.prevent="submitExpense" class="p-6 space-y-5">
                <div>
                    <label :class="labelClass">Description <span class="text-rose-500">*</span></label>
                    <input v-model="expenseForm.description" type="text" :class="[inputClass, expenseForm.errors.description ? errorClass : '']" />
                    <p v-if="expenseForm.errors.description" :class="errorMsgClass">{{ expenseForm.errors.description }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label :class="labelClass">Amount (₦) <span class="text-rose-500">*</span></label>
                        <input v-model="expenseForm.amount" type="number" min="0" step="0.01" :class="[inputClass, expenseForm.errors.amount ? errorClass : '']" />
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
                    <button @click="showExpenseModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">Cancel</button>
                    <button @click="submitExpense" :disabled="expenseForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="expenseForm.processing" class="w-4 h-4 animate-spin" />
                        {{ editingExpense ? 'Save Changes' : 'Log Expense' }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Resource Modal -->
        <Modal :show="showResourceModal" :title="editingResource ? 'Edit Resource' : 'Add Resource'" size="md" @close="showResourceModal = false">
            <div class="p-6 space-y-4">
                <div>
                    <label :class="labelClass">Name <span class="text-rose-500">*</span></label>
                    <input v-model="resourceForm.name" type="text" :class="[inputClass, resourceForm.errors.name ? errorClass : '']" />
                    <p v-if="resourceForm.errors.name" :class="errorMsgClass">{{ resourceForm.errors.name }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label :class="labelClass">Type</label>
                        <input v-model="resourceForm.type" type="text" :class="inputClass" list="resource-types" />
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
                        <input v-model="resourceForm.quantity" type="number" min="0" step="0.01" :class="[inputClass, resourceForm.errors.quantity ? errorClass : '']" />
                        <p v-if="resourceForm.errors.quantity" :class="errorMsgClass">{{ resourceForm.errors.quantity }}</p>
                    </div>
                    <div>
                        <label :class="labelClass">Unit Cost</label>
                        <input v-model="resourceForm.cost" type="number" min="0" step="0.01" :class="inputClass" />
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showResourceModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">Cancel</button>
                    <button @click="submitResource" :disabled="resourceForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="resourceForm.processing" class="w-4 h-4 animate-spin" />
                        {{ editingResource ? 'Save Changes' : 'Add Resource' }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Add Member Modal -->
        <Modal :show="showAddMemberModal" title="Add Member" size="sm" @close="showAddMemberModal = false">
            <div class="p-6 space-y-4">
                <div>
                    <label :class="labelClass">Select User <span class="text-rose-500">*</span></label>
                    <select v-model="memberForm.user_id" :class="inputClass">
                        <option value="">— Select a user —</option>
                        <option v-for="u in availableUsers" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                    <p v-if="memberForm.errors.user_id" :class="errorMsgClass">{{ memberForm.errors.user_id }}</p>
                </div>
                <div v-if="availableUsers.length === 0" class="text-sm text-slate-400 text-center py-2">
                    All users are already members.
                </div>
            </div>
            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showAddMemberModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">Cancel</button>
                    <button @click="addMember" :disabled="memberForm.processing || !memberForm.user_id"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="memberForm.processing" class="w-4 h-4 animate-spin" />
                        Add Member
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Daily Log Modal -->
        <Modal :show="showLogModal" :title="editingLog ? 'Edit Daily Log' : 'New Daily Log'" size="lg" @close="showLogModal = false">
            <div class="p-6 space-y-5">
                <div>
                    <label :class="labelClass">Date <span class="text-rose-500">*</span></label>
                    <input v-model="logForm.date" type="date" :class="inputClass" :disabled="!!editingLog" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label :class="labelClass">
                            Weather
                            <span v-if="fetchingWeather" class="text-xs text-indigo-500 ml-1">fetching...</span>
                            <span v-else-if="project.location" class="text-xs text-slate-400 ml-1">(auto-fetched)</span>
                            <span v-else class="text-xs text-amber-500 ml-1">(add location to project)</span>
                        </label>
                        <input v-model="logForm.weather" type="text" placeholder="e.g. Partly cloudy" :class="inputClass" />
                    </div>
                    <div>
                        <label :class="labelClass">Temperature (°C)</label>
                        <input v-model="logForm.temperature" type="number" step="0.1" :class="inputClass" />
                    </div>
                </div>
                <div>
                    <label :class="labelClass">Workers on Site <span class="text-rose-500">*</span></label>
                    <input v-model="logForm.workers_on_site" type="number" min="0" :class="[inputClass, logForm.errors.workers_on_site ? errorClass : '']" />
                    <p v-if="logForm.errors.workers_on_site" :class="errorMsgClass">{{ logForm.errors.workers_on_site }}</p>
                </div>
                <div>
                    <label :class="labelClass">Work Performed <span class="text-rose-500">*</span></label>
                    <textarea v-model="logForm.work_performed" rows="3" :class="[inputClass, 'resize-none', logForm.errors.work_performed ? errorClass : '']"></textarea>
                    <p v-if="logForm.errors.work_performed" :class="errorMsgClass">{{ logForm.errors.work_performed }}</p>
                </div>
                <div>
                    <label :class="labelClass">Equipment Used</label>
                    <input v-model="logForm.equipment_used" type="text" :class="inputClass" />
                </div>
                <div>
                    <label :class="labelClass">Delays / Issues</label>
                    <textarea v-model="logForm.delays_issues" rows="2" :class="[inputClass, 'resize-none']"></textarea>
                </div>
            </div>
            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showLogModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">Cancel</button>
                    <button @click="submitLog" :disabled="logForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="logForm.processing" class="w-4 h-4 animate-spin" />
                        {{ editingLog ? 'Save Changes' : 'Save Log' }}
                    </button>
                </div>
            </template>
        </Modal>

        <!-- Punch List Modal -->
        <Modal :show="showPunchModal" :title="editingItem ? 'Edit Item' : 'Add Punch List Item'" size="md" @close="showPunchModal = false">
            <div class="p-6 space-y-4">
                <div>
                    <label :class="labelClass">Description <span class="text-rose-500">*</span></label>
                    <textarea v-model="punchForm.description" rows="2" :class="[inputClass, 'resize-none', punchForm.errors.description ? errorClass : '']"></textarea>
                    <p v-if="punchForm.errors.description" :class="errorMsgClass">{{ punchForm.errors.description }}</p>
                </div>
                <div>
                    <label :class="labelClass">Location on Site</label>
                    <input v-model="punchForm.location_on_site" type="text" placeholder="e.g. Master Bedroom, Kitchen" :class="inputClass" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label :class="labelClass">Priority</label>
                        <select v-model="punchForm.priority" :class="inputClass">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div>
                        <label :class="labelClass">Due Date</label>
                        <input v-model="punchForm.due_date" type="date" :class="inputClass" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label :class="labelClass">Assign To</label>
                        <select v-model="punchForm.assigned_to" :class="inputClass">
                            <option value="">— Unassigned —</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div v-if="editingItem">
                        <label :class="labelClass">Status</label>
                        <select v-model="punchForm.status" :class="inputClass">
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
            </div>
            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showPunchModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">Cancel</button>
                    <button @click="submitPunch" :disabled="punchForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="punchForm.processing" class="w-4 h-4 animate-spin" />
                        {{ editingItem ? 'Save Changes' : 'Add Item' }}
                    </button>
                </div>
            </template>
        </Modal>

    </AuthenticatedLayout>
</template>
