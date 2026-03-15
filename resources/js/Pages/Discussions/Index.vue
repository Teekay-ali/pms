<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import {
    MessageSquare, Plus, Pin, Lock, ThumbsUp,
    Search, Tag, Clock, Eye, Loader2
} from 'lucide-vue-next'

const props = defineProps({
    discussions: Object,
    categories:  Array,
    filters:     Object,
})

const page    = usePage()
const authId  = computed(() => page.props.auth.user?.id)
const roles   = computed(() => page.props.auth.roles ?? [])
const canPin  = computed(() => roles.value.some(r => ['admin', 'project_manager'].includes(r)))

const showModal    = ref(false)
const search       = ref(props.filters?.search ?? '')
const categoryFilter = ref(props.filters?.category ?? '')

const form = useForm({
    title:       '',
    body:        '',
    category_id: '',
})

const submit = () => {
    form.post(route('discussions.store'), {
        onSuccess: () => { showModal.value = false; form.reset() },
    })
}

const applyFilter = () => {
    router.get(route('discussions.index'), {
        search:   search.value || undefined,
        category: categoryFilter.value || undefined,
    }, { preserveState: true, replace: true })
}

const fmtDate = (d) => {
    if (!d) return ''
    const date = new Date(d)
    const now  = new Date()
    const diff = Math.floor((now - date) / 1000)
    if (diff < 60)     return 'just now'
    if (diff < 3600)   return `${Math.floor(diff / 60)}m ago`
    if (diff < 86400)  return `${Math.floor(diff / 3600)}h ago`
    if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const initials = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() ?? '?'

// Input classes
const inputClass = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all'
const labelClass = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5'
const errorMsgClass = 'mt-1.5 text-xs text-rose-500'
</script>

<template>
    <Head title="Discussions" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Discussions</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                        {{ discussions?.total ?? 0 }} topics
                    </p>
                </div>
                <button
                    @click="showModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    <Plus class="w-4 h-4" /> New Discussion
                </button>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">

                <!-- Sidebar: Categories -->
                <aside class="lg:w-56 shrink-0 space-y-2">
                    <button
                        @click="categoryFilter = ''; applyFilter()"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition-colors"
                        :class="!categoryFilter ? 'bg-indigo-600/10 text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                    >
                        <span class="flex items-center gap-2">
                            <MessageSquare class="w-4 h-4" /> All Topics
                        </span>
                        <span class="text-xs text-slate-400">{{ discussions?.total ?? 0 }}</span>
                    </button>

                    <div class="pt-2">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 px-3 mb-1.5">Categories</p>
                        <button
                            v-for="cat in categories"
                            :key="cat.id"
                            @click="categoryFilter = cat.id; applyFilter()"
                            class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition-colors"
                            :class="categoryFilter == cat.id ? 'bg-indigo-600/10 text-indigo-600 dark:text-indigo-400' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        >
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full shrink-0" :style="{ background: cat.color }"></span>
                                {{ cat.name }}
                            </span>
                            <span class="text-xs text-slate-400">{{ cat.discussions_count }}</span>
                        </button>
                    </div>
                </aside>

                <!-- Main content -->
                <div class="flex-1 min-w-0 space-y-4">

                    <!-- Search -->
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search discussions..."
                            class="w-full pl-9 pr-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                            @keyup.enter="applyFilter"
                        />
                    </div>

                    <!-- Empty -->
                    <div v-if="!discussions?.data?.length" class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <MessageSquare class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                        <p class="text-slate-500 dark:text-slate-400 font-medium">No discussions yet</p>
                        <p class="text-sm text-slate-400 mt-1">Start a conversation with your team</p>
                    </div>

                    <!-- Discussion list -->
                    <div v-else class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
                        <Link
                            v-for="d in discussions.data"
                            :key="d.id"
                            :href="route('discussions.show', d.id)"
                            class="flex gap-4 px-5 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group"
                        >
                            <!-- Avatar -->
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white text-xs font-bold shrink-0 mt-0.5">
                                {{ initials(d.author?.name) }}
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                    <Pin v-if="d.is_pinned" class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                                    <Lock v-if="d.is_locked" class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                    <span
                                        v-if="d.category"
                                        class="px-2 py-0.5 rounded-full text-[10px] font-semibold text-white"
                                        :style="{ background: d.category.color }"
                                    >
                                        {{ d.category.name }}
                                    </span>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors truncate">
                                        {{ d.title }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-slate-400">
                                    <span>{{ d.author?.name }}</span>
                                    <span class="flex items-center gap-1"><Clock class="w-3 h-3" /> {{ fmtDate(d.last_activity_at) }}</span>
                                    <span class="flex items-center gap-1"><MessageSquare class="w-3 h-3" /> {{ d.replies_count }}</span>
                                    <span class="flex items-center gap-1"><Eye class="w-3 h-3" /> {{ d.views }}</span>
                                    <span class="flex items-center gap-1"><ThumbsUp class="w-3 h-3" /> {{ d.reactions_count }}</span>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="discussions?.last_page > 1" class="flex justify-center gap-1">
                        <button
                            v-for="page in discussions.last_page"
                            :key="page"
                            @click="router.get(route('discussions.index', { page, category: categoryFilter || undefined, search: search || undefined }))"
                            class="w-9 h-9 rounded-lg text-sm font-medium transition-colors"
                            :class="page === discussions.current_page
                                ? 'bg-indigo-600 text-white'
                                : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Discussion Modal -->
        <Modal :show="showModal" title="New Discussion" size="lg" @close="showModal = false">
            <div class="p-6 space-y-5">
                <div>
                    <label :class="labelClass">Title <span class="text-rose-500">*</span></label>
                    <input v-model="form.title" type="text" placeholder="What would you like to discuss?" :class="[inputClass, form.errors.title ? '!border-rose-400' : '']" />
                    <p v-if="form.errors.title" :class="errorMsgClass">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label :class="labelClass">Category</label>
                    <select v-model="form.category_id" :class="inputClass">
                        <option value="">— No category —</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <div>
                    <label :class="labelClass">Body <span class="text-rose-500">*</span></label>
                    <textarea
                        v-model="form.body"
                        rows="6"
                        placeholder="Share your thoughts..."
                        :class="[inputClass, 'resize-none', form.errors.body ? '!border-rose-400' : '']"
                    ></textarea>
                    <p v-if="form.errors.body" :class="errorMsgClass">{{ form.errors.body }}</p>
                </div>
            </div>

            <template #footer>
                <div class="flex items-center justify-end gap-3">
                    <button @click="showModal = false" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors"
                    >
                        <Loader2 v-if="form.processing" class="w-4 h-4 animate-spin" />
                        Post Discussion
                    </button>
                </div>
            </template>
        </Modal>

    </AuthenticatedLayout>
</template>
