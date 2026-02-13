<?php
/**
 * Right sidebar (Friends & Suggestions) element
 * @var \App\View\AppView $this
 */

// Static friend list data (will be dynamic in the future)
$friends = [
    [
        'name' => 'Sarah Johnson',
        'username' => 'sarahj',
        'avatar_color' => 'from-pink-500 to-rose-500',
        'online' => true,
        'mutual_friends' => 0
    ],
    [
        'name' => 'Mike Chen',
        'username' => 'mikechen',
        'avatar_color' => 'from-blue-500 to-cyan-500',
        'online' => true,
        'mutual_friends' => 0
    ],
    [
        'name' => 'Emma Davis',
        'username' => 'emmad',
        'avatar_color' => 'from-purple-500 to-indigo-500',
        'online' => false,
        'mutual_friends' => 0
    ],
    [
        'name' => 'Alex Turner',
        'username' => 'alexturner',
        'avatar_color' => 'from-green-500 to-emerald-500',
        'online' => true,
        'mutual_friends' => 0
    ],
    [
        'name' => 'Lisa Park',
        'username' => 'lisapark',
        'avatar_color' => 'from-orange-500 to-amber-500',
        'online' => false,
        'mutual_friends' => 0
    ]
];

$suggestions = [
    [
        'name' => 'David Kim',
        'username' => 'davidkim',
        'avatar_color' => 'from-teal-500 to-cyan-500',
        'mutual_friends' => 3
    ],
    [
        'name' => 'Rachel Green',
        'username' => 'rachelg',
        'avatar_color' => 'from-violet-500 to-purple-500',
        'mutual_friends' => 5
    ],
    [
        'name' => 'Tom Anderson',
        'username' => 'tomanderson',
        'avatar_color' => 'from-red-500 to-pink-500',
        'mutual_friends' => 2
    ]
];
?>

<!-- Right Sidebar - Hidden on mobile, visible from medium screens (768px+) -->
<aside class="hidden md:block w-72 bg-white/90 backdrop-blur rounded-2xl shadow-lg p-6
         sticky top-20 self-start max-h-[calc(100vh-5.5rem)] overflow-y-auto no-scrollbar z-10">
    
    <!-- Friends Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold tracking-tight text-blue-700">Friends</h3>
            <span class="text-xs text-blue-500 bg-blue-50 px-2 py-1 rounded-full">
                <?= count(array_filter($friends, fn($f) => $f['online'])) ?> online
            </span>
        </div>

        <ul class="space-y-2">
            <?php foreach ($friends as $friend): ?>
            <li>
                <a href="#" class="flex items-center gap-3 p-2 rounded-lg hover:bg-blue-50 transition-colors group">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br <?= $friend['avatar_color'] ?> 
                                    flex items-center justify-center text-white font-bold text-sm shadow">
                            <?= strtoupper(substr($friend['name'], 0, 1)) ?>
                        </div>
                        <?php if ($friend['online']): ?>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-blue-900 truncate group-hover:text-blue-700">
                            <?= htmlspecialchars($friend['name'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <p class="text-xs text-blue-500 truncate">
                            @<?= htmlspecialchars($friend['username'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-5 w-5 text-blue-400 opacity-0 group-hover:opacity-100 transition-opacity" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Friend Suggestions -->
    <div class="pt-6 border-t border-blue-100">
        <h3 class="text-lg font-extrabold tracking-tight text-blue-700 mb-4">Suggestions</h3>
        
        <ul class="space-y-3">
            <?php foreach ($suggestions as $suggestion): ?>
            <li>
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-blue-50 transition-colors group">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br <?= $suggestion['avatar_color'] ?> 
                                flex items-center justify-center text-white font-bold text-sm shadow flex-shrink-0">
                        <?= strtoupper(substr($suggestion['name'], 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-blue-900 truncate">
                            <?= htmlspecialchars($suggestion['name'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <p class="text-xs text-blue-500">
                            <?= $suggestion['mutual_friends'] ?> mutual friends
                        </p>
                    </div>
                    <button class="px-3 py-1 text-xs font-semibold text-blue-600 bg-blue-50 
                                   hover:bg-blue-100 rounded-full transition-colors opacity-0 
                                   group-hover:opacity-100">
                        Add
                    </button>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>

        <button class="w-full mt-4 text-sm font-semibold text-blue-600 hover:text-blue-700 
                       py-2 hover:bg-blue-50 rounded-lg transition-colors">
            See All Suggestions
        </button>
    </div>
</aside>

<style>
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
