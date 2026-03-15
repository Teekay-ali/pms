<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Bell, Check, CheckCheck, Trash2, ExternalLink } from 'lucide-vue-next'

const page  = usePage()
const open  = ref(false)
const items = ref([])

const unreadCount = computed(() => page.props.auth.unread_notifications_count ?? 0)

// Close on outside click
const bell = ref(null)
const handleOutside = (e) => {
    if (bell.value && !bell.value.contains(e.target)) open.value = false
}
onMounted(() => {
    document.addEventListener('mousedown', handleOutside)
    startPolling()
})
onUnmounted(() => {
    document.removeEventListener('mousedown', handleOutside)
    stopPolling()
})

// Polling
let pollTimer = null
const poll = async () => {
    try {
        const res  = await fetch(route('notifications.poll'), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        const data = await res.json()
        items.value = data.notifications
        // Update shared prop without full page reload
        page.props.auth.unread_notifications_count = data.unread_count
    } catch {}
}
const startPolling = () => {
    poll()
    pollTimer = setInterval(poll, 60000)
}
const stopPolling = () => clearInterval(pollTimer)

const toggle = () => {
    open.value = !open.value
    if (open.value) poll()
}

const markRead = (id) => {
    router.post(route('notifications.read', id), {}, {
        preserveScroll: true,
        onSuccess: () => poll(),
    })
}

const markAllRead = () => {
    router.post(route('notifications.read-all'), {}, {
        preserveScroll: true,
        onSuccess: () => { poll(); open.value = false },
    })
}

const typeIcon = (type) => {
    const map = {
        task_assigned:         '📋',
        task_status_changed:   '🔄',
        task_overdue:          '⚠️',
        project_created:       '🏗️',
        project_status_changed:'📊',
        added_to_project:      '👥',
        expense_submitted:     '💰',
        expense_approved:      '✅',
        expense_rejected:      '❌',
        budget_alert:          '⚠️',
        discussion_replied:    '💬',
    }
    return map[type] ?? '🔔'
}
</script>

<template>
    <div ref="bell" class="relative">
        <!-- Bell button -->
        <button
            @click="toggle"
            class="relative p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
        >
            <Bell class="w-5 h-5" />
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95 translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                class="absolute right-0 top-full mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl z-50 overflow-hidden"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        Notifications
                        <span v-if="unreadCount > 0" class="ml-1.5 text-xs bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded-full">
                            {{ unreadCount }} new
                        </span>
                    </span>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllRead"
                        class="flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400 hover:underline"
                    >
                        <CheckCheck class="w-3.5 h-3.5" /> Mark all read
                    </button>
                </div>

                <!-- Items -->
                <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                    <div v-if="items.length === 0" class="py-8 text-center text-sm text-slate-400">
                        No new notifications
                    </div>
                    <div
                        v-for="n in items"
                        :key="n.id"
                        class="flex gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                    >
                        <span class="text-lg shrink-0 mt-0.5">{{ typeIcon(n.data.type) }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-800 dark:text-slate-100 leading-snug">{{ n.data.title }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-snug line-clamp-2">{{ n.data.message }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">{{ n.created_at }}</p>
                        </div>
                        <button
                            @click="markRead(n.id)"
                            class="shrink-0 p-1 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                            title="Mark as read"
                        >
                            <Check class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-2.5 border-t border-slate-100 dark:border-slate-800">
                    <a
                    :href="route('notifications.index')"
                    class="flex items-center justify-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 hover:underline"
                    >
                    View all notifications <ExternalLink class="w-3 h-3" />
                    </a>
                </div>
            </div>
        </Transition>
    </div>
</template>
