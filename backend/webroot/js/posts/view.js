const el = document.getElementById('post-view-app');

if (el && window.Vue && window.postViewData && window.PostCardMixin) {
    const { createApp } = Vue;

    createApp({
        mixins: [PostCardMixin],
        data() {
            return {
                profileUser: null, // For left sidebar display
                post: window.postViewData
            };
        },

        mounted() {
            this.fetchCurrentUserProfile();
            // Compute and show relative time for the post
            if (this.post) {
                if (this.post.created_at) {
                    this.post.time = this.formatRelativeTime(this.post.created_at);
                }

                if (this.post.commentsList && this.post.commentsList.length > 0) {
                    this.post.commentsList.forEach(c => {
                        if (c.created_at) c.time = this.formatRelativeTime(c.created_at);
                    });
                }

                // update post and comment relative times every minute
                setInterval(() => {
                    if (this.post && this.post.created_at) {
                        this.post.time = this.formatRelativeTime(this.post.created_at);
                    }
                    if (this.post && this.post.commentsList) {
                        this.post.commentsList.forEach(c => {
                            if (c.created_at) c.time = this.formatRelativeTime(c.created_at);
                        });
                    }
                }, 60000);
            }
            
            // Check if URL has a comment anchor and if that comment exists
            this.checkCommentAnchor();
        },

        methods: {
            async fetchCurrentUserProfile() {
                try {
                    const response = await fetch('/api/profile/current');
                    if (!response.ok) return;
                    
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
                    }
                } catch (error) {
                    console.error('Error fetching profile:', error);
                }
            },
            
            checkCommentAnchor() {
                // Check if there's a comment hash in the URL
                const hash = window.location.hash;
                if (hash && hash.startsWith('#comment-')) {
                    const commentId = hash.replace('#comment-', '');
                    
                    // Wait a bit for the DOM to render
                    setTimeout(() => {
                        const commentElement = document.getElementById('comment-' + commentId);
                        
                        if (!commentElement) {
                            // Comment doesn't exist - show notification
                            this.showCommentNotFoundAlert();
                        } else {
                            // Comment exists - scroll to it
                            commentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            commentElement.classList.add('highlight-comment');
                            setTimeout(() => {
                                commentElement.classList.remove('highlight-comment');
                            }, 2000);
                        }
                    }, 500);
                }
            },
            
            showCommentNotFoundAlert() {
                // Create a toast notification
                const toast = document.createElement('div');
                toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 z-50 bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 animate-fade-in';
                toast.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-semibold">Comment not found - it may have been deleted</span>
                `;
                document.body.appendChild(toast);
                
                // Remove after 5 seconds
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity 0.5s';
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 500);
                }, 5000);
            }
        }
    }).mount(el);
} else {
    console.error('Post view app failed to mount:', {
        el: !!el,
        Vue: !!window.Vue,
        postViewData: !!window.postViewData,
        PostCardMixin: !!window.PostCardMixin
    });
}