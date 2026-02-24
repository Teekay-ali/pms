<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Eye, EyeOff, HardHat, Loader2 } from 'lucide-vue-next'
import { ref } from 'vue'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const showPassword = ref(false)

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Sign In" />

    <div class="min-h-screen bg-slate-950 flex">

        <!-- Left Panel - Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex-col justify-between p-12">

            <!-- Grid pattern overlay -->
            <div class="absolute inset-0 opacity-10"
                 style="background-image: linear-gradient(rgba(99,102,241,0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,0.3) 1px, transparent 1px); background-size: 40px 40px;">
            </div>

            <!-- Floating cards decoration -->
            <div class="absolute top-1/4 right-8 w-64 h-36 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-5 rotate-6 shadow-2xl">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
                    <span class="text-white/60 text-xs font-medium">Project Active</span>
                </div>
                <p class="text-white font-semibold text-sm">Highway Bridge Construction</p>
                <div class="mt-3 h-1.5 bg-white/10 rounded-full overflow-hidden">
                    <div class="h-full w-3/4 bg-indigo-400 rounded-full"></div>
                </div>
                <p class="text-white/40 text-xs mt-1.5">75% Complete</p>
            </div>

            <div class="absolute top-1/2 right-16 w-56 h-28 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10 p-5 -rotate-3 shadow-2xl">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                    <span class="text-white/60 text-xs font-medium">Budget Alert</span>
                </div>
                <p class="text-white font-semibold text-sm">$2.4M of $3.1M used</p>
                <p class="text-white/40 text-xs mt-1">12 expenses pending approval</p>
            </div>

            <!-- Logo -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <HardHat class="w-5 h-5 text-white" />
                </div>
                <span class="text-white font-bold text-xl tracking-tight">SitePro</span>
            </div>

            <!-- Center text -->
            <div class="relative z-10">
                <h1 class="text-4xl font-bold text-white leading-tight mb-4">
                    Manage every project,<br />
                    <span class="text-indigo-400">from blueprint to handover.</span>
                </h1>
                <p class="text-slate-400 text-lg leading-relaxed max-w-md">
                    A unified platform for construction teams to track projects, budgets, resources, and procurement — all in one place.
                </p>
            </div>

            <!-- Bottom stats -->
            <div class="relative z-10 grid grid-cols-3 gap-6">
                <div>
                    <p class="text-2xl font-bold text-white">98%</p>
                    <p class="text-slate-500 text-sm mt-0.5">On-time delivery</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">11</p>
                    <p class="text-slate-500 text-sm mt-0.5">Role-based access</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">∞</p>
                    <p class="text-slate-500 text-sm mt-0.5">Projects & tasks</p>
                </div>
            </div>
        </div>

        <!-- Right Panel - Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md">

                <!-- Mobile logo -->
                <div class="flex items-center gap-3 mb-10 lg:hidden">
                    <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center">
                        <HardHat class="w-4 h-4 text-white" />
                    </div>
                    <span class="text-white font-bold text-lg">SitePro</span>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-white mb-1">Welcome back</h2>
                    <p class="text-slate-400">Sign in to your SitePro account</p>
                </div>

                <!-- Status message -->
                <div v-if="status" class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                    <p class="text-emerald-400 text-sm">{{ status }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Email address
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            placeholder="you@company.com"
                            class="w-full px-4 py-3 bg-slate-800/50 border rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            :class="form.errors.email ? 'border-red-500/50' : 'border-slate-700'"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-400">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-slate-300">
                                Password
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors"
                            >
                                Forgot password?
                            </Link>
                        </div>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                placeholder="••••••••"
                                class="w-full px-4 py-3 bg-slate-800/50 border rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all pr-12"
                                :class="form.errors.password ? 'border-red-500/50' : 'border-slate-700'"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors"
                            >
                                <Eye v-if="!showPassword" class="w-4 h-4" />
                                <EyeOff v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-400">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="form.remember = !form.remember"
                            class="w-5 h-5 rounded-md border flex items-center justify-center transition-all flex-shrink-0"
                            :class="form.remember
                                ? 'bg-indigo-600 border-indigo-600'
                                : 'bg-slate-800 border-slate-600 hover:border-slate-500'"
                        >
                            <svg v-if="form.remember" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                        <span class="text-sm text-slate-400">Remember me for 30 days</span>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 flex items-center justify-center gap-2 mt-2"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        <span>{{ form.processing ? 'Signing in...' : 'Sign in to SitePro' }}</span>
                    </button>

                </form>

                <!-- Register link -->
                <p class="mt-8 text-center text-sm text-slate-500">
                    Don't have an account?
                    <Link :href="route('register')" class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors ml-1">
                        Request access
                    </Link>
                </p>

            </div>
        </div>
    </div>
</template>
