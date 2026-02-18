const el = document.getElementById('dashboard-app');

// Remove v-cloak to show content even if Vue doesn't mount
if (el) {
    el.removeAttribute('v-cloak');
}

// Debug logging
console.log('Dashboard page loaded:', {
    el: !!el,
    Vue: !!window.Vue,
    PostCardMixin: !!window.PostCardMixin,
    PostComposerMixin: !!window.PostComposerMixin
});

if (el && window.Vue && window.PostCardMixin && window.PostComposerMixin) {
    const { createApp } = Vue;

    createApp({
        mixins: [PostCardMixin, PostComposerMixin],
        data() {
            return {
                profileUser: null, // For left sidebar display
                currentUserId: null,

                posts: [],
                isLoading: true
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
            }
        },

        computed: {
            // Additional computed properties can go here
        }
    }).mount(el);
} else {
    console.error('Dashboard app failed to mount:', {
        el: !!el,
        Vue: !!window.Vue,
        PostCardMixin: !!window.PostCardMixin,
        PostComposerMixin: !!window.PostComposerMixin
    });
    
    // Show error message on page
    if (el) {
        const missing = [];
        if (!window.Vue) missing.push('Vue.js');
        if (!window.PostCardMixin) missing.push('PostCardMixin');
        if (!window.PostComposerMixin) missing.push('PostComposerMixin');
        
        el.innerHTML = `
            <div class="min-h-screen flex items-center justify-center p-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 max-w-2xl">
                    <h2 class="text-red-800 text-xl font-bold mb-4">Failed to load page</h2>
                    <p class="text-red-700 mb-4">The following dependencies are missing:</p>
                    <ul class="list-disc list-inside text-red-600 mb-4">
                        ${missing.map(m => `<li>${m}</li>`).join('')}
                    </ul>
                    <p class="text-red-600 text-sm">Check the browser console (F12) for more details.</p>
                    <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Reload Page
                    </button>
                </div>
            </div>
        `;
    }
}
