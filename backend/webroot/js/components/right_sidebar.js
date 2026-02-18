/**
 * Right Sidebar Mixin
 * 
 * Provides friends list and friend suggestions functionality for the right sidebar
 */

window.RightSidebarMixin = {
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
                // TODO: Implement suggestions API endpoint
                // For now, just set empty array
                this.suggestions = [];
            } catch (error) {
                console.error('Error fetching suggestions:', error);
                this.suggestions = [];
            } finally {
                this.loadingSuggestions = false;
            }
        },

        async sendFriendRequestToSuggestion(suggestion) {
            if (suggestion.sending) return;

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

                if (data.success) {
                    // Remove from suggestions
                    this.suggestions = this.suggestions.filter(s => s.id !== suggestion.id);
                } else {
                    alert('Failed to send friend request: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error sending friend request:', error);
                alert('Failed to send friend request. Please try again.');
            } finally {
                suggestion.sending = false;
            }
        }
    }
};
