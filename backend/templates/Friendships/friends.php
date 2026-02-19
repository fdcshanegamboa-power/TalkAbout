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

            <!-- Page Header with Stats -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl lg:rounded-2xl shadow-xl p-6 lg:p-8 text-white">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-3xl xl:text-4xl font-extrabold tracking-tight">
                            My Friends
                        </h1>
                        <p class="text-blue-100 mt-2 text-sm lg:text-base">
                            Manage your friendships, requests, and discover new connections
                        </p>
                    </div>
                    <div class="flex gap-3 md:gap-4">
                        <div class="bg-white/20 backdrop-blur rounded-lg px-4 py-3 text-center min-w-[80px]">
                            <div class="text-2xl lg:text-3xl font-bold">{{ friends.length }}</div>
                            <div class="text-xs lg:text-sm text-blue-100 mt-1">Friends</div>
                        </div>
                        <div class="bg-white/20 backdrop-blur rounded-lg px-4 py-3 text-center min-w-[80px]">
                            <div class="text-2xl lg:text-3xl font-bold">{{ pendingRequests.length }}</div>
                            <div class="text-xs lg:text-sm text-blue-100 mt-1">Requests</div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Friend Requests Section -->
                <div v-if="pendingRequests.length > 0" class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 lg:px-6 py-4 border-b border-blue-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg lg:text-xl font-bold text-blue-900">Friend Requests</h2>
                                <p class="text-xs lg:text-sm text-blue-600">{{ pendingRequests.length }} pending request{{ pendingRequests.length !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 lg:p-6 space-y-3">
                        <div v-for="request in pendingRequests" :key="request.id" 
                             class="group flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 transition-all duration-200 border border-blue-100">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <a :href="'/profile/' + request.username" 
                                   class="relative w-14 h-14 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold shadow-lg overflow-hidden flex-shrink-0 ring-2 ring-blue-200 group-hover:ring-blue-300 transition">
                                    <img v-if="request.profile_photo" 
                                         :src="'/img/profiles/' + request.profile_photo" 
                                         :alt="request.full_name"
                                         class="w-full h-full object-cover" />
                                    <span v-else class="text-lg">{{ request.full_name ? request.full_name.charAt(0) : 'U' }}</span>
                                </a>
                                <div class="min-w-0 flex-1">
                                    <a :href="'/profile/' + request.username" 
                                       class="text-base font-bold text-blue-900 hover:text-blue-700 block truncate">
                                        {{ request.full_name || request.username }}
                                    </a>
                                    <p class="text-sm text-blue-600 truncate">@{{ request.username }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2 sm:flex-shrink-0">
                                <button @click="acceptRequest(request)" 
                                        :disabled="request.processing"
                                        class="flex-1 sm:flex-none px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-bold rounded-lg hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Accept
                                </button>
                                <button @click="rejectRequest(request)" 
                                        :disabled="request.processing"
                                        class="flex-1 sm:flex-none px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Decline
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sent Requests Section -->
                <div v-if="sentRequests.length > 0" class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-4 lg:px-6 py-4 border-b border-amber-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg lg:text-xl font-bold text-amber-900">Sent Requests</h2>
                                <p class="text-xs lg:text-sm text-amber-600">{{ sentRequests.length }} pending request{{ sentRequests.length !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-4 lg:p-6 space-y-3">
                        <div v-for="request in sentRequests" :key="request.id" 
                             class="group flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 hover:from-amber-100 hover:to-orange-100 transition-all duration-200 border border-amber-100">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <a :href="'/profile/' + request.username" 
                                   class="relative w-14 h-14 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white font-bold shadow-lg overflow-hidden flex-shrink-0 ring-2 ring-amber-200 group-hover:ring-amber-300 transition">
                                    <img v-if="request.profile_photo" 
                                         :src="'/img/profiles/' + request.profile_photo" 
                                         :alt="request.full_name"
                                         class="w-full h-full object-cover" />
                                    <span v-else class="text-lg">{{ request.full_name ? request.full_name.charAt(0) : 'U' }}</span>
                                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-amber-500 rounded-full border-2 border-white flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                </a>
                                <div class="min-w-0 flex-1">
                                    <a :href="'/profile/' + request.username" 
                                       class="text-base font-bold text-gray-900 hover:text-gray-700 block truncate">
                                        {{ request.full_name || request.username }}
                                    </a>
                                    <p class="text-sm text-gray-600 truncate">@{{ request.username }}</p>
                                    <p class="text-xs text-amber-600 mt-0.5 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Waiting for response
                                    </p>
                                </div>
                            </div>
                            <button @click="cancelRequest(request)" 
                                    :disabled="request.processing"
                                    class="w-full sm:w-auto px-4 py-2.5 bg-gray-200 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-all sm:flex-shrink-0 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Cancel Request
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Combined Friends & Suggestions Section -->
                <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 overflow-hidden">
                    <!-- Tab Switcher (Mobile/Tablet Only) -->
                    <div class="lg:hidden bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-200">
                        <div class="flex">
                            <button @click="activeTab = 'friends'" 
                                    :class="activeTab === 'friends' ? 'bg-white text-blue-900 font-bold' : 'text-blue-600'"
                                    class="flex-1 px-4 py-3 text-sm font-semibold transition-all flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                All Friends ({{ friends.length }})
                            </button>
                            <button v-if="suggestions && suggestions.length > 0"
                                    @click="activeTab = 'suggestions'" 
                                    :class="activeTab === 'suggestions' ? 'bg-white text-blue-900 font-bold' : 'text-blue-600'"
                                    class="flex-1 px-4 py-3 text-sm font-semibold transition-all flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                Suggestions ({{ suggestions.length }})
                            </button>
                        </div>
                    </div>

                    <!-- All Friends Tab Content -->
                    <div :class="{'hidden': activeTab !== 'friends'}" class="lg:!block">
                        <div class="hidden lg:block bg-gradient-to-r from-blue-50 to-cyan-50 px-4 lg:px-6 py-4 border-b border-blue-200">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-cyan-600 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg lg:text-xl font-bold text-blue-900">All Friends</h2>
                                    <p class="text-xs lg:text-sm text-blue-600">{{ friends.length }} friend{{ friends.length !== 1 ? 's' : '' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4 lg:p-6">
                            <div v-if="loadingFriends" class="text-center py-12">
                                <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600"></div>
                                <p class="text-blue-600 mt-4 font-semibold">Loading friends...</p>
                            </div>

                            <div v-else-if="friends.length === 0" class="text-center py-16">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 mx-auto text-blue-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-xl text-blue-700 font-bold">No friends yet</p>
                                <p class="text-sm text-blue-500 mt-2 max-w-md mx-auto">Start connecting with people to build your network! Check out the suggestions or search for users.</p>
                            </div>

                            <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div v-for="friend in friends" :key="friend.friend_id" 
                                     class="group flex items-center justify-between gap-3 p-4 rounded-xl bg-gradient-to-r from-blue-50 to-cyan-50 hover:from-blue-100 hover:to-cyan-100 transition-all duration-200 border border-blue-100">
                                    <div class="flex items-center gap-3 flex-1 min-w-0 overflow-hidden">
                                        <a :href="'/profile/' + friend.username" 
                                           class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gradient-to-br from-blue-600 to-cyan-600 flex items-center justify-center text-white font-bold shadow-lg overflow-hidden flex-shrink-0 ring-2 ring-blue-200 group-hover:ring-blue-300 transition">
                                            <img v-if="friend.profile_photo" 
                                                 :src="'/img/profiles/' + friend.profile_photo" 
                                                 :alt="friend.full_name"
                                                 class="w-full h-full object-cover" />
                                            <span v-else class="text-lg">{{ friend.full_name ? friend.full_name.charAt(0) : 'U' }}</span>
                                        </a>
                                        <div class="flex-1 min-w-0 overflow-hidden">
                                            <a :href="'/profile/' + friend.username" 
                                               class="text-base sm:text-lg font-bold text-blue-900 hover:text-blue-700 block truncate">
                                                {{ friend.full_name || friend.username }}
                                            </a>
                                            <p class="text-sm text-blue-600 truncate">@{{ friend.username }}</p>
                                        </div>
                                    </div>
                                    <button @click="confirmUnfriend(friend)" 
                                            class="px-3 sm:px-4 py-2 bg-red-100 text-red-600 text-xs sm:text-sm font-bold rounded-lg hover:bg-red-200 transition-all flex-shrink-0 whitespace-nowrap flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
                                        </svg>
                                        <span class="hidden sm:inline">Unfriend</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Suggestions Tab Content -->
                    <div v-if="suggestions && suggestions.length > 0" :class="{'hidden': activeTab !== 'suggestions'}" class="lg:!block">
                        <div class="hidden lg:block bg-gradient-to-r from-blue-50 to-indigo-50 px-4 lg:px-6 py-4 border-b border-blue-200">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg lg:text-xl font-bold text-blue-900">People You May Know</h2>
                                    <p class="text-xs lg:text-sm text-blue-600">{{ suggestions.length }} suggested connection{{ suggestions.length !== 1 ? 's' : '' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4 lg:p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="suggestion in suggestions" :key="suggestion.id" 
                                     class="group relative bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-5 border border-blue-200 hover:border-blue-300 hover:shadow-lg transition-all duration-200">
                                    <div class="flex flex-col items-center text-center">
                                        <a :href="'/profile/' + suggestion.username" 
                                           class="relative w-20 h-20 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold shadow-lg overflow-hidden flex-shrink-0 ring-4 ring-blue-100 group-hover:ring-blue-200 transition mb-3">
                                            <img v-if="suggestion.profile_photo" 
                                                 :src="'/img/profiles/' + suggestion.profile_photo" 
                                                 :alt="suggestion.full_name"
                                                 class="w-full h-full object-cover" />
                                            <span v-else class="text-2xl">{{ suggestion.full_name ? suggestion.full_name.charAt(0) : 'U' }}</span>
                                        </a>
                                        
                                        <a :href="'/profile/' + suggestion.username" 
                                           class="text-base font-bold text-gray-900 hover:text-blue-700 block truncate w-full mb-1">
                                            {{ suggestion.full_name || suggestion.username }}
                                        </a>
                                        <p class="text-sm text-gray-600 truncate w-full mb-3">@{{ suggestion.username }}</p>
                                        
                                        <div v-if="suggestion.mutual_friends_count > 0" class="flex items-center gap-1 text-xs text-blue-600 bg-blue-100 px-3 py-1 rounded-full mb-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            {{ suggestion.mutual_friends_count }} mutual friend{{ suggestion.mutual_friends_count !== 1 ? 's' : '' }}
                                        </div>
                                        
                                        <button @click="sendFriendRequestToSuggestion(suggestion)" 
                                                :disabled="suggestion.sending"
                                                class="w-full px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-bold rounded-lg hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                            <svg v-if="!suggestion.sending" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                            <span v-if="!suggestion.sending">Add Friend</span>
                                            <span v-else class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Sending...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
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
