<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Login');
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
                    Sign in to your account
                </p>
            </div>

            <div id="login-app">
                <?= $this->Form->create(null, [
                    'id' => 'login-form',
                    '@submit.prevent' => 'handleSubmit',
                    'class' => 'space-y-5'
                ]) ?>

                <!-- Username -->
                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-1">
                        Username
                    </label>
                    <?= $this->Form->control('username', [
                        'type' => 'text',
                        'label' => false,
                        'class' => 'w-full px-4 py-2.5 text-sm rounded-xl border border-blue-200 bg-white
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition',
                        'placeholder' => 'Enter your username',
                        'required' => true,
                        'v-model' => 'form.username'
                    ]) ?>
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
                        'placeholder' => 'Enter your password',
                        'required' => true,
                        'v-model' => 'form.password'
                    ]) ?>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white
                           py-2.5 rounded-xl font-semibold
                           hover:from-blue-700 hover:to-indigo-700
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                           transition-all duration-200
                           disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span v-if="!loading">Sign In</span>
                    <span v-else>Signing in...</span>
                </button>

                <?= $this->Form->end() ?>

                <!-- Footer -->
                <div class="text-center mt-6">
                    <p class="text-sm text-blue-700">
                        Don’t have an account?
                        <?= $this->Html->link(
                            'Register here',
                            ['controller' => 'Users', 'action' => 'register'],
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
                username: '',
                password: ''
            },
            loading: false
        }
    },
    methods: {
        handleSubmit() {
            this.loading = true;
            document.getElementById('login-form').submit();
        }
    }
}).mount('#login-app');
</script>
