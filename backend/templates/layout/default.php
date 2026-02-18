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
    <script>
        // Auto-dismiss flash messages after a timeout and add manual close buttons
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.querySelector('.fixed.top-4.right-4.z-50');
            if (!container) return;

            const TIMEOUT = 5000; // ms before auto-dismiss

            Array.from(container.children).forEach(function (el) {
                // style for smooth fade-out
                el.style.transition = 'opacity 300ms ease, transform 300ms ease';
                el.style.display = 'flex';
                el.style.alignItems = 'center';
                el.style.gap = '0.5rem';
                el.style.padding = '0.75rem 1rem';
                el.style.borderRadius = '0.5rem';
                el.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';

                // add a close button
                const btn = document.createElement('button');
                btn.setAttribute('type', 'button');
                btn.setAttribute('aria-label', 'Close');
                btn.className = 'ml-auto flex-shrink-0 inline-flex items-center justify-center h-6 w-6 rounded-full hover:bg-black/10 transition-colors';
                btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
                btn.style.background = 'transparent';
                btn.style.border = 'none';
                btn.style.cursor = 'pointer';

                btn.addEventListener('click', function () {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-6px)';
                    setTimeout(function () { el.remove(); }, 300);
                });

                // append button to flash element
                el.appendChild(btn);

                // auto dismiss after timeout
                setTimeout(function () {
                    if (!document.body.contains(el)) return;
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-6px)';
                    setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 300);
                }, TIMEOUT);
            });
        });
    </script>
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