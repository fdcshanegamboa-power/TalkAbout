<?php
/**
 * Right sidebar (Trends) element
 * @var \App\View\AppView $this
 */
?>

<aside class="w-72 bg-white/90 backdrop-blur rounded-2xl shadow-lg p-6
         sticky top-6 self-start h-[calc(100vh-3rem)] overflow-y-auto">
    <div class="mb-6">
        <h3 class="text-xl font-extrabold tracking-tight text-blue-700">Trends</h3>
        <p class="text-xs text-blue-500 mt-1">What people are talking about</p>
    </div>

    <nav>
        <ul class="space-y-3">
            <li class="p-3 rounded-lg hover:bg-blue-50">
                <p class="text-sm font-semibold text-blue-700">#TalkAboutLaunch</p>
                <p class="text-xs text-blue-400">12.3K posts</p>
            </li>

            <li class="p-3 rounded-lg hover:bg-blue-50">
                <p class="text-sm font-semibold text-blue-700">#Design</p>
                <p class="text-xs text-blue-400">4.1K posts</p>
            </li>

            <li class="p-3 rounded-lg hover:bg-blue-50">
                <p class="text-sm font-semibold text-blue-700">#OpenSource</p>
                <p class="text-xs text-blue-400">8.7K posts</p>
            </li>

            <li class="p-3 rounded-lg hover:bg-blue-50">
                <p class="text-sm font-semibold text-blue-700">#WebDev</p>
                <p class="text-xs text-blue-400">21K posts</p>
            </li>
        </ul>
    </nav>
</aside>
