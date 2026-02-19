const el = document.getElementById('friends-app');

// Remove v-cloak to show content even if Vue doesn't mount
if (el) {
    el.removeAttribute('v-cloak');
}

// Debug logging
console.log('Friends page loaded:', {
    el: !!el,
    Vue: !!window.Vue,
    RightSidebarMixin: !!window.RightSidebarMixin
});

if (el && window.Vue) {
    const { createApp } = Vue;

    const mixins = [];
    if (window.ModalMixin) {
        mixins.push(ModalMixin);
    }
    if (window.RightSidebarMixin) {
        mixins.push(RightSidebarMixin);
    }

    createApp({
        mixins: mixins,
        data() {
            return {
                profileUser: null, // For left sidebar display
                currentUserId: null,
                
                // From RightSidebarMixin (fallback if mixin not loaded)
                friends: [],
                loadingFriends: false,
                suggestions: [],
                loadingSuggestions: false,
                
                pendingRequests: [], // Incoming friend requests
                loadingPendingRequests: false,
                
                sentRequests: [], // Outgoing friend requests
                loadingSentRequests: false,
                
                loading: true
            };
        },

        mounted() {
            this.fetchCurrentUserProfile();
            this.fetchFriends(); // Fetch friends from mixin or fallback
            this.fetchPendingRequests();
            this.fetchSentRequests();
            if (this.fetchSuggestions) {
                this.fetchSuggestions();
            }
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

            // Fallback fetchFriends if RightSidebarMixin not loaded
            async fetchFriends() {
                this.loadingFriends = true;
                this.updateLoadingState();
                console.log('Fetching friends from fallback method...');
                try {
                    const response = await fetch('/api/friendships/friends');
                    const data = await response.json();

                    console.log('Friends API response:', data);

                    if (data.success) {
                        this.friends = data.friends || [];
                        console.log('Friends loaded:', this.friends.length, 'friends');
                    } else {
                        console.error('Failed to fetch friends:', data.message);
                        this.friends = [];
                    }
                } catch (error) {
                    console.error('Error fetching friends:', error);
                    this.friends = [];
                } finally {
                    this.loadingFriends = false;
                    this.updateLoadingState();
                }
            },

            async fetchPendingRequests() {
                this.loadingPendingRequests = true;
                try {
                    const response = await fetch('/api/friendships/requests');
                    const data = await response.json();

                    if (data.success) {
                        this.pendingRequests = (data.requests || []).map(req => ({
                            id: req.id,
                            friendship_id: req.id,
                            user_id: req.requester_id,
                            username: req.requester_username,
                            full_name: req.requester_full_name,
                            profile_photo: req.requester_profile_photo,
                            created_at: req.created_at,
                            processing: false
                        }));
                    } else {
                        console.error('Failed to fetch pending requests:', data.message);
                        this.pendingRequests = [];
                    }
                } catch (error) {
                    console.error('Error fetching pending requests:', error);
                    this.pendingRequests = [];
                } finally {
                    this.loadingPendingRequests = false;
                    this.updateLoadingState();
                }
            },

            async fetchSentRequests() {
                this.loadingSentRequests = true;
                try {
                    const response = await fetch('/api/friendships/sent');
                    const data = await response.json();

                    if (data.success) {
                        this.sentRequests = (data.requests || []).map(req => ({
                            id: req.id,
                            friendship_id: req.id,
                            user_id: req.addressee_id,
                            username: req.addressee_username,
                            full_name: req.addressee_full_name,
                            profile_photo: req.addressee_profile_photo,
                            created_at: req.created_at,
                            processing: false
                        }));
                    } else {
                        console.error('Failed to fetch sent requests:', data.message);
                        this.sentRequests = [];
                    }
                } catch (error) {
                    console.error('Error fetching sent requests:', error);
                    this.sentRequests = [];
                } finally {
                    this.loadingSentRequests = false;
                    this.updateLoadingState();
                }
            },

            updateLoadingState() {
                this.loading = this.loadingPendingRequests || this.loadingSentRequests || this.loadingFriends;
            },

            async acceptRequest(request) {
                if (request.processing) return;

                request.processing = true;
                try {
                    const response = await fetch('/api/friendships/accept', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            friendship_id: request.friendship_id
                        })
                    });

                    const data = await response.json();

                    console.log('Accept friend request response:', data);

                    if (data.success) {
                        // Remove from pending requests
                        this.pendingRequests = this.pendingRequests.filter(r => r.id !== request.id);
                        console.log('Friend request accepted, refreshing friends list');
                        // Refresh friends list (from RightSidebarMixin)
                        if (this.fetchFriends) {
                            await this.fetchFriends();
                            console.log('Friends list after refresh:', this.friends);
                        } else {
                            console.warn('fetchFriends not available');
                        }
                    } else {
                        console.error('Failed to accept friend request:', data);
                        this.showErrorModal({
                            title: 'Failed to Accept Friend Request',
                            message: data.message || 'Unknown error'
                        });
                    }
                } catch (error) {
                    console.error('Error accepting friend request:', error);
                    this.showErrorModal({
                        title: 'Error',
                        message: 'Failed to accept friend request. Please try again.'
                    });
                } finally {
                    request.processing = false;
                }
            },

            async rejectRequest(request) {
                if (request.processing) return;

                request.processing = true;
                try {
                    const response = await fetch('/api/friendships/reject', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            friendship_id: request.friendship_id
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Remove from pending requests
                        this.pendingRequests = this.pendingRequests.filter(r => r.id !== request.id);
                    } else {
                        this.showErrorModal({
                            title: 'Failed to Reject Friend Request',
                            message: data.message || 'Unknown error'
                        });
                    }
                } catch (error) {
                    console.error('Error rejecting friend request:', error);
                    this.showErrorModal({
                        title: 'Error',
                        message: 'Failed to reject friend request. Please try again.'
                    });
                } finally {
                    request.processing = false;
                }
            },

            async cancelRequest(request) {
                if (request.processing) return;

                request.processing = true;
                try {
                    const response = await fetch('/api/friendships/cancel', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            friendship_id: request.friendship_id
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Remove from sent requests
                        this.sentRequests = this.sentRequests.filter(r => r.id !== request.id);
                    } else {
                        this.showErrorModal({
                            title: 'Failed to Cancel Friend Request',
                            message: data.message || 'Unknown error'
                        });
                    }
                } catch (error) {
                    console.error('Error cancelling friend request:', error);
                    this.showErrorModal({
                        title: 'Error',
                        message: 'Failed to cancel friend request. Please try again.'
                    });
                } finally {
                    request.processing = false;
                }
            },

            async confirmUnfriend(friend) {
                const confirmed = await this.showConfirmModal({
                    title: 'Unfriend',
                    message: `Are you sure you want to unfriend ${friend.full_name || friend.username}?`,
                    confirmText: 'Unfriend',
                    cancelText: 'Cancel'
                });
                
                if (confirmed) {
                    this.unfriend(friend);
                }
            },

            async unfriend(friend) {
                try {
                    const response = await fetch('/api/friendships/unfriend', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            friend_id: friend.friend_id
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Remove from friends list
                        this.friends = this.friends.filter(f => f.friend_id !== friend.friend_id);
                    } else {
                        this.showErrorModal({
                            title: 'Failed to Unfriend',
                            message: data.message || 'Unknown error'
                        });
                    }
                } catch (error) {
                    console.error('Error unfriending user:', error);
                    this.showErrorModal({
                        title: 'Error',
                        message: 'Failed to unfriend. Please try again.'
                    });
                }
            }
        },

        computed: {
            // Additional computed properties can go here
        }
    }).mount(el);
} else {
    console.error('Friends app failed to mount:', {
        el: !!el,
        Vue: !!window.Vue
    });
    
    // Show error message on page
    if (el) {
        const missing = [];
        if (!window.Vue) missing.push('Vue.js');
        
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
