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

<aside class="w-64 bg-white/90 backdrop-blur rounded-2xl shadow-lg p-6
         sticky top-6 self-start h-[calc(100vh-3rem)] overflow-y-auto">
    <!-- Brand -->
    <div class="mb-6">
        <h3 class="text-2xl font-extrabold tracking-tight text-blue-700">
            Talk<span class="text-indigo-600">About</span>
        </h3>
    </div>

    <!-- User Profile Section -->
    <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'profile']) ?>" 
       class="block mb-6 p-3 rounded-xl hover:bg-blue-50 transition group">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md overflow-hidden flex-shrink-0">
                <?php if (!empty($profilePhoto)): ?>
                    <img src="<?= $this->Url->build('/img/profiles/' . htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8')) ?>" 
                         alt="Profile" class="w-full h-full object-cover" />
                <?php else: ?>
                    <?= strtoupper(substr($displayName ?: 'U', 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-blue-800 truncate group-hover:text-blue-900">
                    <?= htmlspecialchars((string)$displayName, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="text-xs text-blue-500 truncate">
                    @<?= htmlspecialchars((string)$username, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        </div>
    </a>

    <!-- Navigation -->
    <nav>
        <ul class="space-y-2">
            <li>
                <a
                    href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>"
                    class="<?= $homeClass ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Home</span>
                </a>
            </li>

            <li>
                <a
                    href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'profile']) ?>"
                    class="<?= $profileClass ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profile</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="mt-auto pt-4 border-t border-blue-100">
        <?= $this->Html->link(
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg><span>Sign out</span>',
            ['controller' => 'Sessions', 'action' => 'logout'],
            [
                'class' => 'flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50 transition',
                'escape' => false
            ]
        ) ?>
    </div>
</aside>
