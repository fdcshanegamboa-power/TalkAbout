/**
 * Post Composer Mixin
 * Provides composer functionality for creating posts with text and images
 */
window.PostComposerMixin = {
    mixins: [window.ModalMixin || {}],
    data() {
        return {
            composer: {
                text: '',
                imageFiles: [],
                imagePreviews: [],
                visibility: 'public'
            },
            isPosting: false,
            composerDragActive: false,
            showVisibilityMenu: false
        };
    },
    
    mounted() {
        // Close visibility menu when clicking outside
        document.addEventListener('click', this.handleClickOutside);
    },
    
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
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
        handleComposerDragOver(e) {
            this.composerDragActive = true;
        },
        
        handleComposerDragLeave(e) {
            this.composerDragActive = false;
        },
        
        handleComposerDrop(e) {
            this.composerDragActive = false;
            const files = Array.from(e.dataTransfer.files || []);
            this.processImageFiles(files);
        },
        
        onImageChange(e) {
            const files = Array.from(e.target.files || []);
            this.processImageFiles(files);
            e.target.value = '';
        },
        
        processImageFiles(files) {
            if (files.length === 0) return;
            
            const maxImages = 10;
            const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const remainingSlots = maxImages - this.composer.imageFiles.length;
            
            if (remainingSlots === 0) {
                this.showWarningModal({
                    title: 'Maximum Images Reached',
                    message: 'Maximum of 10 images reached. Remove some images to add more.'
                });
                return;
            }
            
            const filesToAdd = files.slice(0, remainingSlots);
            const skippedCount = files.length - filesToAdd.length;
            
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
            
            let message = '';
            if (hasErrors) {
                message = errors.join('\n') + '\n\nSupported formats: JPEG, PNG, GIF, WebP\nMaximum size: 5MB per image';
            }
            
            if (skippedCount > 0) {
                if (message) message += '\n\n';
                message += `${skippedCount} file${skippedCount > 1 ? 's were' : ' was'} skipped (maximum 10 images).`;
            }
            
            if (message) {
                this.showWarningModal({
                    title: 'Some Files Not Added',
                    message: message
                });
            }
        },
        
        removeImage(index) {
            this.composer.imageFiles.splice(index, 1);
            this.composer.imagePreviews.splice(index, 1);
        },
        
        toggleVisibilityMenu(event) {
            event.stopPropagation();
            this.showVisibilityMenu = !this.showVisibilityMenu;
        },
        
        setVisibility(visibility) {
            this.composer.visibility = visibility;
            this.showVisibilityMenu = false;
        },
        
        handleClickOutside(event) {
            // Close visibility menu when clicking outside
            if (this.showVisibilityMenu) {
                const target = event.target;
                const visibilityButton = target.closest('button');
                const visibilityMenu = target.closest('.absolute.left-0.mt-2.w-48');
                
                if (!visibilityButton && !visibilityMenu) {
                    this.showVisibilityMenu = false;
                }
            }
        },
        
        async createPost() {
            if (!this.canPost || this.isPosting) return;
            
            this.isPosting = true;
            
            try {
                const formData = new FormData();
                formData.append('content_text', this.composer.text || '');
                formData.append('visibility', this.composer.visibility || 'public');
                
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
                        editVisibility: data.post.visibility || 'public',
                        showEditVisibilityMenu: false,
                        isSaving: false,
                        editImages: [],
                        newEditImages: [],
                        newEditImageFiles: [],
                        imagesToDelete: [],
                        editDragActive: false,
                        commentDragActive: false
                    };
                    
                    // Add to posts array if it exists
                    if (this.posts) {
                        this.posts.unshift(newPost);
                    }
                    
                    // Clear composer
                    this.composer.text = '';
                    this.composer.imageFiles = [];
                    this.composer.imagePreviews = [];
                    this.composer.visibility = 'public';
                    
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                } else {
                    this.showErrorModal({
                        title: 'Failed to Create Post',
                        message: data.message || 'Unknown error'
                    });
                }
            } catch (error) {
                console.error('Error creating post:', error);
                this.showErrorModal({
                    title: 'Error',
                    message: 'Failed to create post. Please try again.'
                });
            } finally {
                this.isPosting = false;
            }
        }
    }
};
