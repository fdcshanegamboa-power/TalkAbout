<?php
/**
 * @var \App\View\AppView $this
 * @var \Authentication\Identity|null $user
 */
$this->assign('title', 'Home');
?>
<?= $this->Html->script('components/modal', ['block' => 'script']) ?>
<?= $this->Html->script('components/post_composer', ['block' => 'script']) ?>
<?= $this->Html->script('components/post_card', ['block' => 'script']) ?>
<?= $this->Html->script('components/right_sidebar', ['block' => 'script']) ?>
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
    
    /* Slide down transition for new posts banner */
    .slide-down-enter-active,
    .slide-down-leave-active {
        transition: all 0.3s ease;
    }
    
    .slide-down-enter-from {
        transform: translateY(-100%);
        opacity: 0;
    }
    
    .slide-down-leave-to {
        transform: translateY(-100%);
        opacity: 0;
    }
</style>

<?= $this->element('top_navbar') ?>

<div id="dashboard-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <?= $this->element('mobile_header') ?>

    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <?= $this->element('left_sidebar', ['active' => 'home']) ?>

            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">
                
                <!-- Pull to Refresh Indicator -->
                <div v-if="isPulling" 
                     class="fixed top-0 left-0 right-0 z-40 flex items-center justify-center pt-20 md:pt-24 pointer-events-none">
                    <div class="bg-blue-600 text-white px-4 py-2 rounded-full shadow-lg flex items-center gap-2"
                         :style="{ opacity: Math.min(pullDistance / pullThreshold, 1) }">
                        <svg v-if="pullDistance < pullThreshold" 
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                        </svg>
                        <svg v-else 
                             class="animate-spin w-5 h-5"
                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-semibold">
                            {{ pullDistance < pullThreshold ? 'Pull to refresh' : 'Release to refresh' }}
                        </span>
                    </div>
                </div>
                
                <!-- New Posts Banner -->
                <transition name="slide-down">
                    <div v-if="hasNewPosts && !isLoading" 
                         @click="loadNewPosts"
                         class="sticky top-16  md:top-20 z-30 cursor-pointer">
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-3 rounded-xl shadow-xl border border-blue-500 flex items-center justify-center gap-2 hover:from-blue-700 hover:to-indigo-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 animate-bounce">
                                <path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z" clip-rule="evenodd" />
                            </svg>
                            <span class="font-semibold">
                                {{ newPostsCount === 1 ? '1 new post' : `${newPostsCount} new posts` }} - Click to load
                            </span>
                        </div>
                    </div>
                </transition>

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

                <div v-if="isLoading && posts.length === 0" class="text-center py-8">
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
                    
                    <!-- Load More Indicator -->
                    <div v-if="isLoadingMore" class="text-center py-6">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                        <p class="text-blue-500 mt-2 text-sm">Loading more posts...</p>
                    </div>
                    
                    <!-- End of Feed -->
                    <div v-if="!hasMore && posts.length > 0" class="text-center py-6">
                        <p class="text-blue-400 text-sm">You've reached the end! 🎉</p>
                    </div>
                </div>


            </main>

            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <?= $this->element('mobile_nav', ['active' => 'home']) ?>
    
    <!-- Confirmation/Alert Modal -->
    <div v-if="modal.show" 
         @click="handleModalCancel"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/20 backdrop-blur-sm p-4"
         style="animation: fadeIn 0.2s ease-in;">
        
        <div @click.stop 
             class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all"
             style="animation: scaleIn 0.2s ease-out;">
            
            <!-- Modal Header -->
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0" v-html="modalIcon"></div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900">{{ modal.title }}</h3>
                        <p class="mt-2 text-sm text-gray-600 whitespace-pre-line">{{ modal.message }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-6 pb-6 flex justify-end gap-3">
                <button v-if="modal.type === 'confirm' && modal.onCancel"
                        @click="handleModalCancel"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                    {{ modal.cancelText }}
                </button>
                <button @click="handleModalConfirm"
                        :class="{
                            'bg-blue-600 hover:bg-blue-700': modal.type === 'confirm' || modal.type === 'info',
                            'bg-green-600 hover:bg-green-700': modal.type === 'success',
                            'bg-red-600 hover:bg-red-700': modal.type === 'error',
                            'bg-yellow-600 hover:bg-yellow-700': modal.type === 'warning'
                        }"
                        class="px-4 py-2 text-white rounded-lg font-semibold transition">
                    {{ modal.confirmText }}
                </button>
            </div>
        </div>
    </div>
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
