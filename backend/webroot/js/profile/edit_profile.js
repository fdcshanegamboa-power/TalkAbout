const { createApp } = Vue;

createApp({
    mixins: [
        ...(window.RightSidebarMixin ? [window.RightSidebarMixin] : [])
    ],
    data() {
        return {
            profileUser: null, // For left sidebar display
            currentUserId: null,
            
            // Mobile header notifications
            notifications: [],
            notificationCount: 0,
            showNotifications: false,
            socket: null
        };
    },
    mounted() {
        this.fetchCurrentUserProfile();
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
                    if (this.currentUserId) {
                        this.socket.emit('authenticate', { userId: this.currentUserId });
                    }
                });
                this.socket.on('notification', (notification) => {
                    this.notifications.unshift(notification);
                    if (!notification.is_read) this.notificationCount++;
                });
                this.socket.on('notificationCount', (data) => {
                    this.notificationCount = data.count;
                });
            } catch (error) {
                console.error('WebSocket init error:', error);
            }
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
                } else if (notification.target_type === 'post' && notification.target_id) {
                    window.location.href = `/posts/view/${notification.target_id}`;
                } else {
                    this.showNotifications = false;
                }
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
}).mount('#edit-profile-app');

// Load Cropper.js (CSS + JS) dynamically if needed
function loadCropper() {
    if (window.Cropper) return Promise.resolve();
    return new Promise((resolve, reject) => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css';
        document.head.appendChild(link);

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load Cropper.js'));
        document.body.appendChild(script);
    });
}

// Modal markup is provided in the PHP template; we use that element directly.

// Profile picture crop flow
async function previewProfilePicture(event) {
    const fileInput = event.target;
    const file = fileInput.files && fileInput.files[0];
    if (!file) return;

    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        alert('File size must be less than 5MB');
        fileInput.value = '';
        return;
    }

    // Validate file type
    if (!file.type.startsWith('image/')) {
        alert('Please upload an image file');
        fileInput.value = '';
        return;
    }

    try {
        await loadCropper();
    } catch (err) {
        console.error(err);
        alert('Failed to load image editor. Try again later.');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const modal = document.getElementById('cropper-modal');
        if (!modal) {
            alert('Crop modal not found in template');
            return;
        }
        const img = document.getElementById('cropper-image');
        img.src = e.target.result;
        modal.classList.remove('hidden');

        let cropper = new Cropper(img, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 1,
            movable: true,
            zoomable: true,
            background: false
        });

        const confirm = document.getElementById('cropper-confirm');
        const cancel = document.getElementById('cropper-cancel');

        const cleanup = () => {
            try { cropper.destroy(); } catch (e) {}
            modal.classList.add('hidden');
            confirm.removeEventListener('click', onConfirm);
            cancel.removeEventListener('click', onCancel);
        };

        const onConfirm = () => {
            cropper.getCroppedCanvas({ width: 400, height: 400, imageSmoothingQuality: 'high' }).toBlob((blob) => {
                if (!blob) {
                    alert('Failed to crop image');
                    return;
                }

                const newFile = new File([blob], file.name || 'profile.png', { type: blob.type });
                const dt = new DataTransfer();
                dt.items.add(newFile);
                fileInput.files = dt.files;

                // Update preview
                const preview = document.getElementById('avatar-preview');
                const currentAvatar = document.getElementById('current-avatar');
                const initial = document.getElementById('avatar-initial');
                if (currentAvatar) currentAvatar.remove();
                if (initial) initial.remove();

                const imgEl = document.createElement('img');
                imgEl.src = URL.createObjectURL(blob);
                imgEl.alt = 'Profile Preview';
                imgEl.className = 'w-full h-full object-cover';
                imgEl.id = 'current-avatar';
                preview.appendChild(imgEl);

                cleanup();
            }, 'image/png');
        };

        const onCancel = () => {
            fileInput.value = '';
            cleanup();
        };

        confirm.addEventListener('click', onConfirm);
        cancel.addEventListener('click', onCancel);
    };
    reader.readAsDataURL(file);
}