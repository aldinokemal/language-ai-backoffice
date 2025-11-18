/**
 * Kendo UI Dialog Helper
 * Provides easy-to-use wrapper functions for common dialog patterns
 */

const KendoDialog = {
    /**
     * Show a confirmation dialog
     * @param {Object} options - Dialog options
     * @param {string} options.title - Dialog title
     * @param {string} options.content - Dialog content/message
     * @param {string} options.confirmText - Confirm button text (default: "OK")
     * @param {string} options.cancelText - Cancel button text (default: "Cancel")
     * @param {Function} options.onConfirm - Callback when confirmed
     * @param {Function} options.onCancel - Callback when cancelled (optional)
     * @param {number} options.width - Dialog width (default: 450)
     */
    confirm: function(options) {
        const defaults = {
            width: 450,
            confirmText: 'OK',
            cancelText: 'Cancel',
            onCancel: function() {}
        };
        
        const settings = Object.assign({}, defaults, options);
        
        return $('<div></div>').kendoDialog({
            width: settings.width + "px",
            title: settings.title,
            closable: false,
            modal: true,
            content: `<p>${settings.content}</p>`,
            actions: [
                { 
                    text: settings.cancelText,
                    action: function() {
                        settings.onCancel();
                        return true;
                    }
                },
                { 
                    text: settings.confirmText, 
                    primary: true,
                    action: function() {
                        settings.onConfirm();
                        return true;
                    }
                }
            ]
        }).data("kendoDialog").open();
    },

    /**
     * Show an alert/info dialog
     * @param {Object} options - Dialog options
     * @param {string} options.title - Dialog title
     * @param {string} options.content - Dialog content/message
     * @param {string} options.buttonText - Button text (default: "OK")
     * @param {Function} options.onClose - Callback when closed (optional)
     * @param {number} options.width - Dialog width (default: 400)
     * @param {string} options.type - Dialog type: 'success', 'error', 'warning', 'info' (optional)
     */
    alert: function(options) {
        const defaults = {
            width: 400,
            buttonText: 'OK',
            onClose: function() {},
            type: null
        };
        
        const settings = Object.assign({}, defaults, options);
        
        let iconClass = '';
        if (settings.type) {
            const iconMap = {
                'success': 'k-i-check-circle',
                'error': 'k-i-x-circle',
                'warning': 'k-i-warning',
                'info': 'k-i-info-circle'
            };
            iconClass = iconMap[settings.type] || '';
        }
        
        const content = iconClass 
            ? `<div class="flex items-center gap-3"><span class="k-icon ${iconClass}" style="font-size: 24px;"></span><p>${settings.content}</p></div>`
            : `<p>${settings.content}</p>`;
        
        return $('<div></div>').kendoDialog({
            width: settings.width + "px",
            title: settings.title,
            closable: true,
            modal: false,
            content: content,
            actions: [
                { 
                    text: settings.buttonText, 
                    primary: true,
                    action: function() {
                        settings.onClose();
                        return true;
                    }
                }
            ]
        }).data("kendoDialog").open();
    },

    /**
     * Show a loading dialog
     * @param {Object} options - Dialog options
     * @param {string} options.title - Dialog title (default: "Loading...")
     * @param {string} options.content - Loading message (default: "Please wait...")
     * @param {number} options.width - Dialog width (default: 300)
     * @returns {Object} Dialog instance with close() method
     */
    loading: function(options) {
        const defaults = {
            title: 'Loading...',
            content: 'Please wait...',
            width: 300
        };
        
        const settings = Object.assign({}, defaults, options);
        
        const dialog = $('<div></div>').kendoDialog({
            width: settings.width + "px",
            title: settings.title,
            closable: false,
            modal: true,
            content: `<div class="text-center"><span class="k-icon k-i-loading" style="font-size: 24px;"></span><p class="mt-3">${settings.content}</p></div>`,
            actions: []
        }).data("kendoDialog");
        
        dialog.open();
        
        return {
            close: function() {
                dialog.close();
                dialog.destroy();
            }
        };
    },

    /**
     * Show a prompt/input dialog
     * @param {Object} options - Dialog options
     * @param {string} options.title - Dialog title
     * @param {string} options.label - Input label
     * @param {string} options.value - Default value (optional)
     * @param {string} options.placeholder - Input placeholder (optional)
     * @param {Function} options.onSubmit - Callback with input value
     * @param {Function} options.onCancel - Callback when cancelled (optional)
     * @param {number} options.width - Dialog width (default: 400)
     */
    prompt: function(options) {
        const defaults = {
            width: 400,
            value: '',
            placeholder: '',
            onCancel: function() {}
        };
        
        const settings = Object.assign({}, defaults, options);
        const inputId = 'kendo-prompt-input-' + Date.now();
        
        const content = `
            <div class="k-form">
                <label class="k-label" for="${inputId}">${settings.label}</label>
                <input type="text" id="${inputId}" class="k-textbox k-input k-input-md k-rounded-md k-input-solid" 
                    value="${settings.value}" placeholder="${settings.placeholder}" style="width: 100%;" />
            </div>
        `;
        
        const dialog = $('<div></div>').kendoDialog({
            width: settings.width + "px",
            title: settings.title,
            closable: false,
            modal: true,
            content: content,
            actions: [
                { 
                    text: 'Cancel',
                    action: function() {
                        settings.onCancel();
                        return true;
                    }
                },
                { 
                    text: 'OK', 
                    primary: true,
                    action: function() {
                        const value = $(`#${inputId}`).val();
                        settings.onSubmit(value);
                        return true;
                    }
                }
            ],
            open: function() {
                // Focus the input when dialog opens
                setTimeout(function() {
                    $(`#${inputId}`).focus();
                }, 100);
            }
        }).data("kendoDialog");
        
        dialog.open();
        
        return dialog;
    }
};

// Make it available globally
window.KendoDialog = KendoDialog;