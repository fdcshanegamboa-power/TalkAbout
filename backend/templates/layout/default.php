<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $this->request->getAttribute('csrfToken') ?>">

    <title><?= $this->fetch('title') ?> - TalkAbout</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    maxWidth: {
                        '9xl': '96rem',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.2s ease-in',
                        'scale-in': 'scaleIn 0.2s ease-out',
                        'slide-down': 'slideDown 0.3s ease-out',
                        'highlight-pulse': 'highlightPulse 2s ease-in-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.9)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                        slideDown: {
                            '0%': { transform: 'translateY(-100%)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        highlightPulse: {
                            '0%, 100%': { backgroundColor: 'transparent' },
                            '50%': { backgroundColor: 'rgba(59, 130, 246, 0.2)' },
                        },
                    },
                },
            },
        }
    </script>

    <!-- Vue 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    <!-- Socket.io Client CDN -->
    <script src="https://cdn.socket.io/4.6.1/socket.io.min.js"></script>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>

    <style>
        [v-cloak] { display: none; }
        
        /* Scrollbar hiding utility */
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        
        /* Mobile safe area */
        @supports (padding-bottom: env(safe-area-inset-bottom)) {
            .safe-area-inset-bottom {
                padding-bottom: env(safe-area-inset-bottom);
            }
        }
        
        /* Highlight animation for comments */
        .highlight-comment {
            animation: highlightPulse 2s ease-in-out;
        }
        
        @keyframes highlightPulse {
            0%, 100% { background-color: transparent; }
            50% { background-color: rgba(59, 130, 246, 0.2); }
        }
    </style>
</head>

<body class="bg-gray-100 antialiased">
    <div id="app">
        <?php $flash = $this->Flash->render() ?>
        <?php if ($flash): ?>
            <div class="fixed top-20 md:top-4 right-4 z-[60] max-w-sm">
                <?= $flash ?>
            </div>
        <?php endif; ?>

        <main class="min-h-screen">
            <?= $this->fetch('content') ?>
        </main>
    </div>

    <?= $this->fetch('postLink') ?>
    
    <!-- Shared Components -->
    <script src="<?= $this->Url->build('/js/components/modal.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= $this->Url->build('/js/shared/flash_messages.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= $this->Url->build('/js/shared/mobile_menu.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= $this->Url->build('/js/shared/navbar.js') ?>?v=<?= time() ?>"></script>
    <?php
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