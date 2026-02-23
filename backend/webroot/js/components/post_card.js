

window.PostCardMixin = {
    mixins: [window.ModalMixin || {}],
    data() {
        return {
            currentUserId: (typeof window !== 'undefined' && window.currentUserId) ? window.currentUserId : null,
            csrfToken: (typeof document !== 'undefined') ? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') : null,
            imageModal: {
                show: false,
                images: [],
                currentIndex: 0
            }
        };
    },
    methods: {
        toggleLike(post) {
            const wasLiked = post.liked;
            const prevLikes = post.likes;

            post.liked = !post.liked;
            post.likes += post.liked ? 1 : -1;

            const url = post.liked ? '/api/posts/like' : '/api/posts/unlike';

            const form = new FormData();
            form.append('post_id', post.id);
            if (this.csrfToken) form.append('_csrfToken', this.csrfToken);

            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                body: form
            })
                .then(async (r) => {
                    const text = await r.text().catch(() => '');
                    let json = null;
                    try {
                        json = text ? JSON.parse(text) : null;
                    } catch (e) {
                        console.error('Failed to parse response:', text);
                    }

                    if (!r.ok) {
                        console.error('Like request failed:', r.status, text);
                        post.liked = wasLiked;
                        post.likes = prevLikes;
                        return;
                    }

                    const d = json || {};
                    if (!d.success) {
                        console.error('Like action unsuccessful:', d);
                        post.liked = wasLiked;
                        post.likes = prevLikes;
                    }
                })
                .catch((err) => {
                    console.error('Network error liking post:', err);
                    post.liked = wasLiked;
                    post.likes = prevLikes;
                });
        },

        toggleComments(post) {
            post.showComments = !post.showComments;

            if (post.showComments && (!post.commentsList || post.commentsList.length === 0)) {
                this.loadComments(post);
            }
        },

        async loadComments(post) {
            post.loadingComments = true;
            try {
                const response = await fetch(`/api/comments/list/${post.id}`);
                const data = await response.json();
                if (data.success) {
                    post.commentsList = (data.comments || []).map(c => ({ ...c, expanded: false }));
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

            this.processCommentImageFile(file, post);
            event.target.value = '';
        },

        handleCommentDragOver(post) {
            if (!post.commentDragActive) {
                post.commentDragActive = true;
            }
        },

        handleCommentDragLeave(post) {
            post.commentDragActive = false;
        },

        handleCommentDrop(event, post) {
            post.commentDragActive = false;
            const files = event.dataTransfer.files;
            if (files && files.length > 0) {
                this.processCommentImageFile(files[0], post);
            }
        },

        processCommentImageFile(file, post) {
            const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!allowedTypes.includes(file.type)) {
                const typeName = file.type || 'unknown type';
                this.showErrorModal({
                    title: 'Unsupported File Type',
                    message: `File "${file.name}" (${typeName}) is not supported.\n\nSupported formats: JPEG, PNG, GIF, WebP`
                });
                return;
            }

            if (file.size === 0) {
                this.showErrorModal({
                    title: 'Invalid File',
                    message: 'The selected file is empty. Please choose a valid image.'
                });
                return;
            }

            if (file.size > maxFileSize) {
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                this.showErrorModal({
                    title: 'File Too Large',
                    message: `Image "${file.name}" is too large (${fileSizeMB}MB).\n\nMaximum allowed size: 5MB`
                });
                return;
            }

            post.commentImageFile = file;

            const reader = new FileReader();
            reader.onload = (e) => {
                post.commentImagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        triggerCommentFileInput(post) {
            const refName = 'comment-image-' + post.id;
            const input = this.$refs[refName];

            // Vue returns an array of refs when used inside v-for
            const element = Array.isArray(input) ? input[0] : input;

            if (element) {
                element.click();
            } else {
                console.error(
                    `Ref ${refName} not found. Check your input ref attribute.`
                );
            }
        },

        removeCommentImage(post) {
            post.commentImageFile = null;
            post.commentImagePreview = null;
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
                    // Add new comment to the list and initialize expansion state
                    if (!post.commentsList) post.commentsList = [];
                    const newComment = { ...(data.comment || {}), expanded: false };
                    post.commentsList.unshift(newComment);

                    // Update comment count
                    post.comments = data.comment_count;

                    // Clear input
                    post.newCommentText = '';
                    this.removeCommentImage(post);
                } else {
                    this.showErrorModal({
                        title: 'Failed to Post Comment',
                        message: data.message || 'Unknown error'
                    });
                }
            } catch (error) {
                console.error('Error posting comment:', error);
                this.showErrorModal({
                    title: 'Error',
                    message: 'Failed to post comment. Please try again.'
                });
            } finally {
                post.isSubmittingComment = false;
            }
        },

        isLongText(text) {
            if (!text) return false;
            // heuristics: long by character count or multiple paragraphs
            return text.length > 250 || text.split('\n').length > 3;
        },

        expandComment(comment) {
            if (!comment) return;
            comment.expanded = true;
        },

        collapseComment(comment) {
            if (!comment) return;
            comment.expanded = false;
        },

        toggleMenu(post) {
            event.stopPropagation();
            // Close other menus if posts array exists
            if (this.posts) {
                this.posts.forEach(p => {
                    if (p.id !== post.id) {
                        p.showMenu = false;
                    }
                });
            }
            post.showMenu = !post.showMenu;
        },

        startEdit(post) {
            post.editText = post.text;
            post.editVisibility = post.visibility || 'public';
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
                this.showErrorModal({
                    title: 'Some Files Not Added',
                    message: errors.join('\n') + '\n\nSupported formats: JPEG, PNG, GIF, WebP\nMaximum size: 5MB per image'
                });
            }
        },

        async saveEdit(post) {
            // Check if there's at least some content (text or images)
            const hasText = post.editText && post.editText.trim().length > 0;
            const hasExistingImages = (post.editImages && post.editImages.length > 0) || false;
            const hasNewImages = (post.newEditImageFiles && post.newEditImageFiles.length > 0) || false;

            // Allow saving if there's text OR any images (existing or new)
            if (!hasText && !hasExistingImages && !hasNewImages) {
                this.showErrorModal({
                    title: 'Empty Post',
                    message: 'Post must have at least some text or images'
                });
                return;
            }

            post.isSaving = true;

            try {
                const formData = new FormData();
                formData.append('post_id', post.id);
                formData.append('content_text', post.editText || ''); // Allow empty text if there are images
                formData.append('visibility', post.editVisibility || 'public');

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
                    post.visibility = post.editVisibility;
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
                    this.showErrorModal({
                        title: 'Failed to Update Post',
                        message: data.message || 'Unknown error'
                    });
                }
            } catch (error) {
                console.error('Error updating post:', error);
                this.showErrorModal({
                    title: 'Error',
                    message: 'Failed to update post. Please try again.'
                });
            } finally {
                post.isSaving = false;
            }
        },

        expandPost(post) {
            if (!post) return;
            post.expanded = true;
        },

        collapsePost(post) {
            if (!post) return;
            post.expanded = false;
        },

        async deletePost(post) {
            const confirmed = await this.showConfirmModal({
                title: 'Delete Post',
                message: 'Are you sure you want to delete this post? This action cannot be undone.',
                confirmText: 'Delete',
                cancelText: 'Cancel'
            });

            if (!confirmed) {
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

                    if (this.posts) {
                        const index = this.posts.findIndex(p => p.id === post.id);
                        if (index !== -1) {
                            this.posts.splice(index, 1);
                        }
                    }

                    if (this.post && this.post.id === post.id) {
                        window.location.href = '/dashboard';
                    }
                } else {
                    this.showErrorModal({
                        title: 'Failed to Delete Post',
                        message: data.message || 'Unknown error'
                    });
                }
            } catch (error) {
                console.error('Error deleting post:', error);
                this.showErrorModal({
                    title: 'Error',
                    message: 'Failed to delete post. Please try again.'
                });
            }
        },

        formatRelativeTime(isoString) {
            try {
                const t = Date.parse(isoString);
                if (isNaN(t)) return isoString;
                const now = Date.now();
                let diff = Math.floor((now - t) / 1000); // seconds
                if (diff < 5) return 'just now';
                if (diff < 60) return diff + 's ago';
                diff = Math.floor(diff / 60); // minutes
                if (diff < 60) return diff + 'm ago';
                diff = Math.floor(diff / 60); // hours
                if (diff < 24) return diff + 'h ago';
                diff = Math.floor(diff / 24); // days
                if (diff < 30) return diff + 'd ago';
                if (diff < 365) return Math.floor(diff / 30) + 'mo ago';
                return Math.floor(diff / 365) + 'y ago';
            } catch (e) {
                return isoString;
            }
        },

        viewProfile(username) {
            if (!username) return;
            window.location.href = `/profile/${username}`;
        },

        toggleCommentLike(comment) {
            const wasLiked = comment.liked;
            const prevLikes = comment.likes;

            // Optimistic UI update
            comment.liked = !comment.liked;
            comment.likes += comment.liked ? 1 : -1;

            const url = comment.liked ? '/api/comments/like' : '/api/comments/unlike';

            // Use FormData to avoid preflight
            const form = new FormData();
            form.append('comment_id', comment.id);
            if (this.csrfToken) form.append('_csrfToken', this.csrfToken);

            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                body: form
            })
                .then(async (r) => {
                    const text = await r.text().catch(() => '');
                    let json = null;
                    try {
                        json = text ? JSON.parse(text) : null;
                    } catch (e) {
                        console.error('Failed to parse response:', text);
                    }

                    if (!r.ok) {
                        console.error('Comment like request failed:', r.status, text);
                        comment.liked = wasLiked;
                        comment.likes = prevLikes;
                        return;
                    }

                    const d = json || {};
                    if (!d.success) {
                        console.error('Comment like action unsuccessful:', d);
                        comment.liked = wasLiked;
                        comment.likes = prevLikes;
                    }
                })
                .catch((err) => {
                    console.error('Network error liking comment:', err);
                    comment.liked = wasLiked;
                    comment.likes = prevLikes;
                });
        },

        async deleteComment(comment, post) {
            if (!comment || !comment.id) return;
            if (comment.isDeleting) return;

            const confirmed = await this.showConfirmModal({
                title: 'Delete Comment',
                message: 'Delete this comment? This action cannot be undone.',
                confirmText: 'Delete',
                cancelText: 'Cancel'
            });

            if (!confirmed) return;

            comment.isDeleting = true;

            try {
                const form = new FormData();
                form.append('comment_id', comment.id);
                if (this.csrfToken) form.append('_csrfToken', this.csrfToken);

                const resp = await fetch('/api/comments/delete', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: form
                });

                const data = await resp.json().catch(() => ({}));

                if (!resp.ok || !data.success) {
                    this.showErrorModal({
                        title: 'Failed to Delete Comment',
                        message: data.message || 'Unknown error'
                    });
                    comment.isDeleting = false;
                    return;
                }

                if (post && post.commentsList && Array.isArray(post.commentsList)) {
                    const idx = post.commentsList.findIndex(c => c.id === comment.id);
                    if (idx !== -1) post.commentsList.splice(idx, 1);
                }

                if (post && typeof post.comments === 'number') {
                    post.comments = Math.max(0, post.comments - 1);
                }
            } catch (err) {
                console.error('Error deleting comment:', err);
                this.showErrorModal({
                    title: 'Error',
                    message: 'Failed to delete comment. Please try again.'
                });
            } finally {
                comment.isDeleting = false;
            }
        },

        closeAllMenus() {
            if (this.posts) {
                this.posts.forEach(post => {
                    post.showMenu = false;
                });
            }
            if (this.post) {
                this.post.showMenu = false;
            }
        },

        openImageModal(images, index = 0) {
            // Support both array of images and single image string
            if (typeof images === 'string') {
                this.imageModal.images = [images];
            } else {
                this.imageModal.images = images || [];
            }
            this.imageModal.currentIndex = index;
            this.imageModal.show = true;

            // Add keyboard event listener
            document.addEventListener('keydown', this.handleModalKeydown);
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
        },

        closeImageModal() {
            this.imageModal.show = false;
            this.imageModal.images = [];
            this.imageModal.currentIndex = 0;

            // Remove keyboard event listener
            document.removeEventListener('keydown', this.handleModalKeydown);
            // Restore body scroll
            document.body.style.overflow = '';
        },

        nextImage() {
            if (this.imageModal.currentIndex < this.imageModal.images.length - 1) {
                this.imageModal.currentIndex++;
            }
        },

        prevImage() {
            if (this.imageModal.currentIndex > 0) {
                this.imageModal.currentIndex--;
            }
        },

        handleModalKeydown(e) {
            if (!this.imageModal.show) return;

            if (e.key === 'Escape') {
                this.closeImageModal();
            } else if (e.key === 'ArrowRight') {
                this.nextImage();
            } else if (e.key === 'ArrowLeft') {
                this.prevImage();
            }
        }
    }
};

// Export to window for global access
if (typeof window !== 'undefined') {
    window.PostCardMixin = PostCardMixin;

    // Also expose the helper directly for templates that call `isLongText(...)`
    try {
        window.isLongText = PostCardMixin.methods.isLongText.bind(PostCardMixin.methods);
    } catch (e) {
        // ignore
    }
}
