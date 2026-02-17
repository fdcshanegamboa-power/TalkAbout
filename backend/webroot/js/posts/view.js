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