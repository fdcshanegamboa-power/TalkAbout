<?php
/**
 * Right sidebar (Friends & Suggestions) element
 * @var \App\View\AppView $this
 * @var string|null $mode - 'friends' to show friends list, 'suggestions' for suggestions, null for both
 */

$mode = $mode ?? null;
$showFriends = $mode !== 'suggestions';
$showSuggestions = $mode !== 'friends';
?>

<!-- Right Sidebar - Hidden on mobile, visible from medium screens (768px+) -->
<aside class="hidden md:block w-72 bg-white/90 backdrop-blur rounded-2xl shadow-lg p-6
         sticky top-20 self-start max-h-[calc(100vh-5.5rem)] overflow-y-auto no-scrollbar z-10">
    
    <?php if ($showFriends): ?>
    <!-- Friends Section -->
    <div <?= $showSuggestions ? 'class="mb-6"' : '' ?>>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold tracking-tight text-blue-700">
                <?= $mode === 'friends' ? 'All Friends' : 'Friends' ?>
            </h3>
            <span class="text-xs text-blue-500 bg-blue-50 px-2 py-1 rounded-full">
                {{ friends.length }}
            </span>
        </div>

        <div v-if="loadingFriends" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <div v-else-if="friends.length === 0" class="text-center py-8">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <p class="text-sm text-blue-500 mt-3">No friends yet</p>
            <p class="text-xs text-blue-400 mt-1">Start connecting with people!</p>
        </div>

        <ul v-else class="space-y-2">
            <li v-for="friend in friends" :key="friend.id">
                <a :href="'/profile/' + friend.username" 
                   class="flex items-center gap-3 p-2 rounded-lg hover:bg-blue-50 transition-colors group">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 
                                    flex items-center justify-center text-white font-bold text-sm shadow overflow-hidden flex-shrink-0">
                            <img v-if="friend.profile_photo" 
                                 :src="friend.profile_photo" 
                                 :alt="friend.full_name"
                                 class="w-full h-full object-cover" />
                            <span v-else>{{ friend.full_name ? friend.full_name.charAt(0) : 'U' }}</span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-blue-900 truncate group-hover:text-blue-700">
                            {{ friend.full_name || friend.username }}
                        </p>
                        <p class="text-xs text-blue-500 truncate">
                            @{{ friend.username }}
                        </p>
                    </div>
                    <?php if ($mode !== 'friends'): ?>
                    
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-5 w-5 text-blue-400 opacity-0 group-hover:opacity-100 transition-opacity" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg> -->
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($showSuggestions): ?>
    <!-- Friend Suggestions -->
    <div <?= $showFriends ? 'class="pt-6 border-t border-blue-100"' : '' ?>>
        <h3 class="text-lg font-extrabold tracking-tight text-blue-700 mb-4">Suggestions</h3>
        
        <div v-if="loadingSuggestions" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>

        <div v-else-if="suggestions.length === 0" class="text-center py-8">
            <p class="text-sm text-blue-500">No suggestions available</p>
        </div>

        <ul v-else class="space-y-3">
            <li v-for="suggestion in suggestions" :key="suggestion.id">
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-blue-50 transition-colors group">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 
                                flex items-center justify-center text-white font-bold text-sm shadow flex-shrink-0 overflow-hidden">
                        <img v-if="suggestion.profile_photo" 
                             :src="suggestion.profile_photo" 
                             :alt="suggestion.full_name"
                             class="w-full h-full object-cover" />
                        <span v-else>{{ suggestion.full_name ? suggestion.full_name.charAt(0) : 'U' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <a :href="'/profile/' + suggestion.username" class="text-sm font-semibold text-blue-900 truncate hover:text-blue-700">
                            {{ suggestion.full_name || suggestion.username }}
                        </a>
                        <p class="text-xs text-blue-500">
                            {{ suggestion.mutual_friends || 0 }} mutual friends
                        </p>
                    </div>
                    <button @click="sendFriendRequestToSuggestion(suggestion)" 
                            :disabled="suggestion.sending"
                            class="px-3 py-1 text-xs font-semibold text-blue-600 bg-blue-50 
                                   hover:bg-blue-100 rounded-full transition-colors
                                   disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="suggestion.sending ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                        <span v-if="suggestion.sending">
                            <svg class="animate-spin h-3 w-3 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span v-else>Add</span>
                    </button>
                </div>
            </li>
        </ul>

        <a href="/friends" class="block w-full mt-4 text-sm font-semibold text-blue-600 hover:text-blue-700 
                       py-2 hover:bg-blue-50 rounded-lg transition-colors text-center">
            See All Suggestions
        </a>
    </div>
    <?php endif; ?>
</aside>
