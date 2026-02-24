<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
    FolderKanban,
    CheckSquare,
    Receipt,
    Package,
    TrendingUp,
    TrendingDown,
    Clock,
    AlertTriangle,
    ArrowRight,
    Users,
    CalendarDays,
    DollarSign,
} from 'lucide-vue-next'

const page = usePage()
const user = computed(() => page.props.auth.user)
const roles = computed(() => page.props.auth.roles ?? [])

const greeting = computed(() => {
    const h = new Date().getHours()
    if (h < 12) return 'Good morning'
    if (h < 17) return 'Good afternoon'
    return 'Good evening'
})

const primaryRole = computed(() => {
    if (!roles.value.length) return 'Team Member'
    return roles.value[0].replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
})

const stats = [
    {
        label: 'Active Projects',
        value: '12',
        change: '+2 this month',
        trend: 'up',
        icon: FolderKanban,
        iconBg: 'bg-indigo-600',
        border: 'border-indigo-100 dark:border-indigo-900/50',
    },
    {
        label: 'Open Tasks',
        value: '48',
        change: '6 due today',
        trend: 'neutral',
        icon: CheckSquare,
        iconBg: 'bg-amber-500',
        border: 'border-amber-100 dark:border-amber-900/50',
    },
    {
        label: 'Total Budget',
        value: '$4.2M',
        change: '78% allocated',
        trend: 'up',
        icon: DollarSign,
        iconBg: 'bg-emerald-600',
        border: 'border-emerald-100 dark:border-emerald-900/50',
    },
    {
        label: 'Pending Expenses',
        value: '17',
        change: 'Awaiting approval',
        trend: 'down',
        icon: Receipt,
        iconBg: 'bg-rose-500',
        border: 'border-rose-100 dark:border-rose-900/50',
    },
]

const recentProjects = [
    { name: 'Highway Bridge Expansion',    status: 'active',   progress: 75, manager: 'James Carter', due: 'Mar 15, 2026', budget: '$1.2M' },
    { name: 'Commercial Tower Block A',    status: 'active',   progress: 42, manager: 'Sarah Mills',  due: 'Jun 30, 2026', budget: '$3.1M' },
    { name: 'Residential Complex Phase 2', status: 'on_hold',  progress: 28, manager: 'David Osei',   due: 'Sep 1, 2026',  budget: '$890K' },
    { name: 'Airport Terminal Renovation', status: 'planning', progress: 5,  manager: 'Aisha Rahman', due: 'Dec 20, 2026', budget: '$5.4M' },
]

const recentTasks = [
    { name: 'Submit structural drawings',  project: 'Highway Bridge',    priority: 'critical', due: 'Today',    assignee: 'JC' },
    { name: 'Review supplier invoices',    project: 'Tower Block A',     priority: 'high',     due: 'Tomorrow', assignee: 'SM' },
    { name: 'Concrete pour inspection',    project: 'Residential Ph. 2', priority: 'high',     due: 'Feb 25',   assignee: 'DO' },
    { name: 'Update resource allocation',  project: 'Airport Terminal',  priority: 'medium',   due: 'Feb 28',   assignee: 'AR' },
    { name: 'Finalize procurement list',   project: 'Highway Bridge',    priority: 'medium',   due: 'Mar 1',    assignee: 'JC' },
]

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

const progressColor = (p) => {
    if (p >= 75) return 'bg-emerald-500'
    if (p >= 40) return 'bg-indigo-500'
    if (p >= 15) return 'bg-amber-500'
    return 'bg-slate-300'
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
                    <p class="text-slate-500 dark:text-slate-400 mt-0.5 text-sm">
                        Here's what's happening across your projects today.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-sm">
                        <CalendarDays class="w-4 h-4 text-slate-400" />
                        <span class="text-sm text-slate-600 dark:text-slate-300 font-medium">
                            {{ new Date().toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stats grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="bg-white dark:bg-slate-900 rounded-2xl border p-5 shadow-sm hover:shadow-md transition-shadow duration-200"
                    :class="stat.border"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm" :class="stat.iconBg">
                            <component :is="stat.icon" class="w-5 h-5 text-white" />
                        </div>
                        <div
                            class="flex items-center gap-1 text-xs font-medium"
                            :class="stat.trend === 'up' ? 'text-emerald-600 dark:text-emerald-400' : stat.trend === 'down' ? 'text-rose-500 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400'"
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
                            <FolderKanban class="w-5 h-5 text-indigo-600" />
                            <h2 class="font-semibold text-slate-900 dark:text-white">Recent Projects</h2>
                        </div>
                        <a href="#" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium flex items-center gap-1 transition-colors">
                            View all <ArrowRight class="w-3.5 h-3.5" />
                        </a>
                    </div>

                    <div class="divide-y divide-slate-50 dark:divide-slate-800">
                        <div
                            v-for="project in recentProjects"
                            :key="project.name"
                            class="px-6 py-4 hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors cursor-pointer group"
                        >
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 text-sm group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                                        {{ project.name }}
                                    </p>
                                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                                        <span class="text-xs text-slate-400 flex items-center gap-1">
                                            <Users class="w-3 h-3" /> {{ project.manager }}
                                        </span>
                                        <span class="text-xs text-slate-400 flex items-center gap-1">
                                            <CalendarDays class="w-3 h-3" /> {{ project.due }}
                                        </span>
                                        <span class="text-xs text-slate-400 flex items-center gap-1">
                                            <DollarSign class="w-3 h-3" /> {{ project.budget }}
                                        </span>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border flex-shrink-0"
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
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 w-8 text-right">{{ project.progress }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Tasks -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <CheckSquare class="w-5 h-5 text-indigo-600" />
                            <h2 class="font-semibold text-slate-900 dark:text-white">Upcoming Tasks</h2>
                        </div>
                        <a href="#" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium flex items-center gap-1 transition-colors">
                            View all <ArrowRight class="w-3.5 h-3.5" />
                        </a>
                    </div>

                    <div class="divide-y divide-slate-50 dark:divide-slate-800">
                        <div
                            v-for="task in recentTasks"
                            :key="task.name"
                            class="px-5 py-3.5 hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors cursor-pointer group"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-[10px] flex-shrink-0 mt-0.5">
                                    {{ task.assignee }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                                        {{ task.name }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5 truncate">{{ task.project }}</p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-2 pl-10">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium border" :class="priorityConfig[task.priority]">
                                    {{ task.priority }}
                                </span>
                                <span class="text-[11px] text-slate-400 flex items-center gap-1">
                                    <Clock class="w-3 h-3" /> {{ task.due }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <!-- Budget overview -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <DollarSign class="w-5 h-5 text-emerald-600" />
                        <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Budget Overview</h3>
                    </div>
                    <div class="space-y-3">
                        <div v-for="b in [
                            { label: 'Allocated', value: '$3.28M', pct: '78%', color: 'bg-emerald-500' },
                            { label: 'Spent',     value: '$1.94M', pct: '46%', color: 'bg-indigo-500' },
                            { label: 'Remaining', value: '$0.92M', pct: '22%', color: 'bg-amber-400' },
                        ]" :key="b.label">
                            <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                                <span>{{ b.label }}</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ b.value }}</span>
                            </div>
                            <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" :class="b.color" :style="{ width: b.pct }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resource alerts -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <Package class="w-5 h-5 text-indigo-600" />
                        <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Resource Alerts</h3>
                    </div>
                    <div class="space-y-3">
                        <div v-for="r in [
                            { name: 'Steel Rods (20mm)',  level: 12, status: 'critical' },
                            { name: 'Cement Bags (50kg)', level: 34, status: 'low' },
                            { name: 'Safety Helmets',     level: 68, status: 'ok' },
                        ]" :key="r.name" class="flex items-center gap-3">
                            <AlertTriangle
                                class="w-4 h-4 flex-shrink-0"
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
                        <a
                            v-for="action in [
                                { label: 'New Project',  icon: FolderKanban, color: 'text-indigo-600 dark:text-indigo-400', bg: 'bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/40' },
                                { label: 'Add Task',     icon: CheckSquare,  color: 'text-emerald-600 dark:text-emerald-400', bg: 'bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:hover:bg-emerald-900/40' },
                                { label: 'Log Expense',  icon: Receipt,      color: 'text-amber-600 dark:text-amber-400', bg: 'bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:hover:bg-amber-900/40' },
                                { label: 'Add Resource', icon: Package,      color: 'text-rose-600 dark:text-rose-400', bg: 'bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40' },
                            ]"
                            :key="action.label"
                            href="#"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors cursor-pointer"
                            :class="action.bg"
                        >
                            <component :is="action.icon" class="w-4 h-4" :class="action.color" />
                            <span class="text-sm font-medium" :class="action.color">{{ action.label }}</span>
                            <ArrowRight class="w-3.5 h-3.5 ml-auto" :class="action.color" />
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
