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
                    sidebarUser: null,
                    notifications: [],
                    notificationCount: 0,
                    showNotifications: false,
                    showUserMenu: false,
                    socket: null,
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
                this.initWebSocket();
                document.addEventListener('click', this.handleClickOutside);
            },


            beforeUnmount() {
                this.disconnectWebSocket();
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
                            this.sidebarUser = {
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

                initWebSocket() {
                    // Check if Socket.io is available
                    if (!window.io) {
                        console.error('Socket.io client not loaded');
                        return;
                    }

                    try {
                        // Connect to WebSocket server (through nginx proxy)
                        this.socket = io(window.location.origin, {
                            path: '/socket.io',
                            transports: ['websocket', 'polling'],
                            reconnection: true,
                            reconnectionDelay: 1000,
                            reconnectionAttempts: 5
                        });

                        // Wait for connection then authenticate
                        this.socket.on('connect', () => {
                            console.log('[WebSocket] Connected');
                            this.authenticateSocket();
                        });

                        // Handle authentication success
                        this.socket.on('authenticated', (data) => {
                            console.log('[WebSocket] Authenticated:', data);
                        });

                        // Handle authentication errors
                        this.socket.on('authError', (data) => {
                            console.error('[WebSocket] Auth error:', data);
                        });

                        // Listen for new notifications
                        this.socket.on('notification', (notification) => {
                            console.log('[WebSocket] New notification:', notification);
                            this.handleNewNotification(notification);
                        });

                        // Listen for notification count updates
                        this.socket.on('notificationCount', (data) => {
                            console.log('[WebSocket] Notification count update:', data.count);
                            this.notificationCount = data.count;
                        });

                        // Handle disconnection
                        this.socket.on('disconnect', () => {
                            console.log('[WebSocket] Disconnected');
                        });

                        // Handle reconnection
                        this.socket.on('reconnect', (attemptNumber) => {
                            console.log('[WebSocket] Reconnected after', attemptNumber, 'attempts');
                            this.authenticateSocket();
                        });

                    } catch (error) {
                        console.error('[WebSocket] Init error:', error);
                    }
                },

                async authenticateSocket() {
                    if (!this.socket) return;

                    try {
                        // Get current user ID from profile
                        const response = await fetch('/api/profile/current');
                        if (!response.ok) return;
                        
                        const data = await response.json();
                        if (data.success && data.user && data.user.id) {
                            // Authenticate the socket connection with user ID
                            this.socket.emit('authenticate', { userId: data.user.id });
                        }
                    } catch (error) {
                        console.error('[WebSocket] Auth error:', error);
                    }
                },

                handleNewNotification(notification) {
                    // Add notification to the beginning of the list
                    this.notifications.unshift(notification);
                    
                    // Update count if unread
                    if (!notification.is_read) {
                        this.notificationCount++;
                    }

                    // Optional: Show browser notification
                    this.showBrowserNotification(notification);
                },

                showBrowserNotification(notification) {
                    if (!('Notification' in window)) return;
                    if (Notification.permission !== 'granted') return;

                    const actorName = notification.actor?.full_name || notification.actor?.username || 'Someone';
                    let title = '';
                    let body = '';

                    switch (notification.type) {
                        case 'post_liked':
                            title = 'New Like';
                            body = `${actorName} liked your post`;
                            break;
                        case 'post_commented':
                            title = 'New Comment';
                            body = `${actorName} commented on your post`;
                            break;
                        case 'comment_liked':
                            title = 'New Like';
                            body = `${actorName} liked your comment`;
                            break;
                        case 'friend_request':
                            title = 'Friend Request';
                            body = `${actorName} sent you a friend request`;
                            break;
                        default:
                            title = 'New Notification';
                            body = notification.message || 'You have a new notification';
                    }

                    try {
                        new Notification(title, { body, icon: '/logo/telupuluh-05.jpg' });
                    } catch (e) {
                        console.log('Notification permission:', e);
                    }
                },

                requestNotificationPermission() {
                    if (!('Notification' in window)) return;
                    if (Notification.permission === 'default') {
                        Notification.requestPermission();
                    }
                },

                disconnectWebSocket() {
                    if (this.socket) {
                        this.socket.disconnect();
                        this.socket = null;
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
    } else if (!navbarEl) {
        // Navbar element not found - this is normal for login/register pages
        console.log('Navbar element not found - skipping navbar initialization');
    } else {
        console.error('Cannot initialize navbar Vue app - Vue.js not available');
    }
});

