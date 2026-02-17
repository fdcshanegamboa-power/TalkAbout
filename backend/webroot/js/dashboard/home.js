const el = document.getElementById('dashboard-app');

if (el && window.Vue && window.PostCardMixin) {
    const { createApp } = Vue;

    createApp({
        mixins: [PostCardMixin],
        data() {
            return {
                profileUser: null, // For left sidebar display
                currentUserId: null,

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
            this.fetchPosts();
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
                            isSaving: false,
                            editImages: [],
                            newEditImages: [],
                            newEditImageFiles: [],
                            imagesToDelete: []
                        }));
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    this.isLoading = false;
                }
            },

            onImageChange(e) {
                const files = Array.from(e.target.files || []);
                if (files.length === 0) return;
                
                const maxImages = 10;
                const remainingSlots = maxImages - this.composer.imageFiles.length;
                const filesToAdd = files.slice(0, remainingSlots);
                
                filesToAdd.forEach(file => {
                    if (!file.type.startsWith('image/')) {
                        alert('Please select only image files');
                        return;
                    }
                    
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Image must be less than 5MB: ' + file.name);
                        return;
                    }
                    
                    this.composer.imageFiles.push(file);
                    
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        this.composer.imagePreviews.push(ev.target.result);
                    };
                    reader.readAsDataURL(file);
                });
                
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
                    
                    this.composer.imageFiles.forEach((file, index) => {
                        formData.append('images[]', file);
                    });
                    
                    const response = await fetch('/api/posts/create', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
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
                            isSaving: false,
                            editImages: [],
                            newEditImages: [],
                            newEditImageFiles: [],
                            imagesToDelete: []
                        };
                        this.posts.unshift(newPost);
                        
                        this.composer.text = '';
                        this.composer.imageFiles = [];
                        this.composer.imagePreviews = [];
                        
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
                return (
                    this.composer.text.trim().length > 0 ||
                    this.composer.imageFiles.length > 0
                );
            }
        }
    }).mount(el);
} else {
    console.error('Dashboard app failed to mount:', {
        el: !!el,
        Vue: !!window.Vue,
        PostCardMixin: !!window.PostCardMixin
    });
}
