<?php
/**
 * @var \App\View\AppView $this
 * @var int|null $currentUserId
 */
$this->assign('title', 'Friends');
?>
<?= $this->Html->script('components/right_sidebar', ['block' => 'script']) ?>
<?= $this->Html->script('friendships/friends', ['block' => 'script']) ?>

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

<div id="friends-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <?= $this->element('mobile_header') ?>

    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <?= $this->element('left_sidebar', ['active' => 'friends']) ?>

            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">

                <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6">
                    <div>
                        <h1 class="text-xl lg:text-2xl xl:text-3xl font-extrabold tracking-tight text-blue-700">
                            My Friends
                        </h1>
                        <p class="text-sm text-blue-600 mt-1">
                            Manage your friendships and friend requests
                        </p>
                    </div>
                </div>

                <!-- Friend Requests Section -->
                <div v-if="pendingRequests.length > 0" class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6">
                    <h2 class="text-lg font-bold text-blue-700 mb-4">Friend Requests</h2>
                    
                    <div class="space-y-3">
                        <div v-for="request in pendingRequests" :key="request.id" 
                             class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <a :href="'/profile/' + request.username" 
                                   class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold shadow overflow-hidden flex-shrink-0">
                                    <img v-if="request.profile_photo" 
                                         :src="'/img/profiles/' + request.profile_photo" 
                                         :alt="request.full_name"
                                         class="w-full h-full object-cover" />
                                    <span v-else>{{ request.full_name ? request.full_name.charAt(0) : 'U' }}</span>
                                </a>
                                <div class="min-w-0 flex-1">
                                    <a :href="'/profile/' + request.username" 
                                       class="text-sm font-semibold text-blue-900 hover:text-blue-700 block truncate">
                                        {{ request.full_name || request.username }}
                                    </a>
                                    <p class="text-xs text-blue-600 truncate">@{{ request.username }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2 sm:flex-shrink-0">
                                <button @click="acceptRequest(request)" 
                                        :disabled="request.processing"
                                        class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                    Accept
                                </button>
                                <button @click="rejectRequest(request)" 
                                        :disabled="request.processing"
                                        class="flex-1 sm:flex-none px-3 sm:px-4 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                    Decline
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sent Requests Section -->
                <div v-if="sentRequests.length > 0" class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6">
                    <h2 class="text-lg font-bold text-blue-700 mb-4">Sent Requests</h2>
                    
                    <div class="space-y-3">
                        <div v-for="request in sentRequests" :key="request.id" 
                             class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <a :href="'/profile/' + request.username" 
                                   class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold shadow overflow-hidden flex-shrink-0">
                                    <img v-if="request.profile_photo" 
                                         :src="'/img/profiles/' + request.profile_photo" 
                                         :alt="request.full_name"
                                         class="w-full h-full object-cover" />
                                    <span v-else>{{ request.full_name ? request.full_name.charAt(0) : 'U' }}</span>
                                </a>
                                <div class="min-w-0 flex-1">
                                    <a :href="'/profile/' + request.username" 
                                       class="text-sm font-semibold text-blue-900 hover:text-blue-700 block truncate">
                                        {{ request.full_name || request.username }}
                                    </a>
                                    <p class="text-xs text-blue-600 truncate">@{{ request.username }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Request pending</p>
                                </div>
                            </div>
                            <button @click="cancelRequest(request)" 
                                    :disabled="request.processing"
                                    class="w-full sm:w-auto px-3 sm:px-4 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition sm:flex-shrink-0">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- All Friends Section -->
                <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6">
                    <h2 class="text-lg font-bold text-blue-700 mb-4">All Friends ({{ friends.length }})</h2>
                    
                    <div v-if="loadingFriends" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        <p class="text-blue-600 mt-4">Loading friends...</p>
                    </div>

                    <div v-else-if="friends.length === 0" class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-blue-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="text-lg text-blue-600 font-semibold">No friends yet</p>
                        <p class="text-sm text-blue-500 mt-2">Start connecting with people to build your network!</p>
                    </div>

                    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div v-for="friend in friends" :key="friend.friend_id" 
                             class="flex items-center justify-between gap-3 p-3 sm:p-4 rounded-lg bg-blue-50 hover:bg-blue-100 transition">
                            <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0 overflow-hidden">
                                <a :href="'/profile/' + friend.username" 
                                   class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold shadow overflow-hidden flex-shrink-0">
                                    <img v-if="friend.profile_photo" 
                                         :src="'/img/profiles/' + friend.profile_photo" 
                                         :alt="friend.full_name"
                                         class="w-full h-full object-cover" />
                                    <span v-else>{{ friend.full_name ? friend.full_name.charAt(0) : 'U' }}</span>
                                </a>
                                <div class="flex-1 min-w-0 overflow-hidden">
                                    <a :href="'/profile/' + friend.username" 
                                       class="text-sm sm:text-base font-semibold text-blue-900 hover:text-blue-700 block truncate">
                                        {{ friend.full_name || friend.username }}
                                    </a>
                                    <p class="text-xs sm:text-sm text-blue-600 truncate">@{{ friend.username }}</p>
                                </div>
                            </div>
                            <button @click="confirmUnfriend(friend)" 
                                    class="px-2 sm:px-3 py-1.5 bg-red-100 text-red-600 text-xs sm:text-sm font-semibold rounded-lg hover:bg-red-200 transition flex-shrink-0 whitespace-nowrap">
                                Unfriend
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-8">
                    <div class="flex flex-col items-center justify-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                        <p class="text-blue-600 mt-4">Loading...</p>
                    </div>
                </div>

            </main>

            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <?= $this->element('mobile_nav', ['active' => 'friends']) ?>
    
    <!-- Confirmation/Alert Modal -->
    <div v-if="modal.show" 
         @click="handleModalCancel"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/30 backdrop-blur-sm p-4"
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

<style>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes scaleIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>
