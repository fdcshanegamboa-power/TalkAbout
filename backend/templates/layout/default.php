<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->fetch('title') ?> - TalkAbout</title>
    
    <!-- Vue 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <!-- Tailwind CSS CDN for quick styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    
    <style>
        [v-cloak] {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div id="app" v-cloak>
        <!-- Flash Messages -->
        <?php $flash = $this->Flash->render() ?>
        <?php if ($flash): ?>
        <div class="fixed top-4 right-4 z-50">
            <?= $flash ?>
        </div>
        <?php endif; ?>
        
        <!-- Main Content -->
        <main class="min-h-screen py-8">
            <?= $this->fetch('content') ?>
        </main>
    </div>
    
    <?= $this->fetch('postLink') ?>
</body>
</html>
