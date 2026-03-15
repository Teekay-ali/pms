<script setup>
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Bell, Check, CheckCheck, Trash2, Trash } from 'lucide-vue-next'

const props = defineProps({
    notifications: Object, // paginated
})

const page = usePage()

const typeIcon = (type) => {
    const map = {
        task_assigned:          '📋',
        task_status_changed:    '🔄',
        task_overdue:           '⚠️',
        project_created:        '🏗️',
        project_status_changed: '📊',
        added_to_project:       '👥',
        expense_submitted:      '💰',
        expense_approved:       '✅',
        expense_rejected:       '❌',
        budget_alert:           '⚠️',
        discussion_replied:     '💬',
    }
    return map[type] ?? '🔔'
}

const typeColor = (type) => {
    const map = {
        task_overdue:     'border-l-amber-500',
        expense_rejected: 'border-l-rose-500',
        expense_approved: 'border-l-emerald-500',
        expense_submitted:'border-l-indigo-500',
        budget_alert: 'border-l-amber-500',
    }
    return map[type] ?? 'border-l-slate-300 dark:border-l-slate-700'
}

const markRead = (id) => {
    router.post(route('notifications.read', id), {}, { preserveScroll: true })
}

const markAllRead = () => {
    router.post(route('notifications.read-all'), {}, { preserveScroll: true })
}

const destroy = (id) => {
    router.delete(route('notifications.destroy', id), { preserveScroll: true })
}

const destroyAll = () => {
    if (confirm('Clear all notifications?')) {
        router.delete(route('notifications.destroy-all'))
    }
}

const fmtDate = (d) => new Date(d).toLocaleString('en-US', {
    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
})

const items = computed(() => props.notifications?.data ?? [])
const unread = computed(() => items.value.filter(n => !n.read_at).length)
</script>

<template>
    <Head title="Notifications" />

    <div class="max-w-3xl mx-auto px-4 py-8">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600/10 dark:bg-indigo-600/20 rounded-xl flex items-center justify-center">
                    <Bell class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white">Notifications</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ notifications?.total ?? 0 }} total
                        <span v-if="unread > 0" class="ml-1 text-indigo-600 dark:text-indigo-400">· {{ unread }} unread</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    v-if="unread > 0"
                    @click="markAllRead"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-colors"
                >
                    <CheckCheck class="w-3.5 h-3.5" /> Mark all read
                </button>
                <button
                    v-if="items.length > 0"
                    @click="destroyAll"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-900/20 dark:hover:text-rose-400 rounded-lg transition-colors"
                >
                    <Trash class="w-3.5 h-3.5" /> Clear all
                </button>
            </div>
        </div>

        <!-- Empty -->
        <div v-if="items.length === 0" class="text-center py-16">
            <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <Bell class="w-8 h-8 text-slate-400" />
            </div>
            <p class="text-slate-500 dark:text-slate-400 font-medium">You're all caught up</p>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">No notifications yet</p>
        </div>

        <!-- List -->
        <div v-else class="space-y-2">
            <div
                v-for="n in items"
                :key="n.id"
                class="flex gap-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl border-l-4 transition-all"
                :class="[typeColor(n.data.type), n.read_at ? 'opacity-60' : '']"
            >
                <span class="text-2xl shrink-0 mt-0.5">{{ typeIcon(n.data.type) }}</span>

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                            {{ n.data.title }}
                            <span
                                v-if="!n.read_at"
                                class="ml-2 inline-block w-1.5 h-1.5 bg-indigo-500 rounded-full align-middle"
                            ></span>
                        </p>
                        <span class="text-[11px] text-slate-400 shrink-0 whitespace-nowrap">{{ fmtDate(n.created_at) }}</span>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ n.data.message }}</p>

                    <!-- Meta tags -->
                    <div v-if="n.data.meta?.project_name" class="flex flex-wrap gap-1.5 mt-2">
                        <span class="text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-full">
                            {{ n.data.meta.project_name }}
                        </span>
                        <span v-if="n.data.meta?.priority" class="text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-full capitalize">
                            {{ n.data.meta.priority }} priority
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-start gap-1 shrink-0">
                    <button
                        v-if="!n.read_at"
                        @click="markRead(n.id)"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                        title="Mark as read"
                    >
                        <Check class="w-4 h-4" />
                    </button>
                    <button
                        @click="destroy(n.id)"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                        title="Delete"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="notifications?.last_page > 1" class="flex items-center justify-center gap-2 mt-6">
            <button
                v-for="page in notifications.last_page"
                :key="page"
                @click="router.get(route('notifications.index', { page }))"
                class="w-8 h-8 text-sm rounded-lg transition-colors"
                :class="page === notifications.current_page
                    ? 'bg-indigo-600 text-white'
                    : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800'"
            >
                {{ page }}
            </button>
        </div>

    </div>
</template>
