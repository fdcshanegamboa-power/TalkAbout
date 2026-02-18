<?php
$this->assign('title', 'Post Not Found');
?>

<style>
    [v-cloak] {
        display: none;
    }
</style>

<?= $this->element('top_navbar') ?>

<div id="not-found-app" v-cloak class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-indigo-100">
    <?= $this->element('mobile_header') ?>

    <div class="max-w-9xl mx-auto px-4 sm:px-6 pt-4 pb-20 md:pt-20 md:pb-6 lg:pb-6">
        <div class="md:flex md:gap-4 lg:gap-6">

            <?= $this->element('left_sidebar', ['active' => 'home']) ?>

            <main class="flex-1 space-y-4 lg:space-y-6 mt-4 md:mt-0">
                <div class="max-w-3xl mx-auto">
                    <!-- Not Found Card -->
                    <div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-lg border border-blue-100 p-8 lg:p-12 text-center">
                        <!-- Icon -->
                        <div class="mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <!-- Title -->
                        <h1 class="text-2xl lg:text-3xl font-bold text-blue-900 mb-3">
                            Content Not Found
                        </h1>

                        <!-- Message -->
                        <p class="text-blue-600 mb-2">
                            This post may have been deleted or is no longer available.
                        </p>
                        <p class="text-sm text-blue-500 mb-8">
                            The content you're looking for cannot be found.
                        </p>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>"
                               class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-full hover:shadow-lg transition-all font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Go to Home
                            </a>

                            <button @click="goBack"
                                    class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-blue-300 text-blue-700 rounded-full hover:bg-blue-50 transition-all font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Go Back
                            </button>
                        </div>
                    </div>
                </div>
            </main>

            <?= $this->element('right_sidebar') ?>
        </div>
    </div>

    <?= $this->element('mobile_nav', ['active' => 'home']) ?>
</div>

<script>
if (window.Vue) {
    const { createApp } = Vue;
    
    createApp({
        data() {
            return {
                profileUser: null
            };
        },
        
        mounted() {
            this.fetchCurrentUserProfile();
        },
        
        methods: {
            async fetchCurrentUserProfile() {
                try {
                    const response = await fetch('/api/profile/current');
                    if (!response.ok) return;
                    
                    const data = await response.json();
                    if (data.success) {
                        const user = data.user;
                        this.profileUser = {
                            full_name: user.full_name || '',
                            username: user.username || '',
                            about: user.about || '',
                            profile_photo: user.profile_photo_path || '',
                            initial: (user.full_name || user.username || 'U').charAt(0).toUpperCase()
                        };
                    }
                } catch (error) {
                    console.error('Error fetching profile:', error);
                }
            },
            
            goBack() {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    window.location.href = '<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'dashboard']) ?>';
                }
            }
        }
    }).mount('#not-found-app');
}
</script>
