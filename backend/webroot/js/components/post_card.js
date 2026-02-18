

const PostCardMixin = {
    data() {
        return {
            currentUserId: (typeof window !== 'undefined' && window.currentUserId) ? window.currentUserId : null
        };
    },
    methods: {
        toggleLike(post) {
            const wasLiked = post.liked;
            const prevLikes = post.likes;

            post.liked = !post.liked;
            post.likes += post.liked ? 1 : -1;

            const url = post.liked ? '/api/posts/like' : '/api/posts/unlike';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) console.warn('CSRF token not found for like request');

            const form = new FormData();
            form.append('post_id', post.id);
            if (csrfToken) form.append('_csrfToken', csrfToken);

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
            
            const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!allowedTypes.includes(file.type)) {
                const typeName = file.type || 'unknown type';
                alert(`File "${file.name}" (${typeName}) is not supported.\n\nSupported formats: JPEG, PNG, GIF, WebP`);
                event.target.value = '';
                return;
            }
            
            if (file.size === 0) {
                alert('The selected file is empty. Please choose a valid image.');
                event.target.value = '';
                return;
            }
            
            if (file.size > maxFileSize) {
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                alert(`Image "${file.name}" is too large (${fileSizeMB}MB).\n\nMaximum allowed size: 5MB`);
                event.target.value = '';
                return;
            }
            
            post.commentImageFile = file;
            
            const reader = new FileReader();
            reader.onload = (e) => {
                post.commentImagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
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
                    alert('Failed to post comment: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error posting comment:', error);
                alert('Failed to post comment. Please try again.');
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

        expandPost(post) {
            if (!post) return;
            post.expanded = true;
        },

        collapsePost(post) {
            if (!post) return;
            post.expanded = false;
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
                    alert('Failed to delete post: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error deleting post:', error);
                alert('Failed to delete post. Please try again.');
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Use FormData to avoid preflight
            const form = new FormData();
            form.append('comment_id', comment.id);
            if (csrfToken) form.append('_csrfToken', csrfToken);

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

            if (!confirm('Delete this comment? This action cannot be undone.')) return;

            comment.isDeleting = true;

            try {
                const form = new FormData();
                form.append('comment_id', comment.id);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) form.append('_csrfToken', csrfToken);

                const resp = await fetch('/api/comments/delete', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: form
                });

                const data = await resp.json().catch(() => ({}));

                if (!resp.ok || !data.success) {
                    alert('Failed to delete comment: ' + (data.message || 'Unknown error'));
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
                alert('Failed to delete comment. Please try again.');
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
