/**
 * Modal Mixin - Reusable modal component for confirmations and alerts
 * 
 * Types:
 * - confirm: Confirmation dialog with Yes/No buttons
 * - success: Success message with OK button
 * - error: Error message with OK button
 * - warning: Warning message with OK button
 * - info: Info message with OK button
 */

window.ModalMixin = {
    data() {
        return {
            modal: {
                show: false,
                type: 'confirm', // confirm, success, error, warning, info
                title: '',
                message: '',
                confirmText: 'Yes',
                cancelText: 'No',
                onConfirm: null,
                onCancel: null
            }
        };
    },

    methods: {
        /**
         * Show a confirmation modal
         * @param {Object} options - Modal options
         * @param {string} options.title - Modal title
         * @param {string} options.message - Modal message
         * @param {string} options.confirmText - Confirm button text (default: "Yes")
         * @param {string} options.cancelText - Cancel button text (default: "No")
         * @returns {Promise<boolean>} - Returns true if confirmed, false if cancelled
         */
        showConfirmModal({ title = 'Confirm', message, confirmText = 'Yes', cancelText = 'No' }) {
            return new Promise((resolve) => {
                this.modal = {
                    show: true,
                    type: 'confirm',
                    title,
                    message,
                    confirmText,
                    cancelText,
                    onConfirm: () => {
                        this.closeModal();
                        resolve(true);
                    },
                    onCancel: () => {
                        this.closeModal();
                        resolve(false);
                    }
                };
            });
        },

        /**
         * Show a success modal
         * @param {Object} options - Modal options
         * @param {string} options.title - Modal title (default: "Success")
         * @param {string} options.message - Success message
         * @param {string} options.buttonText - Button text (default: "OK")
         * @returns {Promise<void>}
         */
        showSuccessModal({ title = 'Success', message, buttonText = 'OK' }) {
            return new Promise((resolve) => {
                this.modal = {
                    show: true,
                    type: 'success',
                    title,
                    message,
                    confirmText: buttonText,
                    onConfirm: () => {
                        this.closeModal();
                        resolve();
                    },
                    onCancel: null
                };
            });
        },

        /**
         * Show an error modal
         * @param {Object} options - Modal options
         * @param {string} options.title - Modal title (default: "Error")
         * @param {string} options.message - Error message
         * @param {string} options.buttonText - Button text (default: "OK")
         * @returns {Promise<void>}
         */
        showErrorModal({ title = 'Error', message, buttonText = 'OK' }) {
            return new Promise((resolve) => {
                this.modal = {
                    show: true,
                    type: 'error',
                    title,
                    message,
                    confirmText: buttonText,
                    onConfirm: () => {
                        this.closeModal();
                        resolve();
                    },
                    onCancel: null
                };
            });
        },

        /**
         * Show a warning modal
         * @param {Object} options - Modal options
         * @param {string} options.title - Modal title (default: "Warning")
         * @param {string} options.message - Warning message
         * @param {string} options.buttonText - Button text (default: "OK")
         * @returns {Promise<void>}
         */
        showWarningModal({ title = 'Warning', message, buttonText = 'OK' }) {
            return new Promise((resolve) => {
                this.modal = {
                    show: true,
                    type: 'warning',
                    title,
                    message,
                    confirmText: buttonText,
                    onConfirm: () => {
                        this.closeModal();
                        resolve();
                    },
                    onCancel: null
                };
            });
        },

        /**
         * Show an info modal
         * @param {Object} options - Modal options
         * @param {string} options.title - Modal title (default: "Info")
         * @param {string} options.message - Info message
         * @param {string} options.buttonText - Button text (default: "OK")
         * @returns {Promise<void>}
         */
        showInfoModal({ title = 'Info', message, buttonText = 'OK' }) {
            return new Promise((resolve) => {
                this.modal = {
                    show: true,
                    type: 'info',
                    title,
                    message,
                    confirmText: buttonText,
                    onConfirm: () => {
                        this.closeModal();
                        resolve();
                    },
                    onCancel: null
                };
            });
        },

        /**
         * Close the modal
         */
        closeModal() {
            this.modal.show = false;
            // Reset after animation
            setTimeout(() => {
                this.modal = {
                    show: false,
                    type: 'confirm',
                    title: '',
                    message: '',
                    confirmText: 'Yes',
                    cancelText: 'No',
                    onConfirm: null,
                    onCancel: null
                };
            }, 300);
        },

        /**
         * Handle modal confirm action
         */
        handleModalConfirm() {
            if (this.modal.onConfirm) {
                this.modal.onConfirm();
            } else {
                this.closeModal();
            }
        },

        /**
         * Handle modal cancel action
         */
        handleModalCancel() {
            if (this.modal.onCancel) {
                this.modal.onCancel();
            } else {
                this.closeModal();
            }
        }
    },

    computed: {
        modalIcon() {
            const icons = {
                success: `<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`,
                error: `<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`,
                warning: `<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>`,
                info: `<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`,
                confirm: `<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>`
            };
            return icons[this.modal.type] || icons.info;
        }
    }
};

// Make it available globally
if (typeof window !== 'undefined') {
    window.ModalMixin = ModalMixin;
}
