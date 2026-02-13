<?php
/**
 * Mobile Bottom Navigation
 * @var \App\View\AppView $this
 * @var string|null $active
 */

$active = $active ?? $this->request->getParam('action');

$baseItem = 'flex flex-col items-center justify-center gap-1 py-2 px-3 rounded-lg transition-all';
$activeItem = 'text-blue-600 bg-blue-50';
$inactiveItem = 'text-gray-500 hover:text-blue-600 hover:bg-blue-50';

$homeClass = $baseItem . ' ' . (($active === 'home' || $active === 'dashboard') ? $activeItem : $inactiveItem);
$profileClass = $baseItem . ' ' . (($active === 'profile' || $active === 'editProfile') ? $activeItem : $inactiveItem);
$settingsClass = $baseItem . ' ' . (($active === 'settings') ? $activeItem : $inactiveItem);
?>

<!-- Mobile Bottom Navigation - Only visible on mobile (< 768px) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur border-t border-blue-100 px-4 py-2 safe-area-inset-bottom">
    <div class="flex items-center justify-around max-w-lg mx-auto">
        <!-- Home -->
        <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>"
           class="<?= $homeClass ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-xs font-medium">Home</span>
        </a>

        <!-- Profile -->
        <a href="<?= $this->Url->build(['controller' => 'Profile', 'action' => 'profile']) ?>"
           class="<?= $profileClass ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-xs font-medium">Profile</span>
        </a>

        <!-- Settings -->
        <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'settings']) ?>"
           class="<?= $settingsClass ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="text-xs font-medium">Settings</span>
        </a>
    </div>
</nav>

<!-- Add padding to bottom of content to prevent bottom nav overlap on mobile -->
<style>
    @media (max-width: 767px) {
        body {
            padding-bottom: env(safe-area-inset-bottom, 0);
        }
    }
</style>
