<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Edit Profile');
$fullName = $user->full_name ?? '';
$username = $user->username ?? '';
$this->Html->script('profile/edit_profile', ['block' => 'script']);

?>

<style>
.no-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
.no-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}
</style>

<?= $this->element('top_navbar') ?>

<div id="edit-profile-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <?= $this->element('mobile_header') ?>

    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <?= $this->element('left_sidebar', ['active' => 'profile']) ?>

            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">

            <div v-if="profileUser" class="flex items-center gap-2 text-sm text-blue-600 font-medium">
                <a href="/profile" class="text-blue-800 font-semibold hover:text-blue-900 hover:underline transition">
                    {{ profileUser.full_name || 'Account' }}
                </a>
                <span class="text-blue-400">→</span>
                <span class="text-blue-700">Edit profile</span>
            </div>

            <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl p-6 lg:p-8">

                <?= $this->Form->create($user, [
                    'type' => 'file',
                    'class' => 'space-y-6'
                ]) ?>

                <div v-if="profileUser" class="flex flex-col items-center pb-6 border-b border-blue-100">
                    <div class="relative group">
                        <div id="avatar-preview" class="w-24 h-24 lg:w-32 lg:h-32 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600
                               flex items-center justify-center text-white text-3xl lg:text-4xl font-extrabold
                               shadow-lg overflow-hidden">
                            <template v-if="profileUser.profile_photo">
                                <img :src="'/img/profiles/' + profileUser.profile_photo" 
                                     alt="Profile" class="w-full h-full object-cover" id="current-avatar" />
                            </template>
                            <template v-else>
                                <span id="avatar-initial">{{ profileUser.initial }}</span>
                            </template>
                        </div>
                        
                        <label for="profile-picture-input" 
                               class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center
                                      opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 lg:h-8 lg:w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </label>
                        
                        <?= $this->Form->file('profile_picture', [
                            'id' => 'profile-picture-input',
                            'accept' => 'image/*',
                            'class' => 'hidden',
                            'onchange' => 'previewProfilePicture(event)'
                        ]) ?>
                    </div>
                    
                    <p class="mt-3 text-xs lg:text-sm text-blue-600">
                        Click to upload a new profile picture
                    </p>
                    <p class="text-xs text-blue-400 mt-1">
                        JPG, PNG or GIF (max. 5MB)
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-2">
                        <span class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Full name
                        </span>
                    </label>
                    <?= $this->Form->control('full_name', [
                        'label' => false,
                        'class' => 'w-full px-4 py-3 rounded-lg border-2 border-blue-200
                                    focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                    transition-all text-blue-900 placeholder-blue-300',
                        'placeholder' => 'Enter your full name'
                    ]) ?>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-2">
                        <span class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                            Username
                        </span>
                    </label>
                    <?= $this->Form->control('username', [
                        'label' => false,
                        'class' => 'w-full px-4 py-3 rounded-lg border-2 border-blue-200
                                    focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                    transition-all text-blue-900 placeholder-blue-300',
                        'disabled' => true
                    ]) ?>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-blue-700 mb-2">
                        <span class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            About
                        </span>
                    </label>
                    <?= $this->Form->control('about', [
                        'type' => 'textarea',
                        'label' => false,
                        'rows' => 4,
                        'class' => 'w-full px-4 py-3 rounded-lg border-2 border-blue-200
                                    focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                    transition-all resize-none text-blue-900 placeholder-blue-300',
                        'placeholder' => 'Tell us about yourself...'
                    ]) ?>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-4">
                    <a
                            :href="profileUser ? '/profile/' + (profileUser.username) : '/profile'"
                            class="text-sm font-medium text-blue-600 hover:underline order-2 sm:order-1"
                        >
                            Cancel
                        </a>

                    <?= $this->Form->button('Save changes', [
                        'type' => 'submit',
                        'class' => 'w-full sm:w-auto px-6 py-2.5 rounded-full
                                    bg-gradient-to-r from-blue-600 to-indigo-600
                                    text-white font-semibold text-sm
                                    hover:from-blue-700 hover:to-indigo-700 transition shadow-lg order-1 sm:order-2'
                    ]) ?>
                </div>

                <?= $this->Form->end() ?>

            </div>
        </main>

            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <?= $this->element('mobile_nav', ['active' => 'profile']) ?>
</div>