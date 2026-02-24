<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    Users,
    Plus,
    Search,
    Pencil,
    Trash2,
    Loader2,
    ChevronLeft,
    ChevronRight,
    Shield,
    KeyRound,
    Mail,
    Eye,
    EyeOff,
    UserCog,
} from 'lucide-vue-next'

const props = defineProps({
    users: Object,
    roles: Array,
})

const page      = usePage()
const perms     = computed(() => page.props.auth.permissions ?? [])
const authUser  = computed(() => page.props.auth.user)
const can       = (p) => perms.value.includes(p)

// ── Filters ────────────────────────────────────────────
const search     = ref('')
const roleFilter = ref('all')

// ── Modal state ────────────────────────────────────────
const showCreateModal = ref(false)
const showEditModal   = ref(false)
const showRoleModal   = ref(false)
const editingUser     = ref(null)
const showPassword    = ref(false)
const showConfirm     = ref(false)

// ── Role config ────────────────────────────────────────
const roleConfig = {
    admin:           { label: 'Admin',           badge: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800' },
    ceo:             { label: 'CEO',             badge: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800' },
    project_manager: { label: 'Project Manager', badge: 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800' },
    finance:         { label: 'Finance',         badge: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800' },
    accountant:      { label: 'Accountant',      badge: 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:border-teal-800' },
    architect:       { label: 'Architect',       badge: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800' },
    hr:              { label: 'HR',              badge: 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800' },
    store_manager:   { label: 'Store Manager',   badge: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800' },
    contractor:      { label: 'Contractor',      badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
    vendor:          { label: 'Vendor',          badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
    site_worker:     { label: 'Site Worker',     badge: 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
}

const getRoleName  = (user) => user.roles?.[0]?.name ?? null
const getRoleLabel = (name) => roleConfig[name]?.label ?? name
const getRoleBadge = (name) => roleConfig[name]?.badge ?? 'bg-slate-50 text-slate-600 border-slate-200'

// ── Filtered rows ──────────────────────────────────────
const filteredUsers = computed(() => {
    let items = props.users?.data ?? []
    if (search.value) {
        const q = search.value.toLowerCase()
        items = items.filter(u =>
            u.name.toLowerCase().includes(q) ||
            u.email.toLowerCase().includes(q)
        )
    }
    if (roleFilter.value !== 'all')
        items = items.filter(u => getRoleName(u) === roleFilter.value)
    return items
})

// ── Summary counts ─────────────────────────────────────
const totalUsers = computed(() => props.users?.total ?? 0)
const roleCounts = computed(() => {
    const all = props.users?.data ?? []
    return props.roles.reduce((acc, r) => {
        acc[r] = all.filter(u => getRoleName(u) === r).length
        return acc
    }, {})
})

// ── Helpers ────────────────────────────────────────────
const initials    = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() ?? '?'
const isSelf      = (user) => user.id === authUser.value?.id
const formatDate  = (d) => d ? new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'

const avatarColor = (name) => {
    const colors = [
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300',
        'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
        'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300',
        'bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300',
        'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300',
        'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
    ]
    return colors[(name?.length ?? 0) % colors.length]
}

// ── Create form ────────────────────────────────────────
const createForm = useForm({
    name:                 '',
    email:                '',
    password:             '',
    password_confirmation: '',
    role:                 '',
    send_welcome:         false,
})

const openCreate = () => {
    createForm.reset()
    showPassword.value = false
    showConfirm.value  = false
    showCreateModal.value = true
}

const submitCreate = () => {
    createForm.post(route('users.store'), {
        onSuccess: () => { showCreateModal.value = false; createForm.reset() },
    })
}

// ── Edit form ──────────────────────────────────────────
const editForm = useForm({
    name:                 '',
    email:                '',
    password:             '',
    password_confirmation: '',
})

const openEdit = (user) => {
    editingUser.value             = user
    editForm.name                 = user.name
    editForm.email                = user.email
    editForm.password             = ''
    editForm.password_confirmation = ''
    showPassword.value            = false
    showEditModal.value           = true
}

const submitEdit = () => {
    editForm.put(route('users.update', editingUser.value.id), {
        onSuccess: () => { showEditModal.value = false; editForm.reset() },
    })
}

// ── Role assignment form ───────────────────────────────
const roleForm = useForm({ role: '' })

const openRoleModal = (user) => {
    editingUser.value = user
    roleForm.role     = getRoleName(user) ?? ''
    showRoleModal.value = true
}

const submitRole = () => {
    roleForm.patch(route('users.assign-role', editingUser.value.id), {
        onSuccess: () => { showRoleModal.value = false; roleForm.reset() },
    })
}

// ── Delete ─────────────────────────────────────────────
const deleteUser = (user) => {
    if (confirm(`Delete ${user.name}? This cannot be undone.`))
        router.delete(route('users.destroy', user.id))
}

const goToPage = (url) => { if (url) router.get(url) }

// ── Classes ────────────────────────────────────────────
const inputClass    = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 dark:focus:border-indigo-600 transition-all'
const errorClass    = '!border-rose-400 dark:!border-rose-600'
const labelClass    = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- ── Page header ──────────────────────────────── -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Users</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                        {{ totalUsers }} registered user{{ totalUsers !== 1 ? 's' : '' }}
                    </p>
                </div>
                <button
                    v-if="can('users.create')"
                    @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-sm"
                >
                    <Plus class="w-4 h-4" /> Add User
                </button>
            </div>

            <!-- ── Filters ──────────────────────────────────── -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <Search class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search name or email…"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                        />
                    </div>
                    <div class="relative">
                        <Shield class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                        <select
                            v-model="roleFilter"
                            class="pl-10 pr-8 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all appearance-none cursor-pointer"
                        >
                            <option value="all">All Roles</option>
                            <option v-for="r in roles" :key="r" :value="r">
                                {{ getRoleLabel(r) }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ── Empty state ──────────────────────────────── -->
            <div
                v-if="filteredUsers.length === 0"
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-16 text-center"
            >
                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <Users class="w-6 h-6 text-slate-400" />
                </div>
                <h3 class="text-base font-semibold text-slate-700 dark:text-slate-200 mb-1">No users found</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    {{ search || roleFilter !== 'all' ? 'Try adjusting your search or filter.' : 'Add your first user to get started.' }}
                </p>
            </div>

            <!-- ── Table ────────────────────────────────────── -->
            <div
                v-else
                class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">User</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="user in filteredUsers"
                            :key="user.id"
                            class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                            :class="{ 'opacity-75': isSelf(user) }"
                        >
                            <!-- Name + avatar -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold shrink-0"
                                        :class="avatarColor(user.name)"
                                    >
                                        {{ initials(user.name) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                            {{ user.name }}
                                            <span
                                                v-if="isSelf(user)"
                                                class="ml-1.5 text-[10px] font-semibold text-indigo-500 dark:text-indigo-400 uppercase tracking-wide"
                                            >You</span>
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                                    <Mail class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                    <span class="text-sm">{{ user.email }}</span>
                                </div>
                            </td>

                            <!-- Role badge — clickable if can assignRole and not self -->
                            <td class="px-6 py-4">
                                <button
                                    v-if="can('roles.assign') && !isSelf(user) && getRoleName(user)"
                                    @click="openRoleModal(user)"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-medium border transition-all hover:ring-2 hover:ring-indigo-400/40 hover:ring-offset-1"
                                    :class="getRoleBadge(getRoleName(user))"
                                    title="Change role"
                                >
                                    {{ getRoleLabel(getRoleName(user)) }}
                                    <UserCog class="w-3 h-3 opacity-60" />
                                </button>
                                <span
                                    v-else-if="getRoleName(user)"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border"
                                    :class="getRoleBadge(getRoleName(user))"
                                >
                                        {{ getRoleLabel(getRoleName(user)) }}
                                    </span>
                                <span v-else class="text-sm text-slate-400">—</span>
                            </td>

                            <!-- Joined -->
                            <td class="px-6 py-4">
                                    <span class="text-sm text-slate-500 dark:text-slate-400">
                                        {{ formatDate(user.created_at) }}
                                    </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        v-if="can('users.update')"
                                        @click="openEdit(user)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors"
                                        title="Edit user"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button
                                        v-if="can('users.delete') && !isSelf(user)"
                                        @click="deleteUser(user)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors"
                                        title="Delete user"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ── Pagination ──────────────────────────── -->
                <div
                    v-if="users.last_page > 1"
                    class="flex items-center justify-between px-6 py-4 border-t border-slate-100 dark:border-slate-800"
                >
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Showing {{ users.from }}–{{ users.to }} of {{ users.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <button
                            @click="goToPage(users.prev_page_url)"
                            :disabled="!users.prev_page_url"
                            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <template v-for="link in users.links.slice(1, -1)" :key="link.label">
                            <button
                                @click="goToPage(link.url)"
                                :disabled="!link.url"
                                class="min-w-[36px] h-9 px-2 rounded-lg border text-sm font-medium transition-colors"
                                :class="link.active
                                    ? 'bg-indigo-600 border-indigo-600 text-white'
                                    : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'"
                            >
                                {{ link.label }}
                            </button>
                        </template>
                        <button
                            @click="goToPage(users.next_page_url)"
                            :disabled="!users.next_page_url"
                            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        >
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- ══ CREATE USER MODAL ═════════════════════════════ -->
        <Modal :show="showCreateModal" title="Add User" size="md" @close="showCreateModal = false">
            <form @submit.prevent="submitCreate" class="p-6 space-y-5">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Full Name <span class="text-rose-500">*</span></label>
                        <input
                            v-model="createForm.name"
                            type="text"
                            placeholder="e.g. James Carter"
                            :class="[inputClass, createForm.errors.name ? errorClass : '']"
                        />
                        <p v-if="createForm.errors.name" :class="errorMsgClass">{{ createForm.errors.name }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label :class="labelClass">Email <span class="text-rose-500">*</span></label>
                        <input
                            v-model="createForm.email"
                            type="email"
                            placeholder="user@example.com"
                            :class="[inputClass, createForm.errors.email ? errorClass : '']"
                        />
                        <p v-if="createForm.errors.email" :class="errorMsgClass">{{ createForm.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label :class="labelClass">Password <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input
                                v-model="createForm.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Min 8 chars, mixed case + number"
                                :class="[inputClass, 'pr-10', createForm.errors.password ? errorClass : '']"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                            >
                                <EyeOff v-if="showPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="createForm.errors.password" :class="errorMsgClass">{{ createForm.errors.password }}</p>
                    </div>

                    <!-- Confirm password -->
                    <div>
                        <label :class="labelClass">Confirm Password <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input
                                v-model="createForm.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                placeholder="Repeat password"
                                :class="[inputClass, 'pr-10']"
                            />
                            <button
                                type="button"
                                @click="showConfirm = !showConfirm"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                            >
                                <EyeOff v-if="showConfirm" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="sm:col-span-2">
                        <label :class="labelClass">Role <span class="text-rose-500">*</span></label>
                        <select v-model="createForm.role" :class="[inputClass, createForm.errors.role ? errorClass : '']">
                            <option value="" disabled>Select a role…</option>
                            <option v-for="r in roles" :key="r" :value="r">{{ getRoleLabel(r) }}</option>
                        </select>
                        <p v-if="createForm.errors.role" :class="errorMsgClass">{{ createForm.errors.role }}</p>
                    </div>

                </div>

                <!-- Send welcome email toggle -->
                <label class="flex items-start gap-3 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                    <input
                        v-model="createForm.send_welcome"
                        type="checkbox"
                        class="mt-0.5 w-4 h-4 rounded accent-indigo-600"
                    />
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Send welcome / verification email</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Sends a Laravel email verification link to the user's inbox.</p>
                    </div>
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="createForm.processing" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="createForm.processing" class="w-4 h-4 animate-spin" />
                        Create User
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ══ EDIT USER MODAL ═══════════════════════════════ -->
        <Modal :show="showEditModal" title="Edit User" size="md" @close="showEditModal = false">
            <form @submit.prevent="submitEdit" class="p-6 space-y-5">

                <div>
                    <label :class="labelClass">Full Name <span class="text-rose-500">*</span></label>
                    <input
                        v-model="editForm.name"
                        type="text"
                        :class="[inputClass, editForm.errors.name ? errorClass : '']"
                    />
                    <p v-if="editForm.errors.name" :class="errorMsgClass">{{ editForm.errors.name }}</p>
                </div>

                <div>
                    <label :class="labelClass">Email <span class="text-rose-500">*</span></label>
                    <input
                        v-model="editForm.email"
                        type="email"
                        :class="[inputClass, editForm.errors.email ? errorClass : '']"
                    />
                    <p v-if="editForm.errors.email" :class="errorMsgClass">{{ editForm.errors.email }}</p>
                </div>

                <!-- Optional password reset section -->
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800 flex items-center gap-2">
                        <KeyRound class="w-4 h-4 text-slate-400" />
                        <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Reset Password</span>
                        <span class="text-xs text-slate-400 dark:text-slate-500">(leave blank to keep current)</span>
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label :class="labelClass">New Password</label>
                            <div class="relative">
                                <input
                                    v-model="editForm.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="New password"
                                    :class="[inputClass, 'pr-10', editForm.errors.password ? errorClass : '']"
                                />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <EyeOff v-if="showPassword" class="w-4 h-4" />
                                    <Eye v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="editForm.errors.password" :class="errorMsgClass">{{ editForm.errors.password }}</p>
                        </div>
                        <div>
                            <label :class="labelClass">Confirm New Password</label>
                            <input
                                v-model="editForm.password_confirmation"
                                type="password"
                                placeholder="Repeat new password"
                                :class="inputClass"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="editForm.processing" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="editForm.processing" class="w-4 h-4 animate-spin" />
                        Save Changes
                    </button>
                </div>
            </form>
        </Modal>

        <!-- ══ ROLE ASSIGNMENT MODAL ═════════════════════════ -->
        <Modal :show="showRoleModal" title="Change Role" size="sm" @close="showRoleModal = false">
            <form @submit.prevent="submitRole" class="p-6 space-y-5">

                <div v-if="editingUser" class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold shrink-0" :class="avatarColor(editingUser.name)">
                        {{ initials(editingUser.name) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ editingUser.name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ editingUser.email }}</p>
                    </div>
                </div>

                <div>
                    <label :class="labelClass">Assign Role <span class="text-rose-500">*</span></label>
                    <select v-model="roleForm.role" :class="[inputClass, roleForm.errors.role ? errorClass : '']">
                        <option value="" disabled>Select a role…</option>
                        <option v-for="r in roles" :key="r" :value="r">{{ getRoleLabel(r) }}</option>
                    </select>
                    <p v-if="roleForm.errors.role" :class="errorMsgClass">{{ roleForm.errors.role }}</p>
                </div>

                <!-- Role descriptions -->
                <div class="text-xs text-slate-500 dark:text-slate-400 space-y-1 px-1">
                    <p><span class="font-semibold text-slate-600 dark:text-slate-300">Admin</span> — full system access</p>
                    <p><span class="font-semibold text-slate-600 dark:text-slate-300">Project Manager</span> — project & task management</p>
                    <p><span class="font-semibold text-slate-600 dark:text-slate-300">Finance / Accountant</span> — budgets & expenses</p>
                    <p><span class="font-semibold text-slate-600 dark:text-slate-300">Site Worker / Contractor</span> — task view & updates only</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showRoleModal = false" class="px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" :disabled="roleForm.processing" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-colors">
                        <Loader2 v-if="roleForm.processing" class="w-4 h-4 animate-spin" />
                        Assign Role
                    </button>
                </div>
            </form>
        </Modal>

    </AuthenticatedLayout>
</template>
