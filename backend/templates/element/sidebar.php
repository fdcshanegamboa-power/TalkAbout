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
$profileClass = $baseItem . ' ' . (($active === 'profile') ? $activeItem : $inactiveItem);

$identity = $this->request->getAttribute('identity');
$displayName = '';
if ($identity) {
    $displayName = $identity->get('full_name') ?? $identity->get('username') ?? '';
}
?>

<aside class="w-64 bg-white/90 backdrop-blur rounded-2xl shadow-lg p-6
         sticky top-6 self-start h-[calc(100vh-3rem)] overflow-y-auto">
    <!-- Brand -->
    <div class="mb-8">
        <h3 class="text-2xl font-extrabold tracking-tight text-blue-700">
            Talk<span class="text-indigo-600">About</span>
        </h3>
        <p class="text-xs text-blue-500 mt-1">
            Dashboard
        </p>
    </div>

    <!-- Navigation -->
    <nav>
        <ul class="space-y-2">
            <li>
                <a
                    href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>"
                    class="<?= $homeClass ?>"
                >
                    <span>Home</span>
                </a>
            </li>

            <li>
                <a
                    href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'profile']) ?>"
                    class="<?= $profileClass ?>"
                >
                    <span>Profile</span>
                </a>
            </li>
        </ul>
    </nav>

    <div class="mt-6 pt-4 border-t border-blue-50">
        <div class="mb-3 text-sm text-blue-600">Signed in</div>
        <div class="flex items-center justify-between">
            <div class="text-sm text-blue-800 font-medium">
                <?= htmlspecialchars((string)$displayName, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div>
                <?= $this->Html->link(
                    'Sign out',
                    ['controller' => 'Sessions', 'action' => 'logout'],
                    [
                        'class' => 'inline-flex items-center gap-1.5
                    text-xs font-semibold text-red-500
                    hover:text-red-600 transition',
                        'escape' => false
                    ]
                ) ?>
            </div>
        </div>
    </div>
</aside>
