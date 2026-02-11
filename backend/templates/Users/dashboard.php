<?php
/**
 * @var \App\View\AppView $this
 * @var \Authentication\Identity|null $user
 */
$this->assign('title', 'Dashboard');
?>

<div class="container mx-auto max-w-4xl px-4">
    <div id="dashboard-app">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Welcome, {{ userName }}!</h1>
                    <p class="text-gray-600 mt-1">{{ userEmail }}</p>
                </div>
                <div>
                    <?= $this->Html->link('Logout', ['action' => 'logout'], [
                        'class' => 'bg-red-600 text-white py-2 px-6 rounded-lg hover:bg-red-700 transition-colors'
                    ]) ?>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div 
                v-for="stat in stats" 
                :key="stat.title"
                class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer"
                @click="stat.action"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">{{ stat.title }}</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ stat.value }}</p>
                    </div>
                    <div :class="stat.iconColor" class="text-4xl">
                        {{ stat.icon }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Activity</h2>
            <div class="space-y-4">
                <div v-if="activities.length === 0" class="text-center py-8 text-gray-500">
                    <p>No recent activity</p>
                    <button 
                        @click="loadSampleActivities"
                        class="mt-4 bg-blue-600 text-white py-2 px-6 rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Load Sample Data
                    </button>
                </div>
                <div 
                    v-for="activity in activities" 
                    :key="activity.id"
                    class="border-l-4 pl-4 py-2"
                    :class="activity.borderColor"
                >
                    <p class="font-medium text-gray-800">{{ activity.title }}</p>
                    <p class="text-sm text-gray-600">{{ activity.description }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ activity.time }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            userName: '<?= h($user->get('username')) ?>',
            userEmail: '<?= h($user->get('email')) ?>',
            stats: [
                {
                    title: 'Messages',
                    value: '0',
                    icon: '💬',
                    iconColor: 'text-blue-500',
                    action: () => console.log('Messages clicked')
                },
                {
                    title: 'Conversations',
                    value: '0',
                    icon: '💭',
                    iconColor: 'text-green-500',
                    action: () => console.log('Conversations clicked')
                },
                {
                    title: 'Notifications',
                    value: '0',
                    icon: '🔔',
                    iconColor: 'text-yellow-500',
                    action: () => console.log('Notifications clicked')
                }
            ],
            activities: []
        }
    },
    methods: {
        loadSampleActivities() {
            this.activities = [
                {
                    id: 1,
                    title: 'Account Created',
                    description: 'Your account was successfully created',
                    time: 'Just now',
                    borderColor: 'border-green-500'
                },
                {
                    id: 2,
                    title: 'Profile Updated',
                    description: 'Your profile information was updated',
                    time: '2 minutes ago',
                    borderColor: 'border-blue-500'
                },
                {
                    id: 3,
                    title: 'Welcome!',
                    description: 'Welcome to TalkAbout. Start exploring features!',
                    time: '5 minutes ago',
                    borderColor: 'border-purple-500'
                }
            ];
        }
    },
    mounted() {
        console.log('Dashboard mounted for user:', this.userName);
    }
}).mount('#dashboard-app');
</script>
