const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

const el = document.getElementById('dashboard-app');

if (el && window.Vue) {
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                userFullName: el.dataset.userFullName || '',
                userName: el.dataset.userName || '',
                currentUserId: Number(el.dataset.currentUserId) || null,

                composer: {
                    text: '',
                    imageFiles: [],
                    imagePreviews: []
                },

                posts: [],
                isLoading: true,
                isPosting: false,

                notifications: [],
                notificationCount: 0,
                showNotifications: false,
                notificationPolling: null
            };
        },

        mounted() {
            this.fetchPosts();
            this.fetchNotifications();
            this.startNotificationPolling();
            document.addEventListener('click', this.handleClickOutside);
        },

        beforeUnmount() {
            this.stopNotificationPolling();
            document.removeEventListener('click', this.handleClickOutside);
        },

        methods: {
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
                            isSaving: false
                        }));
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    this.isLoading = false;
                }
            },

            // Post creation methods
            onImageChange(e) {
                const files = Array.from(e.target.files || []);
                if (files.length === 0) return;
                
                // Limit to 10 images
                const maxImages = 10;
                const remainingSlots = maxImages - this.composer.imageFiles.length;
                const filesToAdd = files.slice(0, remainingSlots);
                
                filesToAdd.forEach(file => {
                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        alert('Please select only image files');
                        return;
                    }
                    
                    // Validate file size (5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Image must be less than 5MB: ' + file.name);
                        return;
                    }
                    
                    this.composer.imageFiles.push(file);
                    
                    // Create preview
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        this.composer.imagePreviews.push(ev.target.result);
                    };
                    reader.readAsDataURL(file);
                });
                
                // Reset input
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
                        // Add new post to the top of the feed
                        const newPost = {
                            ...data.post,
                            showComments: false,
                            commentsList: [],
                            newCommentText: '',
                            commentImageFile: null,
                            commentImagePreview: null,
                            loadingComments: false,
                            isSubmittingComment: false,
                            showMenu: false,
                            isEditing: false,
                            editText: data.post.text,
                            isSaving: false
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
            },

            // Post editing methods
            toggleMenu(post) {
                event.stopPropagation();
                // Close other menus
                this.posts.forEach(p => {
                    if (p.id !== post.id) {
                        p.showMenu = false;
                    }
                });
                post.showMenu = !post.showMenu;
            },
            
            startEdit(post) {
                post.editText = post.text;
                post.isEditing = true;
                post.showMenu = false;
                
                // Initialize image editing data
                post.editImages = post.images ? post.images.map((path, idx) => ({
                    path: path,
                    originalIndex: idx
                })) : [];
                post.newEditImages = [];
                post.newEditImageFiles = [];
                post.imagesToDelete = [];
            },
            
            cancelEdit(post) {
                post.isEditing = false;
                post.editText = post.text;
                
                // Clear image editing data
                post.editImages = [];
                post.newEditImages = [];
                post.newEditImageFiles = [];
                post.imagesToDelete = [];
            },
            
            removeExistingImage(post, index) {
                const removedImage = post.editImages[index];
                post.imagesToDelete.push(removedImage.path);
                post.editImages.splice(index, 1);
            },
            
            removeNewEditImage(post, index) {
                post.newEditImages.splice(index, 1);
                post.newEditImageFiles.splice(index, 1);
            },
            
            handleEditImageSelect(event, post) {
                const files = Array.from(event.target.files);
                
                files.forEach(file => {
                    if (file && file.type.startsWith('image/')) {
                        post.newEditImageFiles.push(file);
                        
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            post.newEditImages.push({
                                preview: e.target.result,
                                file: file
                            });
                        };
                        reader.readAsDataURL(file);
                    }
                });
                
                // Reset input
                event.target.value = '';
            },
            
            async saveEdit(post) {
                if (!post.editText || post.editText.trim() === '') {
                    alert('Post content cannot be empty');
                    return;
                }
                
                post.isSaving = true;
                
                try {
                    const formData = new FormData();
                    formData.append('post_id', post.id);
                    formData.append('content_text', post.editText);
                    
                    // Add images to delete
                    if (post.imagesToDelete && post.imagesToDelete.length > 0) {
                        formData.append('images_to_delete', JSON.stringify(post.imagesToDelete));
                    }
                    
                    // Add new images
                    if (post.newEditImageFiles && post.newEditImageFiles.length > 0) {
                        post.newEditImageFiles.forEach((file, index) => {
                            formData.append('new_images[]', file);
                        });
                    }
                    
                    const response = await fetch('/api/posts/update', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        post.text = post.editText;
                        post.isEditing = false;
                        
                        // Update images if returned from server
                        if (data.post && data.post.images) {
                            post.images = data.post.images;
                        }
                        
                        // Clear editing data
                        post.editImages = [];
                        post.newEditImages = [];
                        post.newEditImageFiles = [];
                        post.imagesToDelete = [];
                    } else {
                        alert('Failed to update post: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error updating post:', error);
                    alert('Failed to update post. Please try again.');
                } finally {
                    post.isSaving = false;
                }
            },
            
            async deletePost(post) {
                if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                    return;
                }
                
                post.showMenu = false;
                
                try {
                    const response = await fetch('/api/posts/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            post_id: post.id
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove post from array
                        const index = this.posts.findIndex(p => p.id === post.id);
                        if (index > -1) {
                            this.posts.splice(index, 1);
                        }
                    } else {
                        alert('Failed to delete post: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error deleting post:', error);
                    alert('Failed to delete post. Please try again.');
                }
            },

            toggleLike(post) {
                const wasLiked = post.liked;
                const prevLikes = post.likes;

                post.liked = !post.liked;
                post.likes += post.liked ? 1 : -1;

                fetch(post.liked ? '/api/posts/like' : '/api/posts/unlike', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ post_id: post.id })
                })
                .then(r => r.json())
                .then(d => {
                    if (!d.success) {
                        post.liked = wasLiked;
                        post.likes = prevLikes;
                    }
                })
                .catch(() => {
                    post.liked = wasLiked;
                    post.likes = prevLikes;
                });
            },

            // Comment methods
            async toggleComments(post) {
                post.showComments = !post.showComments;
                
                // Load comments if opening for the first time
                if (post.showComments && post.commentsList.length === 0) {
                    await this.loadComments(post);
                }
            },
            
            async loadComments(post) {
                post.loadingComments = true;
                try {
                    const response = await fetch(`/api/comments/list/${post.id}`);
                    const data = await response.json();
                    if (data.success) {
                        post.commentsList = data.comments;
                    }
                } catch (error) {
                    console.error('Error loading comments:', error);
                } finally {
                    post.loadingComments = false;
                }
            },
            
            handleCommentImageSelect(event, post) {
                const file = event.target.files[0];
                if (!file) return;
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file');
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image must be less than 5MB');
                    return;
                }
                
                post.commentImageFile = file;
                
                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    post.commentImagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            
            removeCommentImage(post) {
                post.commentImageFile = null;
                post.commentImagePreview = null;
                // Reset file input
                const input = document.getElementById(`comment-image-${post.id}`);
                if (input) input.value = '';
            },
            
            async submitComment(post) {
                if (post.isSubmittingComment) return;
                if (!post.newCommentText && !post.commentImageFile) return;
                
                post.isSubmittingComment = true;
                
                try {
                    const formData = new FormData();
                    formData.append('post_id', post.id);
                    formData.append('content_text', post.newCommentText || '');
                    
                    if (post.commentImageFile) {
                        formData.append('image', post.commentImageFile);
                    }
                    
                    const response = await fetch('/api/comments/add', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Add comment to the list
                        post.commentsList.unshift(data.comment);
                        post.comments = data.comment_count;
                        
                        // Clear form
                        post.newCommentText = '';
                        post.commentImageFile = null;
                        post.commentImagePreview = null;
                        
                        // Reset file input
                        const input = document.getElementById(`comment-image-${post.id}`);
                        if (input) input.value = '';
                    } else {
                        alert('Failed to post comment: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error posting comment:', error);
                    alert('Failed to post comment. Please try again.');
                } finally {
                    post.isSubmittingComment = false;
                }
            },
            
            async deleteComment(post, comment) {
                if (!confirm('Are you sure you want to delete this comment?')) {
                    return;
                }
                
                try {
                    const response = await fetch(`/api/comments/delete/${comment.id}`, {
                        method: 'POST'
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove comment from list
                        const index = post.commentsList.findIndex(c => c.id === comment.id);
                        if (index !== -1) {
                            post.commentsList.splice(index, 1);
                            post.comments = Math.max(0, (post.comments || 0) - 1);
                        }
                    } else {
                        alert('Failed to delete comment: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error deleting comment:', error);
                    alert('Failed to delete comment. Please try again.');
                }
            },

            viewProfile(username) {
                if (!username) return;
                window.location.href =
                    username === this.userName
                        ? '/profile'
                        : `/profile/${username}`;
            },

            startNotificationPolling() {
                this.notificationPolling = setInterval(
                    this.fetchNotifications,
                    30000
                );
            },

            stopNotificationPolling() {
                if (this.notificationPolling) {
                    clearInterval(this.notificationPolling);
                }
            },

            async fetchNotifications() {
                try {
                    const res = await fetch('/api/notifications/unread');
                    const data = await res.json();
                    if (data.success) {
                        this.notifications = data.notifications;
                        this.notificationCount = data.count || 0;
                    }
                } catch (e) {
                    console.error(e);
                }
            },

            toggleNotifications() {
                this.showNotifications = !this.showNotifications;
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
                if (
                    this.showNotifications &&
                    !e.target.closest('[data-notification-container]')
                ) {
                    this.showNotifications = false;
                }
            }
        },

        computed: {
            canPost() {
                return (
                    this.composer.text.trim().length > 0 ||
                    this.composer.imageFiles.length > 0
                );
            }
        }
    }).mount(el);
}
