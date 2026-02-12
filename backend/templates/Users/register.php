<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Register');
?>

<div class="container mx-auto max-w-md px-4">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">TalkAbout</h1>
            <p class="text-gray-600 mt-2">Create your account</p>
        </div>
        
        <div id="register-app">
            <?= $this->Form->create($user, [
                'id' => 'register-form',
                '@submit.prevent' => 'handleSubmit',
                'class' => 'space-y-6'
            ]) ?>
            
            <div>
                <label for="full-name" class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name
                </label>
                <?= $this->Form->control('full_name', [
                    'label' => false,
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                    'placeholder' => 'Enter your full name',
                    'required' => true,
                    'v-model' => 'form.full_name'
                ]) ?>
                <?php if ($user->getError('full_name')): ?>
                    <p class="text-red-500 text-sm mt-1">
                        <?= htmlspecialchars(implode(', ', $user->getError('full_name')), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    Username
                </label>
                <?= $this->Form->control('username', [
                    'label' => false,
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                    'placeholder' => 'Choose a username',
                    'required' => true,
                    'v-model' => 'form.username'
                ]) ?>
                <?php if ($user->getError('username')): ?>
                    <p class="text-red-500 text-sm mt-1">
                        <?= htmlspecialchars(implode(', ', $user->getError('username')), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password
                </label>
                <?= $this->Form->control('password', [
                    'type' => 'password',
                    'label' => false,
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                    'placeholder' => 'Choose a password (min 8 characters)',
                    'required' => true,
                    'v-model' => 'form.password',
                    '@input' => 'validatePassword'
                ]) ?>
                <?php if ($user->getError('password')): ?>
                    <p class="text-red-500 text-sm mt-1">
                        <?= htmlspecialchars(implode(', ', $user->getError('password')), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>
                <div v-if="form.password" class="mt-2">
                    <div class="flex items-center text-xs" :class="passwordStrength.color">
                        <div class="flex-1">
                            <div class="h-1 rounded-full bg-gray-200">
                                <div 
                                    class="h-1 rounded-full transition-all duration-300"
                                    :class="passwordStrength.bgColor"
                                    :style="{width: passwordStrength.width}"
                                ></div>
                            </div>
                        </div>
                        <span class="ml-2">{{ passwordStrength.text }}</span>
                    </div>
                </div>
            </div>
            
            <div>
                <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-2">
                    Confirm Password
                </label>
                <input
                    type="password"
                    id="confirm-password"
                    v-model="confirmPassword"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Re-enter your password"
                    required
                />
                <p v-if="confirmPassword && !passwordsMatch" class="text-red-500 text-sm mt-1">
                    Passwords do not match
                </p>
                <p v-if="passwordsMatch && confirmPassword" class="text-green-500 text-sm mt-1">
                    Passwords match
                </p>
            </div>
            
            <div>
                <button 
                    type="submit"
                    :disabled="loading || !isFormValid"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:bg-blue-400 disabled:cursor-not-allowed"
                >
                    <span v-if="!loading">Create Account</span>
                    <span v-else>Creating account...</span>
                </button>
            </div>
            
            <?= $this->Form->end() ?>
            
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <?= $this->Html->link('Login here', ['controller' => 'Sessions', 'action' => 'login'], [
                        'class' => 'text-blue-600 hover:text-blue-800 font-medium'
                    ]) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            form: {
                full_name: '<?= htmlspecialchars($user->full_name ?? '', ENT_QUOTES, 'UTF-8') ?>',
                username: '<?= htmlspecialchars($user->username ?? '', ENT_QUOTES, 'UTF-8') ?>',
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
        }
    },
    computed: {
        passwordsMatch() {
            return this.form.password === this.confirmPassword;
        },
        isFormValid() {
            return this.form.full_name && 
                   this.form.username && 
                   this.form.password && 
                   this.form.password.length >= 8 &&
                   this.passwordsMatch;
        }
    },
    methods: {
        validatePassword() {
            const password = this.form.password;
            const length = password.length;
            
            if (length === 0) {
                this.passwordStrength = { text: '', color: '', bgColor: '', width: '0%' };
            } else if (length < 8) {
                this.passwordStrength = {
                    text: 'Too short',
                    color: 'text-red-600',
                    bgColor: 'bg-red-500',
                    width: '25%'
                };
            } else if (length < 12) {
                const hasNumber = /\d/.test(password);
                const hasSpecial = /[!@#$%^&*]/.test(password);
                
                if (!hasNumber && !hasSpecial) {
                    this.passwordStrength = {
                        text: 'Weak',
                        color: 'text-orange-600',
                        bgColor: 'bg-orange-500',
                        width: '50%'
                    };
                } else {
                    this.passwordStrength = {
                        text: 'Fair',
                        color: 'text-yellow-600',
                        bgColor: 'bg-yellow-500',
                        width: '75%'
                    };
                }
            } else {
                const hasUpper = /[A-Z]/.test(password);
                const hasLower = /[a-z]/.test(password);
                const hasNumber = /\d/.test(password);
                const hasSpecial = /[!@#$%^&*]/.test(password);
                
                const complexity = [hasUpper, hasLower, hasNumber, hasSpecial].filter(Boolean).length;
                
                if (complexity >= 3) {
                    this.passwordStrength = {
                        text: 'Strong',
                        color: 'text-green-600',
                        bgColor: 'bg-green-500',
                        width: '100%'
                    };
                } else {
                    this.passwordStrength = {
                        text: 'Fair',
                        color: 'text-yellow-600',
                        bgColor: 'bg-yellow-500',
                        width: '75%'
                    };
                }
            }
        },
        handleSubmit() {
            if (!this.isFormValid) {
                return;
            }
            this.loading = true;
            // Let the form submit normally to CakePHP
            document.getElementById('register-form').submit();
        }
    }
}).mount('#register-app');
</script>
