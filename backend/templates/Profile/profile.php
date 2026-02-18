<?php
/**
 * @var \App\View\AppView $this
 * @var \Authentication\Identity|null $user
 */
$this->assign('title', 'Profile');
$this->Html->script('components/post_card', ['block' => 'script']);
$this->Html->script('profile/profile', ['block' => 'script']);


$fullName = '';
$username = '';
$about = '';
$profilePhoto = '';
$userId = null;

if (!empty($user)) {
    if (is_array($user)) {
        $fullName = $user['full_name'] ?? '';
        $username = $user['username'] ?? '';
        $about = $user['about'] ?? '';
        $profilePhoto = $user['profile_photo_path'] ?? '';
        $userId = $user['id'] ?? null;
    } elseif (is_object($user)) {
        if (method_exists($user, 'get')) {
            $fullName = $user->get('full_name') ?? '';
            $username = $user->get('username') ?? '';
            $about = $user->get('about') ?? '';
            $profilePhoto = $user->get('profile_photo_path') ?? '';
            $userId = $user->get('id') ?? null;
        } else {
            $fullName = $user->full_name ?? '';
            $username = $user->username ?? '';
            $about = $user->about ?? '';
            $profilePhoto = $user->profile_photo_path ?? '';
            $userId = $user->id ?? null;
        }
    }
}
?>

<style>
.no-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
.no-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}
</style>

<div id="profile-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">

    <?= $this->element('mobile_header') ?>

    <?= $this->element('top_navbar') ?>

    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <?= $this->element('left_sidebar', ['active' => 'profile']) ?>
        
            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">
        
            <div v-if="profileUser" class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl p-6 lg:p-8">
                <div class="flex flex-col items-center text-center">
        
                    <div class="w-20 h-20 lg:w-28 lg:h-28 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600
                           flex items-center justify-center text-white text-3xl lg:text-4xl font-extrabold
                           shadow-lg overflow-hidden">
                        <template v-if="profileUser.profile_photo">
                            <img :src="'/img/profiles/' + profileUser.profile_photo" 
                                 alt="Profile" class="w-full h-full object-cover" />
                        </template>
                        <template v-else>
                            {{ profileUser.initial }}
                        </template>
                    </div>
        
                    <h1 class="mt-4 text-xl lg:text-2xl font-extrabold text-blue-800">
                        {{ profileUser.full_name || 'Your Name' }}
                    </h1>
        
                    <p class="text-blue-500 text-xs lg:text-sm">
                        @{{ profileUser.username || 'username' }}
                    </p>
        
                    <p class="mt-3 text-xs lg:text-sm text-blue-600 max-w-xl px-4">
                        {{ profileUser.about || 'Add a short bio here — tell people about yourself.' }}
                    </p>
        
                    <div class="mt-5">
                        <a href="/profile/edit" 
                           class="px-4 lg:px-6 py-2 rounded-full border border-blue-500 text-blue-600 font-semibold text-xs lg:text-sm hover:bg-blue-50 transition focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
                            Edit profile
                        </a>
                    </div>
        
                </div>
            </div>
            
            <div v-else class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl p-6 lg:p-8">
                <div class="flex flex-col items-center text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-blue-600">Loading profile...</p>
                </div>
            </div>

            <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6"
                 @dragover.prevent="handleComposerDragOver"
                 @dragleave.prevent="handleComposerDragLeave"
                 @drop.prevent="handleComposerDrop"
                 :class="composerDragActive ? 'ring-2 ring-blue-500 ring-inset' : ''">
                <div class="flex items-start gap-3 lg:gap-4">
                    <div class="flex-1">
                        <textarea v-model="composer.text" rows="3" placeholder="Share something with your followers..."
                                  class="w-full resize-none border-0 focus:ring-0 text-sm lg:text-base text-blue-800 placeholder-blue-400 bg-transparent"></textarea>

                        <!-- Drag and Drop Zone (shown when no images) -->
                        <div v-show="!composer.imagePreviews.length"
                             class="mt-3 lg:mt-4 border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer border-blue-200 bg-blue-50/50"
                             @click="$refs.fileInput?.click()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-blue-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="text-sm text-blue-600 font-medium">Drag & drop images here or click to browse</p>
                            <p class="text-xs text-blue-400 mt-1">JPEG, PNG, GIF, WebP • Max 5MB each • Up to 10 images</p>
                        </div>

                        <div v-if="composer.imagePreviews.length > 0" class="mt-3 lg:mt-4">
                            <div class="grid grid-cols-2 gap-2">
                            <div v-for="(preview, index) in composer.imagePreviews" :key="index" class="relative">
                                <img :src="preview" alt="preview" class="rounded-lg h-24 lg:h-32 w-full object-cover" />
                                <button @click="removeImage(index)" 
                                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                            <p v-if="composer.imageFiles.length < 10" class="text-xs text-blue-500 mt-2 text-center">
                                💡 Drag & drop more images or click "Add images" below ({{ 10 - composer.imageFiles.length }} remaining)
                            </p>
                        </div>

                        <div class="mt-3 lg:mt-4 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-1 lg:gap-2 cursor-pointer text-blue-600 hover:text-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0016.586 6L13 2.414A2 2 0 0011.586 2H4z"/></svg>
                                    <span class="text-xs lg:text-sm hidden sm:inline">Add images</span>
                                    <input type="file" accept="image/*" multiple @change="onImageChange" class="hidden" ref="fileInput" />
                                </label>
                                <span v-if="composer.imageFiles.length > 0" class="text-xs text-blue-500">
                                    {{ composer.imageFiles.length }} <span class="hidden sm:inline">image{{ composer.imageFiles.length > 1 ? 's' : '' }}</span>
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

            <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg lg:text-xl font-extrabold text-blue-700">My Posts</h2>
                    <span v-if="!isLoading" class="text-xs lg:text-sm text-blue-600">{{ posts.length }} post{{ posts.length !== 1 ? 's' : '' }}</span>
                </div>
            </div>

            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-blue-600 mt-2">Loading your posts...</p>
            </div>

            <div v-else class="space-y-4">
                <div v-if="posts.length === 0" class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100 p-8 text-center">
                    <p class="text-blue-600">You haven't posted anything yet. Share your first post above!</p>
                </div>

                <div v-for="post in posts" :key="post.id">
                    <?= $this->element('post_card', ['canEdit' => true, 'profilePhoto' => $profilePhoto]) ?>
                </div>
            </div>

        </main>

            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <?= $this->element('mobile_nav', ['active' => 'profile']) ?>
</div>