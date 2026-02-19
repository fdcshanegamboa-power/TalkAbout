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

<div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-lg border border-blue-100 p-4 lg:p-6">
    <div class="flex items-start gap-3 lg:gap-4">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white text-base lg:text-lg font-extrabold overflow-hidden">
                <template v-if="post.profile_photo">
                    <img :src="'/img/profiles/' + post.profile_photo" 
                         alt="Profile" class="w-full h-full object-cover" />
                </template>
                <template v-else>
                    {{ post.initial }}
                </template>
            </div>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <div @click="viewProfile(post.username)" class="text-sm lg:text-base text-blue-800 font-medium truncate cursor-pointer hover:text-blue-600 hover:underline transition">{{ post.author }}</div>
                    <div class="flex items-center gap-1.5 text-xs text-blue-400">
                        <!-- Visibility Icon -->
                        <span v-if="post.visibility === 'friends'" :title="'Friends only'" class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-blue-500">
                                <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                            </svg>
                        </span>
                        <span v-else :title="'Public'" class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-blue-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6.115 5.19.319 1.913A6 6 0 0 0 8.11 10.36L9.75 12l-.387.775c-.217.433-.132.956.21 1.298l1.348 1.348c.21.21.329.497.329.795v1.089c0 .426.24.815.622 1.006l.153.076c.433.217.956.132 1.298-.21l.723-.723a8.7 8.7 0 0 0 2.288-4.042 1.087 1.087 0 0 0-.358-1.099l-1.33-1.108c-.251-.21-.582-.299-.905-.245l-1.17.195a1.125 1.125 0 0 1-.98-.314l-.295-.295a1.125 1.125 0 0 1 0-1.591l.13-.132a1.125 1.125 0 0 1 1.3-.21l.603.302a.809.809 0 0 0 1.086-1.086L14.25 7.5l1.256-.837a4.5 4.5 0 0 0 1.528-1.732l.146-.292M6.115 5.19A9 9 0 1 0 17.18 4.64M6.115 5.19A8.965 8.965 0 0 1 12 3c1.929 0 3.716.607 5.18 1.64" />
                            </svg>
                        </span>
                        <span>{{ post.time }}</span>
                    </div>
                </div>


                <div v-if="String(post.user_id) === String(currentUserId)" class="relative flex-shrink-0">
    <button @click="toggleMenu(post)" class="text-blue-500 hover:text-blue-700 p-1.5 lg:p-1 rounded-full hover:bg-blue-50 transition">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z" />
        </svg>
    </button>
    
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

            </div>

            <div v-if="post.isEditing" class="mt-2 lg:mt-3">
                <textarea id="post-composer" v-model="post.editText" rows="3" 
                          class="w-full resize-none border border-blue-300 rounded-lg p-2 lg:p-3 text-sm lg:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent text-blue-800"></textarea>
                
                <!-- Visibility Selector for Edit -->
                <div class="mt-2 relative">
                    <button @click="post.showEditVisibilityMenu = !post.showEditVisibilityMenu" type="button"
                        class="flex items-center gap-1 text-xs lg:text-sm text-blue-600 hover:text-blue-700 px-2 py-1 rounded-lg hover:bg-blue-50 transition">
                        <svg v-if="post.editVisibility === 'public'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6.115 5.19.319 1.913A6 6 0 0 0 8.11 10.36L9.75 12l-.387.775c-.217.433-.132.956.21 1.298l1.348 1.348c.21.21.329.497.329.795v1.089c0 .426.24.815.622 1.006l.153.076c.433.217.956.132 1.298-.21l.723-.723a8.7 8.7 0 0 0 2.288-4.042 1.087 1.087 0 0 0-.358-1.099l-1.33-1.108c-.251-.21-.582-.299-.905-.245l-1.17.195a1.125 1.125 0 0 1-.98-.314l-.295-.295a1.125 1.125 0 0 1 0-1.591l.13-.132a1.125 1.125 0 0 1 1.3-.21l.603.302a.809.809 0 0 0 1.086-1.086L14.25 7.5l1.256-.837a4.5 4.5 0 0 0 1.528-1.732l.146-.292M6.115 5.19A9 9 0 1 0 17.18 4.64M6.115 5.19A8.965 8.965 0 0 1 12 3c1.929 0 3.716.607 5.18 1.64" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                            <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                        </svg>
                        <span>{{ post.editVisibility === 'public' ? 'Public' : 'Friends' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    
                    <div v-if="post.showEditVisibilityMenu" @click.stop class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-blue-100 py-1 z-50">
                        <button @click="post.editVisibility = 'public'; post.showEditVisibilityMenu = false" class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 flex items-center gap-2"
                            :class="post.editVisibility === 'public' ? 'text-blue-700 font-medium' : 'text-blue-600'">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6.115 5.19.319 1.913A6 6 0 0 0 8.11 10.36L9.75 12l-.387.775c-.217.433-.132.956.21 1.298l1.348 1.348c.21.21.329.497.329.795v1.089c0 .426.24.815.622 1.006l.153.076c.433.217.956.132 1.298-.21l.723-.723a8.7 8.7 0 0 0 2.288-4.042 1.087 1.087 0 0 0-.358-1.099l-1.33-1.108c-.251-.21-.582-.299-.905-.245l-1.17.195a1.125 1.125 0 0 1-.98-.314l-.295-.295a1.125 1.125 0 0 1 0-1.591l.13-.132a1.125 1.125 0 0 1 1.3-.21l.603.302a.809.809 0 0 0 1.086-1.086L14.25 7.5l1.256-.837a4.5 4.5 0 0 0 1.528-1.732l.146-.292M6.115 5.19A9 9 0 1 0 17.18 4.64M6.115 5.19A8.965 8.965 0 0 1 12 3c1.929 0 3.716.607 5.18 1.64" />
                            </svg>
                            <div>
                                <div>Public</div>
                                <div class="text-xs text-blue-400">Anyone can see this post</div>
                            </div>
                        </button>
                        <button @click="post.editVisibility = 'friends'; post.showEditVisibilityMenu = false" class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 flex items-center gap-2"
                            :class="post.editVisibility === 'friends' ? 'text-blue-700 font-medium' : 'text-blue-600'">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                            </svg>
                            <div>
                                <div>Friends</div>
                                <div class="text-xs text-blue-400">Only friends can see this</div>
                            </div>
                        </button>
                    </div>
                </div>
                
                <div v-if="post.editImages && post.editImages.length > 0" class="mt-2">
                    <label class="text-xs font-semibold text-blue-700 mb-1 block">Current Images:</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div v-for="(img, idx) in post.editImages" :key="idx" class="relative bg-black rounded-lg overflow-hidden border-2 border-blue-200">
                            <img :src="img.path" class="h-32 w-full object-contain" />
                            <button @click="removeExistingImage(post, idx)" 
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 shadow-lg z-10">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="post.newEditImages && post.newEditImages.length > 0" class="mt-2">
                    <label class="text-xs font-semibold text-blue-700 mb-1 block">New Images:</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div v-for="(img, idx) in post.newEditImages" :key="idx" class="relative bg-black rounded-lg overflow-hidden border-2 border-green-200">
                            <img :src="img.preview" class="h-32 w-full object-contain" />
                            <button @click="removeNewEditImage(post, idx)" 
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 shadow-lg z-10">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Drag and Drop Zone for Edit Images -->
                <div class="mt-2"
                     @dragover.prevent="handleEditDragOver(post)"
                     @dragleave.prevent="handleEditDragLeave(post)"
                     @drop.prevent="handleEditDrop($event, post)"
                     :class="post.editDragActive ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-gray-50'"
                     class="border-2 border-dashed rounded-lg p-4 text-center transition-colors cursor-pointer"
                     @click="document.getElementById('edit-post-images-' + post.id).click()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-xs text-gray-600">Drag & drop or click to add images</p>
                    <p class="text-xs text-gray-400 mt-0.5">JPEG, PNG, GIF, WebP • Max 5MB</p>
                </div>

                <div class="mt-2 flex items-center gap-2">
                    <label :for="'edit-post-images-' + post.id" class="cursor-pointer px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-200 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        Add Images
                    </label>
                    <input :id="'edit-post-images-' + post.id" 
                           type="file" 
                           accept="image/*"
                           multiple
                           @change="handleEditImageSelect($event, post)"
                           class="hidden" />

                    <button @click="saveEdit(post)" :disabled="post.isSaving" 
                            class="px-3 lg:px-4 py-1.5 lg:py-2 bg-blue-600 text-white rounded-lg text-xs lg:text-sm font-semibold hover:bg-blue-700 disabled:opacity-50">
                        {{ post.isSaving ? 'Saving...' : 'Save' }}
                    </button>
                    <button @click="cancelEdit(post)" 
                            class="px-3 lg:px-4 py-1.5 lg:py-2 bg-gray-200 text-gray-700 rounded-lg text-xs lg:text-sm font-semibold hover:bg-gray-300">
                        Cancel
                    </button>
                </div>
            </div>
            
                <div v-else>
                <div v-if="post.text" class="text-sm lg:text-base text-blue-700 mt-1">
                    <div :class="(post.expanded ? 'max-h-96 overflow-auto' : 'max-h-20 overflow-hidden') + ' break-all whitespace-normal'" style="white-space: pre-line;">
                        {{ post.text }}
                    </div>

                    <div class="mt-1">
                        <button v-if="!post.expanded && isLongText(post.text)" @click="expandPost(post)" class="text-xs text-blue-600 font-semibold">See more</button>
                        <button v-else-if="post.expanded && isLongText(post.text)" @click="collapsePost(post)" class="text-xs text-blue-600 font-semibold">Show less</button>
                    </div>
                </div>
                
                <div v-if="post.images && post.images.length > 0" class="mt-2 lg:mt-3">
                    <div v-if="post.images.length === 1" class="bg-black rounded-lg overflow-hidden">
                        <img :src="post.images[0]" 
                             @click="openImageModal(post.images, 0)"
                             class="max-h-64 lg:max-h-96 w-full object-contain cursor-pointer hover:opacity-90 transition" />
                    </div>
                    <div v-else-if="post.images.length === 2" class="grid grid-cols-2 gap-1 lg:gap-2">
                        <div class="bg-black rounded-lg overflow-hidden">
                            <img :src="post.images[0]" 
                                 @click="openImageModal(post.images, 0)"
                                 class="h-40 lg:h-64 w-full object-contain cursor-pointer hover:opacity-90 transition" />
                        </div>
                        <div class="bg-black rounded-lg overflow-hidden">
                            <img :src="post.images[1]" 
                                 @click="openImageModal(post.images, 1)"
                                 class="h-40 lg:h-64 w-full object-contain cursor-pointer hover:opacity-90 transition" />
                        </div>
                    </div>
                    <div v-else class="grid grid-cols-2 gap-1 lg:gap-2">
                        <div v-for="(img, idx) in post.images.slice(0, 4)" :key="idx" 
                             :class="idx === 3 && post.images.length > 4 ? 'relative' : ''"
                             class="bg-black rounded-lg overflow-hidden">
                            <img :src="img" 
                                 @click="openImageModal(post.images, idx)"
                                 class="h-32 lg:h-48 w-full object-contain cursor-pointer hover:opacity-90 transition" />
                            <div v-if="idx === 3 && post.images.length > 4" 
                                 class="absolute inset-0 bg-black/60 flex items-center justify-center rounded-lg cursor-pointer"
                                 @click="openImageModal(post.images, idx)">
                                <span class="text-white text-2xl lg:text-3xl font-bold">+{{ post.images.length - 4 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-2 lg:mt-3 pt-2 lg:pt-3 border-t border-blue-100 flex items-center gap-4 lg:gap-6">
                    <button @click="toggleLike(post)" :class="post.liked ? 'text-indigo-600' : 'text-blue-500'" class="flex items-center gap-1.5 lg:gap-2 text-xs lg:text-sm font-semibold hover:scale-105 transition">
                        <span v-if="!post.liked" class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 lg:w-5 lg:h-5">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                            </svg>
                        </span>
                        <span v-else class="inline-flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 lg:w-5 lg:h-5">
                              <path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                            </svg>
                        </span>
                        <span>{{ post.likes }}</span>
                    </button>
                    
                    <button @click="toggleComments(post)" class="flex items-center gap-1.5 lg:gap-2 text-xs lg:text-sm font-semibold text-blue-500 hover:scale-105 transition hover:text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 lg:w-5 lg:h-5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                        </svg>
                        <span>{{ post.comments || 0 }}</span>
                    </button>
                </div>

                <div v-if="post.showComments" class="mt-3 pt-3 border-t border-blue-100">
                    <div class="mb-3">
                        <textarea v-model="post.newCommentText" 
                                  rows="2"
                                  placeholder="Write a comment..."
                                  class="w-full resize-none border border-blue-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-blue-800"></textarea>
                        
                        <div v-if="post.commentImagePreview" class="mt-2 relative inline-block bg-black rounded-lg overflow-hidden">
                            <img :src="post.commentImagePreview" class="h-20 object-contain" />
                            <button @click="removeCommentImage(post)" 
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 z-10">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Drag and Drop Zone for Comment Images -->
                        <div v-if="!post.commentImagePreview"
                             class="mt-2"
                             @dragover.prevent="handleCommentDragOver(post)"
                             @dragleave.prevent="handleCommentDragLeave(post)"
                             @drop.prevent="handleCommentDrop($event, post)"
                             :class="post.commentDragActive ? 'border-blue-500 bg-blue-50' : 'border-gray-300 bg-gray-50'"
                             class="border-2 border-dashed rounded-lg p-3 text-center transition-colors cursor-pointer"
                             @click="document.getElementById('comment-image-' + post.id).click()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="text-xs text-gray-600">Drag & drop or click to add image</p>
                            <p class="text-xs text-gray-400 mt-0.5">JPEG, PNG, GIF, WebP • Max 5MB</p>
                        </div>
                        
                        <div class="mt-2 flex items-center gap-2">
                            <label :for="'comment-image-' + post.id" class="cursor-pointer px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-200 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                                Image
                            </label>
                            <input :id="'comment-image-' + post.id" 
                                   type="file" 
                                   accept="image/*"
                                   @change="handleCommentImageSelect($event, post)"
                                   class="hidden" />
                            
                            <button @click="submitComment(post)" 
                                    :disabled="post.isSubmittingComment || (!post.newCommentText && !post.commentImageFile)"
                                    class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                {{ post.isSubmittingComment ? 'Posting...' : 'Post Comment' }}
                            </button>
                        </div>
                    </div>

                    <div v-if="post.loadingComments" class="text-center py-4 text-blue-500 text-sm">
                        Loading comments...
                    </div>
                    
                    <div v-else-if="post.commentsList && post.commentsList.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
                        <div v-for="comment in post.commentsList" :key="comment.id" :id="'comment-' + comment.id" class="relative flex gap-2 bg-blue-50/50 rounded-lg p-2">
                            <button v-if="String(comment.user_id) === String(currentUserId)" 
                                    @click="deleteComment(comment, post)" 
                                    class="absolute top-2 right-2 p-1 rounded-full hover:bg-red-100 text-blue-400 hover:text-red-600 transition-colors group"
                                    title="Delete comment">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white text-xs font-bold overflow-hidden">
                                    <template v-if="comment.profile_photo">
                                        <img :src="'/img/profiles/' + comment.profile_photo" 
                                             alt="Profile" class="w-full h-full object-cover" />
                                    </template>
                                    <template v-else>
                                        {{ comment.initial }}
                                    </template>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0 pr-6">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold text-blue-800">{{ comment.author }}</span>
                                    <span class="text-xs text-blue-400">{{ comment.time }}</span>
                                </div>
                                <div v-if="comment.content_text" class="text-sm text-blue-700 break-words">
                                            <div :class="(comment.expanded ? 'max-h-64 overflow-auto' : 'max-h-20 overflow-hidden') + ' break-all whitespace-normal'" style="white-space: pre-line;">
                                                {{ comment.content_text }}
                                            </div>

                                            <div class="mt-1">
                                                <button v-if="!comment.expanded && isLongText(comment.content_text)" @click="expandComment(comment)" class="text-xs text-blue-600 font-semibold">See more</button>
                                                <button v-else-if="comment.expanded && isLongText(comment.content_text)" @click="collapseComment(comment)" class="text-xs text-blue-600 font-semibold">Show less</button>
                                            </div>
                                        </div>
                                <div v-if="comment.content_image_path" class="mt-1">
                                    <img :src="'/img/comments/' + comment.content_image_path" 
                                         @click="openImageModal('/img/comments/' + comment.content_image_path, 0)"
                                         class="rounded-lg max-h-40 border border-blue-200 cursor-pointer hover:opacity-90 transition" />
                                </div>
                                <div class="mt-1 flex items-center gap-2">
                                    <button @click="toggleCommentLike(comment)" 
                                            :class="comment.liked ? 'text-indigo-600' : 'text-blue-500'" 
                                            class="flex items-center gap-1 text-xs font-semibold hover:scale-105 transition">
                                        <svg v-if="!comment.liked" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5">
                                            <path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                                        </svg>
                                        <span>{{ comment.likes || 0 }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-3 text-blue-400 text-sm">
                        No comments yet. Be the first to comment!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div v-if="imageModal.show" 
     @click="closeImageModal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm p-4"
     style="animation: fadeIn 0.2s ease-in;">
    
    <!-- Close Button -->
    <button @click="closeImageModal" 
            class="absolute top-4 right-4 lg:top-6 lg:right-6 text-white hover:text-gray-300 p-2 rounded-full bg-black/50 hover:bg-black/70 transition z-10">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 lg:w-8 lg:h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
    </button>
    
    <!-- Previous Button -->
    <button v-if="imageModal.images.length > 1 && imageModal.currentIndex > 0"
            @click.stop="prevImage"
            class="absolute left-4 lg:left-6 text-white hover:text-gray-300 p-2 lg:p-3 rounded-full bg-black/50 hover:bg-black/70 transition z-10">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 lg:w-8 lg:h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
    </button>
    
    <!-- Image Container -->
    <div @click.stop class="relative max-w-7xl max-h-full">
        <img :src="imageModal.images[imageModal.currentIndex]" 
             class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl"
             style="animation: scaleIn 0.2s ease-out;" />
        
        <!-- Image Counter -->
        <div v-if="imageModal.images.length > 1" 
             class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/70 text-white px-4 py-2 rounded-full text-sm font-semibold">
            {{ imageModal.currentIndex + 1 }} / {{ imageModal.images.length }}
        </div>
    </div>
    
    <!-- Next Button -->
    <button v-if="imageModal.images.length > 1 && imageModal.currentIndex < imageModal.images.length - 1"
            @click.stop="nextImage"
            class="absolute right-4 lg:right-6 text-white hover:text-gray-300 p-2 lg:p-3 rounded-full bg-black/50 hover:bg-black/70 transition z-10">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 lg:w-8 lg:h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
    </button>
</div>

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