(() => {
    const el = document.getElementById('settings-app');
    if (!el) return;

    if (!window.Vue) return;
    const { createApp } = Vue;

    createApp({
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
                    alert('Please fill in all fields correctly');
                    return;
                }
                this.loading = true;
                document.getElementById('settings-form').submit();
            }
        }
    }).mount(el);
})();
