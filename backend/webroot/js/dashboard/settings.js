const el = document.getElementById('settings-app');

// Remove v-cloak to show content even if Vue doesn't mount
if (el) {
    el.removeAttribute('v-cloak');
}

// Debug logging
console.log('Settings page loaded:', {
    el: !!el,
    Vue: !!window.Vue
});

if (el && window.Vue) {
    const { createApp } = Vue;

    createApp({
        mixins: [window.ModalMixin || {}],
        data() {
            return {
                profileUser: null,
                form: {
                    current_password: '',
                    new_password: ''
                },
                confirmPassword: '',
                loading: false,
                passwordStrength: {
                    text: '',
                    color: '',
                    bgColor: '',
                    width: '0%'
                }
            };
        },

        mounted() {
            this.fetchCurrentUserProfile();
        },

        computed: {
            passwordsMatch() {
                return this.form.new_password === this.confirmPassword;
            },
            isFormValid() {
                return this.form.current_password &&
                       this.form.new_password &&
                       this.form.new_password.length >= 8 &&
                       this.passwordsMatch;
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

            validatePassword() {
                const p = this.form.new_password;
                if (!p) {
                    this.passwordStrength = { text: '', color: '', bgColor: '', width: '0%' };
                    return;
                }

                if (p.length < 8) {
                    this.passwordStrength = { text: 'Too short', color: 'text-red-600', bgColor: 'bg-red-500', width: '25%' };
                } else if (p.length < 12) {
                    this.passwordStrength = { text: 'Fair', color: 'text-yellow-600', bgColor: 'bg-yellow-500', width: '75%' };
                } else {
                    this.passwordStrength = { text: 'Strong', color: 'text-green-600', bgColor: 'bg-green-500', width: '100%' };
                }
            },

            handleSubmit() {
                if (!this.isFormValid) {
                    this.showErrorModal({
                        title: 'Invalid Form',
                        message: 'Please fill in all fields correctly'
                    });
                    return;
                }
                this.loading = true;
                document.getElementById('settings-form').submit();
            }
        }
    }).mount(el);
} else {
    console.error('Settings app failed to mount:', {
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
