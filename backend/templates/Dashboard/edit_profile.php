<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Edit Profile');

$fullName = $user->full_name ?? '';
$username = $user->username ?? '';
?>

<style>
/* Hide scrollbar but keep scrolling */
.no-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
.no-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}
</style>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <!-- Mobile Header -->
    <?= $this->element('mobile_header') ?>

    <!-- Top Navbar (Desktop/Tablet) -->
    <?= $this->element('top_navbar') ?>

    <!-- Main Container with proper padding for fixed navbar and bottom nav -->
    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <!-- Sidebar -->
            <?= $this->element('left_sidebar', ['active' => 'profile']) ?>

            <!-- Main content -->
            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">

            <!-- Context Header / Breadcrumb -->
            <div class="flex items-center gap-2 text-sm text-blue-600 font-medium">
                <?= $this->Html->link(
                    htmlspecialchars($fullName ?: 'Account', ENT_QUOTES, 'UTF-8'),
                    ['action' => 'profile'],
                    ['class' => 'text-blue-800 font-semibold hover:text-blue-900 hover:underline transition', 'escape' => false]
                ) ?>
                <span class="text-blue-400">→</span>
                <span class="text-blue-700">Edit profile</span>
            </div>

            <!-- Edit Card -->
            <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl p-6 lg:p-8">

                <?= $this->Form->create($user, [
                    'type' => 'file',
                    'class' => 'space-y-6'
                ]) ?>

                <!-- Profile Picture Section -->
                <div class="flex flex-col items-center pb-6 border-b border-blue-100">
                    <div class="relative group">
                        <!-- Avatar Display -->
                        <div id="avatar-preview" class="w-24 h-24 lg:w-32 lg:h-32 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600
                               flex items-center justify-center text-white text-3xl lg:text-4xl font-extrabold
                               shadow-lg overflow-hidden">
                            <?php if (!empty($user->profile_photo_path)): ?>
                                <img src="<?= $this->Url->build('/img/profiles/' . htmlspecialchars($user->profile_photo_path, ENT_QUOTES, 'UTF-8')) ?>" 
                                     alt="Profile" class="w-full h-full object-cover" id="current-avatar" />
                            <?php else: ?>
                                <span id="avatar-initial"><?= strtoupper(substr($fullName ?: $username ?: 'U', 0, 1)) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Upload Button Overlay -->
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

                <!-- Full name -->
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

                <!-- Username -->
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
                        'placeholder' => 'Choose a username'
                    ]) ?>
                </div>

                <!-- About -->
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

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-4">
                    <?= $this->Html->link(
                        'Cancel',
                        ['action' => 'profile'],
                        ['class' => 'text-sm font-medium text-blue-600 hover:underline order-2 sm:order-1']
                    ) ?>

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

            <!-- Right sidebar -->
            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <?= $this->element('mobile_nav', ['active' => 'profile']) ?>
</div>

<script>
    // Profile picture preview
    function previewProfilePicture(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            event.target.value = '';
            return;
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please upload an image file');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            const currentAvatar = document.getElementById('current-avatar');
            const initial = document.getElementById('avatar-initial');
            
            // Remove existing content
            if (currentAvatar) currentAvatar.remove();
            if (initial) initial.remove();
            
            // Add new image
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Profile Preview';
            img.className = 'w-full h-full object-cover';
            img.id = 'current-avatar';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
</script>