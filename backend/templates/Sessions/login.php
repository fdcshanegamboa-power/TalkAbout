<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Login');
?>

<div class="container mx-auto max-w-md px-4">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">TalkAbout</h1>
            <p class="text-gray-600 mt-2">Sign in to your account</p>
        </div>
        
        <div id="login-app">
            <?= $this->Form->create(null, [
                'id' => 'login-form',
                '@submit.prevent' => 'handleSubmit',
                'class' => 'space-y-6'
            ]) ?>
            
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    Username
                </label>
                <?= $this->Form->control('username', [
                    'type' => 'text',
                    'label' => false,
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                    'placeholder' => 'Enter your username',
                    'required' => true,
                    'v-model' => 'form.username'
                ]) ?>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password
                </label>
                <?= $this->Form->control('password', [
                    'type' => 'password',
                    'label' => false,
                    'class' => 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                    'placeholder' => 'Enter your password',
                    'required' => true,
                    'v-model' => 'form.password'
                ]) ?>
            </div>
            
            <div>
                <button 
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:bg-blue-400"
                >
                    <span v-if="!loading">Sign In</span>
                    <span v-else>Signing in...</span>
                </button>
            </div>
            
            <?= $this->Form->end() ?>
            
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <?= $this->Html->link('Register here', ['controller' => 'Users', 'action' => 'register'], [
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
                username: '',
                password: ''
            },
            loading: false
        }
    },
    methods: {
        handleSubmit() {
            this.loading = true;
            // Let the form submit normally to CakePHP
            document.getElementById('login-form').submit();
        }
    }
}).mount('#login-app');
</script>
