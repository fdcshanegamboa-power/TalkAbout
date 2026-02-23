<?php
/**
 * Shared Modal Element
 * 
 * Requires ModalMixin to be included in the Vue app.
 * Uses consistent styling across all pages.
 * 
 * @var \App\View\AppView $this
 */
?>

<!-- Confirmation/Alert Modal -->
<div v-if="modal.show" 
     @click="handleModalCancel"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/20 backdrop-blur-sm p-4"
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
