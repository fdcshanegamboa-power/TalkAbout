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
        <?= $this->element('left_sidebar', ['active' => 'home']) ?>

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

                <div v-for="post in posts" :key="post.id">
                    <?= $this->element('post_card', ['canEdit' => false]) ?>
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
            // Store previous state in case we need to revert
            const wasLiked = post.liked;
            const previousLikes = post.likes;
            
            // Optimistically update UI
            post.liked = !post.liked;
            post.likes = post.liked ? previousLikes + 1 : previousLikes - 1;
            
            // Call API
            const endpoint = post.liked ? '/api/posts/like' : '/api/posts/unlike';
            
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    post_id: post.id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update with actual count from server
                    post.likes = data.likes;
                } else {
                    // Revert on error
                    post.liked = wasLiked;
                    post.likes = previousLikes;
                    console.error('Failed to toggle like:', data.message);
                }
            })
            .catch(error => {
                // Revert on error
                post.liked = wasLiked;
                post.likes = previousLikes;
                console.error('Error toggling like:', error);
            });
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
