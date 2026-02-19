<?php
/**
 * Post Composer Element
 * @var \App\View\AppView $this
 * @var string $placeholder Optional placeholder text
 */
$placeholder = $placeholder ?? "What's happening?";
?>

<div class="bg-white/90 backdrop-blur rounded-xl lg:rounded-2xl shadow-xl border border-blue-100 p-4 lg:p-6 overflow-visible relative"
     @dragover.prevent="handleComposerDragOver"
     @dragleave.prevent="handleComposerDragLeave"
     @drop.prevent="handleComposerDrop"
     :class="[composerDragActive ? 'ring-2 ring-blue-500 ring-inset' : '', showVisibilityMenu ? 'z-[100]' : 'z-0']">
    <div class="flex items-start gap-3 lg:gap-4 overflow-visible">
        <div class="flex-1 overflow-visible">
            <textarea v-model="composer.text" rows="3" placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') ?>"
                class="w-full resize-none border-0 focus:ring-0 text-sm lg:text-base text-blue-800 placeholder-blue-400 bg-transparent"></textarea>

            <!-- Drag and Drop Zone (shown when no images) -->
            <div v-show="!composer.imagePreviews.length" 
                 class="mt-3 lg:mt-4 border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer border-blue-200 bg-blue-50/50"
                 @click="$refs.fileInput?.click()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-blue-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="text-sm text-blue-600 font-medium">Drag & drop images here or click to browse</p>
                <p class="text-xs text-blue-400 mt-1">JPEG, PNG, GIF, WebP • Max 5MB each • Up to 10 images</p>
            </div>

            <div v-if="composer.imagePreviews.length > 0" class="mt-3 lg:mt-4">
                <div class="grid grid-cols-2 gap-2">
                    <div v-for="(preview, index) in composer.imagePreviews" :key="index" class="relative bg-black rounded-lg overflow-hidden">
                        <img :src="preview" alt="preview"
                            class="h-24 lg:h-32 w-full object-contain" />
                        <button @click="removeImage(index)"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <p v-if="composer.imageFiles.length < 10" class="text-xs text-blue-500 mt-2 text-center">
                    💡 Drag & drop more images or click "Add images" below ({{ 10 - composer.imageFiles.length }} remaining)
                </p>
            </div>

            <div class="mt-3 lg:mt-4 flex items-center justify-between gap-2 overflow-visible">
                <div class="flex items-center gap-2 overflow-visible">
                    <label
                        class="flex items-center gap-1 lg:gap-2 cursor-pointer text-blue-600 hover:text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path
                                d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0016.586 6L13 2.414A2 2 0 0011.586 2H4z" />
                        </svg>
                        <span class="text-xs lg:text-sm hidden sm:inline">Add images</span>
                        <input type="file" accept="image/*" multiple @change="onImageChange"
                            class="hidden" ref="fileInput" />
                    </label>
                    <span v-if="composer.imageFiles.length > 0" class="text-xs text-blue-500">
                        {{ composer.imageFiles.length }} <span class="hidden sm:inline">image{{
                            composer.imageFiles.length > 1 ? 's' : '' }}</span>
                    </span>
                    
                    <!-- Visibility Selector -->
                    <div class="relative ml-2 overflow-visible">
                        <button @click="toggleVisibilityMenu" type="button"
                            class="flex items-center gap-1 text-xs lg:text-sm text-blue-600 hover:text-blue-700 px-2 py-1 rounded-lg hover:bg-blue-50 transition">
                            <svg v-if="composer.visibility === 'public'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6.115 5.19.319 1.913A6 6 0 0 0 8.11 10.36L9.75 12l-.387.775c-.217.433-.132.956.21 1.298l1.348 1.348c.21.21.329.497.329.795v1.089c0 .426.24.815.622 1.006l.153.076c.433.217.956.132 1.298-.21l.723-.723a8.7 8.7 0 0 0 2.288-4.042 1.087 1.087 0 0 0-.358-1.099l-1.33-1.108c-.251-.21-.582-.299-.905-.245l-1.17.195a1.125 1.125 0 0 1-.98-.314l-.295-.295a1.125 1.125 0 0 1 0-1.591l.13-.132a1.125 1.125 0 0 1 1.3-.21l.603.302a.809.809 0 0 0 1.086-1.086L14.25 7.5l1.256-.837a4.5 4.5 0 0 0 1.528-1.732l.146-.292M6.115 5.19A9 9 0 1 0 17.18 4.64M6.115 5.19A8.965 8.965 0 0 1 12 3c1.929 0 3.716.607 5.18 1.64" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                                <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                            </svg>
                            <span class="hidden sm:inline">{{ composer.visibility === 'public' ? 'Public' : 'Friends' }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div v-if="showVisibilityMenu" @click.stop class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-blue-100 py-1 z-[9999]">
                            <button @click="setVisibility('public')" class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 flex items-center gap-2"
                                :class="composer.visibility === 'public' ? 'text-blue-700 font-medium' : 'text-blue-600'">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6.115 5.19.319 1.913A6 6 0 0 0 8.11 10.36L9.75 12l-.387.775c-.217.433-.132.956.21 1.298l1.348 1.348c.21.21.329.497.329.795v1.089c0 .426.24.815.622 1.006l.153.076c.433.217.956.132 1.298-.21l.723-.723a8.7 8.7 0 0 0 2.288-4.042 1.087 1.087 0 0 0-.358-1.099l-1.33-1.108c-.251-.21-.582-.299-.905-.245l-1.17.195a1.125 1.125 0 0 1-.98-.314l-.295-.295a1.125 1.125 0 0 1 0-1.591l.13-.132a1.125 1.125 0 0 1 1.3-.21l.603.302a.809.809 0 0 0 1.086-1.086L14.25 7.5l1.256-.837a4.5 4.5 0 0 0 1.528-1.732l.146-.292M6.115 5.19A9 9 0 1 0 17.18 4.64M6.115 5.19A8.965 8.965 0 0 1 12 3c1.929 0 3.716.607 5.18 1.64" />
                                </svg>
                                <div>
                                    <div>Public</div>
                                    <div class="text-xs text-blue-400">Anyone can see this post</div>
                                </div>
                            </button>
                            <button @click="setVisibility('friends')" class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 flex items-center gap-2"
                                :class="composer.visibility === 'friends' ? 'text-blue-700 font-medium' : 'text-blue-600'">
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
                </div>

                <div>
                    <button @click="createPost" :disabled="!canPost || isPosting"
                        class="bg-blue-600 text-white px-3 lg:px-4 py-2 rounded-lg lg:rounded-xl text-sm font-semibold hover:bg-blue-700 disabled:opacity-50">
                        {{ isPosting ? 'Posting...' : 'Post' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
