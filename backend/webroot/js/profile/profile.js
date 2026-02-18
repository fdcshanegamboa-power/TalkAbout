const el = document.getElementById('profile-app');

if (el && window.Vue && window.PostCardMixin) {
    const { createApp } = Vue;
    
    createApp({
        // Prefer the global window.PostCardMixin (safe when scripts load in different scopes)
        mixins: [window.PostCardMixin || PostCardMixin],
        data() {
            return {
                currentUserId: el.dataset.currentUserId,
                profileUser: null, // For left sidebar display
                // Notification state (some templates reference these)
                notifications: [],
                notificationCount: 0,
                showNotifications: false,
                composer: {
                    text: '',
                    imageFiles: [],
                    imagePreviews: []
                },
                posts: [],
                isLoading: true,
                isPosting: false
            };
        },
        mounted() {
            console.info('Profile app mounting');
            this.fetchCurrentUserProfile();
            this.fetchUserPosts();
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
                            imagesToDelete: []
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
            
            onImageChange(e) {
                const files = Array.from(e.target.files || []);
                if (files.length === 0) return;
                
                const maxImages = 10;
                const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                const remainingSlots = maxImages - this.composer.imageFiles.length;
                const filesToAdd = files.slice(0, remainingSlots);
                
                let hasErrors = false;
                const errors = [];
                
                filesToAdd.forEach(file => {
                    if (!allowedTypes.includes(file.type)) {
                        const typeName = file.type || 'unknown type';
                        errors.push(`"${file.name}" (${typeName}) is not supported`);
                        hasErrors = true;
                        return;
                    }
                    
                    if (file.size > maxFileSize) {
                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        errors.push(`"${file.name}" (${fileSizeMB}MB) exceeds the 5MB limit`);
                        hasErrors = true;
                        return;
                    }
                    
                    if (file.size === 0) {
                        errors.push(`"${file.name}" is empty`);
                        hasErrors = true;
                        return;
                    }
                    
                    this.composer.imageFiles.push(file);
                    
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        this.composer.imagePreviews.push(ev.target.result);
                    };
                    reader.readAsDataURL(file);
                });
                
                if (hasErrors) {
                    alert('Some files could not be added:\n\n' + errors.join('\n') + '\n\nSupported formats: JPEG, PNG, GIF, WebP\nMaximum size: 5MB per image');
                }
                
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
                        // Add new post to the top of the feed with edit properties
                        const newPost = {
                            ...data.post,
                            showMenu: false,
                            isEditing: false,
                            editText: data.post.text,
                            isSaving: false,
                            showComments: false,
                            commentsList: [],
                            newCommentText: '',
                            commentImageFile: null,
                            commentImagePreview: null,
                            loadingComments: false,
                            isSubmittingComment: false
                        };
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
            }
            ,
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