/**
 * Mobile Menu Component
 * Handles dropdown menu functionality for mobile devices
 */

(function() {
    'use strict';
    
    function initMobileMenu() {
        const menuToggle = document.getElementById('mobile-menu-toggle');
        const menuClose = document.getElementById('mobile-menu-close');
        const menuOverlay = document.getElementById('mobile-dropdown-menu');
        const menuPanel = document.getElementById('mobile-menu-panel');
        
        if (!menuToggle || !menuOverlay || !menuPanel) return;
        
        function openMenu() {
            menuOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Trigger animation
            setTimeout(() => {
                menuPanel.classList.remove('translate-x-full');
            }, 10);
        }
        
        function closeMenu() {
            menuPanel.classList.add('translate-x-full');
            setTimeout(() => {
                menuOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }, 300);
        }
        
        menuToggle.addEventListener('click', openMenu);
        
        if (menuClose) {
            menuClose.addEventListener('click', closeMenu);
        }
        
        // Close when clicking overlay
        menuOverlay.addEventListener('click', function(e) {
            if (e.target === menuOverlay) {
                closeMenu();
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileMenu);
    } else {
        initMobileMenu();
    }
})();
