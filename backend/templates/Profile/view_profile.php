<?php
/**
 * @var \App\View\AppView $this
 * @var \Authentication\Identity|null $user
 * @var int|null $currentUserId
 * @var bool $isOwnProfile
 */
$this->assign('title', 'Profile');
$this->Html->script('components/post_card', ['block' => 'script']);
$this->Html->script('profile/view_profile', ['block' => 'script']);

$username = '';
$userId = null;

if (!empty($user)) {
    if (is_array($user)) {
        $username = $user['username'] ?? '';
        $userId = $user['id'] ?? null;
    } elseif (is_object($user)) {
        if (method_exists($user, 'get')) {
            $username = $user->get('username') ?? '';
            $userId = $user->get('id') ?? null;
        } else {
            $username = $user->username ?? '';
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

<?= $this->element('top_navbar') ?>

<div id="profile-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100"
     data-profile-username="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
     data-user-id="<?= (int) ($userId ?? 0) ?>"
     data-current-user-id="<?= (int) ($currentUserId ?? 0) ?>"
     data-is-own-profile="<?= $isOwnProfile ? 'true' : 'false' ?>">

    <?= $this->element('mobile_header') ?>

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
                        {{ profileUser.full_name || 'User' }}
                    </h1>
        
                    <p class="text-blue-500 text-xs lg:text-sm">
                        @{{ profileUser.username }}
                    </p>
        
                    <p class="mt-3 text-xs lg:text-sm text-blue-600 max-w-xl px-4">
                        {{ profileUser.about || 'No bio yet.' }}
                    </p>
        
                    <div v-if="isOwnProfile" class="mt-5">
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

            <div v-if="isOwnProfile" class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6">
                <div class="flex items-start gap-3 lg:gap-4">
                    <div class="flex-1">
                        <textarea v-model="composer.text" rows="3" placeholder="Share something with your followers..."
                                  class="w-full resize-none border-0 focus:ring-0 text-sm lg:text-base text-blue-800 placeholder-blue-400 bg-transparent"></textarea>

                        <div v-if="composer.imagePreviews.length > 0" class="mt-3 lg:mt-4 grid grid-cols-2 gap-2">
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
                    <h2 class="text-lg lg:text-xl font-extrabold text-blue-700">{{ isOwnProfile ? 'My Posts' : 'Posts' }}</h2>
                    <span v-if="!isLoading" class="text-xs lg:text-sm text-blue-600">{{ posts.length }} post{{ posts.length !== 1 ? 's' : '' }}</span>
                </div>
            </div>

            <div v-if="isLoading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-blue-600 mt-2">Loading posts...</p>
            </div>

            <div v-else class="space-y-4">
                <div v-if="posts.length === 0" class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100 p-8 text-center">
                    <p class="text-blue-600">{{ isOwnProfile ? "You haven't posted anything yet. Share your first post above!" : "This user hasn't posted anything yet." }}</p>
                </div>

                <div v-for="post in posts" :key="post.id">
                    <?= $this->element('post_card', ['canEdit' => $isOwnProfile]) ?>
                </div>
            </div>

        </main>

            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <?= $this->element('mobile_nav', ['active' => 'profile']) ?>
</div>
