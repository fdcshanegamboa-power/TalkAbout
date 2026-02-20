/**
 * Left Sidebar Mixin
 * 
 * Provides current user profile functionality for the left sidebar
 */

window.LeftSidebarMixin = {
    data() {
        return {
            profileUser: null,
            currentUserId: null,
            loadingProfile: false
        };
    },

    methods: {
        async fetchCurrentUserProfile() {
            this.loadingProfile = true;
            console.log('LeftSidebarMixin: Fetching current user profile...');
            try {
                const response = await fetch('/api/profile/current');
                if (!response.ok) {
                    console.error('Failed to fetch profile:', response.status);
                    return;
                }
                
                const data = await response.json();
                console.log('Current user profile response:', data);
                
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
                    console.log('Current user profile loaded:', this.profileUser);
                } else {
                    console.error('Failed to fetch current user profile:', data.message);
                }
            } catch (error) {
                console.error('Error fetching current user profile:', error);
            } finally {
                this.loadingProfile = false;
            }
        }
    }
};
