<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Settings');

$fullName = $user->full_name ?? '';
$username = $user->username ?? '';
?>

<style>
/* Hide scrollbar but keep scrolling */
.no-scrollbar {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
.no-scrollbar::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}
</style>

<div id="settings-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <!-- Mobile Header -->
    <?= $this->element('mobile_header') ?>

    <!-- Top Navbar (Desktop/Tablet) -->
    <?= $this->element('top_navbar') ?>

    <!-- Main Container with proper padding for fixed navbar and bottom nav -->
    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <!-- Sidebar -->
            <?= $this->element('left_sidebar', ['active' => 'settings']) ?>

            <!-- Main content -->
            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">

            <!-- Page Header - Hidden on mobile (shown in top bar) -->
            <div class="hidden md:block">
                <h1 class="text-2xl lg:text-3xl font-extrabold text-blue-800">Settings</h1>
                <p class="text-sm text-blue-600 mt-1">Manage your account security and preferences</p>
            </div>

            <!-- Security Section -->
            <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-blue-100">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 
                                flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg lg:text-xl font-bold text-blue-800">Change Password</h2>
                        <p class="text-xs lg:text-sm text-blue-600">Update your password to keep your account secure</p>
                    </div>
                </div>

                <?= $this->Form->create(null, [
                    'url' => ['action' => 'settings'],
                    'class' => 'space-y-5'
                ]) ?>

                    <!-- Current Password -->
                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Current Password
                            </span>
                        </label>
                        <?= $this->Form->control('current_password', [
                            'type' => 'password',
                            'label' => false,
                            'required' => true,
                            'class' => 'w-full px-4 py-3 rounded-lg border-2 border-blue-200
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                        transition-all text-blue-900 placeholder-blue-300',
                            'placeholder' => 'Enter your current password'
                        ]) ?>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                New Password
                            </span>
                        </label>
                        <?= $this->Form->control('new_password', [
                            'type' => 'password',
                            'label' => false,
                            'required' => true,
                            'class' => 'w-full px-4 py-3 rounded-lg border-2 border-blue-200
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                        transition-all text-blue-900 placeholder-blue-300',
                            'placeholder' => 'Enter new password (min. 8 characters)'
                        ]) ?>
                        <p class="text-xs text-blue-500 mt-1">Must be at least 8 characters long</p>
                    </div>

                    <!-- Confirm New Password -->
                    <div>
                        <label class="block text-sm font-semibold text-blue-700 mb-2">
                            <span class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Confirm New Password
                            </span>
                        </label>
                        <?= $this->Form->control('confirm_password', [
                            'type' => 'password',
                            'label' => false,
                            'required' => true,
                            'class' => 'w-full px-4 py-3 rounded-lg border-2 border-blue-200
                                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                        transition-all text-blue-900 placeholder-blue-300',
                            'placeholder' => 'Confirm your new password'
                        ]) ?>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-6 border-t border-blue-100">
                        <?= $this->Html->link(
                            'Cancel',
                            ['controller' => 'Profile', 'action' => 'profile'],
                            ['class' => 'text-sm font-medium text-blue-600 hover:underline order-2 sm:order-1']
                        ) ?>

                        <?= $this->Form->button('Update Password', [
                            'type' => 'submit',
                            'class' => 'w-full sm:w-auto px-6 py-2.5 rounded-full
                                        bg-gradient-to-r from-blue-600 to-indigo-600
                                        text-white font-semibold text-sm
                                        hover:from-blue-700 hover:to-indigo-700 transition shadow-lg
                                        flex items-center justify-center gap-2 order-1 sm:order-2'
                        ]) ?>
                    </div>

                <?= $this->Form->end() ?>

            </div>
        </main>

            <!-- Right sidebar -->
            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <?= $this->element('mobile_nav', ['active' => 'settings']) ?>
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            // Notification data
            notifications: [],
            notificationCount: 0,
            showNotifications: false,
            notificationPolling: null
        };
    },
    mounted() {
        this.fetchNotifications();
        this.startNotificationPolling();
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        this.stopNotificationPolling();
        document.removeEventListener('click', this.handleClickOutside);
    },
    methods: {
        async fetchNotifications() {
            try {
                const response = await fetch('/api/notifications/unread');
                const data = await response.json();
                if (data.success) {
                    this.notifications = data.notifications;
                    this.notificationCount = data.count || 0;
                }
            } catch (error) {
                console.error('Error fetching notifications:', error);
            }
        },
        
        toggleNotifications() {
            this.showNotifications = !this.showNotifications;
        },
        
        handleClickOutside(event) {
            if (this.showNotifications && !event.target.closest('[data-notification-container]')) {
                this.showNotifications = false;
            }
        },
        
        async handleNotificationClick(notification) {
            if (!notification.is_read) {
                await this.markNotificationAsRead(notification.id);
            }
            this.showNotifications = false;
        },
        
        async markNotificationAsRead(notificationId) {
            try {
                const response = await fetch(`/api/notifications/mark-as-read/${notificationId}`, {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
                    const notification = this.notifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.is_read = true;
                        this.notificationCount = Math.max(0, this.notificationCount - 1);
                    }
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },
        
        async markAllAsRead() {
            try {
                const response = await fetch('/api/notifications/mark-all-as-read', {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
                    this.notifications.forEach(n => n.is_read = true);
                    this.notificationCount = 0;
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },
        
        async deleteNotification(notificationId) {
            try {
                const response = await fetch(`/api/notifications/delete/${notificationId}`, {
                    method: 'POST'
                });
                const data = await response.json();
                if (data.success) {
                    const index = this.notifications.findIndex(n => n.id === notificationId);
                    if (index !== -1) {
                        const wasUnread = !this.notifications[index].is_read;
                        this.notifications.splice(index, 1);
                        if (wasUnread) {
                            this.notificationCount = Math.max(0, this.notificationCount - 1);
                        }
                    }
                }
            } catch (error) {
                console.error('Error deleting notification:', error);
            }
        },
        
        startNotificationPolling() {
            this.notificationPolling = setInterval(() => {
                this.fetchNotifications();
            }, 30000);
        },
        
        stopNotificationPolling() {
            if (this.notificationPolling) {
                clearInterval(this.notificationPolling);
            }
        },
        
        formatNotificationTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return `${diffMins}m ago`;
            if (diffHours < 24) return `${diffHours}h ago`;
            if (diffDays < 7) return `${diffDays}d ago`;
            
            return date.toLocaleDateString();
        }
    }
}).mount('#settings-app');
</script>