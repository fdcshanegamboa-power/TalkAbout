const { createApp } = Vue;

createApp({
    data() {
        return {
            profileUser: null // For left sidebar display
        };
    },
    mounted() {
        this.fetchCurrentUserProfile();
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
        }
    }
}).mount('#edit-profile-app');

// Profile picture preview
function previewProfilePicture(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            event.target.value = '';
            return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please upload an image file');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            const currentAvatar = document.getElementById('current-avatar');
            const initial = document.getElementById('avatar-initial');
            
            // Remove existing content
            if (currentAvatar) currentAvatar.remove();
            if (initial) initial.remove();
            
            // Add new image
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Profile Preview';
            img.className = 'w-full h-full object-cover';
            img.id = 'current-avatar';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }