<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, usePage, Link } from '@inertiajs/vue3'
import { User, Lock, Shield, ChevronRight, Loader2, Eye, EyeOff, CheckCircle } from 'lucide-vue-next'

defineProps({
    mustVerifyEmail: Boolean,
    status: String,
})

const page = usePage()
const user = computed(() => page.props.auth.user)
const roles = computed(() => page.props.auth.roles ?? [])

const activeTab = ref('profile')

const tabs = [
    { key: 'profile',  label: 'Profile',         icon: User },
    { key: 'password', label: 'Password',         icon: Lock },
]

// ── Profile form ───────────────────────────────────────
const profileForm = useForm({
    name:  user.value.name,
    email: user.value.email,
})

const submitProfile = () => {
    profileForm.patch(route('profile.update'), { preserveScroll: true })
}

// ── Password form ──────────────────────────────────────
const showCurrent = ref(false)
const showNew     = ref(false)
const showConfirm = ref(false)

const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
})

const submitPassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation')
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password')
            }
        },
    })
}

// ── Styles ─────────────────────────────────────────────
const inputClass    = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-300 dark:focus:border-indigo-600 transition-all'
const errorClass    = '!border-rose-400 dark:!border-rose-500'
const labelClass    = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'

const primaryRole = computed(() =>
    roles.value[0]?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) ?? 'No Role'
)

const userInitials = computed(() =>
    user.value?.name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() ?? '?'
)
</script>

<template>
    <Head title="Settings" />

    <div class="max-w-4xl mx-auto px-4 py-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Settings</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your account preferences</p>
        </div>

        <div class="flex gap-6">

            <!-- Sidebar tabs -->
            <aside class="w-52 shrink-0">
                <!-- Avatar card -->
                <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl mb-3 text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl flex items-center justify-center text-white text-lg font-bold shadow-lg mx-auto mb-3">
                        {{ userInitials }}
                    </div>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ user.name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ user.email }}</p>
                    <span class="inline-flex items-center gap-1 mt-2 px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-medium rounded-full">
                        <Shield class="w-3 h-3" />
                        {{ primaryRole }}
                    </span>
                </div>

                <!-- Tab list -->
                <nav class="space-y-0.5">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                        :class="activeTab === tab.key
                            ? 'bg-indigo-600/10 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400'
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-800 dark:hover:text-slate-200'"
                    >
                        <component :is="tab.icon" class="w-4 h-4 shrink-0" />
                        {{ tab.label }}
                        <ChevronRight
                            class="w-3.5 h-3.5 ml-auto transition-transform"
                            :class="activeTab === tab.key ? 'text-indigo-500 opacity-100' : 'opacity-0'"
                        />
                    </button>
                </nav>
            </aside>

            <!-- Content panel -->
            <div class="flex-1 min-w-0">

                <!-- ── Profile tab ──────────────────────────── -->
                <div v-if="activeTab === 'profile'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
                    <div class="mb-6">
                        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Profile Information</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Update your name and email address.</p>
                    </div>

                    <form @submit.prevent="submitProfile" class="space-y-5">
                        <div>
                            <label :class="labelClass">Full Name <span class="text-rose-500">*</span></label>
                            <input
                                v-model="profileForm.name"
                                type="text"
                                autocomplete="name"
                                :class="[inputClass, profileForm.errors.name ? errorClass : '']"
                                placeholder="Your full name"
                            />
                            <p v-if="profileForm.errors.name" :class="errorMsgClass">{{ profileForm.errors.name }}</p>
                        </div>

                        <div>
                            <label :class="labelClass">Email Address <span class="text-rose-500">*</span></label>
                            <input
                                v-model="profileForm.email"
                                type="email"
                                autocomplete="email"
                                :class="[inputClass, profileForm.errors.email ? errorClass : '']"
                                placeholder="you@example.com"
                            />
                            <p v-if="profileForm.errors.email" :class="errorMsgClass">{{ profileForm.errors.email }}</p>
                        </div>

                        <!-- Email unverified notice -->
                        <div v-if="mustVerifyEmail && !user.email_verified_at"
                             class="flex items-start gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                            <p class="text-sm text-amber-700 dark:text-amber-400">
                                Your email is unverified.
                                <Link :href="route('verification.send')" method="post" as="button"
                                      class="underline hover:text-amber-900 dark:hover:text-amber-300">
                                    Resend verification email.
                                </Link>
                            </p>
                        </div>

                        <div class="flex items-center gap-3 pt-1">
                            <button
                                type="submit"
                                :disabled="profileForm.processing"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors"
                            >
                                <Loader2 v-if="profileForm.processing" class="w-4 h-4 animate-spin" />
                                Save Changes
                            </button>
                            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-x-2" enter-to-class="opacity-100 translate-x-0" leave-active-class="transition ease-in duration-150" leave-to-class="opacity-0">
                                <span v-if="profileForm.recentlySuccessful" class="flex items-center gap-1.5 text-sm text-emerald-600 dark:text-emerald-400">
                                    <CheckCircle class="w-4 h-4" /> Saved
                                </span>
                            </Transition>
                        </div>
                    </form>
                </div>

                <!-- ── Password tab ─────────────────────────── -->
                <div v-if="activeTab === 'password'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6">
                    <div class="mb-6">
                        <h2 class="text-base font-semibold text-slate-800 dark:text-slate-100">Change Password</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Use a long, random password to keep your account secure.</p>
                    </div>

                    <form @submit.prevent="submitPassword" class="space-y-5">
                        <!-- Current password -->
                        <div>
                            <label :class="labelClass">Current Password <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input
                                    v-model="passwordForm.current_password"
                                    :type="showCurrent ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    :class="[inputClass, 'pr-10', passwordForm.errors.current_password ? errorClass : '']"
                                    placeholder="••••••••"
                                />
                                <button type="button" @click="showCurrent = !showCurrent"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                    <EyeOff v-if="showCurrent" class="w-4 h-4" />
                                    <Eye v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.current_password" :class="errorMsgClass">{{ passwordForm.errors.current_password }}</p>
                        </div>

                        <!-- New password -->
                        <div>
                            <label :class="labelClass">New Password <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input
                                    v-model="passwordForm.password"
                                    :type="showNew ? 'text' : 'password'"
                                    autocomplete="new-password"
                                    :class="[inputClass, 'pr-10', passwordForm.errors.password ? errorClass : '']"
                                    placeholder="••••••••"
                                />
                                <button type="button" @click="showNew = !showNew"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                    <EyeOff v-if="showNew" class="w-4 h-4" />
                                    <Eye v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.password" :class="errorMsgClass">{{ passwordForm.errors.password }}</p>
                        </div>

                        <!-- Confirm password -->
                        <div>
                            <label :class="labelClass">Confirm New Password <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input
                                    v-model="passwordForm.password_confirmation"
                                    :type="showConfirm ? 'text' : 'password'"
                                    autocomplete="new-password"
                                    :class="[inputClass, 'pr-10', passwordForm.errors.password_confirmation ? errorClass : '']"
                                    placeholder="••••••••"
                                />
                                <button type="button" @click="showConfirm = !showConfirm"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                    <EyeOff v-if="showConfirm" class="w-4 h-4" />
                                    <Eye v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.password_confirmation" :class="errorMsgClass">{{ passwordForm.errors.password_confirmation }}</p>
                        </div>

                        <div class="flex items-center gap-3 pt-1">
                            <button
                                type="submit"
                                :disabled="passwordForm.processing"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors"
                            >
                                <Loader2 v-if="passwordForm.processing" class="w-4 h-4 animate-spin" />
                                Update Password
                            </button>
                            <Transition enter-active-class="transition ease-out duration-300" enter-from-class="opacity-0 translate-x-2" enter-to-class="opacity-100 translate-x-0" leave-active-class="transition ease-in duration-150" leave-to-class="opacity-0">
                                <span v-if="passwordForm.recentlySuccessful" class="flex items-center gap-1.5 text-sm text-emerald-600 dark:text-emerald-400">
                                    <CheckCircle class="w-4 h-4" /> Updated
                                </span>
                            </Transition>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>
