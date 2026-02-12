<?php
/**
 * @var \App\View\AppView $this
 * @var \Authentication\Identity|null $user
 */
$this->assign('title', 'Profile');

/**
 * Safely extract user info (no backend assumptions)
 */
$fullName = '';
$username = '';
$about = '';
$profilePhoto = '';

if (!empty($user)) {
    if (is_array($user)) {
        $fullName = $user['full_name'] ?? '';
        $username = $user['username'] ?? '';
            $about = $user['about'] ?? '';
            $profilePhoto = $user['profile_photo_path'] ?? '';
    } elseif (is_object($user)) {
        if (method_exists($user, 'get')) {
            $fullName = $user->get('full_name') ?? '';
            $username = $user->get('username') ?? '';
                $about = $user->get('about') ?? '';
                $profilePhoto = $user->get('profile_photo_path') ?? '';
        } else {
            $fullName = $user->full_name ?? '';
            $username = $user->username ?? '';
                $about = $user->about ?? '';
                $profilePhoto = $user->profile_photo_path ?? '';
        }
    }
}
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
    <div
        class="max-w-9xl mx-auto px-4 sm:px-6 flex gap-6 py-6 min-h-screen"
    >

        <!-- Sidebar -->
    <?= $this->element('sidebar', ['active' => 'profile']) ?>
    
    <!-- Main content (scrollable) -->
    <main class="flex-1 space-y-6 overflow-y-auto max-h-[calc(100vh-3rem)] no-scrollbar">
    
        <!-- Profile Header -->
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl p-8">
            <div class="flex flex-col items-center text-center">
    
                <!-- Avatar -->
                <div class="w-28 h-28 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600
                       flex items-center justify-center text-white text-4xl font-extrabold
                       shadow-lg overflow-hidden">
                    <?php if (!empty($profilePhoto)): ?>
                        <img src="<?= $this->Url->build('/img/profiles/' . htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8')) ?>" 
                             alt="Profile" class="w-full h-full object-cover" />
                    <?php else: ?>
                        <?= strtoupper(substr($fullName ?: $username ?: 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
    
                <!-- Name -->
                <h1 class="mt-4 text-2xl font-extrabold text-blue-800">
                    <?= htmlspecialchars($fullName ?: 'Your Name', ENT_QUOTES, 'UTF-8') ?>
                </h1>
    
                <!-- Username -->
                <p class="text-blue-500 text-sm">
                    @<?= htmlspecialchars($username ?: 'username', ENT_QUOTES, 'UTF-8') ?>
                </p>
    
                <!-- Bio -->
                <p class="mt-3 text-sm text-blue-600 max-w-xl">
                    <?= htmlspecialchars(
                        $about ?: 'Add a short bio here — tell people about yourself.',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>
    
                <!-- Actions -->
                <div class="mt-5">
                    <?= $this->Html->link(
                        'Edit profile',
                        '/profile/edit',
                        [
                            'class' => 'px-6 py-2 rounded-full border border-blue-500 text-blue-600 font-semibold text-sm hover:bg-blue-50 transition focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2'
                        ]
                    ) ?>
                </div>
    
            </div>
        </div>
        </main>

        <!-- Right sidebar -->
        <?= $this->element('right_sidebar') ?>
    </div>
</div>
