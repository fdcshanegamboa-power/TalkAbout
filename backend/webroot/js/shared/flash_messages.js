/**
 * Flash Messages Component
 * Handles auto-dismissal and manual closing of flash/alert messages
 */

(function() {
    'use strict';

    const TIMEOUT = 5000; // milliseconds before auto-dismiss
    const ANIMATION_DURATION = 300; // milliseconds for fade-out animation

    function initFlashMessages() {
        // Try both selectors to find the flash message container
        const container = document.querySelector('.fixed.top-20.md\\:top-4.right-4.z-\\[60\\]') ||
                         document.querySelector('.fixed.top-4.right-4.z-50');
        
        if (!container) {
            console.log('No flash message container found');
            return;
        }

        Array.from(container.children).forEach(function(messageEl) {
            setupFlashMessage(messageEl);
        });
    }

    function setupFlashMessage(messageEl) {
        // Skip if already set up
        if (messageEl.dataset.flashInitialized === 'true') {
            return;
        }
        messageEl.dataset.flashInitialized = 'true';

        // Apply smooth transition styles
        messageEl.style.transition = `opacity ${ANIMATION_DURATION}ms ease, transform ${ANIMATION_DURATION}ms ease`;
        
        // Ensure flex layout for proper button positioning
        if (!messageEl.style.display || messageEl.style.display === 'block') {
            messageEl.style.display = 'flex';
            messageEl.style.alignItems = 'center';
            messageEl.style.gap = '0.5rem';
        }

        // Apply proper styling
        if (!messageEl.classList.contains('p-4') && !messageEl.classList.contains('p-3')) {
            messageEl.style.padding = '0.75rem 1rem';
            messageEl.style.borderRadius = '0.5rem';
            messageEl.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
        }

        // Add close button if it doesn't exist
        if (!messageEl.querySelector('[data-flash-close]')) {
            const closeBtn = createCloseButton();
            messageEl.appendChild(closeBtn);
            
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                dismissMessage(messageEl);
            });
        }

        // Set up auto-dismiss
        const autoDismissTimer = setTimeout(function() {
            dismissMessage(messageEl);
        }, TIMEOUT);

        // Store timer ID so it can be cleared if manually dismissed
        messageEl.dataset.autoDismissTimer = autoDismissTimer;
    }

    function createCloseButton() {
        const button = document.createElement('button');
        button.setAttribute('type', 'button');
        button.setAttribute('aria-label', 'Close notification');
        button.setAttribute('data-flash-close', 'true');
        button.className = 'ml-auto flex-shrink-0 inline-flex items-center justify-center h-6 w-6 rounded-full hover:bg-black/10 transition-colors';
        button.style.background = 'transparent';
        button.style.border = 'none';
        button.style.cursor = 'pointer';
        
        button.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        `;
        
        return button;
    }

    function dismissMessage(messageEl) {
        if (!messageEl || !messageEl.parentNode) {
            return;
        }

        // Clear auto-dismiss timer if it exists
        if (messageEl.dataset.autoDismissTimer) {
            clearTimeout(parseInt(messageEl.dataset.autoDismissTimer));
        }

        // Fade out animation
        messageEl.style.opacity = '0';
        messageEl.style.transform = 'translateY(-6px)';
        
        // Remove from DOM after animation completes
        setTimeout(function() {
            if (messageEl.parentNode) {
                messageEl.parentNode.removeChild(messageEl);
            }
        }, ANIMATION_DURATION);
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFlashMessages);
    } else {
        initFlashMessages();
    }

    // Expose function globally for dynamic flash messages
    window.FlashMessages = {
        init: initFlashMessages,
        setup: setupFlashMessage,
        dismiss: dismissMessage,
        
        /**
         * Create and show a new flash message programmatically
         * @param {string} message - The message text
         * @param {string} type - Message type: 'success', 'error', 'warning', 'info'
         */
        show: function(message, type = 'info') {
            const container = document.querySelector('.fixed.top-20.md\\:top-4.right-4.z-\\[60\\]') ||
                             document.querySelector('.fixed.top-4.right-4.z-50');
            
            if (!container) {
                console.warn('Flash message container not found. Creating one...');
                return;
            }

            const messageEl = document.createElement('div');
            
            // Apply different styles based on type
            const typeStyles = {
                success: 'bg-green-100 border border-green-400 text-green-700',
                error: 'bg-red-100 border border-red-400 text-red-700',
                warning: 'bg-yellow-100 border border-yellow-400 text-yellow-700',
                info: 'bg-blue-100 border border-blue-400 text-blue-700'
            };
            
            messageEl.className = `${typeStyles[type] || typeStyles.info} px-4 py-3 rounded relative mb-2`;
            messageEl.style.display = 'flex';
            messageEl.style.alignItems = 'center';
            messageEl.style.gap = '0.5rem';
            
            const textNode = document.createElement('span');
            textNode.textContent = message;
            messageEl.appendChild(textNode);
            
            container.appendChild(messageEl);
            setupFlashMessage(messageEl);
        }
    };

})();
