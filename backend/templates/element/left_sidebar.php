<?php
/**
 * Sidebar element
 * @var \App\View\AppView $this
 * @var string|null $active
 */

$active = $active ?? $this->request->getParam('action');

$baseItem = 'flex items-center px-4 py-2.5 rounded-xl text-sm font-medium transition';
$activeItem = 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow';
$inactiveItem = 'text-blue-700 hover:bg-blue-50';

$homeClass = $baseItem . ' ' . (($active === 'home' || $active === 'dashboard') ? $activeItem : $inactiveItem);
$profileClass = $baseItem . ' ' . (($active === 'profile' || $active === 'editProfile') ? $activeItem : $inactiveItem);

$identity = $this->request->getAttribute('identity');
$displayName = '';
$username = '';
$profilePhoto = '';

if ($identity) {
    // Get user ID and load full entity to get profile photo
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
            // Fallback to identity data
            $displayName = $identity->get('full_name') ?? $identity->get('username') ?? '';
            $username = $identity->get('username') ?? '';
        }
    } else {
        $displayName = $identity->get('full_name') ?? $identity->get('username') ?? '';
        $username = $identity->get('username') ?? '';
    }
}
?>

<!-- Left Sidebar - Hidden on mobile (< 768px), Full width on tablet+ (768px+) -->
<aside id="left-sidebar" 
       class="hidden md:block md:sticky md:top-20 md:z-10
              md:w-64 md:max-h-[calc(100vh-5.5rem)] 
              bg-white/90 backdrop-blur md:rounded-2xl 
              md:shadow-lg md:p-6 
              overflow-y-auto
              md:self-start">
    
    <!-- User Profile Section - Enlarged and Emphasized -->
    <a href="<?= $this->Url->build(['controller' => 'Profile', 'action' => 'profile']) ?>"
        class="block mb-8 p-4 rounded-xl hover:bg-blue-50 transition group">
        <div class="flex flex-col items-center gap-3">
            <!-- Profile Image - Larger and more prominent -->
            <div class="relative">
                <div
                    class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold text-3xl shadow-lg overflow-hidden flex-shrink-0 ring-4 ring-blue-100 group-hover:ring-blue-200 transition">
                    <?php if (!empty($profilePhoto)): ?>
                        <img src="<?= $this->Url->build('/img/profiles/' . htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8')) ?>"
                            alt="Your Profile" class="w-full h-full object-cover" />
                    <?php else: ?>
                        <?= strtoupper(substr($displayName ?: 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <!-- Online status indicator -->
                <div class="absolute bottom-0 right-0 w-5 h-5 bg-green-500 rounded-full border-4 border-white"></div>
            </div>
            <!-- User Info - Always shown, centered below photo -->
            <div class="w-full text-center">
                <div class="text-xs font-medium text-blue-500 uppercase tracking-wide mb-0.5">
                    You
                </div>
                <div class="text-base font-bold text-blue-900 truncate group-hover:text-blue-700">
                    <?= htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="text-sm text-blue-600 truncate">
                    @<?= htmlspecialchars((string) $username, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </div>
    </a>

    <!-- Navigation -->
    <nav>
        <ul class="space-y-2">
            <li>
                <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>"
                    class="<?= $homeClass ?>"
                    title="Home">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Home</span>
                </a>
            </li>

            <li>
                <a href="<?= $this->Url->build(['controller' => 'Profile', 'action' => 'profile']) ?>"
                    class="<?= $profileClass ?>"
                    title="Profile">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <?php
                $settingsClass = $baseItem . ' ' . (($active === 'settings') ? $activeItem : $inactiveItem);
                ?>
                <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'settings']) ?>"
                    class="<?= $settingsClass ?>"
                    title="Settings">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="mt-auto pt-4 border-t border-blue-100">
        <?= $this->Html->link(
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg><span>Sign out</span>',
            ['controller' => 'Sessions', 'action' => 'logout'],
            [
                'class' => 'flex items-center gap-3 w-full py-2.5 px-4 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50 transition',
                'escape' => false,
                'title' => 'Sign out'
            ]
        ) ?>
    </div>
</aside>