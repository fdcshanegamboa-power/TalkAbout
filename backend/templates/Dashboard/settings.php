<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Settings');
?>
<?= $this->Html->script('components/left_sidebar', ['block' => 'script']) ?>
<?= $this->Html->script('dashboard/settings', ['block' => 'script']) ?>

<?= $this->element('top_navbar') ?>

<div id="settings-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <?= $this->element('mobile_header') ?>

    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <?= $this->element('left_sidebar', ['active' => 'settings']) ?>

            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">

            <div class="hidden md:block">
                <h1 class="text-2xl lg:text-3xl font-extrabold text-blue-800">Settings</h1>
                <p class="text-sm text-blue-600 mt-1">Manage your account security and preferences</p>
            </div>

            <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-blue-100">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 
                                flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg lg:text-xl font-bold text-blue-800">Change Password</h2>
                        <p class="text-xs lg:text-sm text-blue-600">Update your password to keep your account secure</p>
                    </div>
                </div>

                <?= $this->Form->create(null, [
                    'url' => ['action' => 'settings'],
                    'id' => 'settings-form',
                    '@submit.prevent' => 'handleSubmit',
                    'class' => 'space-y-5'
                ]) ?>

                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Current Password
                            </span>
                        </label>
                        <?= $this->Form->control('current_password', [
                            'type' => 'password',
                            'label' => false,
                            'required' => true,
                            'v-model' => 'form.current_password',
                            'class' => 'w-full px-4 py-3 rounded-lg border-2 border-blue-200
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                        transition-all text-blue-900 placeholder-blue-300',
                            'placeholder' => 'Enter your current password'
                        ]) ?>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                New Password
                            </span>
                        </label>
                        <?= $this->Form->control('new_password', [
                            'type' => 'password',
                            'label' => false,
                            'required' => true,
                            'v-model' => 'form.new_password',
                            '@input' => 'validatePassword',
                            'class' => 'w-full px-4 py-3 rounded-lg border-2 border-blue-200
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                        transition-all text-blue-900 placeholder-blue-300',
                            'placeholder' => 'Enter new password (min. 8 characters)'
                        ]) ?>

                        <div v-if="form.new_password" class="mt-3 space-y-2">
                            <div class="flex items-center text-xs" :class="passwordStrength.color">
                                <div class="flex-1">
                                    <div class="h-2 rounded-full bg-gray-200">
                                        <div
                                            class="h-2 rounded-full transition-all duration-300"
                                            :class="passwordStrength.bgColor"
                                            :style="{ width: passwordStrength.width }"
                                        ></div>
                                    </div>
                                </div>
                                <span class="ml-3 font-semibold">{{ passwordStrength.text }}</span>
                            </div>
                            
                            <!-- Password Requirements Checklist -->
                            <div class="mt-3 space-y-1.5 bg-blue-50 rounded-lg p-3 border border-blue-100">
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
                        
                        <!-- Warning if same password -->
                        <div v-if="isSamePassword" class="mt-2 flex items-start gap-2 bg-red-50 border border-red-200 rounded-lg p-3">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-red-800">Cannot use the same password</p>
                                <p class="text-xs text-red-600 mt-0.5">Your new password must be different from your current password</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Confirm New Password
                            </span>
                        </label>
                        <?= $this->Form->control('confirm_password', [
                            'type' => 'password',
                            'label' => false,
                            'required' => true,
                            'v-model' => 'confirmPassword',
                            'class' => 'w-full px-4 py-3 rounded-lg border-2 border-blue-200
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                        transition-all text-blue-900 placeholder-blue-300',
                            'placeholder' => 'Confirm your new password'
                        ]) ?>
                        <div v-if="confirmPassword && !passwordsMatch" class="mt-2 flex items-center gap-2 text-red-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-xs font-medium">Passwords do not match</p>
                        </div>
                        <div v-if="passwordsMatch && confirmPassword" class="mt-2 flex items-center gap-2 text-green-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-xs font-medium">Passwords match</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-6 border-t border-blue-100">
                        <a
                            :href="sidebarUser ? '/profile/' + (sidebarUser.username) : '/profile'"
                            class="text-sm font-medium text-blue-600 hover:underline order-2 sm:order-1"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            :disabled="loading || !isFormValid"
                            class="w-full sm:w-auto px-6 py-2.5 rounded-full
                                        bg-gradient-to-r from-blue-600 to-indigo-600
                                        text-white font-semibold text-sm
                                        hover:from-blue-700 hover:to-indigo-700 transition shadow-lg
                                        flex items-center justify-center gap-2 order-1 sm:order-2 disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <span v-if="!loading">Update Password</span>
                            <span v-else>Updating...</span>
                        </button>
                    </div>

                <?= $this->Form->end() ?>

            </div>
        </main>

        </div>
    </div>

    <?= $this->element('mobile_nav', ['active' => 'settings']) ?>
    
    <?= $this->element('modal') ?>
</div>