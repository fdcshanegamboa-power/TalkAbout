const el = document.getElementById('profile-app');

if (el && window.Vue && window.PostCardMixin && window.PostComposerMixin) {
    const { createApp } = Vue;
    
    createApp({
        // Prefer the global window.PostCardMixin (safe when scripts load in different scopes)
        mixins: [
            window.PostCardMixin || PostCardMixin, 
            window.PostComposerMixin || PostComposerMixin,
            ...(window.RightSidebarMixin ? [window.RightSidebarMixin] : [])
        ],
        data() {
            return {
                currentUserId: el.dataset.currentUserId,
                profileUser: null, // For left sidebar display
                // Notification state (some templates reference these)
                notifications: [],
                notificationCount: 0,
                showNotifications: false,
                posts: [],
                isLoading: true
            };
        },
        mounted() {
            console.info('Profile app mounting');
            this.fetchCurrentUserProfile();
            this.fetchUserPosts();
            if (this.fetchFriends) {
                this.fetchFriends();
            }
            if (this.fetchSuggestions) {
                this.fetchSuggestions();
            }
            // Close menu when clicking outside
            document.addEventListener('click', this.closeAllMenus);
        },
        beforeUnmount() {
            document.removeEventListener('click', this.closeAllMenus);
        },
        methods: {
            async fetchCurrentUserProfile() {
                try {
                    const response = await fetch('/api/profile/current');
                    if (!response.ok) {
                        console.error('Failed to fetch profile:', response.status);
                        // Set a fallback profile
                        this.profileUser = {
                            full_name: '',
                            username: '',
                            about: '',
                            profile_photo: '',
                            initial: 'U'
                        };
                        return;
                    }
                    
                    const data = await response.json();
                    if (data.success) {
                        const user = data.user;
                        this.profileUser = {
                            full_name: user.full_name || '',
                            username: user.username || '',
                            about: user.about || '',
                            profile_photo: user.profile_photo_path || '',
                            initial: (user.full_name || user.username || 'U').charAt(0).toUpperCase()
                        };

                        // If the page is at /profile (no username), update the URL to include the username
                        try {
                            const currentPath = window.location.pathname.replace(/\/+$/, '');
                            if ((currentPath === '/profile' || currentPath === '/profile') && this.profileUser.username) {
                                const newPath = `/profile/${this.profileUser.username}`;
                                window.history.replaceState(null, '', newPath);
                            }
                        } catch (e) {
                            console.error('Failed to update profile URL:', e);
                        }
                    }
                } catch (error) {
                    console.error('Error fetching current user profile:', error);
                    // Set a fallback profile
                    this.profileUser = {
                        full_name: '',
                        username: '',
                        about: '',
                        profile_photo: '',
                        initial: 'U'
                    };
                }
            },
            
            async fetchUserPosts() {
                this.isLoading = true;
                try {
                    const response = await fetch('/api/posts/user');
                    const text = await response.text();

                    let data = null;
                    try {
                        data = text ? JSON.parse(text) : null;
                    } catch (e) {
                        console.error('Failed to parse /api/posts/user response as JSON:', e, text);
                        // show the raw response in console for debugging and stop further processing
                        this.isLoading = false;
                        return;
                    }

                    if (data && data.success) {
                        this.posts = data.posts.map(post => ({
                            ...post,
                            showMenu: false,
                            isEditing: false,
                            editText: post.text,
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
                    } else if (data && data.success === false) {
                        console.error('API responded with error for /api/posts/user:', data.message || data);
                    }
                } catch (error) {
                    console.error('Error fetching posts:', error);
                } finally {
                    this.isLoading = false;
                }
            },
            
            isLongText(text) {
                if (!text) return false;
                // heuristics: long by character count or multiple paragraphs
                return text.length > 250 || text.split('\n').length > 3;
            }
            ,
            toggleNotifications() {
                this.showNotifications = !this.showNotifications;
            },

            async markAllAsRead() {
                try {
                    await fetch('/api/notifications/mark-all-as-read', { method: 'POST' });
                    this.notifications.forEach(n => n.is_read = true);
                    this.notificationCount = 0;
                } catch (e) {
                    console.error('Failed to mark notifications as read', e);
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
            canPost() {
                return (this.composer.text && this.composer.text.trim().length > 0) || 
                       this.composer.imageFiles.length > 0;
            }
        }
    }).mount('#profile-app');
} else {
    console.error('Profile app failed to mount:', {
        el: !!el,
        Vue: !!window.Vue,
        PostCardMixin: !!window.PostCardMixin
    });
}