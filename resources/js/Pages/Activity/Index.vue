<script setup>
import { computed, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Activity, FolderKanban, CheckSquare, Receipt, Package, Truck, User, Filter } from 'lucide-vue-next'

const props = defineProps({
    activities: Object,
})

const search = ref('')

// ── Helpers ────────────────────────────────────────────
const fmtDate = (d) => new Date(d).toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: '2-digit', minute: '2-digit',
})

const modelIcon = (type) => {
    const map = {
        'App\\Models\\Project':  FolderKanban,
        'App\\Models\\Task':     CheckSquare,
        'App\\Models\\Expense':  Receipt,
        'App\\Models\\Resource': Package,
        'App\\Models\\Vendor':   Truck,
    }
    return map[type] ?? Activity
}

const modelLabel = (type) => {
    const map = {
        'App\\Models\\Project':  'Project',
        'App\\Models\\Task':     'Task',
        'App\\Models\\Expense':  'Expense',
        'App\\Models\\Resource': 'Resource',
        'App\\Models\\Vendor':   'Vendor',
    }
    return map[type] ?? 'Record'
}

const eventColor = (event) => {
    const map = {
        created: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
        updated: 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800',
        deleted: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800',
    }
    return map[event] ?? 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700'
}

const modelColor = (type) => {
    const map = {
        'App\\Models\\Project':  'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'App\\Models\\Task':     'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
        'App\\Models\\Expense':  'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'App\\Models\\Resource': 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'App\\Models\\Vendor':   'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
    }
    return map[type] ?? 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
}

// Format changed attributes nicely
const formatChanges = (properties) => {
    if (!properties?.attributes) return null
    const attrs = properties.attributes
    const old   = properties.old ?? {}
    return Object.entries(attrs)
        .filter(([key]) => key !== 'updated_at')
        .map(([key, val]) => ({
            key:    key.replace(/_/g, ' '),
            oldVal: old[key] ?? null,
            newVal: val,
        }))
}

const filteredActivities = computed(() => {
    if (!search.value) return props.activities?.data ?? []
    const q = search.value.toLowerCase()
    return (props.activities?.data ?? []).filter(a =>
        a.description?.toLowerCase().includes(q) ||
        a.causer?.name?.toLowerCase().includes(q) ||
        modelLabel(a.subject_type).toLowerCase().includes(q)
    )
})

const goToPage = (url) => { if (url) router.get(url) }
</script>

<template>
    <Head title="Activity Log" />
        <AuthenticatedLayout>
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Activity Log</h1>
                        <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                            {{ activities?.total ?? 0 }} total events recorded
                        </p>
                    </div>

                    <!-- Search -->
                    <div class="relative w-full sm:w-64">
                        <Filter class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Filter by user, model..."
                            class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                        />
                    </div>
                </div>

                <!-- Empty -->
                <div v-if="filteredActivities.length === 0" class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                    <Activity class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                    <p class="text-slate-500 dark:text-slate-400 font-medium">No activity recorded yet</p>
                    <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Actions on projects, tasks, expenses and more will appear here</p>
                </div>

                <!-- Activity list -->
                <div v-else class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div
                            v-for="activity in filteredActivities"
                            :key="activity.id"
                            class="flex gap-4 px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                        >
                            <!-- Model icon -->
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                                 :class="modelColor(activity.subject_type)">
                                <component :is="modelIcon(activity.subject_type)" class="w-4 h-4" />
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <!-- Who -->
                                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                        {{ activity.causer?.name ?? 'System' }}
                                    </span>

                                    <!-- Event badge -->
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border capitalize"
                                          :class="eventColor(activity.event)">
                                        {{ activity.event }}
                                    </span>

                                    <!-- Model type -->
                                    <span class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ modelLabel(activity.subject_type) }}
                                    </span>

                                    <!-- Subject name -->
                                    <span v-if="activity.properties?.attributes?.name"
                                          class="text-sm font-medium text-slate-700 dark:text-slate-200 truncate max-w-xs">
                                        "{{ activity.properties.attributes.name }}"
                                    </span>
                                </div>

                                <!-- Changed fields -->
                                <div v-if="activity.event === 'updated' && formatChanges(activity.properties)?.length"
                                     class="flex flex-wrap gap-2 mt-1.5">
                                    <div
                                        v-for="change in formatChanges(activity.properties)"
                                        :key="change.key"
                                        class="flex items-center gap-1 text-xs bg-slate-100 dark:bg-slate-800 rounded-lg px-2 py-1"
                                    >
                                        <span class="text-slate-500 dark:text-slate-400 capitalize">{{ change.key }}:</span>
                                        <span v-if="change.oldVal !== null" class="text-rose-500 line-through">{{ change.oldVal }}</span>
                                        <span v-if="change.oldVal !== null" class="text-slate-400">→</span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ change.newVal }}</span>
                                    </div>
                                </div>

                                <!-- Timestamp -->
                                <p class="text-xs text-slate-400 mt-1.5">{{ fmtDate(activity.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="activities?.last_page > 1"
                         class="flex items-center justify-between px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Showing {{ activities.from }}–{{ activities.to }} of {{ activities.total }}
                        </p>
                        <div class="flex items-center gap-1">
                            <button
                                @click="goToPage(activities.prev_page_url)"
                                :disabled="!activities.prev_page_url"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                            >←</button>
                            <button
                                @click="goToPage(activities.next_page_url)"
                                :disabled="!activities.next_page_url"
                                class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                            >→</button>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
</template>
