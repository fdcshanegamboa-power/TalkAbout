<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Login');
?>
<?= $this->Html->script('sessions/login', ['block' => 'script']) ?>

<style>
    [v-cloak] {
        display: none;
    }
</style>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 px-4 py-8">
    <div class="w-full max-w-md">
        <div class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl border border-blue-200/50 p-8 hover:shadow-blue-200/50 transition-shadow duration-300">

            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <?= $this->Html->image('/logo/telupuluh-05.jpg', [
                        'alt' => 'TalkAbout Logo',
                        'class' => 'w-20 h-20 rounded-2xl shadow-lg object-cover ring-2 ring-blue-200'
                    ]) ?>
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight text-blue-700">
                    Talk<span class="text-indigo-600">About</span>
                </h1>
                <p class="text-sm text-blue-600 mt-2 font-medium">
                    Sign in to your account
                </p>
            </div>

            <div id="login-app" v-cloak>
                <?= $this->Form->create(null, [
                    'id' => 'login-form',
                    '@submit.prevent' => 'handleSubmit',
                    'class' => 'space-y-5'
                ]) ?>

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
                    <span v-if="!loading">Sign In</span>
                    <span v-else class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Signing in...
                    </span>
                </button>

                <?= $this->Form->end() ?>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-blue-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-2 text-blue-500 font-semibold">or</span>
                    </div>
                </div>

                <div class="text-center">
                    <p class="text-sm text-blue-700">
                        Don't have an account?
                        <?= $this->Html->link(
                            'Register here',
                            ['controller' => 'Users', 'action' => 'register'],
                            ['class' => 'text-blue-600 hover:text-indigo-700 font-bold transition-colors duration-200 underline-offset-2 hover:underline']
                        ) ?>
                    </p>
                </div>
                
                <?= $this->element('modal') ?>
            </div>

        </div>
    </div>
</div>
