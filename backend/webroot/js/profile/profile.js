const el = document.getElementById('profile-app');

if (el && window.Vue && window.PostCardMixin) {
    const { createApp } = Vue;
    
    createApp({
        mixins: [PostCardMixin],
        data() {
            return {
                currentUserId: el.dataset.currentUserId,
                profileUser: null, // For left sidebar display
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
                    const data = await response.json();
                    if (data.success) {
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