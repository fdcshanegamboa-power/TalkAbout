<?php
/**
 * Post Card Element
 * @var \App\View\AppView $this
 * @var bool $canEdit Whether the current user can edit this post
 * @var string|null $profilePhoto Current user's profile photo (for profile page)
 */

$canEdit = $canEdit ?? false;
$profilePhoto = $profilePhoto ?? '';
?>

<div class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100 p-6">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white text-lg font-extrabold overflow-hidden">
                <?php if ($canEdit && !empty($profilePhoto)): ?>
                    <img :src="'<?= $this->Url->build('/img/profiles/' . htmlspecialchars($profilePhoto, ENT_QUOTES, 'UTF-8')) ?>'" 
                         alt="Profile" class="w-full h-full object-cover" />
                <?php else: ?>
                    {{ post.initial }}
                <?php endif; ?>
            </div>
        </div>

        <div class="flex-1">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-blue-800 font-medium">{{ post.author }}</div>
                    <div class="text-xs text-blue-400">{{ post.time }}</div>
                </div>

                <?php if ($canEdit): ?>
                    <!-- Three-dot menu for editable posts only -->
                    <div class="relative">
                        <button @click="toggleMenu(post)" class="text-blue-500 hover:text-blue-700 p-1 rounded-full hover:bg-blue-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                            </svg>
                        </button>
                        
                        <!-- Dropdown menu -->
                        <div v-if="post.showMenu" @click.stop class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-blue-100 py-1 z-50">
                            <button @click="startEdit(post)" class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                                Edit post
                            </button>
                            <button @click="deletePost(post)" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                                Delete post
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Edit mode -->
            <div v-if="post.isEditing" class="mt-3">
                <textarea v-model="post.editText" rows="3" 
                          class="w-full resize-none border border-blue-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent text-blue-800"></textarea>
                <div class="mt-2 flex items-center gap-2">
                    <button @click="saveEdit(post)" :disabled="post.isSaving" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-50">
                        {{ post.isSaving ? 'Saving...' : 'Save' }}
                    </button>
                    <button @click="cancelEdit(post)" 
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
            
            <!-- Normal view -->
            <div v-else>
                <div v-if="post.text" class="text-blue-700 mt-1">{{ post.text }}</div>
                
                <!-- Multiple images display -->
                <div v-if="post.images && post.images.length > 0" class="mt-3">
                    <div v-if="post.images.length === 1">
                        <img :src="post.images[0]" class="rounded-lg max-h-96 w-full object-cover" />
                    </div>
                    <div v-else-if="post.images.length === 2" class="grid grid-cols-2 gap-2">
                        <img v-for="(img, idx) in post.images" :key="idx" :src="img" 
                             class="rounded-lg h-64 w-full object-cover" />
                    </div>
                    <div v-else class="grid grid-cols-2 gap-2">
                        <img v-for="(img, idx) in post.images.slice(0, 4)" :key="idx" :src="img" 
                             :class="idx === 3 && post.images.length > 4 ? 'relative' : ''"
                             class="rounded-lg h-48 w-full object-cover" />
                        <div v-if="post.images.length > 4" 
                             class="absolute bottom-2 right-2 bg-black/70 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            +{{ post.images.length - 4 }}
                        </div>
                    </div>
                </div>

                <!-- Reactions section at the bottom for ALL posts -->
                <div class="mt-3 pt-3 border-t border-blue-100 flex items-center gap-6">
                    <!-- Like button -->
                    <button @click="toggleLike(post)" :class="post.liked ? 'text-indigo-600' : 'text-blue-500'" class="flex items-center gap-2 text-sm font-semibold hover:scale-105 transition">
                        <span v-if="!post.liked" class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                            </svg>
                        </span>
                        <span v-else class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                              <path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                            </svg>
                        </span>
                        <span>{{ post.likes }}</span>
                    </button>
                    
                    <button class="flex items-center gap-2 text-sm font-semibold text-blue-500 hover:scale-105 transition hover:text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                        </svg>
                        <span>{{ post.comments || 0 }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>