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
                passwordRequirements: {
                    minLength: false,
                    hasUpper: false,
                    hasLower: false,
                    hasNumber: false,
                    hasSpecial: false
                },
                validationErrors: {
                    full_name: '',
                    username: '',
                    password: '',
                    confirmPassword: ''
                },
                usernameCheck: {
                    checking: false,
                    available: null,
                    message: ''
                },
                usernameCheckTimeout: null
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
                       !this.validationErrors.password &&
                       this.passwordRequirements.minLength &&
                       this.passwordRequirements.hasUpper &&
                       this.passwordRequirements.hasLower &&
                       this.passwordRequirements.hasNumber;
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
                    this.usernameCheck = { checking: false, available: null, message: '' };
                } else if (username.length < 3) {
                    this.validationErrors.username = 'Username must be at least 3 characters';
                    this.usernameCheck = { checking: false, available: null, message: '' };
                } else if (username.length > 15) {
                    this.validationErrors.username = 'Username must be less than 15 characters';
                    this.usernameCheck = { checking: false, available: null, message: '' };
                } else if (/\s/.test(username)) {
                    this.validationErrors.username = 'Username cannot contain whitespace';
                    this.usernameCheck = { checking: false, available: null, message: '' };
                } else if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    this.validationErrors.username = 'Username can only contain letters, numbers, and underscores';
                    this.usernameCheck = { checking: false, available: null, message: '' };
                } else {
                    this.validationErrors.username = '';
                    // Trigger real-time availability check
                    this.checkUsernameAvailability();
                }
            },
            checkUsernameAvailability() {
                // Clear any existing timeout
                if (this.usernameCheckTimeout) {
                    clearTimeout(this.usernameCheckTimeout);
                }
                
                // Reset state
                this.usernameCheck.checking = true;
                this.usernameCheck.available = null;
                this.usernameCheck.message = '';
                
                // Debounce API call by 400ms
                this.usernameCheckTimeout = setTimeout(async () => {
                    const username = this.form.username;
                    if (!username || this.validationErrors.username) {
                        this.usernameCheck.checking = false;
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/users/check-username?username=${encodeURIComponent(username)}`);
                        const data = await response.json();
                        
                        this.usernameCheck.checking = false;
                        this.usernameCheck.available = data.available;
                        this.usernameCheck.message = data.message || '';
                        
                        // Set validation error if username is taken
                        if (!data.available) {
                            this.validationErrors.username = data.message;
                        }
                    } catch (error) {
                        console.error('Error checking username:', error);
                        this.usernameCheck.checking = false;
                        this.usernameCheck.available = null;
                        this.usernameCheck.message = '';
                    }
                }, 400);
            },
            validatePassword() {
                const p = this.form.password;
                
                // Check individual requirements
                this.passwordRequirements.minLength = p.length >= 8;
                this.passwordRequirements.hasLower = /[a-z]/.test(p);
                this.passwordRequirements.hasUpper = /[A-Z]/.test(p);
                this.passwordRequirements.hasNumber = /[0-9]/.test(p);
                this.passwordRequirements.hasSpecial = /[^a-zA-Z0-9]/.test(p);
                
                // Validation errors
                if (!p) {
                    this.validationErrors.password = 'Password is required';
                    this.passwordStrength = { 
                        text: '', 
                        color: '', 
                        bgColor: '', 
                        width: '0%' 
                    };
                    this.passwordRequirements = {
                        minLength: false,
                        hasUpper: false,
                        hasLower: false,
                        hasNumber: false,
                        hasSpecial: false
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
                } else if (!this.passwordRequirements.minLength || 
                          !this.passwordRequirements.hasUpper || 
                          !this.passwordRequirements.hasLower || 
                          !this.passwordRequirements.hasNumber) {
                    this.validationErrors.password = 'Password does not meet requirements';
                } else {
                    this.validationErrors.password = '';
                }
                
                // Password strength indicator
                const complexity = [
                    this.passwordRequirements.hasLower,
                    this.passwordRequirements.hasUpper,
                    this.passwordRequirements.hasNumber,
                    this.passwordRequirements.hasSpecial
                ].filter(Boolean).length;
                
                if (p.length >= 12 && complexity >= 4) {
                    this.passwordStrength = { 
                        text: 'Strong', 
                        color: 'text-green-600', 
                        bgColor: 'bg-green-500', 
                        width: '100%' 
                    };
                } else if (complexity >= 3) {
                    this.passwordStrength = { 
                        text: 'Good', 
                        color: 'text-blue-600', 
                        bgColor: 'bg-blue-500', 
                        width: '75%' 
                    };
                } else if (complexity >= 2) {
                    this.passwordStrength = { 
                        text: 'Fair', 
                        color: 'text-yellow-600', 
                        bgColor: 'bg-yellow-500', 
                        width: '50%' 
                    };
                } else {
                    this.passwordStrength = { 
                        text: 'Weak', 
                        color: 'text-red-600', 
                        bgColor: 'bg-red-500', 
                        width: '25%' 
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