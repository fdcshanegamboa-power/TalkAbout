/**
 * Right Sidebar Mixin
 * 
 * Provides friends list and friend suggestions functionality for the right sidebar
 */

window.RightSidebarMixin = {
    mixins: [window.ModalMixin || {}],
    data() {
        return {
            friends: [],
            loadingFriends: false,
            
            suggestions: [],
            loadingSuggestions: false
        };
    },

    methods: {
        async fetchFriends() {
            this.loadingFriends = true;
            if (this.updateLoadingState) {
                this.updateLoadingState();
            }
            console.log('RightSidebarMixin: Fetching friends...');
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
                if (this.updateLoadingState) {
                    this.updateLoadingState();
                }
            }
        },

        async fetchSuggestions() {
            this.loadingSuggestions = true;
            try {
                const response = await fetch('/api/friendships/suggestions?limit=10');
                const data = await response.json();

                console.log('Suggestions API response:', data);

                if (data.success) {
                    // Initialize each suggestion with sending flag for reactivity
                    this.suggestions = (data.suggestions || []).map(s => ({
                        ...s,
                        sending: false
                    }));
                    console.log('Suggestions loaded:', this.suggestions.length, 'suggestions');
                } else {
                    console.error('Failed to fetch suggestions:', data.message);
                    this.suggestions = [];
                }
            } catch (error) {
                console.error('Error fetching suggestions:', error);
                this.suggestions = [];
            } finally {
                this.loadingSuggestions = false;
            }
        },

        async sendFriendRequestToSuggestion(suggestion) {
            if (suggestion.sending) return;

            // Use Vue.set or direct assignment to ensure reactivity
            suggestion.sending = true;
            
            try {
                const response = await fetch('/api/friendships/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        addressee_id: suggestion.id
                    })
                });

                const data = await response.json();
                console.log('Send friend request response:', data);

                if (data.success) {
                    // Remove from suggestions list
                    const index = this.suggestions.findIndex(s => s.id === suggestion.id);
                    if (index > -1) {
                        this.suggestions.splice(index, 1);
                    }
                    console.log('Friend request sent, suggestion removed. Remaining:', this.suggestions.length);
                    
                    // Add to sent requests list if it exists (on friends page)
                    if (this.sentRequests) {
                        this.sentRequests.unshift({
                            id: data.friendship_id || Date.now(), // Use returned ID or timestamp
                            friendship_id: data.friendship_id || Date.now(),
                            user_id: suggestion.id,
                            username: suggestion.username,
                            full_name: suggestion.full_name,
                            profile_photo: suggestion.profile_photo,
                            created_at: new Date().toISOString(),
                            processing: false
                        });
                        console.log('Added to sent requests. Total sent:', this.sentRequests.length);
                    }
                } else {
                    suggestion.sending = false;
                    this.showErrorModal({
                        title: 'Failed to Send Friend Request',
                        message: data.message || 'Unknown error'
                    });
                }
            } catch (error) {
                console.error('Error sending friend request:', error);
                suggestion.sending = false;
                this.showErrorModal({
                    title: 'Error',
                    message: 'Failed to send friend request. Please try again.'
                });
            }
        }
    }
};
