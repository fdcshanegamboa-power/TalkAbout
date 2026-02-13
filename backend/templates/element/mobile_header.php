<?php
/**
 * Mobile Header with Logo and Menu
 * @var \App\View\AppView $this
 * @var string|null $pageTitle
 */

$identity = $this->request->getAttribute('identity');
$displayName = '';
$username = '';
$profilePhoto = '';

if ($identity) {
    $userId = null;
    if (method_exists($identity, 'getIdentifier')) {
        $userId = $identity->getIdentifier();
    } elseif (method_exists($identity, 'get')) {
        $userId = $identity->get('id');
    } elseif (isset($identity->id)) {
        $userId = $identity->id;
    }

    if ($userId) {
        $usersTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Users');
        try {
            $user = $usersTable->get($userId);
            $displayName = $user->full_name ?? $user->username ?? '';
            $username = $user->username ?? '';
            $profilePhoto = $user->profile_photo_path ?? '';
        } catch (\Exception $e) {
            $displayName = $identity->get('full_name') ?? $identity->get('username') ?? '';
            $username = $identity->get('username') ?? '';
        }
    } else {
        $displayName = $identity->get('full_name') ?? $identity->get('username') ?? '';
        $username = $identity->get('username') ?? '';
    }
}
?>

<!-- Mobile Header - Only visible on mobile (< 768px) -->
<div class="md:hidden sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-blue-100">
    <div class="flex items-center justify-between px-4 py-3">
        <!-- Logo Section (Left) -->
        <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>"
           class="flex items-center gap-2">
            <img src="<?= $this->Url->build('/logo/telupuluh-05.jpg') ?>" 
                 alt="TalkAbout" 
                 class="w-8 h-8 rounded-lg object-cover" />
            <h1 class="text-lg font-extrabold text-blue-700">
                Talk<span class="text-indigo-600">About</span>
            </h1>
        </a>

        <!-- Hamburger Menu Button (Right) -->
        <button id="mobile-menu-toggle" 
                class="p-2 rounded-lg text-blue-700 hover:bg-blue-50 transition-colors"
                aria-label="Open menu">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
</div>

<!-- Mobile Dropdown Menu -->
<div id="mobile-dropdown-menu" 
     class="md:hidden fixed inset-0 z-50 hidden"
     style="background-color: rgba(0, 0, 0, 0.4);">
    <div class="absolute top-0 right-0 w-72 h-full bg-white shadow-2xl transform transition-transform duration-300 translate-x-full"
         id="mobile-menu-panel">
        
        <!-- Menu Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-white text-lg font-bold">Menu</h2>
                <button id="mobile-menu-close" 
                        class="text-white/90 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- User Info -->
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-bold shadow overflow-hidden flex-shrink-0">
                    <?php if (!empty($profilePhoto)): ?>
                        <img src="<?= $this->Url->build('/img/profiles/' . htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8')) ?>"
                            alt="Profile" class="w-full h-full object-cover" />
                    <?php else: ?>
                        <?= strtoupper(substr($displayName ?: 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white font-semibold truncate">
                        <?= htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="text-white/80 text-sm truncate">
                        @<?= htmlspecialchars((string) $username, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'profile']) ?>"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg text-blue-800 hover:bg-blue-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="font-medium">View Profile</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'editProfile']) ?>"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg text-blue-800 hover:bg-blue-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span class="font-medium">Edit Profile</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'settings']) ?>"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg text-blue-800 hover:bg-blue-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="font-medium">Settings</span>
                    </a>
                </li>
                <li class="border-t border-blue-100 mt-4 pt-2">
                    <a href="<?= $this->Url->build(['controller' => 'Sessions', 'action' => 'logout']) ?>"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="font-medium">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<script>
(function() {
    function initMobileMenu() {
        const menuToggle = document.getElementById('mobile-menu-toggle');
        const menuClose = document.getElementById('mobile-menu-close');
        const menuOverlay = document.getElementById('mobile-dropdown-menu');
        const menuPanel = document.getElementById('mobile-menu-panel');
        
        if (!menuToggle || !menuOverlay || !menuPanel) return;
        
        function openMenu() {
            menuOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Trigger animation
            setTimeout(() => {
                menuPanel.classList.remove('translate-x-full');
            }, 10);
        }
        
        function closeMenu() {
            menuPanel.classList.add('translate-x-full');
            setTimeout(() => {
                menuOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }
        
        menuToggle.addEventListener('click', openMenu);
        
        if (menuClose) {
            menuClose.addEventListener('click', closeMenu);
        }
        
        // Close when clicking overlay
        menuOverlay.addEventListener('click', function(e) {
            if (e.target === menuOverlay) {
                closeMenu();
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileMenu);
    } else {
        initMobileMenu();
    }
})();
</script>
