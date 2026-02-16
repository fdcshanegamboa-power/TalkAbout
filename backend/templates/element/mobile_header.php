<?php
/**
 * Mobile Header with Logo and Menu
 * @var \App\View\AppView $this
 * @var string|null $pageTitle
 */
?>

<!-- Mobile Header - Only visible on mobile (< 768px) -->
<div class="md:hidden sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-blue-100">
    <div class="flex items-center justify-between px-4 py-3">
        <!-- Logo Section (Left) -->
        <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>"
           class="flex items-center gap-2">
            <img src="<?= $this->Url->build('/logo/telupuluh-05.jpg') ?>" 
                 alt="TalkAbout" 
                 class="w-8 h-8 rounded-lg object-cover" />
            <h1 class="text-lg font-extrabold text-blue-700">
                Talk<span class="text-indigo-600">About</span>
            </h1>
        </a>

        <!-- Hamburger Menu Button (Right) -->
        <div class="flex items-center gap-2">
            <!-- Notifications -->
            <div v-if="typeof notifications !== 'undefined'" data-notification-container>
                <button @click="toggleNotifications"
                        class="relative p-2 rounded-lg text-blue-700 hover:bg-blue-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span v-if="notificationCount > 0" 
                          class="absolute top-1 right-1 min-w-[16px] h-[16px] bg-red-500 text-white text-xs 
                                 font-bold rounded-full flex items-center justify-center px-1">
                        {{ notificationCount > 9 ? '9+' : notificationCount }}
                    </span>
                </button>
            </div>
            
            <button id="mobile-menu-toggle" 
                    class="p-2 rounded-lg text-blue-700 hover:bg-blue-50 transition-colors"
                    aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Mobile Notifications Panel -->
<div v-if="typeof notifications !== 'undefined' && showNotifications" 
     @click="showNotifications = false"
     class="md:hidden fixed inset-0 z-50 bg-black/40"
     data-notification-container>
    <div @click.stop 
         class="absolute inset-x-0 top-0 max-h-[80vh] bg-white rounded-b-2xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-blue-900 text-lg">Notifications</h3>
                <div class="flex items-center gap-2">
                    <button v-if="notifications.length > 0" 
                            @click="markAllAsRead"
                            class="text-xs text-blue-600 hover:text-blue-800 font-semibold">
                        Mark all read
                    </button>
                    <button @click="showNotifications = false"
                            class="p-1 hover:bg-blue-100 rounded-full">
                        <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Notification List -->
        <div class="overflow-y-auto max-h-[calc(80vh-4rem)]">
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
                 class="p-4 border-b border-blue-50 active:bg-blue-100 transition-colors relative"
                 :class="{ 'bg-blue-50/50': !notification.is_read }">
                
                <!-- Unread indicator -->
                <div v-if="!notification.is_read" 
                     class="absolute left-2 top-1/2 -translate-y-1/2 w-2 h-2 bg-blue-500 rounded-full"></div>
                
                <div class="flex items-start gap-3 ml-3">
                    <!-- Icon -->
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
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-blue-900">
                            <span class="font-semibold">{{ notification.actor?.full_name || notification.actor?.username || 'Someone' }}</span>
                            <span v-if="notification.type === 'post_liked'"> liked your post</span>
                            <span v-else-if="notification.type === 'post_commented'"> commented on your post</span>
                            <span v-else> interacted with your content</span>
                        </p>
                        <p class="text-xs text-blue-500 mt-1">{{ formatNotificationTime(notification.created_at) }}</p>
                    </div>
                    
                    <!-- Delete button -->
                    <button @click.stop="deleteNotification(notification.id)"
                            class="flex-shrink-0 p-1 hover:bg-red-100 rounded-full transition-colors">
                        <svg class="w-4 h-4 text-blue-400 hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Dropdown Menu -->
<div id="mobile-dropdown-menu" 
     class="md:hidden fixed inset-0 z-50 hidden"
     style="background-color: rgba(0, 0, 0, 0.4);">
    <div class="absolute top-0 right-0 w-72 h-full bg-white shadow-2xl transform transition-transform duration-300 translate-x-full"
         id="mobile-menu-panel">
        
        <!-- Menu Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-white text-lg font-bold">Menu</h2>
                <button id="mobile-menu-close" 
                        class="text-white/90 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- User Info -->
            <div v-if="profileUser" class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-bold shadow overflow-hidden flex-shrink-0">
                    <template v-if="profileUser.profile_photo">
                        <img :src="'/img/profiles/' + profileUser.profile_photo"
                            alt="Profile" class="w-full h-full object-cover" />
                    </template>
                    <template v-else>
                        {{ profileUser.initial }}
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white font-semibold truncate">
                        {{ profileUser.full_name || 'User' }}
                    </div>
                    <div class="text-white/80 text-sm truncate">
                        @{{ profileUser.username || 'username' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="<?= $this->Url->build(['controller' => 'Profile', 'action' => 'profile']) ?>"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg text-blue-800 hover:bg-blue-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="font-medium">View Profile</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build(['controller' => 'Profile', 'action' => 'editProfile']) ?>"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg text-blue-800 hover:bg-blue-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span class="font-medium">Edit Profile</span>
                    </a>
                </li>
                <li>
                    <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'settings']) ?>"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg text-blue-800 hover:bg-blue-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="font-medium">Settings</span>
                    </a>
                </li>
                <li class="border-t border-blue-100 mt-4 pt-2">
                    <a href="<?= $this->Url->build(['controller' => 'Sessions', 'action' => 'logout']) ?>"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="font-medium">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<script>
(function() {
    function initMobileMenu() {
        const menuToggle = document.getElementById('mobile-menu-toggle');
        const menuClose = document.getElementById('mobile-menu-close');
        const menuOverlay = document.getElementById('mobile-dropdown-menu');
        const menuPanel = document.getElementById('mobile-menu-panel');
        
        if (!menuToggle || !menuOverlay || !menuPanel) return;
        
        function openMenu() {
            menuOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Trigger animation
            setTimeout(() => {
                menuPanel.classList.remove('translate-x-full');
            }, 10);
        }
        
        function closeMenu() {
            menuPanel.classList.add('translate-x-full');
            setTimeout(() => {
                menuOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }
        
        menuToggle.addEventListener('click', openMenu);
        
        if (menuClose) {
            menuClose.addEventListener('click', closeMenu);
        }
        
        // Close when clicking overlay
        menuOverlay.addEventListener('click', function(e) {
            if (e.target === menuOverlay) {
                closeMenu();
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileMenu);
    } else {
        initMobileMenu();
    }
})();
</script>
