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
    <div id="dashboard-app" v-cloak class="max-w-9xl mx-auto px-4 sm:px-6 flex gap-6 py-6 min-h-screen">


        <!-- Sidebar -->
        <?= $this->element('sidebar', ['active' => 'home']) ?>

        <!-- Main content (scrollable) -->
        <main class="flex-1 space-y-6 overflow-y-auto max-h-[calc(100vh-3rem)] no-scrollbar">

            <!-- Header -->
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-700">
                        Home Feed
                    </h1>
                    <p class="text-sm text-blue-600 mt-1">
                        What's happening now
                    </p>
                </div>
            </div>

            <!-- Post composer -->
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <textarea v-model="composer.text" rows="3" placeholder="What's happening?"
                                  class="w-full resize-none border-0 focus:ring-0 text-blue-800 placeholder-blue-400 bg-transparent"></textarea>

                        <!-- Multiple image previews -->
                        <div v-if="composer.imagePreviews.length > 0" class="mt-4 grid grid-cols-2 gap-2">
                            <div v-for="(preview, index) in composer.imagePreviews" :key="index" class="relative">
                                <img :src="preview" alt="preview" class="rounded-lg h-32 w-full object-cover" />
                                <button @click="removeImage(index)" 
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-2 cursor-pointer text-blue-600 hover:text-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0016.586 6L13 2.414A2 2 0 0011.586 2H4z"/></svg>
                                    <span class="text-sm">Add images</span>
                                    <input type="file" accept="image/*" multiple @change="onImageChange" class="hidden" ref="fileInput" />
                                </label>
                                <span v-if="composer.imageFiles.length > 0" class="text-xs text-blue-500">
                                    {{ composer.imageFiles.length }} image{{ composer.imageFiles.length > 1 ? 's' : '' }} selected
                                </span>
                            </div>

                            <div>
                                <button @click="createPost" :disabled="!canPost || isPosting" 
                                        class="bg-blue-600 text-white px-4 py-2 rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50">
                                    {{ isPosting ? 'Posting...' : 'Post' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feed / Posts -->
            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-blue-600 mt-2">Loading posts...</p>
            </div>

            <div v-else class="space-y-4">
                <div v-if="posts.length === 0" class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100 p-8 text-center">
                    <p class="text-blue-600">No posts yet. Be the first to share something!</p>
                </div>

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
                                    <button @click="toggleLike(post)" :class="post.liked ? 'text-indigo-600' : 'text-blue-500'" class="flex items-center gap-2 text-sm font-semibold hover:scale-110 transition">
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

                            <div v-if="post.text" class="text-blue-700 mt-1">{{ post.text }}</div>
                            <div v-if="post.about" class="text-sm text-blue-600 mt-2">{{ post.about }}</div>
                            
                            <!-- Multiple images display -->
                            <div v-if="post.images && post.images.length > 0" class="mt-3">
                                <div v-if="post.images.length === 1">
                                    <img :src="post.images[0]" class="rounded-lg max-h-96 w-full object-cover" />
                                </div>
                                <div v-else-if="post.images.length === 2" class="grid grid-cols-2 gap-2">
                                    <img v-for="(img, idx) in post.images" :key="idx" :src="img" 
                                         class="rounded-lg h-64 w-full object-cover" />
                                </div>
                                <div v-else class="grid grid-cols-2 gap-2">
                                    <img v-for="(img, idx) in post.images.slice(0, 4)" :key="idx" :src="img" 
                                         :class="idx === 3 && post.images.length > 4 ? 'relative' : ''"
                                         class="rounded-lg h-48 w-full object-cover" />
                                    <div v-if="post.images.length > 4" 
                                         class="absolute bottom-2 right-2 bg-black/70 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                        +{{ post.images.length - 4 }}
                                    </div>
                                </div>
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
            userFullName: <?= json_encode($user['full_name'] ?? '') ?>,
            userName: <?= json_encode($user['username'] ?? '') ?>,
            userAbout: <?= json_encode($user['about'] ?? '') ?>,
            composer: {
                text: '',
                imageFiles: [],
                imagePreviews: []
            },
            posts: [],
            isLoading: true,
            isPosting: false
        };
    },
    mounted() {
        this.fetchPosts();
    },
    methods: {
        async fetchPosts() {
            this.isLoading = true;
            try {
                const response = await fetch('/api/posts/list');
                const data = await response.json();
                if (data.success) {
                    this.posts = data.posts;
                }
            } catch (error) {
                console.error('Error fetching posts:', error);
            } finally {
                this.isLoading = false;
            }
        },
        
        toggleLike(post) {
            post.liked = !post.liked;
            post.likes = (post.likes || 0) + (post.liked ? 1 : -1);
            // TODO: Call API to save like
        },
        
        onImageChange(e) {
            const files = Array.from(e.target.files || []);
            if (files.length === 0) return;
            
            // Limit to 10 images
            const maxImages = 10;
            const remainingSlots = maxImages - this.composer.imageFiles.length;
            const filesToAdd = files.slice(0, remainingSlots);
            
            filesToAdd.forEach(file => {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select only image files');
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image must be less than 5MB: ' + file.name);
                    return;
                }
                
                this.composer.imageFiles.push(file);
                
                // Create preview
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.composer.imagePreviews.push(ev.target.result);
                };
                reader.readAsDataURL(file);
            });
            
            // Reset input
            e.target.value = '';
        },
        
        removeImage(index) {
            this.composer.imageFiles.splice(index, 1);
            this.composer.imagePreviews.splice(index, 1);
        },
        
        async createPost() {
            if (!this.canPost || this.isPosting) return;
            
            this.isPosting = true;
            
            try {
                const formData = new FormData();
                formData.append('content_text', this.composer.text || '');
                
                // Append all images
                this.composer.imageFiles.forEach((file, index) => {
                    formData.append('images[]', file);
                });
                
                const response = await fetch('/api/posts/create', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Add new post to the top of the feed
                    this.posts.unshift(data.post);
                    
                    // Clear composer
                    this.composer.text = '';
                    this.composer.imageFiles = [];
                    this.composer.imagePreviews = [];
                    
                    // Reset file input
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                } else {
                    alert('Failed to create post: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error creating post:', error);
                alert('Failed to create post. Please try again.');
            } finally {
                this.isPosting = false;
            }
        }
    },
    computed: {
        canPost() {
            return (this.composer.text && this.composer.text.trim().length > 0) || 
                   this.composer.imageFiles.length > 0;
        }
    }
}).mount('#dashboard-app');
</script>
