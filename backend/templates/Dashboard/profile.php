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

if (!empty($user)) {
    if (is_array($user)) {
        $fullName = $user['full_name'] ?? '';
        $username = $user['username'] ?? '';
    } elseif (is_object($user)) {
        if (method_exists($user, 'get')) {
            $fullName = $user->get('full_name') ?? '';
            $username = $user->get('username') ?? '';
        } else {
            $fullName = $user->full_name ?? '';
            $username = $user->username ?? '';
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
        id="dashboard-app"
        class="max-w-9xl mx-auto px-4 sm:px-6 flex gap-6 py-6 min-h-screen"
    >

        <!-- Sidebar -->
        <?= $this->element('sidebar', ['active' => 'profile']) ?>

        <!-- Main content (scrollable) -->
        <main class="flex-1 space-y-6 overflow-y-auto max-h-[calc(100vh-3rem)] no-scrollbar">

            <!-- Profile Header -->
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-6">
                <div class="flex items-center gap-6">

                    <!-- Avatar (placeholder) -->
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600
                                flex items-center justify-center text-white text-3xl font-extrabold">
                        <?= strtoupper(substr($fullName ?: $username ?: 'U', 0, 1)) ?>
                    </div>

                    <!-- User Info -->
                    <div>
                        <h1 class="text-2xl font-extrabold text-blue-700">
                            <?= htmlspecialchars($fullName ?: 'Your Name', ENT_QUOTES, 'UTF-8') ?>
                        </h1>
                        <p class="text-blue-600">
                            @<?= htmlspecialchars($username ?: 'username', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-6">
                <h2 class="text-lg font-extrabold text-blue-700 mb-4">
                    Profile Information
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-blue-600">Full Name</p>
                        <p class="mt-1 text-blue-800 font-semibold">
                            <?= htmlspecialchars($fullName ?: '—', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-blue-600">Username</p>
                        <p class="mt-1 text-blue-800 font-semibold">
                            <?= htmlspecialchars($username ?: '—', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-blue-600">Email</p>
                        <p class="mt-1 text-blue-400 italic">
                            Not available yet
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-medium text-blue-600">Account Status</p>
                        <span class="inline-flex items-center mt-1 px-3 py-1 rounded-full
                                     text-xs font-semibold bg-green-100 text-green-700">
                            Active
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl border border-blue-100 p-6 flex justify-between items-center">
                <p class="text-sm text-blue-500">
                    Profile editing will be available soon.
                </p>

                <a
                    href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>"
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white
                           px-5 py-2.5 rounded-xl font-semibold
                           hover:from-blue-700 hover:to-indigo-700 transition
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Back to Dashboard
                </a>
            </div>

        </main>

        <!-- Right sidebar -->
        <?= $this->element('right_sidebar') ?>
    </div>
</div>
