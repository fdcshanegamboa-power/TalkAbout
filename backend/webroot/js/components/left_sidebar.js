/**
 * Left Sidebar Mixin
 * 
 * Provides current user profile functionality for the left sidebar and navbar
 * Note: Uses 'sidebarUser' to avoid collision with profile page's 'profileUser'
 */

window.LeftSidebarMixin = {
    data() {
        return {
            sidebarUser: null,
            currentUserId: null,
            loadingSidebarProfile: false,
            // Navbar state
            showUserMenu: false,
            searchQuery: '',
            searchResults: { users: [], posts: [] },
            showSearchResults: false,
            searchLoading: false,
            searchTimeout: null
        };
    },

    mounted() {
        // Add click outside listener for dropdowns
        document.addEventListener('click', this.handleClickOutside);
    },

    beforeUnmount() {
        // Remove click outside listener
        document.removeEventListener('click', this.handleClickOutside);
    },

    methods: {
        async fetchCurrentUserProfile() {
            this.loadingSidebarProfile = true;
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
                    this.sidebarUser = {
                        full_name: user.full_name || '',
                        username: user.username || '',
                        about: user.about || '',
                        profile_photo: user.profile_photo_path || '',
                        initial: (user.full_name || user.username || 'U').charAt(0).toUpperCase()
                    };
                    console.log('Current user profile loaded:', this.sidebarUser);
                } else {
                    console.error('Failed to fetch current user profile:', data.message);
                }
            } catch (error) {
                console.error('Error fetching current user profile:', error);
            } finally {
                this.loadingSidebarProfile = false;
            }
        },

        // Navbar methods
        toggleUserMenu() {
            this.showUserMenu = !this.showUserMenu;
            // Close other dropdowns
            if (this.showUserMenu) {
                this.showNotifications = false;
                this.showSearchResults = false;
            }
        },

        handleCreatePost() {
            // Focus on the post composer if it exists on the page
            const composer = document.querySelector('textarea[placeholder*="What"]');
            if (composer) {
                composer.focus();
                composer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                // Navigate to home with compose mode
                window.location.href = '/#compose';
            }
        },

        handleClickOutside(e) {
            // Close user menu when clicking outside
            if (this.showUserMenu && !e.target.closest('[data-user-menu]')) {
                this.showUserMenu = false;
            }
            // Close search results when clicking outside
            if (this.showSearchResults && !e.target.closest('[data-search-container]')) {
                this.showSearchResults = false;
            }
            // Close notifications when clicking outside
            if (this.showNotifications && !e.target.closest('[data-notification-container]')) {
                this.showNotifications = false;
            }
        },

        // Search functionality
        handleSearch() {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }
            
            if (!this.searchQuery.trim()) {
                this.searchResults = { users: [], posts: [] };
                return;
            }
            
            this.searchLoading = true;
            this.searchTimeout = setTimeout(() => {
                this.performSearch();
            }, 300);
        },

        async performSearch() {
            try {
                const response = await fetch(`/api/search?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                
                if (data.success) {
                    this.searchResults = {
                        users: data.users || [],
                        posts: data.posts || []
                    };
                }
            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.searchLoading = false;
            }
        }
    }
};
