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
                return this.form.full_name &&
                       this.form.username &&
                       this.form.password.length >= 8 &&
                       this.passwordsMatch;
            }
        },
        methods: {
            validatePassword() {
                const p = this.form.password;
                if (!p) {
                    this.passwordStrength = { 
                        text: '', 
                        color: '', 
                        bgColor: '', 
                        width: '0%' 
                    };
                    return;
                }
    
                if (p.length < 8) {
                    this.passwordStrength = { 
                        text: 'Too short', 
                        color: 'text-red-600', 
                        bgColor: 'bg-red-500', 
                        width: '25%' 
                    };
                } else if (p.length < 12) {
                    this.passwordStrength = { 
                        text: 'Fair', 
                        color: 'text-yellow-600', 
                        bgColor: 'bg-yellow-500', 
                        width: '75%' 
                    };
                } else {
                    this.passwordStrength = { 
                        text: 'Strong', 
                        color: 'text-green-600', 
                        bgColor: 'bg-green-500', 
                        width: '100%' 
                    };
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
                document.getElementById('register-form').submit();
            }
        }
    }).mount('#register-app');
}