const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

const el = document.getElementById('login-app');

if (el && window.Vue) {
    const { createApp } = Vue;

    createApp({
        mixins: [window.ModalMixin || {}],
        data() {
            return {
                form: {
                    username: '',
                    password: ''
                },
                loading: false
            };
        },
        mounted() {
            const usernameInput = document.querySelector('input[name="username"]');
            const passwordInput = document.querySelector('input[name="password"]');
            
            if (usernameInput) this.form.username = usernameInput.value || '';
            if (passwordInput) this.form.password = passwordInput.value || '';
        },
        methods: {
            handleSubmit() {
                if (!this.form.username || !this.form.password) {
                    this.showErrorModal({
                        title: 'Missing Credentials',
                        message: 'Please enter both username and password'
                    });
                    return;
                }
                this.loading = true;
                document.getElementById('login-form').submit();
            }
        },
        computed: {
            isFormValid() {
                return this.form.username && this.form.password;
            }
        }
    }).mount('#login-app');
}