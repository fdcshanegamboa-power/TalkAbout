<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Register');
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-8">

            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-extrabold tracking-tight text-blue-700">
                    Talk<span class="text-indigo-600">About</span>
                </h1>
                <p class="text-sm text-blue-600 mt-2">
                    Create your account to get started
                </p>
            </div>

            <div id="register-app">
                <?= $this->Form->create($user, [
                    'id' => 'register-form',
                    '@submit.prevent' => 'handleSubmit',
                    'class' => 'space-y-5'
                ]) ?>

                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-1">
                        Full Name
                    </label>
                    <?= $this->Form->control('full_name', [
                        'label' => false,
                        'class' => 'w-full px-4 py-2.5 text-sm rounded-xl border border-blue-200 bg-white
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition',
                        'placeholder' => 'Enter your full name',
                        'required' => true,
                        'v-model' => 'form.full_name'
                    ]) ?>
                    <?php if ($user->getError('full_name')): ?>
                        <p class="text-red-600 text-xs mt-1">
                            <?= htmlspecialchars(implode(', ', $user->getError('full_name')), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-1">
                        Username
                    </label>
                    <?= $this->Form->control('username', [
                        'label' => false,
                        'class' => 'w-full px-4 py-2.5 text-sm rounded-xl border border-blue-200 bg-white
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition',
                        'placeholder' => 'Choose a username',
                        'required' => true,
                        'v-model' => 'form.username'
                    ]) ?>
                    <?php if ($user->getError('username')): ?>
                        <p class="text-red-600 text-xs mt-1">
                            <?= htmlspecialchars(implode(', ', $user->getError('username')), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-1">
                        Password
                    </label>
                    <?= $this->Form->control('password', [
                        'type' => 'password',
                        'label' => false,
                        'class' => 'w-full px-4 py-2.5 text-sm rounded-xl border border-blue-200 bg-white
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition',
                        'placeholder' => 'Minimum 8 characters',
                        'required' => true,
                        'v-model' => 'form.password',
                        '@input' => 'validatePassword'
                    ]) ?>
                    <?php if ($user->getError('password')): ?>
                        <p class="text-red-600 text-xs mt-1">
                            <?= htmlspecialchars(implode(', ', $user->getError('password')), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>

                    <div v-if="form.password" class="mt-2">
                        <div class="flex items-center text-xs" :class="passwordStrength.color">
                            <div class="flex-1">
                                <div class="h-1.5 rounded-full bg-blue-100">
                                    <div
                                        class="h-1.5 rounded-full transition-all duration-300"
                                        :class="passwordStrength.bgColor"
                                        :style="{ width: passwordStrength.width }"
                                    ></div>
                                </div>
                            </div>
                            <span class="ml-2 font-medium">{{ passwordStrength.text }}</span>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-1">
                        Confirm Password
                    </label>
                    <input
                        type="password"
                        v-model="confirmPassword"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-blue-200 bg-white
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Re-enter your password"
                        required
                    />
                    <p v-if="confirmPassword && !passwordsMatch" class="text-red-600 text-xs mt-1">
                        Passwords do not match
                    </p>
                    <p v-if="passwordsMatch && confirmPassword" class="text-green-600 text-xs mt-1">
                        Passwords match
                    </p>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="loading || !isFormValid"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white
                           py-2.5 rounded-xl font-semibold
                           hover:from-blue-700 hover:to-indigo-700
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                           transition-all duration-200
                           disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span v-if="!loading">Create Account</span>
                    <span v-else>Creating account...</span>
                </button>

                <?= $this->Form->end() ?>

                <!-- Footer -->
                <div class="text-center mt-6">
                    <p class="text-sm text-blue-700">
                        Already have an account?
                        <?= $this->Html->link(
                            'Login here',
                            ['controller' => 'Sessions', 'action' => 'login'],
                            ['class' => 'text-blue-600 hover:text-indigo-700 font-semibold transition']
                        ) ?>
                    </p>
                </div>
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
                   this.form.password.length >= 8 &&
                   this.passwordsMatch;
        }
    },
    methods: {
        validatePassword() {
            const p = this.form.password;
            if (!p) return this.passwordStrength = { text:'', color:'', bgColor:'', width:'0%' };

            if (p.length < 8) {
                this.passwordStrength = { text:'Too short', color:'text-red-600', bgColor:'bg-red-500', width:'25%' };
            } else if (p.length < 12) {
                this.passwordStrength = { text:'Fair', color:'text-yellow-600', bgColor:'bg-yellow-500', width:'75%' };
            } else {
                this.passwordStrength = { text:'Strong', color:'text-green-600', bgColor:'bg-green-500', width:'100%' };
            }
        },
        handleSubmit() {
            if (!this.isFormValid) return;
            this.loading = true;
            document.getElementById('register-form').submit();
        }
    }
}).mount('#register-app');
</script>
