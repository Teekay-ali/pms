<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { toast, Toaster } from 'vue-sonner'
import { useDarkMode } from '@/Composables/useDarkMode'
import {
    HardHat,
    LayoutDashboard,
    FolderKanban,
    CheckSquare,
    Package,
    Receipt,
    Truck,
    Users,
    Settings,
    ChevronLeft,
    Bell,
    Search,
    LogOut,
    ChevronDown,
    Menu,
    X,
    Shield,
    UserCircle,
    Sun,
    Moon,
} from 'lucide-vue-next'

const page = usePage()
const user = computed(() => page.props.auth.user)
const permissions = computed(() => page.props.auth.permissions ?? [])
const roles = computed(() => page.props.auth.roles ?? [])

const can = (permission) => permissions.value.includes(permission)
const hasRole = (role) => roles.value.includes(role)

const { isDark, toggle: toggleDark } = useDarkMode()

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) toast.success(flash.success)
        if (flash?.error)   toast.error(flash.error)
        if (flash?.warning) toast.warning(flash.warning)
        if (flash?.info)    toast.info(flash.info)
    },
    { deep: true }
)

const sidebarCollapsed = ref(false)
const mobileOpen = ref(false)
const userMenuOpen = ref(false)

const navGroups = computed(() => [
    {
        label: 'Overview',
        items: [
            { label: 'Dashboard', route: 'dashboard', icon: LayoutDashboard, show: true },
        ],
    },
    {
        label: 'Project Management',
        items: [
            { label: 'Projects', route: 'projects.index', icon: FolderKanban, show: can('projects.view') },
            { label: 'Tasks',    route: 'tasks.index',    icon: CheckSquare,  show: can('tasks.view') },
        ],
    },
    {
        label: 'Operations',
        items: [
            { label: 'Resources', route: 'resources.index', icon: Package,  show: can('resources.view') },
            { label: 'Expenses',  route: 'expenses.index',  icon: Receipt,  show: can('expenses.view') },
            { label: 'Vendors',   route: 'vendors.index',   icon: Truck,    show: can('vendors.view') },
        ],
    },
    {
        label: 'Administration',
        items: [
            { label: 'Users',    route: 'dashboard', icon: Users,    show: hasRole('admin') || hasRole('hr') },
            { label: 'Settings', route: 'dashboard', icon: Settings, show: hasRole('admin') },
        ],
    },
])

const visibleGroups = computed(() =>
    navGroups.value
        .map(g => ({ ...g, items: g.items.filter(i => i.show) }))
        .filter(g => g.items.length)
)

const isActive = (routeName) => route().current(routeName)

const userInitials = computed(() => {
    if (!user.value?.name) return '?'
    return user.value.name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
})

const primaryRole = computed(() => {
    if (!roles.value.length) return 'No Role'
    return roles.value[0].replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
})

const logout = () => {
    userMenuOpen.value = false
    router.post(route('logout'))
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex transition-colors duration-200">
        <Toaster position="top-right" richColors closeButton />

        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileOpen"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm z-20 lg:hidden"
                @click="mobileOpen = false"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed top-0 left-0 h-full z-30 flex flex-col transition-all duration-300 ease-in-out"
            :class="[
                sidebarCollapsed ? 'w-[72px]' : 'w-64',
                mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
            style="background: linear-gradient(180deg, #0f172a 0%, #0d1424 60%, #0f1729 100%);"
        >
            <!-- Top accent line -->
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"></div>

            <!-- Logo -->
            <div class="flex items-center h-16 px-4 flex-shrink-0 border-b border-white/5">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="relative w-9 h-9 flex-shrink-0">
                        <div class="absolute inset-0 bg-indigo-600 rounded-xl shadow-lg shadow-indigo-500/40"></div>
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-400/20 to-transparent rounded-xl"></div>
                        <div class="relative w-full h-full flex items-center justify-center">
                            <HardHat class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <Transition
                        enter-active-class="transition-all duration-200 delay-75"
                        enter-from-class="opacity-0 -translate-x-2"
                        enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition-all duration-100"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div v-if="!sidebarCollapsed">
                            <p class="text-white font-bold text-base tracking-tight whitespace-nowrap leading-none">SitePro</p>
                            <p class="text-indigo-400/70 text-[10px] whitespace-nowrap tracking-widest uppercase mt-0.5">Construction OS</p>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-5 scrollbar-none">
                <template v-for="group in visibleGroups" :key="group.label">
                    <div>
                        <Transition
                            enter-active-class="transition-all duration-200"
                            enter-from-class="opacity-0"
                            enter-to-class="opacity-100"
                            leave-active-class="transition-all duration-100"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="!sidebarCollapsed"
                               class="text-[10px] font-semibold tracking-widest uppercase text-slate-500 px-3 mb-1.5">
                                {{ group.label }}
                            </p>
                            <div v-else class="border-t border-white/5 mb-2"></div>
                        </Transition>

                        <div class="space-y-0.5">
                            <Link
                                v-for="item in group.items"
                                :key="item.label"
                                :href="route(item.route)"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 group relative"
                                :class="isActive(item.route)
                                    ? 'bg-indigo-600/20 text-white'
                                    : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'"
                            >
                                <div
                                    v-if="isActive(item.route)"
                                    class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 bg-indigo-400 rounded-full"
                                ></div>
                                <div
                                    class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-all duration-150"
                                    :class="isActive(item.route)
                                        ? 'bg-indigo-600 shadow-lg shadow-indigo-500/30'
                                        : 'bg-white/5 group-hover:bg-white/10'"
                                >
                                    <component
                                        :is="item.icon"
                                        class="w-4 h-4"
                                        :class="isActive(item.route) ? 'text-white' : 'text-slate-400 group-hover:text-slate-200'"
                                    />
                                </div>
                                <Transition
                                    enter-active-class="transition-all duration-200"
                                    enter-from-class="opacity-0"
                                    enter-to-class="opacity-100"
                                    leave-active-class="transition-all duration-100"
                                    leave-from-class="opacity-100"
                                    leave-to-class="opacity-0"
                                >
                                    <span v-if="!sidebarCollapsed" class="text-sm font-medium whitespace-nowrap">
                                        {{ item.label }}
                                    </span>
                                </Transition>

                                <!-- Tooltip -->
                                <div
                                    v-if="sidebarCollapsed"
                                    class="absolute left-full ml-3 px-2.5 py-1.5 bg-slate-800 border border-slate-700 text-white text-xs rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50 shadow-xl"
                                >
                                    {{ item.label }}
                                    <div class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1 w-2 h-2 bg-slate-800 border-l border-b border-slate-700 rotate-45"></div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </template>
            </nav>

            <!-- User section -->
            <div class="border-t border-white/5 p-3 flex-shrink-0">
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0 translate-y-1"
                >
                    <div v-if="userMenuOpen && !sidebarCollapsed"
                         class="mb-2 p-2 bg-white/5 border border-white/10 rounded-xl space-y-0.5">
                        <Link
                            :href="route('dashboard')"
                            class="flex items-center gap-3 px-3 py-2 text-sm text-slate-300 hover:bg-white/5 hover:text-white rounded-lg transition-colors"
                        >
                            <UserCircle class="w-4 h-4" /> Profile
                        </Link>
                        <button
                            @click="logout"
                            class="w-full flex items-center gap-3 px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-lg transition-colors"
                        >
                            <LogOut class="w-4 h-4" /> Sign out
                        </button>
                    </div>
                </Transition>

                <div
                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 cursor-pointer transition-colors overflow-hidden"
                    :class="userMenuOpen ? 'bg-white/5' : ''"
                    @click="userMenuOpen = !userMenuOpen"
                >
                    <div class="relative flex-shrink-0">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-lg flex items-center justify-center text-white text-xs font-bold shadow-lg">
                            {{ userInitials }}
                        </div>
                        <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-emerald-400 rounded-full border-2 border-slate-900"></div>
                    </div>
                    <Transition
                        enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-all duration-100"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div v-if="!sidebarCollapsed" class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white truncate leading-none mb-1">{{ user?.name }}</p>
                            <p class="text-[11px] text-slate-500 truncate flex items-center gap-1">
                                <Shield class="w-3 h-3 text-indigo-400 flex-shrink-0" />
                                {{ primaryRole }}
                            </p>
                        </div>
                    </Transition>
                    <ChevronDown
                        v-if="!sidebarCollapsed"
                        class="w-4 h-4 text-slate-600 flex-shrink-0 transition-transform duration-200"
                        :class="userMenuOpen ? 'rotate-180 text-slate-400' : ''"
                    />
                </div>
            </div>

            <!-- Collapse toggle -->
            <button
                @click="sidebarCollapsed = !sidebarCollapsed"
                class="hidden lg:flex absolute -right-3 top-[72px] w-6 h-6 bg-slate-800 hover:bg-indigo-600 border border-slate-700 hover:border-indigo-500 rounded-full items-center justify-center transition-all duration-200 shadow-lg group"
            >
                <ChevronLeft
                    class="w-3 h-3 text-slate-400 group-hover:text-white transition-all duration-300"
                    :class="sidebarCollapsed ? 'rotate-180' : ''"
                />
            </button>
        </aside>

        <!-- Main content -->
        <div
            class="flex-1 flex flex-col min-h-screen transition-all duration-300"
            :class="sidebarCollapsed ? 'lg:ml-[72px]' : 'lg:ml-64'"
        >
            <!-- Top bar -->
            <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center px-4 lg:px-6 gap-4 sticky top-0 z-10 flex-shrink-0 shadow-sm transition-colors duration-200">

                <!-- Mobile menu toggle -->
                <button
                    @click="mobileOpen = !mobileOpen"
                    class="lg:hidden p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 dark:hover:text-slate-300 rounded-lg transition-colors"
                >
                    <Menu v-if="!mobileOpen" class="w-5 h-5" />
                    <X v-else class="w-5 h-5" />
                </button>

                <!-- Search -->
                <div class="flex-1 max-w-sm">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input
                            type="text"
                            placeholder="Search projects, tasks..."
                            class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 transition-all"
                        />
                    </div>
                </div>

                <!-- Right actions -->
                <div class="flex items-center gap-2 ml-auto">

                    <!-- Dark mode toggle -->
                    <button
                        @click="toggleDark"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                    >
                        <Sun v-if="isDark" class="w-5 h-5" />
                        <Moon v-else class="w-5 h-5" />
                    </button>

                    <!-- Notifications -->
                    <button class="relative p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                        <Bell class="w-5 h-5" />
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-indigo-500 rounded-full ring-2 ring-white dark:ring-slate-900"></span>
                    </button>

                    <!-- Divider -->
                    <div class="w-px h-6 bg-slate-200 dark:bg-slate-700 mx-1"></div>

                    <!-- User -->
                    <div class="flex items-center gap-2.5">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 leading-none mb-0.5">{{ user?.name }}</p>
                            <p class="text-xs text-slate-400">{{ primaryRole }}</p>
                        </div>
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-lg flex items-center justify-center text-white text-xs font-bold shadow-sm cursor-pointer">
                            {{ userInitials }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 lg:p-6 overflow-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
