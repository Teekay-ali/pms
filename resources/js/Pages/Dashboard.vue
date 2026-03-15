<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
    FolderKanban,
    CheckSquare,
    Receipt,
    Package,
    TrendingUp,
    TrendingDown,
    Clock,
    ArrowRight,
    CalendarDays,
    DollarSign,
    AlertTriangle,
    Users,
} from 'lucide-vue-next'

const page = usePage()
const user = computed(() => page.props.auth.user)

const props = defineProps({
    stats:          Object,
    recentProjects: Array,
    upcomingTasks:  Array,
})

// ── Greeting ───────────────────────────────────────────
const greeting = computed(() => {
    const h = new Date().getHours()
    if (h < 12) return 'Good morning'
    if (h < 17) return 'Good afternoon'
    return 'Good evening'
})

// ── Stats cards ────────────────────────────────────────
const statCards = computed(() => [
    {
        label:  'Active Projects',
        value:  props.stats.activeProjects,
        change: 'currently running',
        trend:  'up',
        icon:   FolderKanban,
    },
    {
        label:  'Open Tasks',
        value:  props.stats.openTasks,
        change: props.stats.tasksDueToday > 0
            ? `${props.stats.tasksDueToday} due today`
            : 'none due today',
        trend:  props.stats.tasksDueToday > 0 ? 'down' : 'neutral',
        icon:   CheckSquare,
    },
    {
        label:  'Total Budget',
        value:  fmtMoney(props.stats.totalBudget),
        change: 'across all projects',
        trend:  'up',
        icon:   DollarSign,
    },
    {
        label:  'Pending Expenses',
        value:  props.stats.pendingExpenses,
        change: 'awaiting approval',
        trend:  props.stats.pendingExpenses > 0 ? 'down' : 'neutral',
        icon:   Receipt,
    },
])

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

// ── Helpers ────────────────────────────────────────────
const progressColor = (p) => {
    if (p >= 75) return 'bg-emerald-500'
    if (p >= 40) return 'bg-indigo-500'
    if (p >= 15) return 'bg-amber-500'
    return 'bg-slate-200 dark:bg-slate-700'
}

function fmtMoney(v) {
    if (!v) return '$0'
    if (v >= 1_000_000) return `$${(v / 1_000_000).toFixed(1)}M`
    if (v >= 1_000)     return `$${(v / 1_000).toFixed(0)}K`
    return `$${v}`
}

const fmtDate = (dateStr) => {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const fmtDueDate = (dateStr) => {
    if (!dateStr) return '—'
    const date      = new Date(dateStr + 'T00:00:00')
    const today     = new Date(); today.setHours(0,0,0,0)
    const tomorrow  = new Date(today); tomorrow.setDate(today.getDate() + 1)
    if (date.getTime() === today.getTime())    return 'Today'
    if (date.getTime() === tomorrow.getTime()) return 'Tomorrow'
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const isOverdue = (dateStr) => {
    if (!dateStr) return false
    const today = new Date(); today.setHours(0,0,0,0)
    return new Date(dateStr + 'T00:00:00') < today
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Page header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        {{ greeting }}, {{ user?.name?.split(' ')[0] }} 👋
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                        Here's what's happening across your projects today.
                    </p>
                </div>
                <div class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-sm">
                    <CalendarDays class="w-4 h-4 text-slate-400" />
                    <span class="text-sm text-slate-600 dark:text-slate-300 font-medium">
                        {{ new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) }}
                    </span>
                </div>
            </div>

            <!-- Stats grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div
                    v-for="stat in statCards"
                    :key="stat.label"
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow duration-200"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <component :is="stat.icon" class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                        </div>
                        <div
                            class="flex items-center gap-1 text-xs font-medium"
                            :class="stat.trend === 'up' ? 'text-emerald-600 dark:text-emerald-400' : stat.trend === 'down' ? 'text-rose-500 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400'"
                        >
                            <TrendingUp v-if="stat.trend === 'up'" class="w-3.5 h-3.5" />
                            <TrendingDown v-else-if="stat.trend === 'down'" class="w-3.5 h-3.5" />
                            <Clock v-else class="w-3.5 h-3.5" />
                            {{ stat.change }}
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-slate-900 dark:text-white mb-1">{{ stat.value }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ stat.label }}</p>
                </div>
            </div>

            <!-- Main grid -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                <!-- Recent Projects -->
                <div class="xl:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <FolderKanban class="w-4 h-4 text-slate-400" />
                            <h2 class="font-semibold text-slate-900 dark:text-white text-sm">Recent Projects</h2>
                        </div>
                        <Link
                            :href="route('projects.index')"
                            class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium flex items-center gap-1 transition-colors"
                        >
                            View all <ArrowRight class="w-3 h-3" />
                        </Link>
                    </div>

                    <!-- Empty state -->
                    <div v-if="!recentProjects.length" class="px-6 py-10 text-center text-sm text-slate-400">
                        No projects yet.
                    </div>

                    <div v-else class="divide-y divide-slate-50 dark:divide-slate-800">
                        <Link
                            v-for="project in recentProjects"
                            :key="project.id"
                            :href="route('projects.show', project.id)"
                            class="block px-6 py-4 hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors group"
                        >
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                                        {{ project.name }}
                                    </p>
                                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                                        <span v-if="project.manager" class="text-xs text-slate-400 flex items-center gap-1">
                                            <Users class="w-3 h-3" /> {{ project.manager }}
                                        </span>
                                        <span v-if="project.due" class="text-xs text-slate-400 flex items-center gap-1">
                                            <CalendarDays class="w-3 h-3" /> {{ fmtDate(project.due) }}
                                        </span>
                                        <span v-if="project.budget" class="text-xs text-slate-400 flex items-center gap-1">
                                            <DollarSign class="w-3 h-3" /> {{ fmtMoney(project.budget) }}
                                        </span>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border shrink-0"
                                    :class="statusConfig[project.status]?.badge"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="statusConfig[project.status]?.dot"></span>
                                    {{ statusConfig[project.status]?.label }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :class="progressColor(project.progress)"
                                        :style="{ width: project.progress + '%' }"
                                    ></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-400 w-8 text-right">{{ project.progress }}%</span>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Upcoming Tasks -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <CheckSquare class="w-4 h-4 text-slate-400" />
                            <h2 class="font-semibold text-slate-900 dark:text-white text-sm">Upcoming Tasks</h2>
                        </div>
                        <Link
                            :href="route('tasks.index')"
                            class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium flex items-center gap-1 transition-colors"
                        >
                            View all <ArrowRight class="w-3 h-3" />
                        </Link>
                    </div>

                    <!-- Empty state -->
                    <div v-if="!upcomingTasks.length" class="px-5 py-10 text-center text-sm text-slate-400">
                        No upcoming tasks.
                    </div>

                    <div v-else class="divide-y divide-slate-50 dark:divide-slate-800">
                        <div
                            v-for="task in upcomingTasks"
                            :key="task.id"
                            class="px-5 py-3.5 hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors group"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 font-bold text-[10px] shrink-0 mt-0.5">
                                    {{ task.assignee ?? '?' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                                        {{ task.name }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5 truncate">{{ task.project }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-2 pl-10">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium border capitalize" :class="priorityConfig[task.priority]">
                                    {{ task.priority }}
                                </span>
                                <span class="text-[11px] flex items-center gap-1" :class="isOverdue(task.due_date) ? 'text-rose-500 dark:text-rose-400' : 'text-slate-400'">
                                    <Clock class="w-3 h-3" />
                                    {{ fmtDueDate(task.due_date) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <!-- Budget overview — placeholder until real data wired -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <DollarSign class="w-4 h-4 text-slate-400" />
                        <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Budget Overview</h3>
                    </div>
                    <div class="space-y-3">
                        <div v-for="b in [
                            { label: 'Allocated', value: '$3.28M', pct: '78%', color: 'bg-indigo-500' },
                            { label: 'Spent',     value: '$1.94M', pct: '46%', color: 'bg-slate-400 dark:bg-slate-500' },
                            { label: 'Remaining', value: '$0.92M', pct: '22%', color: 'bg-slate-200 dark:bg-slate-700' },
                        ]" :key="b.label">
                            <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                                <span>{{ b.label }}</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ b.value }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" :class="b.color" :style="{ width: b.pct }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resource alerts — placeholder until real data wired -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <Package class="w-4 h-4 text-slate-400" />
                        <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Resource Alerts</h3>
                    </div>
                    <div class="space-y-3">
                        <div v-for="r in [
                            { name: 'Steel Rods (20mm)',  level: 12, status: 'critical' },
                            { name: 'Cement Bags (50kg)', level: 34, status: 'low' },
                            { name: 'Safety Helmets',     level: 68, status: 'ok' },
                        ]" :key="r.name" class="flex items-center gap-3">
                            <AlertTriangle
                                class="w-4 h-4 shrink-0"
                                :class="r.status === 'critical' ? 'text-rose-500' : r.status === 'low' ? 'text-amber-500' : 'text-emerald-500'"
                            />
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-slate-700 dark:text-slate-200 truncate">{{ r.name }}</p>
                                <p class="text-[11px] text-slate-400">{{ r.level }}% remaining</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick actions -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
                    <h3 class="font-semibold text-slate-900 dark:text-white text-sm mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <Link
                            v-for="action in [
                                { label: 'New Project',  icon: FolderKanban, route: 'projects.index' },
                                { label: 'Add Task',     icon: CheckSquare,  route: 'tasks.index'    },
                                { label: 'Log Expense',  icon: Receipt,      route: 'expenses.index' },
                                { label: 'Add Resource', icon: Package,      route: 'resources.index'},
                            ]"
                            :key="action.label"
                            :href="route(action.route)"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors group"
                        >
                            <component :is="action.icon" class="w-4 h-4 text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" />
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ action.label }}</span>
                            <ArrowRight class="w-3.5 h-3.5 ml-auto text-slate-300 dark:text-slate-600 group-hover:text-indigo-400 transition-colors" />
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
