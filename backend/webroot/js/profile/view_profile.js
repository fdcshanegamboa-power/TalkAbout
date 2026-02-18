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
    PostComposerMixin: !!window.PostComposerMixin
});

if (el && window.Vue && window.PostCardMixin && window.PostComposerMixin) {
    const { createApp } = Vue;
    
    createApp({
        mixins: [
            PostCardMixin, 
            PostComposerMixin,
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
            
            this.fetchProfileUser();
            this.fetchUserPosts();
            if (this.fetchFriends) {
                this.fetchFriends();
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
                            isSaving: false,
                            showComments: false,
                            loadingComments: false,
                            commentsList: [],
                            newCommentText: '',
                            commentImageFile: null,
                            commentImagePreview: null,
                            isSubmittingComment: false,
                            // Edit images
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
                        alert('Friend request sent!');
                    } else {
                        console.error('Failed to send friend request:', data);
                        alert('Failed to send friend request: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error sending friend request:', error);
                    alert('Failed to send friend request. Please try again.');
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
                        alert('Could not find the friend request to cancel. Please refresh the page.');
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
                        alert('Failed to cancel friend request: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error cancelling friend request:', error);
                    alert('Failed to cancel friend request. Please try again.');
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
                        alert('Could not find the friend request to accept. Please refresh the page.');
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
                        alert('Friend request accepted!');
                        // Refresh friends list if available
                        if (this.fetchFriends) {
                            console.log('Calling fetchFriends()');
                            await this.fetchFriends();
                        } else {
                            console.warn('fetchFriends method not available');
                        }
                    } else {
                        console.error('Failed to accept friend request:', data);
                        alert('Failed to accept friend request: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error accepting friend request:', error);
                    alert('Failed to accept friend request. Please try again.');
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
                        alert('Could not find the friend request to reject. Please refresh the page.');
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
                        alert('Failed to reject friend request: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error rejecting friend request:', error);
                    alert('Failed to reject friend request. Please try again.');
                } finally {
                    this.processingFriendRequest = false;
                }
            },

            async unfriend() {
                if (this.isOwnProfile || !this.userId || this.processingFriendRequest) return;

                if (!confirm('Are you sure you want to unfriend this user?')) return;

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
                        alert('Failed to unfriend: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error unfriending:', error);
                    alert('Failed to unfriend. Please try again.');
                } finally {
                    this.processingFriendRequest = false;
                }
            },
            
            closeAllMenus() {
                this.posts.forEach(post => {
                    post.showMenu = false;
                });
            },
            
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
                this.processEditImageFiles(files, post);
                event.target.value = '';
            },
            
            handleEditDragOver(post) {
                if (!post.editDragActive) {
                    post.editDragActive = true;
                }
            },
            
            handleEditDragLeave(post) {
                post.editDragActive = false;
            },
            
            handleEditDrop(event, post) {
                post.editDragActive = false;
                const files = Array.from(event.dataTransfer.files || []);
                this.processEditImageFiles(files, post);
            },
            
            processEditImageFiles(files, post) {
                const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                let hasErrors = false;
                const errors = [];
                
                files.forEach(file => {
                    if (!allowedTypes.includes(file.type)) {
                        const typeName = file.type || 'unknown type';
                        errors.push(`"${file.name}" (${typeName}) is not supported`);
                        hasErrors = true;
                        return;
                    }
                    
                    if (file.size === 0) {
                        errors.push(`"${file.name}" is empty`);
                        hasErrors = true;
                        return;
                    }
                    
                    if (file.size > maxFileSize) {
                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        errors.push(`"${file.name}" (${fileSizeMB}MB) exceeds the 5MB limit`);
                        hasErrors = true;
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        post.newEditImages.push({
                            preview: e.target.result
                        });
                    };
                    reader.readAsDataURL(file);
                    post.newEditImageFiles.push(file);
                });
                
                if (hasErrors) {
                    alert('Some files could not be added:\n\n' + errors.join('\n') + '\n\nSupported formats: JPEG, PNG, GIF, WebP\nMaximum size: 5MB per image');
                }
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
                        post.imagesToDelete.forEach(img => formData.append('images_to_delete[]', img));
                    }
                    
                    // Add new images
                    if (post.newEditImageFiles && post.newEditImageFiles.length > 0) {
                        post.newEditImageFiles.forEach((file, index) => {
                            formData.append(`new_images[${index}]`, file);
                        });
                    }
                    
                    const response = await fetch('/api/posts/update', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Update post in UI with proper Vue reactivity
                        post.text = post.editText;
                        // Backend returns images in data.post.images
                        post.images = data.post?.images || [];
                        post.isEditing = false;
                        post.expanded = false;
                        post.time = data.post?.time || post.time; // Update timestamp if provided

                        // Clear editing data
                        post.editImages = [];
                        post.newEditImages = [];
                        post.newEditImageFiles = [];
                        post.imagesToDelete = [];
                        
                        // Force Vue reactivity update
                        if (this.$forceUpdate) {
                            this.$forceUpdate();
                        }
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
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            post_id: post.id
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove post from list
                        const index = this.posts.findIndex(p => p.id === post.id);
                        if (index > -1) {
                            this.posts.splice(index, 1);
                        }
                    } else {
                        alert(data.message || 'Failed to delete post');
                    }
                } catch (error) {
                    console.error('Error deleting post:', error);
                    alert('Failed to delete post. Please try again.');
                }
            },
            
            toggleLike(post) {
                // Store previous state in case we need to revert
                const wasLiked = post.liked;
                const previousLikes = post.likes;
                
                // Optimistically update UI
                post.liked = !post.liked;
                post.likes = post.liked ? previousLikes + 1 : previousLikes - 1;
                
                // Call API
                const endpoint = post.liked ? '/api/posts/like' : '/api/posts/unlike';
                
                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        post_id: post.id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update with actual count from server
                        post.likes = data.likes;
                    } else {
                        // Revert on failure
                        post.liked = wasLiked;
                        post.likes = previousLikes;
                    }
                })
                .catch(error => {
                    // Revert on error
                    post.liked = wasLiked;
                    post.likes = previousLikes;
                    console.error('Error toggling like:', error);
                });
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
                        alert('Failed to create post: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error creating post:', error);
                    alert('Failed to create post. Please try again.');
                } finally {
                    this.isPosting = false;
                }
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
                        // server expects the field name 'image'
                        formData.append('image', post.commentImageFile);
                    }
                    
                    const response = await fetch('/api/comments/add', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Clear comment form
                        post.newCommentText = '';
                        post.commentImageFile = null;
                        post.commentImagePreview = null;
                        
                        // Reload comments
                        await this.loadComments(post);
                        
                        // Update comment count
                        post.comments = (post.comments || 0) + 1;
                        
                        // Reset file input
                        const input = document.getElementById(`comment-image-${post.id}`);
                        if (input) input.value = '';
                    } else {
                        alert(data.message || 'Failed to post comment');
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
                        if (index > -1) {
                            post.commentsList.splice(index, 1);
                            post.comments = Math.max(0, (post.comments || 0) - 1);
                        }
                    } else {
                        alert(data.message || 'Failed to delete comment');
                    }
                } catch (error) {
                    console.error('Error deleting comment:', error);
                    alert('Failed to delete comment. Please try again.');
                }
            },
            
            viewProfile(username) {
                if (!username) return;
                window.location.href = `/profile/${username}`;
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
