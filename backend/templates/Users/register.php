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
                        'autocomplete' => 'off',
                        'maxlength' => 150,
                        'v-model' => 'form.full_name',
                        '@input' => 'validateFullName'
                    ]) ?>
                    <!-- Frontend validation error -->
                    <p v-if="validationErrors.full_name" class="text-red-600 text-xs mt-1">
                        {{ validationErrors.full_name }}
                    </p>
                    <!-- Backend validation error (only show if no frontend error) -->
                    <?php if ($user->getError('full_name')): ?>
                        <p v-if="!validationErrors.full_name" class="text-red-600 text-xs mt-1">
                            <?= htmlspecialchars(implode(', ', $user->getError('full_name')), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-medium text-blue-700">
                            Username
                        </label>
                        <span v-if="form.username.length > 0" 
                              class="text-xs font-medium"
                              :class="{
                                  'text-red-600': form.username.length >= 15,
                                  'text-yellow-600': form.username.length >= 12 && form.username.length < 15,
                                  'text-blue-500': form.username.length < 12
                              }">
                            {{ form.username.length }}/15
                        </span>
                    </div>
                    <?= $this->Form->control('username', [
                        'label' => false,
                        'value' => $user->username ?? '',
                        'class' => 'w-full px-4 py-2.5 text-sm rounded-xl border transition',
                        ':class' => '{
                            "border-red-300 bg-red-50 focus:ring-red-500 focus:border-red-500": validationErrors.username,
                            "border-blue-200 bg-white focus:ring-blue-500 focus:border-blue-500": !validationErrors.username
                        }',
                        'placeholder' => 'Choose a username (letters, numbers, underscore)',
                        'required' => true,
                        'autocomplete' => 'off',
                        'maxlength' => 15,
                        'v-model' => 'form.username',
                        '@input' => 'validateUsername'
                    ]) ?>
                    <!-- Frontend validation error -->
                    <p v-if="validationErrors.username" class="text-red-600 text-xs mt-1">
                        {{ validationErrors.username }}
                    </p>
                    <!-- Backend validation error (only show if no frontend error) -->
                    <?php if ($user->getError('username')): ?>
                        <p v-if="!validationErrors.username" class="text-red-600 text-xs mt-1">
                            <?= htmlspecialchars(implode(', ', $user->getError('username')), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>
                    <!-- Real-time availability check feedback -->
                    <p v-if="usernameCheck.checking" class="text-gray-500 text-xs mt-1 flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Checking availability...
                    </p>
                    <p v-if="!validationErrors.username && usernameCheck.available === true" class="text-green-600 text-xs mt-1 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ usernameCheck.message }}
                    </p>
                    <!-- Helper text -->
                    <?php $hasUsernameError = !empty($user->getError('username')); ?>
                    <p v-if="!validationErrors.username && !usernameCheck.checking && usernameCheck.available === null && !<?= json_encode($hasUsernameError) ?>" class="text-blue-500 text-xs mt-1">
                        Only letters, numbers, and underscores. No spaces allowed.
                    </p>
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
                        'placeholder' => 'Min 8 characters, no spaces',
                        'required' => true,
                        'autocomplete' => 'new-password',
                        'v-model' => 'form.password',
                        '@input' => 'validatePassword'
                    ]) ?>
                    <!-- Frontend validation error -->
                    <p v-if="validationErrors.password" class="text-red-600 text-xs mt-1">
                        {{ validationErrors.password }}
                    </p>
                    <!-- Backend validation error (only show if no frontend error) -->
                    <?php if ($user->getError('password')): ?>
                        <p v-if="!validationErrors.password" class="text-red-600 text-xs mt-1">
                            <?= htmlspecialchars(implode(', ', $user->getError('password')), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>

                    <div v-if="form.password && !validationErrors.password" class="mt-2">
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

                    <!-- Password Requirements Checklist -->
                    <div v-if="form.password" class="mt-3 space-y-1.5 bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <p class="text-xs font-semibold text-blue-700 mb-2">Password must contain:</p>
                        <div class="flex items-center gap-2 text-xs" :class="passwordRequirements.minLength ? 'text-green-600' : 'text-gray-500'">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path v-if="passwordRequirements.minLength" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 9a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span :class="passwordRequirements.minLength ? 'font-medium' : ''">At least 8 characters</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs" :class="passwordRequirements.hasUpper ? 'text-green-600' : 'text-gray-500'">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path v-if="passwordRequirements.hasUpper" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 9a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span :class="passwordRequirements.hasUpper ? 'font-medium' : ''">One uppercase letter</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs" :class="passwordRequirements.hasLower ? 'text-green-600' : 'text-gray-500'">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path v-if="passwordRequirements.hasLower" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 9a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span :class="passwordRequirements.hasLower ? 'font-medium' : ''">One lowercase letter</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs" :class="passwordRequirements.hasNumber ? 'text-green-600' : 'text-gray-500'">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path v-if="passwordRequirements.hasNumber" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 9a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span :class="passwordRequirements.hasNumber ? 'font-medium' : ''">One number</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs" :class="passwordRequirements.hasSpecial ? 'text-green-600' : 'text-gray-400'">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path v-if="passwordRequirements.hasSpecial" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 9a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span :class="passwordRequirements.hasSpecial ? 'font-medium' : ''">One special character (optional)</span>
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
                        @input="validateConfirmPassword"
                        autocomplete="new-password"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-blue-200 bg-white
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                        placeholder="Re-enter your password"
                        required
                    />
                    <p v-if="validationErrors.confirmPassword" class="text-red-600 text-xs mt-1">
                        {{ validationErrors.confirmPassword }}
                    </p>
                    <p v-if="passwordsMatch && confirmPassword && !validationErrors.confirmPassword" class="text-green-600 text-xs mt-1">
                        ✓ Passwords match
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
                        Already have an account?
                        <?= $this->Html->link(
                            'Login here',
                            ['controller' => 'Sessions', 'action' => 'login'],
                            ['class' => 'text-blue-600 hover:text-indigo-700 font-bold transition-colors duration-200 underline-offset-2 hover:underline']
                        ) ?>
                    </p>
                </div>
                
                <!-- Confirmation/Alert Modal -->
    <div v-if="modal.show" 
         @click="handleModalCancel"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/30 backdrop-blur-sm p-4"
         style="animation: fadeIn 0.2s ease-in;">
        
        <div @click.stop 
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all"
             style="animation: scaleIn 0.2s ease-out;">
            
            <!-- Modal Header -->
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0" v-html="modalIcon"></div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900">{{ modal.title }}</h3>
                        <p class="mt-2 text-sm text-gray-600 whitespace-pre-line">{{ modal.message }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-6 pb-6 flex justify-end gap-3">
                <button v-if="modal.type === 'confirm' && modal.onCancel"
                        @click="handleModalCancel"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                    {{ modal.cancelText }}
                </button>
                <button @click="handleModalConfirm"
                        :class="{
                            'bg-blue-600 hover:bg-blue-700': modal.type === 'confirm' || modal.type === 'info',
                            'bg-green-600 hover:bg-green-700': modal.type === 'success',
                            'bg-red-600 hover:bg-red-700': modal.type === 'error',
                            'bg-yellow-600 hover:bg-yellow-700': modal.type === 'warning'
                        }"
                        class="px-4 py-2 text-white rounded-lg font-semibold transition">
                    {{ modal.confirmText }}
                </button>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes scaleIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>
