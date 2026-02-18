<?php
/**
 * @var \App\View\AppView $this
 * @var \Authentication\Identity|null $user
 */
$this->assign('title', 'Home');
?>
<?= $this->Html->script('components/post_composer', ['block' => 'script']) ?>
<?= $this->Html->script('components/post_card', ['block' => 'script']) ?>
<?= $this->Html->script('dashboard/home', ['block' => 'script']) ?>

<style>
    [v-cloak] {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
</style>

<?= $this->element('top_navbar') ?>

<div id="dashboard-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <?= $this->element('mobile_header') ?>

    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <?= $this->element('left_sidebar', ['active' => 'home']) ?>

            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">

                <div
                    class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6">
                    <div>
                        <h1 class="text-xl lg:text-2xl xl:text-3xl font-extrabold tracking-tight text-blue-700">
                            Home Feed
                        </h1>
                        <p class="text-sm text-blue-600 mt-1">
                            What's happening now
                        </p>
                    </div>
                </div>

                <?= $this->element('post_composer', ['placeholder' => "What's happening?"]) ?>

                <div v-if="isLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="text-blue-600 mt-2">Loading posts...</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-if="posts.length === 0"
                        class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100 p-8 text-center">
                        <p class="text-blue-600">No posts yet. Be the first to share something!</p>
                    </div>

                    <div v-for="post in posts" :key="post.id">
                        <?= $this->element('post_card', ['canEdit' => false]) ?>
                    </div>
                </div>


            </main>

            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <?= $this->element('mobile_nav', ['active' => 'home']) ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash === '#compose') {
        const composer = document.getElementById('post-composer');
        if (composer) {
            composer.focus();
            composer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});
</script>
