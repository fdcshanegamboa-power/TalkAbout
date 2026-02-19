const el = document.getElementById('dashboard-app');

// Remove v-cloak to show content even if Vue doesn't mount
if (el) {
    el.removeAttribute('v-cloak');
}

// Debug logging
console.log('Dashboard page loaded:', {
    el: !!el,
    Vue: !!window.Vue,
    PostCardMixin: !!window.PostCardMixin,
    PostComposerMixin: !!window.PostComposerMixin,
    RightSidebarMixin: !!window.RightSidebarMixin
});

if (el && window.Vue && window.PostCardMixin && window.PostComposerMixin) {
    const { createApp } = Vue;

    const mixins = [PostCardMixin, PostComposerMixin];
    if (window.RightSidebarMixin) {
        mixins.push(RightSidebarMixin);
    }

    createApp({
        mixins: mixins,
        data() {
            return {
                profileUser: null, // For left sidebar display
                currentUserId: null,

                posts: [],
                isLoading: true,
                
                // For mobile header notifications
                notifications: [],
                notificationCount: 0,
                showNotifications: false,
                socket: null
            };
        },

        mounted() {
            this.fetchCurrentUserProfile();
            this.fetchPosts();
            this.fetchNotifications();
            this.initWebSocket();
            if (this.fetchFriends) {
                this.fetchFriends();
            }
            if (this.fetchSuggestions) {
                this.fetchSuggestions();
            }
        },
        
        beforeUnmount() {
            if (this.socket) {
                this.socket.disconnect();
            }
        },

        methods: {
            async fetchCurrentUserProfile() {
                try {
                    const response = await fetch('/api/profile/current');
                    if (!response.ok) {
                        console.error('Failed to fetch profile:', response.status);
                        return;
                    }
                    
                    const data = await response.json();
                    if (data.success) {
                        const user = data.user;
                        this.currentUserId = user.id || null;
                        this.profileUser = {
                            full_name: user.full_name || '',
                            username: user.username || '',
                            about: user.about || '',
                            profile_photo: user.profile_photo_path || '',
                            initial: (user.full_name || user.username || 'U').charAt(0).toUpperCase()
                        };
                    }
                } catch (error) {
                    console.error('Error fetching current user profile:', error);
                }
            },
            
            async fetchPosts() {
                this.isLoading = true;
                try {
                    const res = await fetch('/api/posts/list');
                    const data = await res.json();

                    if (data.success) {
                        this.posts = data.posts.map(post => ({
                            ...post,
                            showComments: false,
                            commentsList: [],
                            newCommentText: '',
                            commentImageFile: null,
                            commentImagePreview: null,
                            loadingComments: false,
                            isSubmittingComment: false,
                            showMenu: false,
                            isEditing: false,
                            editText: post.text,
                            editVisibility: post.visibility || 'public',
                            showEditVisibilityMenu: false,
                            isSaving: false,
                            editImages: [],
                            newEditImages: [],
                            newEditImageFiles: [],
                            imagesToDelete: [],
                            editDragActive: false,
                            commentDragActive: false
                        }));
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    this.isLoading = false;
                }
            },
            
            async fetchNotifications() {
                try {
                    const res = await fetch('/api/notifications');
                    const data = await res.json();
                    if (data.success) {
                        this.notifications = data.notifications || [];
                        this.notificationCount = this.notifications.filter(n => !n.is_read).length;
                    }
                } catch (e) {
                    console.error('Error fetching notifications:', e);
                }
            },
            
            initWebSocket() {
                if (!window.io) return;

                try {
                    this.socket = io(window.location.origin, {
                        path: '/socket.io',
                        transports: ['websocket', 'polling'],
                        reconnection: true
                    });

                    this.socket.on('connect', () => {
                        this.authenticateSocket();
                    });

                    this.socket.on('notification', (notification) => {
                        this.notifications.unshift(notification);
                        if (!notification.is_read) {
                            this.notificationCount++;
                        }
                    });

                    this.socket.on('notificationCount', (data) => {
                        this.notificationCount = data.count;
                    });
                } catch (error) {
                    console.error('WebSocket init error:', error);
                }
            },
            
            async authenticateSocket() {
                if (!this.socket || !this.currentUserId) return;
                this.socket.emit('authenticate', { userId: this.currentUserId });
            },
            
            toggleNotifications() {
                this.showNotifications = !this.showNotifications;
            },
            
            async handleNotificationClick(notification) {
                try {
                    if (!notification.is_read) {
                        await fetch(`/api/notifications/mark-as-read/${notification.id}`, { method: 'POST' });
                        notification.is_read = true;
                        this.notificationCount = Math.max(0, this.notificationCount - 1);
                    }

                    if (notification.type === 'friend_request') {
                        window.location.href = '/friends';
                        return;
                    }

                    if (notification.target_type === 'post' && notification.target_id) {
                        window.location.href = `/posts/view/${notification.target_id}`;
                        return;
                    }

                    if (notification.target_type === 'comment' && notification.target_id) {
                        const postId = notification.post_id || notification.target_post_id;
                        if (postId) {
                            window.location.href = `/posts/view/${postId}#comment-${notification.target_id}`;
                        }
                        return;
                    }

                    this.showNotifications = false;
                } catch (e) {
                    console.error('Error handling notification click:', e);
                }
            },
            
            async markAllAsRead() {
                try {
                    const response = await fetch('/api/notifications/mark-all-as-read', { method: 'POST' });
                    const data = await response.json();
                    if (data.success) {
                        this.notifications.forEach(n => n.is_read = true);
                        this.notificationCount = 0;
                    }
                } catch (error) {
                    console.error('Error marking all as read:', error);
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
            }
        },

        computed: {
            // Additional computed properties can go here
        }
    }).mount(el);
} else {
    console.error('Dashboard app failed to mount:', {
        el: !!el,
        Vue: !!window.Vue,
        PostCardMixin: !!window.PostCardMixin,
        PostComposerMixin: !!window.PostComposerMixin
    });
    
    // Show error message on page
    if (el) {
        const missing = [];
        if (!window.Vue) missing.push('Vue.js');
        if (!window.PostCardMixin) missing.push('PostCardMixin');
        if (!window.PostComposerMixin) missing.push('PostComposerMixin');
        
        el.innerHTML = `
            <div class="min-h-screen flex items-center justify-center p-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-2xl">
                    <h2 class="text-red-800 text-xl font-bold mb-4">Failed to load page</h2>
                    <p class="text-red-700 mb-4">The following dependencies are missing:</p>
                    <ul class="list-disc list-inside text-red-600 mb-4">
                        ${missing.map(m => `<li>${m}</li>`).join('')}
                    </ul>
                    <p class="text-red-600 text-sm">Check the browser console (F12) for more details.</p>
                    <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Reload Page
                    </button>
                </div>
            </div>
        `;
    }
}
