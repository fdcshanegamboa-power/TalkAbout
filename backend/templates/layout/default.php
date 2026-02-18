<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $this->request->getAttribute('csrfToken') ?>">

    <title><?= $this->fetch('title') ?> - TalkAbout</title>

    <!-- Vue 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <!-- Tailwind CSS CDN for quick styling -->
    <script src="https://cdn.tailwindcss.com"></script>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>

    <style>
        [v-cloak] {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div id="app">
        <!-- Flash Messages -->
        <?php $flash = $this->Flash->render() ?>
        <?php if ($flash): ?>
            <div class="fixed top-20 md:top-4 right-4 z-[60]">
                <?= $flash ?>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="min-h-screen">
            <?= $this->fetch('content') ?>
        </main>
    </div>

    <?= $this->fetch('postLink') ?>
    
    <!-- Shared Components -->
    <script src="<?= $this->Url->build('/js/shared/flash_messages.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= $this->Url->build('/js/shared/navbar.js') ?>?v=<?= time() ?>"></script>
    <?php
    // Expose current user id to frontend JS for permission checks (e.g. comment deletion)
    $me = $this->request->getAttribute('identity');
    $meId = null;
    if ($me) {
        if (is_array($me)) {
            $meId = $me['id'] ?? null;
        } elseif (is_object($me)) {
            if (method_exists($me, 'get')) {
                $meId = $me->get('id');
            } else {
                $meId = $me->id ?? null;
            }
        }
    }
    ?>
    <script>
        window.currentUserId = <?= $meId !== null ? json_encode((int)$meId) : 'null' ?>;
    </script>
    <?= $this->fetch('script') ?>
</body>

</html>