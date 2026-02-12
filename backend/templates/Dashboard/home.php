<?php
/**
 * @var \App\View\AppView $this
 * @var \Authentication\Identity|null $user
 */
$this->assign('title', 'Home');
?>

<style>
/* Hide scrollbar but keep scrolling */
.no-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
.no-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}
</style>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <div id="dashboard-app" class="max-w-9xl mx-auto px-4 sm:px-6 flex gap-6 py-6 min-h-screen">


        <!-- Sidebar -->
        <?= $this->element('sidebar', ['active' => 'home']) ?>

        <!-- Main content (scrollable) -->
        <main class="flex-1 space-y-6 overflow-y-auto max-h-[calc(100vh-3rem)] no-scrollbar">

            <!-- Header -->
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-700">
                            Welcome, {{ userFullName }}!
                        </h1>
                        <p class="text-sm text-blue-600 mt-1">
                            @{{ userName }}
                        </p>
                    </div>

                    <div class="text-sm text-blue-500">&nbsp;</div>
                </div>
            </div>

            <!-- Post composer -->
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <textarea v-model="composer.text" rows="3" placeholder="What's happening?"
                                  class="w-full resize-none border-0 focus:ring-0 text-blue-800 placeholder-blue-400 bg-transparent"></textarea>

                        <div v-if="composer.imagePreview" class="mt-4">
                            <img :src="composer.imagePreview" alt="preview" class="rounded-lg max-h-48 object-cover" />
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-2 cursor-pointer text-blue-600 hover:text-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0016.586 6L13 2.414A2 2 0 0011.586 2H4z"/></svg>
                                    <span class="text-sm">Add image</span>
                                    <input type="file" accept="image/*" @change="onImageChange" class="hidden" />
                                </label>
                            </div>

                            <div>
                                <button @click="createPost" :disabled="!canPost" class="bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50">
                                    Post
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feed / Posts -->
            <div class="space-y-4">
                <div v-for="post in posts" :key="post.id" class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100 p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white text-lg font-extrabold">
                                {{ post.initial }}
                            </div>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-blue-800 font-medium">{{ post.author }}</div>
                                    <div class="text-xs text-blue-400">{{ post.time }}</div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button @click="toggleLike(post)" :class="post.liked ? 'text-indigo-600' : 'text-blue-500'" class="flex items-center gap-2 text-sm font-semibold">
                                        <span v-if="!post.liked" class="inline-flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 w-5 h-5">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                            </svg>
                                        </span>
                                        <span v-else class="inline-flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6 w-5 h-5">
                                              <path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                                            </svg>
                                        </span>
                                        <span>{{ post.likes }}</span>
                                    </button>
                                </div>
                            </div>

                            <div class="text-blue-700 mt-1">{{ post.text }}</div>
                            <div v-if="post.about" class="text-sm text-blue-600 mt-2">{{ post.about }}</div>
                            <div v-if="post.image" class="mt-3">
                                <img :src="post.image" class="rounded-lg max-h-64 object-cover w-full" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </main>

        <!-- Right sidebar -->
        <?= $this->element('right_sidebar') ?>
    </div>
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            userFullName: '<?= htmlspecialchars((string)($user['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>',
            userName: '<?= htmlspecialchars((string)($user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>',
            userAbout: '<?= htmlspecialchars((string)($user['about'] ?? ''), ENT_QUOTES, 'UTF-8') ?>',
            composer: {
                text: '',
                imageFile: null,
                imagePreview: null
            },
            posts: []
        };
    },
    methods: {
        toggleLike(post) {
            post.liked = !post.liked;
            post.likes = (post.likes || 0) + (post.liked ? 1 : -1);
        },
        onImageChange(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            this.composer.imageFile = file;
            const reader = new FileReader();
            reader.onload = (ev) => {
                this.composer.imagePreview = ev.target.result;
            };
            reader.readAsDataURL(file);
        },
        createPost() {
            if (!this.canPost) return;
            const id = Date.now();
            const authorLabel = (this.userFullName || this.userName || 'You').toString();
            const initial = (authorLabel && authorLabel.trim().length) ? authorLabel.trim().charAt(0).toUpperCase() : 'U';
            this.posts.unshift({
                id,
                author: authorLabel,
                about: this.userAbout || '',
                initial,
                text: this.composer.text,
                image: this.composer.imagePreview,
                time: 'Just now',
                likes: 0,
                liked: false
            });
            this.composer.text = '';
            this.composer.imageFile = null;
            this.composer.imagePreview = null;
            const fileInputs = document.querySelectorAll('input[type=file]');
            fileInputs.forEach(i => i.value = null);
        }
    },
    computed: {
        canPost() {
            return (this.composer.text && this.composer.text.trim().length > 0) || this.composer.imageFile || this.composer.imagePreview;
        }
    }
}).mount('#dashboard-app');
</script>
