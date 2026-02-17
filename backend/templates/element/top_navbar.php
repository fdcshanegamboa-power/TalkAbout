<?php
/**
 * Top Navigation Bar (Desktop/Tablet only)
 * @var \App\View\AppView $this
 */
?>

<nav id="navbar-app" v-cloak class="hidden md:block fixed top-0 left-0 right-0 z-50 bg-white border-b border-blue-100 shadow-sm">
    <div class="max-w-9xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
            
            <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>"
               class="flex items-center gap-2">
                <img src="<?= $this->Url->build('/logo/telupuluh-05.jpg') ?>" 
                     alt="TalkAbout" 
                     class="w-8 h-8 rounded-lg object-cover" />
                <span class="text-xl font-extrabold text-blue-700">
                    Talk<span class="text-indigo-600">About</span>
                </span>
            </a>

            <div class="flex-1 max-w-xl mx-4">
                <div class="relative">
                    <input type="text" 
                           placeholder="Search TalkAbout..." 
                           class="w-full pl-10 pr-4 py-2 rounded-full bg-blue-50 border-2 border-transparent 
                                  focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-200 
                                  transition-all text-sm text-blue-900 placeholder-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-5 w-5 absolute left-3 top-2.5 text-blue-400" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-3">
                
                <button @click="handleCreatePost" 
                        class="hidden lg:flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 
                               text-white rounded-full hover:shadow-lg transition-all font-semibold text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Create Post</span>
                </button>

                <div class="relative" data-notification-container>
                    <button @click="toggleNotifications" 
                            class="p-2 rounded-full hover:bg-blue-50 transition-colors relative group">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="h-6 w-6 text-blue-700 group-hover:text-blue-900" 
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span v-if="notificationCount > 0" 
                              class="absolute top-1 right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-xs 
                                     font-bold rounded-full flex items-center justify-center px-1">
                            {{ notificationCount > 9 ? '9+' : notificationCount }}
                        </span>
                    </button>
                    
                    <div v-if="showNotifications" 
                         @click.stop
                         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl border border-blue-100 
                                overflow-hidden z-50 max-h-[500px] flex flex-col">
                        <div class="p-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-blue-900 text-lg">Notifications</h3>
                                <button v-if="notifications.length > 0" 
                                        @click="markAllAsRead"
                                        class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
                                    Mark all as read
                                </button>
                            </div>
                        </div>
                        
                        <div class="overflow-y-auto flex-1">
                            <div v-if="notifications.length === 0" 
                                 class="p-8 text-center text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     class="h-12 w-12 mx-auto mb-3 text-blue-300" 
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <p class="text-sm font-medium">No notifications yet</p>
                                <p class="text-xs mt-1">We'll notify you when something happens</p>
                            </div>
                            
                            <div v-for="notification in notifications" 
                                 :key="notification.id"
                                 @click="handleNotificationClick(notification)"
                                 class="p-4 border-b border-blue-50 hover:bg-blue-50 transition-colors cursor-pointer relative"
                                 :class="{ 'bg-blue-50/50': !notification.is_read }">
                                
                                <div v-if="!notification.is_read" 
                                     class="absolute left-2 top-1/2 -translate-y-1/2 w-2 h-2 bg-blue-500 rounded-full"></div>
                                
                                <div class="flex items-start gap-3 ml-3">
                                    <div class="flex-shrink-0 mt-1">
                                        <div v-if="notification.type === 'post_liked'" 
                                             class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" />
                                            </svg>
                                        </div>
                                        <div v-else-if="notification.type === 'post_commented'" 
                                             class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                        </div>
                                        <div v-else 
                                             class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-blue-900">
                                            <span class="font-semibold">{{ notification.actor?.full_name || notification.actor?.username || 'Someone' }}</span>
                                            <span v-if="notification.type === 'post_liked'"> liked your post</span>
                                            <span v-else-if="notification.type === 'post_commented'"> commented on your post</span>
                                            <span v-else> interacted with your content</span>
                                        </p>
                                        <p class="text-xs text-blue-500 mt-1">{{ formatNotificationTime(notification.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages (optional) -->
                <button class="p-2 rounded-full hover:bg-blue-50 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-6 w-6 text-blue-700 group-hover:text-blue-900" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </button>

                <!-- User Profile Dropdown -->
                <div v-if="profileUser" class="relative" id="user-menu-container" data-user-menu>
                    <button @click="toggleUserMenu" 
                            class="flex items-center gap-2 p-1 rounded-full hover:bg-blue-50 transition-colors">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 
                                    flex items-center justify-center text-white font-bold shadow overflow-hidden">
                            <template v-if="profileUser.profile_photo">
                                <img :src="'/img/profiles/' + profileUser.profile_photo"
                                     alt="Profile" class="w-full h-full object-cover" />
                            </template>
                            <template v-else>
                                {{ profileUser.initial }}
                            </template>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="h-4 w-4 text-blue-700" 
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div v-if="showUserMenu" @click.stop
                         class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-blue-100 py-2 z-50">
                        <!-- User Info -->
                        <div class="px-4 py-3 border-b border-blue-100">
                            <p class="text-sm font-semibold text-blue-900">
                                {{ profileUser.full_name || 'User' }}
                            </p>
                            <p class="text-xs text-blue-500">
                                @{{ profileUser.username || 'username' }}
                            </p>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-2">
                            <a v-if="profileUser" :href="'/profile/' + profileUser.username"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-blue-800 hover:bg-blue-50 transition-colors" title="Profile">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Your Profile</span>
                            </a>
                            <a href="<?= $this->Url->build(['controller' => 'Profile', 'action' => 'editProfile']) ?>"
                               class="flex items-center gap-3 px-4 py-2 text-sm text-blue-800 hover:bg-blue-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Profile
                            </a>
                            <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'settings']) ?>"
                               class="flex items-center gap-3 px-4 py-2 text-sm text-blue-800 hover:bg-blue-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Settings
                            </a>
                        </div>

                        <!-- Logout -->
                        <div class="border-t border-blue-100 pt-2">
                            <a href="<?= $this->Url->build(['controller' => 'Sessions', 'action' => 'logout']) ?>"
                               class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</nav>

<script>
function focusPostComposer() {
    const composer = document.querySelector('textarea[placeholder*="What\'s happening"]');
    if (composer) {
        composer.focus();
        composer.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
