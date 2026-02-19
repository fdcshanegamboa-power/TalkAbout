const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

const el = document.getElementById('register-app');

if (el && window.Vue) {
    const { createApp } = Vue;
    
    createApp({
        mixins: [window.ModalMixin || {}],
        data() {
            return {
                form: {
                    full_name: '',
                    username: '',
                    password: ''
                },
                confirmPassword: '',
                loading: false,
                passwordStrength: {
                    text: '',
                    color: '',
                    bgColor: '',
                    width: '0%'
                },
                validationErrors: {
                    full_name: '',
                    username: '',
                    password: '',
                    confirmPassword: ''
                }
            };
        },
        mounted() {
            const fullNameInput = document.querySelector('input[name="full_name"]');
            const usernameInput = document.querySelector('input[name="username"]');
            
            if (fullNameInput) this.form.full_name = fullNameInput.value || '';
            if (usernameInput) this.form.username = usernameInput.value || '';
        },
        computed: {
            passwordsMatch() {
                return this.form.password === this.confirmPassword;
            },
            isFormValid() {
                return this.form.full_name.trim() &&
                       this.form.username &&
                       this.form.password.length >= 8 &&
                       this.passwordsMatch &&
                       !this.validationErrors.full_name &&
                       !this.validationErrors.username &&
                       !this.validationErrors.password;
            }
        },
        methods: {
            validateFullName() {
                const name = this.form.full_name;
                if (!name) {
                    this.validationErrors.full_name = 'Full name is required';
                } else if (name.trim() === '') {
                    this.validationErrors.full_name = 'Full name cannot be only whitespace';
                } else if (name.length > 150) {
                    this.validationErrors.full_name = 'Full name must be less than 150 characters';
                } else {
                    this.validationErrors.full_name = '';
                }
            },
            validateUsername() {
                const username = this.form.username;
                if (!username) {
                    this.validationErrors.username = 'Username is required';
                } else if (username.length < 3) {
                    this.validationErrors.username = 'Username must be at least 3 characters';
                } else if (username.length > 50) {
                    this.validationErrors.username = 'Username must be less than 50 characters';
                } else if (/\s/.test(username)) {
                    this.validationErrors.username = 'Username cannot contain whitespace';
                } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    this.validationErrors.username = 'Username can only contain letters, numbers, and underscores';
                } else {
                    this.validationErrors.username = '';
                }
            },
            validatePassword() {
                const p = this.form.password;
                
                // Validation errors
                if (!p) {
                    this.validationErrors.password = 'Password is required';
                    this.passwordStrength = { 
                        text: '', 
                        color: '', 
                        bgColor: '', 
                        width: '0%' 
                    };
                    return;
                } else if (/\s/.test(p)) {
                    this.validationErrors.password = 'Password cannot contain whitespace';
                    this.passwordStrength = { 
                        text: 'Invalid', 
                        color: 'text-red-600', 
                        bgColor: 'bg-red-500', 
                        width: '25%' 
                    };
                    return;
                } else if (p.length < 8) {
                    this.validationErrors.password = 'Password must be at least 8 characters';
                    this.passwordStrength = { 
                        text: 'Too short', 
                        color: 'text-red-600', 
                        bgColor: 'bg-red-500', 
                        width: '25%' 
                    };
                    return;
                } else {
                    this.validationErrors.password = '';
                }
                
                // Password strength indicator
                if (p.length < 12) {
                    const hasLower = /[a-z]/.test(p);
                    const hasUpper = /[A-Z]/.test(p);
                    const hasNumber = /[0-9]/.test(p);
                    const hasSpecial = /[^a-zA-Z0-9]/.test(p);
                    const complexity = [hasLower, hasUpper, hasNumber, hasSpecial].filter(Boolean).length;
                    
                    if (complexity >= 3) {
                        this.passwordStrength = { 
                            text: 'Good', 
                            color: 'text-blue-600', 
                            bgColor: 'bg-blue-500', 
                            width: '75%' 
                        };
                    } else {
                        this.passwordStrength = { 
                            text: 'Fair', 
                            color: 'text-yellow-600', 
                            bgColor: 'bg-yellow-500', 
                            width: '50%' 
                        };
                    }
                } else {
                    this.passwordStrength = { 
                        text: 'Strong', 
                        color: 'text-green-600', 
                        bgColor: 'bg-green-500', 
                        width: '100%' 
                    };
                }
            },
            validateConfirmPassword() {
                if (this.confirmPassword && !this.passwordsMatch) {
                    this.validationErrors.confirmPassword = 'Passwords do not match';
                } else {
                    this.validationErrors.confirmPassword = '';
                }
            },
            handleSubmit() {
                // Validate all fields one more time
                this.validateFullName();
                this.validateUsername();
                this.validatePassword();
                this.validateConfirmPassword();
                
                if (!this.isFormValid) {
                    const errors = Object.values(this.validationErrors).filter(e => e);
                    this.showErrorModal({
                        title: 'Form Validation Error',
                        message: errors.length > 0 ? errors.join('\n') : 'Please fill in all fields correctly'
                    });
                    return;
                }
                this.loading = true;
                document.getElementById('register-form').submit();
            }
        }
    }).mount('#register-app');
}