document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - Starting navbar initialization');
    const navbarEl = document.getElementById('navbar-app');
    console.log('navbar-app element:', navbarEl);
    console.log('Vue available:', !!window.Vue);

    if (navbarEl && window.Vue) {
        console.log('Initializing Vue navbar app...');
        const { createApp } = Vue;

        const app = createApp({
            data() {
                return {
                    profileUser: null,
                    notifications: [],
                    notificationCount: 0,
                    showNotifications: false,
                    showUserMenu: false,
                    notificationPolling: null,
                    searchQuery: '',
                    searchResults: { users: [], posts: [] },
                    showSearchResults: false,
                    searchLoading: false,
                    searchTimeout: null
                };
            },

            mounted() {
                console.log('Vue navbar app mounted successfully');
                this.fetchCurrentUserProfile();
                this.fetchNotifications();
                this.startNotificationPolling();
                document.addEventListener('click', this.handleClickOutside);
            },


            beforeUnmount() {
                this.stopNotificationPolling();
                document.removeEventListener('click', this.handleClickOutside);
            },

            methods: {
                async fetchCurrentUserProfile() {
                    try {
                        const response = await fetch('/api/profile/current');
                        if (!response.ok) return;
                        
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
                        }
                    } catch (error) {
                        console.error('Error fetching profile:', error);
                    }
                },

                async fetchNotifications() {
                    try {
                        const res = await fetch('/api/notifications');
                        const data = await res.json();
                        if (data.success) {
                            this.notifications = data.notifications;
                            this.notificationCount = (this.notifications || []).filter(n => !n.is_read).length;
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                startNotificationPolling() {
                    this.notificationPolling = setInterval(() => {
                        this.fetchNotifications();
                    }, 1000);
                },

                stopNotificationPolling() {
                    if (this.notificationPolling) {
                        clearInterval(this.notificationPolling);
                    }
                },

                toggleNotifications() {
                    this.showNotifications = !this.showNotifications;
                },

                toggleUserMenu() {
                    this.showUserMenu = !this.showUserMenu;
                },

                async handleNotificationClick(notification) {
    try {
        if (!notification.is_read) {
            this.markNotificationAsRead(notification.id);

            notification.is_read = true;
            this.notificationCount = Math.max(0, this.notificationCount - 1);
        }

        // Friend request notification - redirect to friends page
        if (notification.type === 'friend_request') {
            window.location.href = '/friends';
            return;
        }

        if (notification.target_type === 'post' && notification.target_id) {
            window.location.href = `/posts/view/${notification.target_id}`;
            return;
        }

        if (notification.target_type === 'comment' && notification.target_id) {
            const postId = notification.post_id || notification.target_post_id || null;
            if (postId) {
                window.location.href = `/posts/view/${postId}#comment-${notification.target_id}`;
                return;
            }

            window.location.href = `/posts/view/${notification.target_id}`;
            return;
        }

        if (notification.target_type === 'user') {
            if (notification.actor?.username) {
                window.location.href = `/profile/${notification.actor.username}`;
            } else {
                window.location.href = `/profile`;
            }
            return;
        }

        if (notification.url) {
            window.location.href = notification.url;
            return;
        }

        this.showNotifications = false;
    } catch (e) {
        console.error('Error handling notification click:', e);
        this.showNotifications = false;
    }
},


                async markNotificationAsRead(notificationId) {
    try {
        await fetch(`/api/notifications/mark-as-read/${notificationId}`, {
            method: 'POST'
        });
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
                            this.notifications.forEach(n => n.is_read = true);
                            this.notificationCount = 0;
                            await this.fetchNotifications();
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
                },

                handleClickOutside(e) {
                    if (this.showNotifications && !e.target.closest('[data-notification-container]')) {
                        this.showNotifications = false;
                    }
                    if (this.showUserMenu && !e.target.closest('[data-user-menu]')) {
                        this.showUserMenu = false;
                    }
                    if (this.showSearchResults && !e.target.closest('[data-search-container]')) {
                        this.showSearchResults = false;
                    }
                },

                handleCreatePost() {
    const composer = document.querySelector('#post-composer');

    if (composer) {
        composer.focus();
        composer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    window.location.href = "/dashboard#compose";
},

                handleSearch() {
                    if (this.searchTimeout) {
                        clearTimeout(this.searchTimeout);
                    }

                    if (this.searchQuery.trim().length < 2) {
                        this.searchResults = { users: [], posts: [] };
                        return;
                    }

                    this.searchLoading = true;
                    this.searchTimeout = setTimeout(() => {
                        this.performSearch();
                    }, 300);
                },

                async performSearch() {
                    try {
                        const response = await fetch(`/api/search?q=${encodeURIComponent(this.searchQuery)}`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.searchResults = {
                                users: data.users || [],
                                posts: data.posts || []
                            };
                        }
                    } catch (error) {
                        console.error('Search error:', error);
                        this.searchResults = { users: [], posts: [] };
                    } finally {
                        this.searchLoading = false;
                    }
                },

            }
        });
        
        app.mount(navbarEl);
        console.log('Vue navbar app mount completed');
    } else {
        console.error('Cannot initialize navbar Vue app:', {
            navbarEl: !!navbarEl,
            vueAvailable: !!window.Vue
        });
    }
});
