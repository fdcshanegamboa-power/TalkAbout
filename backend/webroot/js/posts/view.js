document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('post-view-app');

    if (el && window.Vue && window.PostCardMixin) {
        const { createApp } = Vue;

        const mixins = [PostCardMixin];
        if (window.LeftSidebarMixin) {
            mixins.push(window.LeftSidebarMixin);
        }
        if (window.RightSidebarMixin) {
            mixins.push(window.RightSidebarMixin);
        }

        createApp({
            mixins: mixins,
            data() {
                return {
                    post: window.postViewData || {
                        id: null,
                        commentsList: [],
                        images: []
                    },
                    // Mobile header notifications support
                    notifications: [],
                    notificationCount: 0,
                    showNotifications: false
                };
            },

            mounted() {
                this.fetchCurrentUserProfile();
                
                // Fetch friends and suggestions for right sidebar
                if (this.fetchFriends) {
                    this.fetchFriends();
                }
                if (this.fetchSuggestions) {
                    this.fetchSuggestions();
                }
                
                // Compute and show relative time for the post
                if (this.post) {
                    if (this.post.created_at) {
                        this.post.time = this.formatRelativeTime(this.post.created_at);
                    }

                    // Ensure commentsList is always an array
                    if (!Array.isArray(this.post.commentsList)) {
                        this.post.commentsList = [];
                    }

                    if (this.post.commentsList.length > 0) {
                        this.post.commentsList.forEach(c => {
                            if (c && c.created_at) c.time = this.formatRelativeTime(c.created_at);
                        });
                    }

                    // update post and comment relative times every minute
                    setInterval(() => {
                        if (this.post && this.post.created_at) {
                            this.post.time = this.formatRelativeTime(this.post.created_at);
                        }
                        if (this.post && Array.isArray(this.post.commentsList)) {
                            this.post.commentsList.forEach(c => {
                                if (c && c.created_at) c.time = this.formatRelativeTime(c.created_at);
                            });
                        }
                    }, 60000);
                }
                
                // Check if URL has a comment anchor and if that comment exists
                this.checkCommentAnchor();
            },

            methods: {
            checkCommentAnchor() {
                // Check if there's a comment hash in the URL
                const hash = window.location.hash;
                if (hash && hash.startsWith('#comment-')) {
                    const commentId = hash.replace('#comment-', '');
                    
                    // Wait a bit for the DOM to render
                    setTimeout(() => {
                        const commentElement = document.getElementById('comment-' + commentId);
                        
                        if (!commentElement) {
                            // Comment doesn't exist - show notification
                            this.showCommentNotFoundAlert();
                        } else {
                            // Comment exists - scroll to it
                            commentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            commentElement.classList.add('highlight-comment');
                            setTimeout(() => {
                                commentElement.classList.remove('highlight-comment');
                            }, 2000);
                        }
                    }, 500);
                }
            },
            
            showCommentNotFoundAlert() {
                // Create a toast notification
                const toast = document.createElement('div');
                toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 z-50 bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 animate-fade-in';
                toast.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-semibold">Comment not found - it may have been deleted</span>
                `;
                document.body.appendChild(toast);
                
                // Remove after 5 seconds
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.5s';
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 500);
                }, 5000);
            },
            
            // Notification methods for mobile header
            toggleNotifications() {
                this.showNotifications = !this.showNotifications;
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
            
            async handleNotificationClick(notification) {
                if (notification.type === 'friend_request') {
                    window.location.href = '/friends';
                } else if (notification.target_type === 'post' && notification.target_id) {
                    window.location.href = `/posts/view/${notification.target_id}`;
                } else if (notification.url) {
                    window.location.href = notification.url;
                }
            },
            
            async markAllAsRead() {
                // Stub for mobile header compatibility
                console.log('Mark all as read');
            },
            
            async deleteNotification(id) {
                // Stub for mobile header compatibility
                console.log('Delete notification', id);
            }
        }
    }).mount(el);
    } else {
        console.error('Post view app failed to mount:', {
            el: !!el,
            Vue: !!window.Vue,
            postViewData: !!window.postViewData,
            PostCardMixin: !!window.PostCardMixin
        });
        
        // Show error message to user
        if (el) {
            el.innerHTML = `
                <div class="min-h-screen flex items-center justify-center p-4">
                    <div class="bg-white/90 backdrop-blur rounded-xl shadow-lg border border-red-200 p-8 max-w-md text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Failed to Load Page</h2>
                        <p class="text-gray-600 mb-4">There was an error loading the content. Please try refreshing the page.</p>
                        <button onclick="window.location.reload()" class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors">
                            Refresh Page
                        </button>
                    </div>
                </div>
            `;
        }
    }
});