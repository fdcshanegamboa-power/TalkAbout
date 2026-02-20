const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

const el = document.getElementById('profile-app');

// Remove v-cloak to show content even if Vue doesn't mount
if (el) {
    el.removeAttribute('v-cloak');
}

// Debug logging
console.log('Profile page loaded:', {
    el: !!el,
    Vue: !!window.Vue,
    PostCardMixin: !!window.PostCardMixin,
    PostComposerMixin: !!window.PostComposerMixin,
    LeftSidebarMixin: !!window.LeftSidebarMixin,
    RightSidebarMixin: !!window.RightSidebarMixin
});

if (el && window.Vue && window.PostCardMixin && window.PostComposerMixin) {
    const { createApp } = Vue;
    
    createApp({
        mixins: [
            ...(window.ModalMixin ? [ModalMixin] : []),
            PostCardMixin, 
            PostComposerMixin,
            ...(window.LeftSidebarMixin ? [LeftSidebarMixin] : []),
            ...(window.RightSidebarMixin ? [RightSidebarMixin] : [])
        ],
        data() {
            return {
                profileUsername: el.dataset.profileUsername,
                userId: el.dataset.userId,
                currentUserId: el.dataset.currentUserId,
                isOwnProfile: el.dataset.isOwnProfile === 'true',
                profileUser: null, // Will hold profile user data
                posts: [],
                isLoading: true,

                // Mobile header notifications
                notifications: [],
                notificationCount: 0,
                showNotifications: false,
                socket: null,

                // Friendship data
                friendshipStatus: null, // null, 'friends', 'pending_sent', 'pending_received', 'none'
                currentFriendshipId: null,
                loadingFriendshipStatus: false,
                processingFriendRequest: false
            };
        },
        mounted() {
            console.log('Profile app mounted');
            console.log('Profile username:', this.profileUsername);
            console.log('User ID (profile being viewed):', this.userId);
            console.log('Current user ID:', this.currentUserId);
            console.log('Is own profile:', this.isOwnProfile);
            console.log('LeftSidebarMixin available:', !!window.LeftSidebarMixin);
            console.log('RightSidebarMixin available:', !!window.RightSidebarMixin);
            console.log('fetchFriends method:', typeof this.fetchFriends);
            console.log('fetchSuggestions method:', typeof this.fetchSuggestions);
            
            // Fetch the profile being viewed
            this.fetchProfileUser();
            this.fetchUserPosts();
            this.fetchNotifications();
            this.initWebSocket();
            
            // Fetch current user profile for left sidebar (from LeftSidebarMixin)
            if (this.fetchCurrentUserProfile) {
                console.log('Calling fetchCurrentUserProfile for left sidebar...');
                this.fetchCurrentUserProfile();
            }
            
            if (this.fetchFriends) {
                console.log('Calling fetchFriends...');
                this.fetchFriends();
            }  else {
                console.warn('fetchFriends method not available!');
            }
            if (this.fetchSuggestions) {
                console.log('Calling fetchSuggestions...');
                this.fetchSuggestions();
            } else {
                console.warn('fetchSuggestions method not available!');
            }
            if (!this.isOwnProfile && this.userId && this.userId !== '0') {
                console.log('Fetching friendship status for user:', this.userId);
                this.fetchFriendshipStatus();
            } else {
                console.log('Skipping friendship status fetch:', {
                    isOwnProfile: this.isOwnProfile,
                    userId: this.userId
                });
            }
            // Close menu when clicking outside
            document.addEventListener('click', this.closeAllMenus);
        },
        beforeUnmount() {
            document.removeEventListener('click', this.closeAllMenus);
            if (this.socket) {
                this.socket.disconnect();
            }
        },
        methods: {
            async fetchProfileUser() {
                try {
                    const url = `/api/profile/user/${this.profileUsername}`;
                    console.log('Fetching profile from:', url);
                    const response = await fetch(url);
                    console.log('Response status:', response.status);
                    
                    if (!response.ok) {
                        console.error('Response not OK:', response.status, response.statusText);
                        // Set a fallback profile so the page doesn't hang
                        this.profileUser = {
                            full_name: '',
                            username: this.profileUsername,
                            about: '',
                            profile_photo: '',
                            initial: (this.profileUsername || 'U').charAt(0).toUpperCase()
                        };
                        return;
                    }
                    
                    const data = await response.json();
                    console.log('Profile response:', data);
                    
                    if (data.success) {
                        this.profileUser = {
                            full_name: data.user.full_name || '',
                            username: data.user.username || '',
                            about: data.user.about || '',
                            profile_photo: data.user.profile_photo_path || '',
                            initial: data.user.initial || (data.user.full_name || data.user.username || 'U').charAt(0).toUpperCase()
                        };
                    } else {
                        console.error('Failed to fetch profile:', data.message);
                        this.profileUser = {
                            full_name: '',
                            username: this.profileUsername,
                            about: '',
                            profile_photo: '',
                            initial: (this.profileUsername || 'U').charAt(0).toUpperCase()
                        };
                    }
                } catch (error) {
                    console.error('Error fetching profile:', error);
                    this.profileUser = {
                        full_name: '',
                        username: this.profileUsername,
                        about: '',
                        profile_photo: '',
                        initial: (this.profileUsername || 'U').charAt(0).toUpperCase()
                    };
                }
            },
            
            async fetchUserPosts() {
                this.isLoading = true;
                try {
                    const url = `/api/posts/user/${this.profileUsername}`;
                    console.log('Fetching posts from:', url);
                    const response = await fetch(url);
                    const data = await response.json();
                    console.log('Posts response:', data);
                    
                    if (data.success) {
                        // Initialize additional properties for each post
                        this.posts = data.posts.map(post => ({
                            ...post,
                            showMenu: false,
                            isEditing: false,
                            editText: post.text,
                            editVisibility: post.visibility || 'public',
                            showEditVisibilityMenu: false,
                            isSaving: false,
                            showComments: false,
                            commentsList: [],
                            newCommentText: '',
                            commentImageFile: null,
                            commentImagePreview: null,
                            loadingComments: false,
                            isSubmittingComment: false,
                            // Image editing properties
                            editImages: [],
                            newEditImages: [],
                            newEditImageFiles: [],
                            imagesToDelete: [],
                            editDragActive: false,
                            commentDragActive: false
                        }));
                        console.log('Processed posts:', this.posts.length);
                    } else {
                        console.error('Failed to fetch posts:', data.message);
                    }
                } catch (error) {
                    console.error('Error fetching posts:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            async fetchFriendshipStatus() {
                if (this.isOwnProfile || !this.userId) return;
                
                this.loadingFriendshipStatus = true;
                try {
                    const response = await fetch(`/api/friendships/status/${this.userId}`);
                    const data = await response.json();

                    console.log('Friendship status response:', data);

                    if (data.success) {
                        // Determine the status based on direction
                        if (data.status === 'pending') {
                            // If current user sent the request
                            this.friendshipStatus = data.is_requester ? 'pending_sent' : 'pending_received';
                        } else if (data.status === 'accepted') {
                            this.friendshipStatus = 'friends';
                        } else if (data.status === 'none') {
                            this.friendshipStatus = 'none';
                        } else {
                            this.friendshipStatus = data.status || 'none';
                        }
                        this.currentFriendshipId = data.friendship_id;
                        console.log('Friendship status set to:', this.friendshipStatus, 'ID:', this.currentFriendshipId);
                    } else {
                        console.error('Failed to fetch friendship status:', data.message);
                        this.friendshipStatus = 'none';
                        this.currentFriendshipId = null;
                    }
                } catch (error) {
                    console.error('Error fetching friendship status:', error);
                    this.friendshipStatus = 'none';
                    this.currentFriendshipId = null;
                } finally {
                    this.loadingFriendshipStatus = false;
                }
            },

            async sendFriendRequest() {
                if (this.isOwnProfile || !this.userId || this.processingFriendRequest) return;

                this.processingFriendRequest = true;
                try {
                    const response = await fetch('/api/friendships/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            addressee_id: parseInt(this.userId)
                        })
                    });

                    const data = await response.json();

                    console.log('Send friend request response:', data);

                    if (data.success) {
                        this.friendshipStatus = 'pending_sent';
                        this.currentFriendshipId = data.friendship_id;
                        console.log('Friend request sent, ID:', data.friendship_id);
                        this.showSuccessModal({
                            title: 'Friend Request Sent',
                            message: 'Friend request sent!'
                        });
                    } else {
                        console.error('Failed to send friend request:', data);
                        this.showErrorModal({
                            title: 'Failed to Send Friend Request',
                            message: data.message || 'Unknown error'
                        });
                    }
                } catch (error) {
                    console.error('Error sending friend request:', error);
                    this.showErrorModal({
                        title: 'Error',
                        message: 'Failed to send friend request. Please try again.'
                    });
                } finally {
                    this.processingFriendRequest = false;
                }
            },

            async cancelFriendRequest() {
                if (this.isOwnProfile || !this.userId || this.processingFriendRequest) return;

                if (!this.currentFriendshipId) {
                    console.error('No friendship ID available for cancellation');
                    // Re-fetch the friendship status to get the ID
                    await this.fetchFriendshipStatus();
                    if (!this.currentFriendshipId) {
                        this.showErrorModal({
                            title: 'Error',
                            message: 'Could not find the friend request to cancel. Please refresh the page.'
                        });
                        return;
                    }
                }

                this.processingFriendRequest = true;
                try {
                    const response = await fetch('/api/friendships/cancel', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            friendship_id: this.currentFriendshipId
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.friendshipStatus = 'none';
                        this.currentFriendshipId = null;
                    } else {
                        this.showErrorModal({
                            title: 'Failed to Cancel Friend Request',
                            message: data.message || 'Unknown error'
                        });
                    }
                } catch (error) {
                    console.error('Error cancelling friend request:', error);
                    this.showErrorModal({
                        title: 'Error',
                        message: 'Failed to cancel friend request. Please try again.'
                    });
                } finally {
                    this.processingFriendRequest = false;
                }
            },

            async acceptFriendRequest() {
                if (this.isOwnProfile || !this.userId || this.processingFriendRequest) return;

                if (!this.currentFriendshipId) {
                    console.error('No friendship ID available for acceptance');
                    // Re-fetch the friendship status to get the ID
                    await this.fetchFriendshipStatus();
                    if (!this.currentFriendshipId) {
                        this.showErrorModal({
                            title: 'Error',
                            message: 'Could not find the friend request to accept. Please refresh the page.'
                        });
                        return;
                    }
                }

                this.processingFriendRequest = true;
                try {
                    const response = await fetch('/api/friendships/accept', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            friendship_id: this.currentFriendshipId
                        })
                    });

                    const data = await response.json();

                    console.log('Accept friend request response:', data);

                    if (data.success) {
                        this.friendshipStatus = 'friends';
                        this.currentFriendshipId = null;
                        console.log('Friend request accepted, refreshing friends list');
                        this.showSuccessModal({
                            title: 'Friend Request Accepted',
                            message: 'Friend request accepted!'
                        });
                        // Refresh friends list if available
                        if (this.fetchFriends) {
                            console.log('Calling fetchFriends()');
                            await this.fetchFriends();
                        } else {
                            console.warn('fetchFriends method not available');
                        }
                    } else {
                        console.error('Failed to accept friend request:', data);
                        this.showErrorModal({
                            title: 'Failed to Accept Friend Request',
                            message: data.message || 'Unknown error'
                        });
                    }
                } catch (error) {
                    console.error('Error accepting friend request:', error);
                    this.showErrorModal({
                        title: 'Error',
                        message: 'Failed to accept friend request. Please try again.'
                    });
                } finally {
                    this.processingFriendRequest = false;
                }
            },

            async rejectFriendRequest() {
                if (this.isOwnProfile || !this.userId || this.processingFriendRequest) return;

                if (!this.currentFriendshipId) {
                    console.error('No friendship ID available for rejection');
                    // Re-fetch the friendship status to get the ID
                    await this.fetchFriendshipStatus();
                    if (!this.currentFriendshipId) {
                        this.showErrorModal({
                            title: 'Error',
                            message: 'Could not find the friend request to reject. Please refresh the page.'
                        });
                        return;
                    }
                }

                this.processingFriendRequest = true;
                try {
                    const response = await fetch('/api/friendships/reject', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            friendship_id: this.currentFriendshipId
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.friendshipStatus = 'none';
                        this.currentFriendshipId = null;
                    } else {
                        this.showErrorModal({
                            title: 'Failed to Reject Friend Request',
                            message: data.message || 'Unknown error'
                        });
                    }
                } catch (error) {
                    console.error('Error rejecting friend request:', error);
                    this.showErrorModal({
                        title: 'Error',
                        message: 'Failed to reject friend request. Please try again.'
                    });
                } finally {
                    this.processingFriendRequest = false;
                }
            },

            async unfriend() {
                if (this.isOwnProfile || !this.userId || this.processingFriendRequest) return;

                const confirmed = await this.showConfirmModal({
                    title: 'Unfriend User',
                    message: 'Are you sure you want to unfriend this user?',
                    confirmText: 'Unfriend',
                    cancelText: 'Cancel'
                });
                
                if (!confirmed) return;

                this.processingFriendRequest = true;
                try {
                    const response = await fetch('/api/friendships/unfriend', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            friend_id: parseInt(this.userId)
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.friendshipStatus = 'none';
                        this.currentFriendshipId = null;
                        // Refresh friends list if available
                        if (this.fetchFriends) {
                            this.fetchFriends();
                        }
                    } else {
                        this.showErrorModal({
                            title: 'Failed to Unfriend',
                            message: data.message || 'Unknown error'
                        });
                    }
                } catch (error) {
                    console.error('Error unfriending:', error);
                    this.showErrorModal({
                        title: 'Error',
                        message: 'Failed to unfriend. Please try again.'
                    });
                } finally {
                    this.processingFriendRequest = false;
                }
            },
            
            // Override createPost to refresh user posts after creating
            async createPost() {
                if (!this.canPost || this.isPosting) return;
                
                this.isPosting = true;
                
                try {
                    const formData = new FormData();
                    formData.append('content_text', this.composer.text || '');
                    
                    this.composer.imageFiles.forEach((file, index) => {
                        formData.append('images[]', file);
                    });
                    
                    const response = await fetch('/api/posts/create', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Clear composer
                        this.composer.text = '';
                        this.composer.imageFiles = [];
                        this.composer.imagePreviews = [];
                        
                        if (this.$refs.fileInput) {
                            this.$refs.fileInput.value = '';
                        }
                        
                        // Refresh user posts
                        await this.fetchUserPosts();
                    } else {
                        this.showErrorModal({
                            title: 'Failed to Create Post',
                            message: data.message || 'Unknown error'
                        });
                    }
                } catch (error) {
                    console.error('Error creating post:', error);
                    this.showErrorModal({
                        title: 'Error',
                        message: 'Failed to create post. Please try again.'
                    });
                } finally {
                    this.isPosting = false;
                }
            },
            
            // Notification methods for mobile header
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
        }
    }).mount('#profile-app');
} else {
    console.error('Profile app failed to mount:', {
        el: !!el,
        Vue: !!window.Vue,
        PostCardMixin: !!window.PostCardMixin,
        PostComposerMixin: !!window.PostComposerMixin
    });
    
    if (el) {
        const missing = [];
        if (!window.Vue) missing.push('Vue.js');
        if (!window.PostCardMixin) missing.push('PostCardMixin');
        if (!window.PostComposerMixin) missing.push('PostComposerMixin');
        
        el.innerHTML = '<div class="min-h-screen flex items-center justify-center p-4"><div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-2xl"><h2 class="text-red-800 text-xl font-bold mb-4">Failed to load profile page</h2><p class="text-red-700 mb-4">Missing: ' + missing.join(', ') + '</p><button onclick="location.reload()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Reload Page</button></div></div>';
    }
}
