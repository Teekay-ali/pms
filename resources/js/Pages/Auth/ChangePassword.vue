<script setup>
import { useForm } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import { HardHat, Loader2, Eye, EyeOff, KeyRound } from 'lucide-vue-next'
import { ref } from 'vue'

const form = useForm({
    password:              '',
    password_confirmation: '',
})

const showPassword = ref(false)
const showConfirm  = ref(false)

function submit() {
    form.post(route('password.change.update'), {
        onFinish: () => form.reset(),
    })
}
</script>

<template>
    <Head title="Change Password" />

    <div class="min-h-screen bg-slate-950 flex items-center justify-center p-4">
        <div class="w-full max-w-md">

            <!-- Logo -->
            <div class="flex items-center justify-center gap-3 mb-8">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <HardHat class="w-5 h-5 text-white" />
                </div>
                <span class="text-white font-bold text-xl tracking-tight">SitePro</span>
            </div>

            <!-- Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">

                <!-- Icon + heading -->
                <div class="flex flex-col items-center text-center mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center mb-4">
                        <KeyRound class="w-6 h-6 text-indigo-400" />
                    </div>
                    <h1 class="text-xl font-bold text-white">Set Your Password</h1>
                    <p class="text-slate-400 text-sm mt-2">You must set a new password before continuing.</p>
                </div>

                <div class="space-y-4">

                    <!-- New password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">New Password</label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Min 8 chars, upper, lower, number"
                                class="w-full px-4 py-3 bg-slate-800/50 border rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all pr-12"
                                :class="form.errors.password ? 'border-red-500/50' : 'border-slate-700'"
                            />
                            <button type="button" @click.prevent="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors">
                                <Eye v-if="!showPassword" class="w-4 h-4" />
                                <EyeOff v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-400">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">Confirm Password</label>
                        <div class="relative">
                            <input
                                v-model="form.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                placeholder="Re-enter your password"
                                class="w-full px-4 py-3 bg-slate-800/50 border rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all pr-12"
                                :class="form.errors.password_confirmation ? 'border-red-500/50' : 'border-slate-700'"
                            />
                            <button type="button" @click.prevent="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors">
                                <Eye v-if="!showConfirm" class="w-4 h-4" />
                                <EyeOff v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-400">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- Password requirements -->
                    <div class="bg-slate-800/50 rounded-xl p-3 text-xs text-slate-400 space-y-1">
                        <p class="font-medium text-slate-300 mb-1.5">Password requirements:</p>
                        <p>• Minimum 8 characters</p>
                        <p>• At least one uppercase letter</p>
                        <p>• At least one lowercase letter</p>
                        <p>• At least one number</p>
                    </div>

                    <!-- Submit -->
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2 mt-2"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        <span>{{ form.processing ? 'Saving...' : 'Set Password & Continue' }}</span>
                    </button>

                </div>
            </div>
        </div>
    </div>
</template>
