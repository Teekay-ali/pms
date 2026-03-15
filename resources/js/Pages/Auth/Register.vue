<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Eye, EyeOff, HardHat, Loader2, ShieldCheck, Users, BarChart3 } from 'lucide-vue-next'
import { ref } from 'vue'

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const showPassword = ref(false)
const showConfirm = ref(false)

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}

const features = [
    {
        icon: ShieldCheck,
        title: 'Role-Based Access',
        description: 'Granular permissions for every team member.',
    },
    {
        icon: BarChart3,
        title: 'Real-Time Reporting',
        description: 'Budget, progress, and resource insights at a glance.',
    },
    {
        icon: Users,
        title: 'Team Collaboration',
        description: 'Connect project managers, engineers, and contractors.',
    },
]
</script>

<template>
    <Head title="Create Account" />

    <div class="min-h-screen bg-slate-950 flex">

        <!-- Left Panel - Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-linear-to-br from-slate-900 via-indigo-950 to-slate-900 flex-col justify-between p-12">

            <!-- Grid pattern overlay -->
            <div class="absolute inset-0 opacity-10"
                 style="background-image: linear-gradient(rgba(99,102,241,0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,0.3) 1px, transparent 1px); background-size: 40px 40px;">
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
                    Built for the way<br />
                    <span class="text-indigo-400">construction teams work.</span>
                </h1>
                <p class="text-slate-400 text-lg leading-relaxed max-w-md mb-10">
                    Join your team on SitePro and get full visibility over every project, task, and budget — from the office or on site.
                </p>

                <!-- Feature list -->
                <div class="space-y-5">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="flex items-start gap-4"
                    >
                        <div class="w-10 h-10 bg-indigo-600/20 border border-indigo-500/20 rounded-xl flex items-center justify-center shrink-0">
                            <component :is="feature.icon" class="w-5 h-5 text-indigo-400" />
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">{{ feature.title }}</p>
                            <p class="text-slate-500 text-sm mt-0.5">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom note -->
            <div class="relative z-10 p-4 bg-white/5 border border-white/10 rounded-xl">
                <p class="text-slate-400 text-sm">
                    <span class="text-white font-medium">New accounts</span> are assigned limited access by default. Your administrator will assign your role once your account is created.
                </p>
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
                    <h2 class="text-2xl font-bold text-white mb-1">Create your account</h2>
                    <p class="text-slate-400">Get started with SitePro today</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Full name
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            required
                            placeholder="John Smith"
                            class="w-full px-4 py-3 bg-slate-800/50 border rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                            :class="form.errors.name ? 'border-red-500/50' : 'border-slate-700'"
                        />
                        <p v-if="form.errors.name" class="mt-1.5 text-xs text-red-400">
                            {{ form.errors.name }}
                        </p>
                    </div>

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
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                placeholder="Min. 8 characters"
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

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">
                            Confirm password
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                placeholder="Re-enter your password"
                                class="w-full px-4 py-3 bg-slate-800/50 border rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all pr-12"
                                :class="form.errors.password_confirmation ? 'border-red-500/50' : 'border-slate-700'"
                            />
                            <button
                                type="button"
                                @click="showConfirm = !showConfirm"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors"
                            >
                                <Eye v-if="!showConfirm" class="w-4 h-4" />
                                <EyeOff v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-400">
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 flex items-center justify-center gap-2 mt-2"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        <span>{{ form.processing ? 'Creating account...' : 'Create account' }}</span>
                    </button>

                </form>

                <!-- Login link -->
                <p class="mt-8 text-center text-sm text-slate-500">
                    Already have an account?
                    <Link :href="route('login')" class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors ml-1">
                        Sign in
                    </Link>
                </p>

            </div>
        </div>
    </div>
</template>
