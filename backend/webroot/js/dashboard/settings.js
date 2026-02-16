(() => {
    const el = document.getElementById('settings-app');
    if (!el) return;

    const { createApp } = Vue;

    createApp({
        data() {
            return {
                profileUser: null, // Will hold current user's profile data
                notifications: [],
                notificationCount: 0,
                showNotifications: false,
                showUserMenu: false,
                notificationPolling: null
            };
        },

        mounted() {
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
                    if (!response.ok) {
                        console.error('Failed to fetch profile:', response.status);
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
                    }
                } catch (error) {
                    console.error('Error fetching current user profile:', error);
                }
            },
            
            async fetchNotifications() {
                try {
                    const response = await fetch('/api/notifications/unread');
                    const data = await response.json();
                    if (data.success) {
                        this.notifications = data.notifications;
                        this.notificationCount = data.count || 0;
                    }
                } catch (err) {
                    console.error('Notification fetch failed:', err);
                }
            },

            toggleNotifications() {
                this.showNotifications = !this.showNotifications;
            },

            handleClickOutside(event) {
                if (
                    this.showNotifications &&
                    !event.target.closest('[data-notification-container]')
                ) {
                    this.showNotifications = false;
                }
                if (
                    this.showUserMenu &&
                    !event.target.closest('[data-user-menu]')
                ) {
                    this.showUserMenu = false;
                }
            },
            
            toggleUserMenu() {
                this.showUserMenu = !this.showUserMenu;
            },

            async handleNotificationClick(notification) {
                if (!notification.is_read) {
                    await this.markNotificationAsRead(notification.id);
                }
                this.showNotifications = false;
            },

            async markNotificationAsRead(id) {
                try {
                    const res = await fetch(`/api/notifications/mark-as-read/${id}`, {
                        method: 'POST'
                    });
                    const data = await res.json();
                    if (data.success) {
                        const n = this.notifications.find(n => n.id === id);
                        if (n) {
                            n.is_read = true;
                            this.notificationCount = Math.max(0, this.notificationCount - 1);
                        }
                    }
                } catch (err) {
                    console.error(err);
                }
            },

            async markAllAsRead() {
                try {
                    const res = await fetch('/api/notifications/mark-all-as-read', {
                        method: 'POST'
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.notifications.forEach(n => (n.is_read = true));
                        this.notificationCount = 0;
                    }
                } catch (err) {
                    console.error(err);
                }
            },

            async deleteNotification(id) {
                try {
                    const res = await fetch(`/api/notifications/delete/${id}`, {
                        method: 'POST'
                    });
                    const data = await res.json();
                    if (data.success) {
                        const i = this.notifications.findIndex(n => n.id === id);
                        if (i !== -1) {
                            const wasUnread = !this.notifications[i].is_read;
                            this.notifications.splice(i, 1);
                            if (wasUnread) {
                                this.notificationCount = Math.max(0, this.notificationCount - 1);
                            }
                        }
                    }
                } catch (err) {
                    console.error(err);
                }
            },

            startNotificationPolling() {
                this.notificationPolling = setInterval(
                    () => this.fetchNotifications(),
                    30000
                );
            },

            stopNotificationPolling() {
                if (this.notificationPolling) {
                    clearInterval(this.notificationPolling);
                }
            },

            formatNotificationTime(ts) {
                const d = new Date(ts);
                const now = new Date();
                const diff = now - d;

                const m = Math.floor(diff / 60000);
                const h = Math.floor(diff / 3600000);
                const days = Math.floor(diff / 86400000);

                if (m < 1) return 'Just now';
                if (m < 60) return `${m}m ago`;
                if (h < 24) return `${h}h ago`;
                if (days < 7) return `${days}d ago`;

                return d.toLocaleDateString();
            }
        }
    }).mount(el);
})();
