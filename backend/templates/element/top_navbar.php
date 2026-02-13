<?php
/**
 * Top Navigation Bar (Desktop/Tablet only)
 * @var \App\View\AppView $this
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

<!-- Top Navbar - Hidden on mobile, visible on tablet/desktop (768px+) -->
<nav class="hidden md:block fixed top-0 left-0 right-0 z-50 bg-white border-b border-blue-100 shadow-sm">
    <div class="max-w-9xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
            
            <!-- Logo & Brand Name (always visible on tablet/desktop) -->
            <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>"
               class="flex items-center gap-2">
                <img src="<?= $this->Url->build('/logo/telupuluh-05.jpg') ?>" 
                     alt="TalkAbout" 
                     class="w-8 h-8 rounded-lg object-cover" />
                <span class="text-xl font-extrabold text-blue-700">
                    Talk<span class="text-indigo-600">About</span>
                </span>
            </a>

            <!-- Search Bar -->
            <div class="flex-1 max-w-xl mx-4">
                <div class="relative">
                    <input type="text" 
                           placeholder="Search TalkAbout..." 
                           class="w-full pl-10 pr-4 py-2 rounded-full bg-blue-50 border-2 border-transparent 
                                  focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-200 
                                  transition-all text-sm text-blue-900 placeholder-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-5 w-5 absolute left-3 top-2.5 text-blue-400" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-3">
                
                <!-- Create Post Button -->
                <button onclick="focusPostComposer()" 
                        class="hidden lg:flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 
                               text-white rounded-full hover:shadow-lg transition-all font-semibold text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Create Post</span>
                </button>

                <!-- Notifications -->
                <div class="relative">
                    <button class="p-2 rounded-full hover:bg-blue-50 transition-colors relative group">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="h-6 w-6 text-blue-700 group-hover:text-blue-900" 
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <!-- Notification Badge -->
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                </div>

                <!-- Messages (optional) -->
                <button class="p-2 rounded-full hover:bg-blue-50 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-6 w-6 text-blue-700 group-hover:text-blue-900" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </button>

                <!-- User Profile Dropdown -->
                <div class="relative" id="user-menu-container">
                    <button id="user-menu-button" 
                            class="flex items-center gap-2 p-1 rounded-full hover:bg-blue-50 transition-colors">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 
                                    flex items-center justify-center text-white font-bold shadow overflow-hidden">
                            <?php if (!empty($profilePhoto)): ?>
                                <img src="<?= $this->Url->build('/img/profiles/' . htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8')) ?>"
                                     alt="Profile" class="w-full h-full object-cover" />
                            <?php else: ?>
                                <?= strtoupper(substr($displayName ?: 'U', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="h-4 w-4 text-blue-700" 
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="user-dropdown" 
                         class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-blue-100 py-2 z-50">
                        <!-- User Info -->
                        <div class="px-4 py-3 border-b border-blue-100">
                            <p class="text-sm font-semibold text-blue-900">
                                <?= htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <p class="text-xs text-blue-500">
                                @<?= htmlspecialchars((string) $username, ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-2">
                            <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'profile']) ?>"
                               class="flex items-center gap-3 px-4 py-2 text-sm text-blue-800 hover:bg-blue-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Your Profile
                            </a>
                            <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'editProfile']) ?>"
                               class="flex items-center gap-3 px-4 py-2 text-sm text-blue-800 hover:bg-blue-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Profile
                            </a>
                            <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'settings']) ?>"
                               class="flex items-center gap-3 px-4 py-2 text-sm text-blue-800 hover:bg-blue-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </a>
                        </div>

                        <!-- Logout -->
                        <div class="border-t border-blue-100 pt-2">
                            <a href="<?= $this->Url->build(['controller' => 'Sessions', 'action' => 'logout']) ?>"
                               class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</nav>

<script>
// User menu dropdown toggle
(function() {
    function initUserMenu() {
        const menuButton = document.getElementById('user-menu-button');
        const dropdown = document.getElementById('user-dropdown');
        const container = document.getElementById('user-menu-container');
        
        if (!menuButton || !dropdown) return;
        
        menuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });
        
        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUserMenu);
    } else {
        initUserMenu();
    }
})();

// Focus post composer function
function focusPostComposer() {
    const composer = document.querySelector('textarea[placeholder*="What\'s happening"]');
    if (composer) {
        composer.focus();
        composer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
