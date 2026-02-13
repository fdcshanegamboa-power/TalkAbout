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

<div id="dashboard-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <!-- Mobile Header -->
    <?= $this->element('mobile_header') ?>

    <!-- Top Navbar (Desktop/Tablet) -->
    <?= $this->element('top_navbar') ?>

    <!-- Main Container with proper padding for fixed navbar and bottom nav -->
    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <!-- Sidebar -->
            <?= $this->element('left_sidebar', ['active' => 'home']) ?>

            <!-- Main content (scrollable) -->
            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">

            <!-- Header - Always visible on all screens -->
            <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6">
                <div>
                    <h1 class="text-xl lg:text-2xl xl:text-3xl font-extrabold tracking-tight text-blue-700">
                        Home Feed
                    </h1>
                    <p class="text-sm text-blue-600 mt-1">
                        What's happening now
                    </p>
                </div>
            </div>

            <!-- Post composer -->
            <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6">
                <div class="flex items-start gap-3 lg:gap-4">
                    <div class="flex-1">
                        <textarea v-model="composer.text" rows="3" placeholder="What's happening?"
                                  class="w-full resize-none border-0 focus:ring-0 text-sm lg:text-base text-blue-800 placeholder-blue-400 bg-transparent"></textarea>

                        <!-- Multiple image previews -->
                        <div v-if="composer.imagePreviews.length > 0" class="mt-3 lg:mt-4 grid grid-cols-2 gap-2">
                            <div v-for="(preview, index) in composer.imagePreviews" :key="index" class="relative">
                                <img :src="preview" alt="preview" class="rounded-lg h-24 lg:h-32 w-full object-cover" />
                                <button @click="removeImage(index)" 
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 lg:mt-4 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-1 lg:gap-2 cursor-pointer text-blue-600 hover:text-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0016.586 6L13 2.414A2 2 0 0011.586 2H4z"/></svg>
                                    <span class="text-xs lg:text-sm hidden sm:inline">Add images</span>
                                    <input type="file" accept="image/*" multiple @change="onImageChange" class="hidden" ref="fileInput" />
                                </label>
                                <span v-if="composer.imageFiles.length > 0" class="text-xs text-blue-500">
                                    {{ composer.imageFiles.length }} <span class="hidden sm:inline">image{{ composer.imageFiles.length > 1 ? 's' : '' }}</span>
                                </span>
                            </div>

                            <div>
                                <button @click="createPost" :disabled="!canPost || isPosting" 
                                        class="bg-blue-600 text-white px-3 lg:px-4 py-2 rounded-lg lg:rounded-xl text-sm font-semibold hover:bg-blue-700 disabled:opacity-50">
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

    <!-- Mobile Bottom Navigation -->
    <?= $this->element('mobile_nav', ['active' => 'home']) ?>
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            userFullName: <?= json_encode($user['full_name'] ?? '') ?>,
            userName: <?= json_encode($user['username'] ?? '') ?>,
            currentUserId: <?= json_encode($user['id'] ?? null) ?>,
            composer: {
                text: '',
                imageFiles: [],
                imagePreviews: []
            },
            posts: [],
            isLoading: true,
            isPosting: false,
            // Notification data
            notifications: [],
            notificationCount: 0,
            showNotifications: false,
            notificationPolling: null
        };
    },
    mounted() {
        this.fetchPosts();
        this.fetchNotifications();
        this.startNotificationPolling();
        
        // Close notifications dropdown when clicking outside
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        this.stopNotificationPolling();
        document.removeEventListener('click', this.handleClickOutside);
    },
    methods: {
        async fetchPosts() {
            this.isLoading = true;
            try {
                const response = await fetch('/api/posts/list');
                const data = await response.json();
                if (data.success) {
                    // Initialize comment properties for each post
                    this.posts = data.posts.map(post => ({
                        ...post,
                        showComments: false,
                        commentsList: [],
                        newCommentText: '',
                        commentImageFile: null,
                        commentImagePreview: null,
                        loadingComments: false,
                        isSubmittingComment: false
                    }));
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
                    // Initialize comment properties for the new post
                    const newPost = {
                        ...data.post,
                        showComments: false,
                        commentsList: [],
                        newCommentText: '',
                        commentImageFile: null,
                        commentImagePreview: null,
                        loadingComments: false,
                        isSubmittingComment: false
                    };
                    
                    // Add new post to the top of the feed
                    this.posts.unshift(newPost);
                    
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
        },
        
        // Notification methods
        async fetchNotifications() {
            try {
                const response = await fetch('/api/notifications/unread');
                const data = await response.json();
                if (data.success) {
                    this.notifications = data.notifications;
                    this.notificationCount = data.count || 0;
                }
            } catch (error) {
                console.error('Error fetching notifications:', error);
            }
        },
        
        toggleNotifications() {
            this.showNotifications = !this.showNotifications;
        },
        
        handleClickOutside(event) {
            // Close notifications dropdown when clicking outside
            if (this.showNotifications && !event.target.closest('[data-notification-container]')) {
                this.showNotifications = false;
            }
        },
        
        async handleNotificationClick(notification) {
            // Mark as read
            if (!notification.is_read) {
                await this.markNotificationAsRead(notification.id);
            }
            
            // Navigate to the relevant post/content if needed
            // For now, just close the dropdown
            this.showNotifications = false;
        },
        
        async markNotificationAsRead(notificationId) {
            try {
                const response = await fetch(`/api/notifications/mark-as-read/${notificationId}`, {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
                    // Update notification in the list
                    const notification = this.notifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.is_read = true;
                        this.notificationCount = Math.max(0, this.notificationCount - 1);
                    }
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },
        
        async markAllAsRead() {
            try {
                const response = await fetch('/api/notifications/mark-all-as-read', {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
                    // Update all notifications
                    this.notifications.forEach(n => n.is_read = true);
                    this.notificationCount = 0;
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },
        
        async deleteNotification(notificationId) {
            try {
                const response = await fetch(`/api/notifications/delete/${notificationId}`, {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
                    // Remove from list
                    const index = this.notifications.findIndex(n => n.id === notificationId);
                    if (index !== -1) {
                        const wasUnread = !this.notifications[index].is_read;
                        this.notifications.splice(index, 1);
                        if (wasUnread) {
                            this.notificationCount = Math.max(0, this.notificationCount - 1);
                        }
                    }
                }
            } catch (error) {
                console.error('Error deleting notification:', error);
            }
        },
        
        startNotificationPolling() {
            // Poll for new notifications every 30 seconds
            this.notificationPolling = setInterval(() => {
                this.fetchNotifications();
            }, 30000);
        },
        
        stopNotificationPolling() {
            if (this.notificationPolling) {
                clearInterval(this.notificationPolling);
            }
        },
        
        formatNotificationTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffHours < 24) return `${diffHours}h ago`;
            if (diffDays < 7) return `${diffDays}d ago`;
            
            return date.toLocaleDateString();
        },
        
        // Comment methods
        async toggleComments(post) {
            post.showComments = !post.showComments;
            
            // Load comments if opening for the first time
            if (post.showComments && post.commentsList.length === 0) {
                await this.loadComments(post);
            }
        },
        
        async loadComments(post) {
            post.loadingComments = true;
            try {
                const response = await fetch(`/api/comments/list/${post.id}`);
                const data = await response.json();
                if (data.success) {
                    post.commentsList = data.comments;
                }
            } catch (error) {
                console.error('Error loading comments:', error);
            } finally {
                post.loadingComments = false;
            }
        },
        
        handleCommentImageSelect(event, post) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Image must be less than 5MB');
                return;
            }
            
            post.commentImageFile = file;
            
            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                post.commentImagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },
        
        removeCommentImage(post) {
            post.commentImageFile = null;
            post.commentImagePreview = null;
            // Reset file input
            const input = document.getElementById(`comment-image-${post.id}`);
            if (input) input.value = '';
        },
        
        async submitComment(post) {
            if (post.isSubmittingComment) return;
            if (!post.newCommentText && !post.commentImageFile) return;
            
            post.isSubmittingComment = true;
            
            try {
                const formData = new FormData();
                formData.append('post_id', post.id);
                formData.append('content_text', post.newCommentText || '');
                
                if (post.commentImageFile) {
                    formData.append('image', post.commentImageFile);
                }
                
                const response = await fetch('/api/comments/add', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Add comment to the list
                    post.commentsList.unshift(data.comment);
                    post.comments = data.comment_count;
                    
                    // Clear form
                    post.newCommentText = '';
                    post.commentImageFile = null;
                    post.commentImagePreview = null;
                    
                    // Reset file input
                    const input = document.getElementById(`comment-image-${post.id}`);
                    if (input) input.value = '';
                } else {
                    alert('Failed to post comment: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error posting comment:', error);
                alert('Failed to post comment. Please try again.');
            } finally {
                post.isSubmittingComment = false;
            }
        },
        
        async deleteComment(post, comment) {
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/comments/delete/${comment.id}`, {
                    method: 'POST'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Remove comment from list
                    const index = post.commentsList.findIndex(c => c.id === comment.id);
                    if (index !== -1) {
                        post.commentsList.splice(index, 1);
                        post.comments = Math.max(0, (post.comments || 0) - 1);
                    }
                } else {
                    alert('Failed to delete comment: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error deleting comment:', error);
                alert('Failed to delete comment. Please try again.');
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
