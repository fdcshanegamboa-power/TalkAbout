/**
 * Post Composer Mixin
 * Provides composer functionality for creating posts with text and images
 */
window.PostComposerMixin = {
    data() {
        return {
            composer: {
                text: '',
                imageFiles: [],
                imagePreviews: []
            },
            isPosting: false
        };
    },

    computed: {
        canPost() {
            return (
                this.composer.text.trim().length > 0 ||
                this.composer.imageFiles.length > 0
            );
        }
    },

    methods: {
        onImageChange(e) {
            const files = Array.from(e.target.files || []);
            if (files.length === 0) return;
            
            const maxImages = 10;
            const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
            const remainingSlots = maxImages - this.composer.imageFiles.length;
            const filesToAdd = files.slice(0, remainingSlots);
            
            let hasErrors = false;
            const errors = [];
            
            filesToAdd.forEach(file => {
                if (!file.type.startsWith('image/')) {
                    errors.push(`"${file.name}" is not an image file`);
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
                alert('Some files could not be added:\n\n' + errors.join('\n'));
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
                    
                    // Add to posts array if it exists
                    if (this.posts) {
                        this.posts.unshift(newPost);
                    }
                    
                    // Clear composer
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
    }
};
