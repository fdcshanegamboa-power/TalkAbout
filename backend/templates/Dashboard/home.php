<?php
/**
 * @var \App\View\AppView $this
 * @var \Authentication\Identity|null $user
 */
$this->assign('title', 'Home');
?>
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

<div id="dashboard-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <?= $this->element('mobile_header') ?>

    <?= $this->element('top_navbar') ?>

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

                <div
                    class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6">
                    <div class="flex items-start gap-3 lg:gap-4">
                        <div class="flex-1">
                            <textarea v-model="composer.text" rows="3" placeholder="What's happening?"
                                class="w-full resize-none border-0 focus:ring-0 text-sm lg:text-base text-blue-800 placeholder-blue-400 bg-transparent"></textarea>

                            <div v-if="composer.imagePreviews.length > 0" class="mt-3 lg:mt-4 grid grid-cols-2 gap-2">
                                <div v-for="(preview, index) in composer.imagePreviews" :key="index" class="relative">
                                    <img :src="preview" alt="preview"
                                        class="rounded-lg h-24 lg:h-32 w-full object-cover" />
                                    <button @click="removeImage(index)"
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 lg:mt-4 flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <label
                                        class="flex items-center gap-1 lg:gap-2 cursor-pointer text-blue-600 hover:text-blue-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path
                                                d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0016.586 6L13 2.414A2 2 0 0011.586 2H4z" />
                                        </svg>
                                        <span class="text-xs lg:text-sm hidden sm:inline">Add images</span>
                                        <input type="file" accept="image/*" multiple @change="onImageChange"
                                            class="hidden" ref="fileInput" />
                                    </label>
                                    <span v-if="composer.imageFiles.length > 0" class="text-xs text-blue-500">
                                        {{ composer.imageFiles.length }} <span class="hidden sm:inline">image{{
                                            composer.imageFiles.length > 1 ? 's' : '' }}</span>
                                    </span>
                                </div>

                                <div>
                                    <button @click="createPost" :disabled="!canPost || isPosting"
                                        class="bg-blue-600 text-white px-3 lg:px-4 py-2 rounded-lg lg:rounded-xl text-sm font-semibold hover:bg-blue-700 disabled:opacity-50">
                                        {{ isPosting ? 'Posting...' : 'Post' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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

</script>