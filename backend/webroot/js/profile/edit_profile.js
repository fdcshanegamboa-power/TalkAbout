const { createApp } = Vue;

createApp({
    data() {
        return {
            profileUser: null, // Will hold current user's profile data
            // Notification data
            notifications: [],
            notificationCount: 0,
            showNotifications: false,
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
            if (this.showNotifications && !event.target.closest('[data-notification-container]')) {
                this.showNotifications = false;
            }
            if (this.showUserMenu && !event.target.closest('[data-user-menu]')) {
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
        
        async markNotificationAsRead(notificationId) {
            try {
                const response = await fetch(`/api/notifications/mark-as-read/${notificationId}`, {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
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
        }
    }
}).mount('#edit-profile-app');

// Profile picture preview
function previewProfilePicture(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            event.target.value = '';
            return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please upload an image file');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            const currentAvatar = document.getElementById('current-avatar');
            const initial = document.getElementById('avatar-initial');
            
            // Remove existing content
            if (currentAvatar) currentAvatar.remove();
            if (initial) initial.remove();
            
            // Add new image
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Profile Preview';
            img.className = 'w-full h-full object-cover';
            img.id = 'current-avatar';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }