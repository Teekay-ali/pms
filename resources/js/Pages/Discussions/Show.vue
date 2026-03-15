<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import AttachmentUploader from '@/Components/AttachmentUploader.vue'
import {
    ArrowLeft, Pin, Lock, ThumbsUp, Trash2,
    MessageSquare, Loader2, Shield
} from 'lucide-vue-next'

const props = defineProps({
    discussion: Object,
    authId:     Number,
})

const page  = usePage()
const roles = computed(() => page.props.auth.roles ?? [])
const canModerate = computed(() => roles.value.some(r => ['admin', 'project_manager'].includes(r)))

const replyForm = useForm({ body: '' })

const submitReply = () => {
    replyForm.post(route('discussions.replies.store', props.discussion.id), {
        preserveScroll: true,
        onSuccess: () => replyForm.reset(),
    })
}

const deleteReply = (replyId) => {
    if (confirm('Delete this reply?')) {
        router.delete(route('discussions.replies.destroy', [props.discussion.id, replyId]), {
            preserveScroll: true,
        })
    }
}

const deleteDiscussion = () => {
    if (confirm('Delete this discussion?')) {
        router.delete(route('discussions.destroy', props.discussion.id))
    }
}

const togglePin = () => router.post(route('discussions.pin', props.discussion.id), {}, { preserveScroll: true })
const toggleLock = () => router.post(route('discussions.lock', props.discussion.id), {}, { preserveScroll: true })

const react = (type, id) => {
    router.post(route('discussions.react', { type, id }), {}, { preserveScroll: true })
}

const hasReacted = (reactions) => reactions?.some(r => r.user_id === props.authId)

const fmtDate = (d) => new Date(d).toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
})

const initials = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() ?? '?'

const inputClass = 'w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all'
</script>

<template>
    <Head :title="discussion.title" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto space-y-6">

            <!-- Back -->
            <Link :href="route('discussions.index')" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <ArrowLeft class="w-4 h-4" /> Back to Discussions
            </Link>

            <!-- Main post -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

                <!-- Header -->
                <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <Pin v-if="discussion.is_pinned" class="w-4 h-4 text-amber-500" />
                                <Lock v-if="discussion.is_locked" class="w-4 h-4 text-slate-400" />
                                <span
                                    v-if="discussion.category"
                                    class="px-2.5 py-0.5 rounded-full text-xs font-semibold text-white"
                                    :style="{ background: discussion.category.color }"
                                >
                                    {{ discussion.category.name }}
                                </span>
                                <span v-if="discussion.project" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    📁 {{ discussion.project.name }}
                                </span>
                            </div>
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ discussion.title }}</h1>
                        </div>

                        <!-- Mod actions -->
                        <div v-if="canModerate || discussion.user_id === authId" class="flex items-center gap-1 shrink-0">
                            <button v-if="canModerate" @click="togglePin"
                                    class="p-2 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors"
                                    :title="discussion.is_pinned ? 'Unpin' : 'Pin'">
                                <Pin class="w-4 h-4" />
                            </button>
                            <button v-if="canModerate" @click="toggleLock"
                                    class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                    :title="discussion.is_locked ? 'Unlock' : 'Lock'">
                                <Lock class="w-4 h-4" />
                            </button>
                            <button v-if="discussion.user_id === authId || canModerate" @click="deleteDiscussion"
                                    class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-colors">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6">
                    <div class="flex gap-4">
                        <div class="shrink-0">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-white text-sm font-bold">
                                {{ initials(discussion.author?.name) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="font-semibold text-slate-800 dark:text-slate-100 text-sm">{{ discussion.author?.name }}</span>
                                <span class="text-xs text-slate-400">{{ fmtDate(discussion.created_at) }}</span>
                                <span class="text-xs text-slate-400">· {{ discussion.views }} views</span>
                            </div>
                            <div class="text-sm text-slate-700 dark:text-slate-200 leading-relaxed whitespace-pre-wrap">{{ discussion.body }}</div>

                            <!-- Attachments -->
                            <div v-if="discussion.attachments?.length || !discussion.is_locked" class="mt-4">
                                <AttachmentUploader
                                    model-type="discussions"
                                    :model-id="discussion.id"
                                    :attachments="discussion.attachments ?? []"
                                    :can-upload="!discussion.is_locked && discussion.user_id === authId"
                                    :can-delete="discussion.user_id === authId"
                                />
                            </div>

                            <!-- Like -->
                            <div class="flex items-center gap-3 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <button
                                    @click="react('discussion', discussion.id)"
                                    class="flex items-center gap-1.5 text-sm px-3 py-1.5 rounded-lg transition-colors"
                                    :class="hasReacted(discussion.reactions)
                                        ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400'
                                        : 'text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800'"
                                >
                                    <ThumbsUp class="w-4 h-4" />
                                    {{ discussion.reactions?.length ?? 0 }} Likes
                                </button>
                                <span class="text-xs text-slate-400">
                                    <MessageSquare class="w-3.5 h-3.5 inline mr-1" />
                                    {{ discussion.replies?.length ?? 0 }} replies
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Replies -->
            <div v-if="discussion.replies?.length" class="space-y-3">
                <h3 class="text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                    {{ discussion.replies.length }} {{ discussion.replies.length === 1 ? 'Reply' : 'Replies' }}
                </h3>

                <div
                    v-for="reply in discussion.replies"
                    :key="reply.id"
                    class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5"
                >
                    <div class="flex gap-4">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ initials(reply.author?.name) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-slate-800 dark:text-slate-100 text-sm">{{ reply.author?.name }}</span>
                                    <span class="text-xs text-slate-400">{{ fmtDate(reply.created_at) }}</span>
                                </div>
                                <button
                                    v-if="reply.user_id === authId || canModerate"
                                    @click="deleteReply(reply.id)"
                                    class="p-1 rounded text-slate-400 hover:text-rose-500 transition-colors"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                </button>
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-200 leading-relaxed whitespace-pre-wrap">{{ reply.body }}</p>

                            <!-- Reply like -->
                            <button
                                @click="react('reply', reply.id)"
                                class="flex items-center gap-1.5 text-xs mt-3 px-2.5 py-1 rounded-lg transition-colors"
                                :class="hasReacted(reply.reactions)
                                    ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400'
                                    : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800'"
                            >
                                <ThumbsUp class="w-3.5 h-3.5" />
                                {{ reply.reactions?.length ?? 0 }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reply form -->
            <div v-if="!discussion.is_locked" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-4">Post a Reply</h3>
                <textarea
                    v-model="replyForm.body"
                    rows="4"
                    placeholder="Share your thoughts..."
                    :class="[inputClass, 'resize-none mb-4']"
                ></textarea>
                <p v-if="replyForm.errors.body" class="text-xs text-rose-500 -mt-3 mb-3">{{ replyForm.errors.body }}</p>
                <button
                    @click="submitReply"
                    :disabled="replyForm.processing || !replyForm.body.trim()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors"
                >
                    <Loader2 v-if="replyForm.processing" class="w-4 h-4 animate-spin" />
                    Post Reply
                </button>
            </div>

            <!-- Locked notice -->
            <div v-else class="flex items-center gap-3 p-4 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <Lock class="w-5 h-5 text-slate-400" />
                <p class="text-sm text-slate-500 dark:text-slate-400">This discussion is locked. No new replies can be posted.</p>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
