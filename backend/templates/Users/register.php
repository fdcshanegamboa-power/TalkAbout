<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Register');
?>
<?= $this->Html->script('users/register', ['block' => 'script']) ?>

<style>
    [v-cloak] {
        display: none;
    }
</style>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-8">

            <div class="text-center mb-8">
                <h1 class="text-4xl font-extrabold tracking-tight text-blue-700">
                    Talk<span class="text-indigo-600">About</span>
                </h1>
                <p class="text-sm text-blue-600 mt-2">
                    Create your account to get started
                </p>
            </div>

            <div id="register-app" v-cloak>
                <?= $this->Form->create($user, [
                    'id' => 'register-form',
                    '@submit.prevent' => 'handleSubmit',
                    'class' => 'space-y-5'
                ]) ?>

                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-1">
                        Full Name
                    </label>
                    <?= $this->Form->control('full_name', [
                        'label' => false,
                        'value' => $user->full_name ?? '',
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

                <div>
                    <label class="block text-sm font-medium text-blue-700 mb-1">
                        Username
                    </label>
                    <?= $this->Form->control('username', [
                        'label' => false,
                        'value' => $user->username ?? '',
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
