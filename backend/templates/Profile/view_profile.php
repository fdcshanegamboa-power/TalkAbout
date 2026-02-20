<?php
/**
 * @var \App\View\AppView $this
 * @var \Authentication\Identity|null $user
 * @var int|null $currentUserId
 * @var bool $isOwnProfile
 */
$this->assign('title', 'Profile');
?>
<?= $this->Html->script('components/modal', ['block' => 'script']) ?>
<?= $this->Html->script('components/post_composer', ['block' => 'script']) ?>
<?= $this->Html->script('components/post_card', ['block' => 'script']) ?>
<?= $this->Html->script('components/right_sidebar', ['block' => 'script']) ?>
<?= $this->Html->script('profile/view_profile', ['block' => 'script']) ?>
<?php
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
                            <img :src="profileUser.profile_photo" 
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

                    <!-- Friend request buttons -->
                    <div v-else class="mt-5">
                        <!-- Loading friendship status -->
                        <div v-if="loadingFriendshipStatus" class="px-4 py-2">
                            <div class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                        </div>

                        <!-- Already friends -->
                        <button v-else-if="friendshipStatus === 'friends'"
                                @click="unfriend"
                                :disabled="processingFriendRequest"
                                class="px-4 lg:px-6 py-2 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold text-xs lg:text-sm hover:from-blue-700 hover:to-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Friends</span>
                        </button>

                        <!-- Friend request sent (pending) -->
                        <button v-else-if="friendshipStatus === 'pending_sent'"
                                @click="cancelFriendRequest"
                                :disabled="processingFriendRequest"
                                class="px-4 lg:px-6 py-2 rounded-full border border-blue-500 text-blue-600 font-semibold text-xs lg:text-sm hover:bg-blue-50 transition focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Request Sent</span>
                        </button>

                        <!-- Friend request received -->
                        <div v-else-if="friendshipStatus === 'pending_received'" class="flex gap-2">
                            <button @click="acceptFriendRequest"
                                    :disabled="processingFriendRequest"
                                    class="px-4 lg:px-6 py-2 rounded-full bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold text-xs lg:text-sm hover:from-green-700 hover:to-emerald-700 transition focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Accept</span>
                            </button>
                            <button @click="rejectFriendRequest"
                                    :disabled="processingFriendRequest"
                                    class="px-4 lg:px-6 py-2 rounded-full border border-red-500 text-red-600 font-semibold text-xs lg:text-sm hover:bg-red-50 transition focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span>Decline</span>
                            </button>
                        </div>

                        <!-- Not friends yet -->
                        <button v-else
                                @click="sendFriendRequest"
                                :disabled="processingFriendRequest"
                                class="px-4 lg:px-6 py-2 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold text-xs lg:text-sm hover:from-blue-700 hover:to-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span>Add Friend</span>
                        </button>
                    </div>
        
                </div>
            </div>
            
            <div v-else class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl p-6 lg:p-8">
                <div class="flex flex-col items-center text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-blue-600">Loading profile...</p>
                </div>
            </div>

            <div v-if="isOwnProfile">
                <?= $this->element('post_composer', ['placeholder' => 'Share something with your followers...']) ?>
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
