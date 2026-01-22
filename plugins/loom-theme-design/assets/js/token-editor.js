/**
 * Token Editor - Input handling and CSS variable updates
 *
 * @package Loom\ThemeManager
 * @since 1.2.0
 */

(function(window) {
    'use strict';

    const { tokens } = window.themeManagerData || {};
    let currentTokens = JSON.parse(JSON.stringify(tokens || {}));
    let savedTokens = JSON.parse(JSON.stringify(tokens || {}));
    let activeColorMode = 'light';
    let hasUnsavedChanges = false;

    /**
     * Convert camelCase to kebab-case
     */
    function camelToKebab(str) {
        return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
    }

    /**
     * Show toast notification
     */
    function toast(message, type = 'success', duration = 4000) {
        const container = document.getElementById('theme-manager-toast-container') ||
            createToastContainer();

        const toastEl = document.createElement('div');
        toastEl.className = `theme-manager-toast ${type}`;
        toastEl.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">&times;</button>
        `;

        container.appendChild(toastEl);

        if (duration > 0) {
            setTimeout(() => {
                toastEl.style.opacity = '0';
                toastEl.style.transform = 'translateX(100%)';
                setTimeout(() => toastEl.remove(), 200);
            }, duration);
        }

        return toastEl;
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'theme-manager-toast-container';
        container.className = 'theme-manager-toast-container';
        document.body.appendChild(container);
        return container;
    }

    /**
     * Mark changes as unsaved
     */
    function markUnsaved() {
        if (!hasUnsavedChanges) {
            hasUnsavedChanges = true;
            const actionsBar = document.getElementById('actions-bar');
            if (actionsBar) {
                actionsBar.classList.add('unsaved');
            }
        }
    }

    /**
     * Mark changes as saved
     */
    function markSaved() {
        hasUnsavedChanges = false;
        savedTokens = JSON.parse(JSON.stringify(currentTokens));
        const actionsBar = document.getElementById('actions-bar');
        if (actionsBar) {
            actionsBar.classList.remove('unsaved');
        }
    }

    /**
     * Update a CSS variable in real-time (for page preview)
     */
    function updateCssVariable(token, value) {
        const parts = token.split('.');
        const category = parts[0];

        // Handle colors with light/dark mode
        if (category === 'colors' && parts.length === 3) {
            const mode = parts[1]; // 'light' or 'dark'
            const name = parts[2];
            const cssVar = `--loom-${camelToKebab(name)}`;

            // Only update document root if we're viewing that mode
            if (mode === activeColorMode) {
                document.documentElement.style.setProperty(cssVar, value);
            }

            // Always update the preview panel for the relevant mode
            updatePreviewVariable(cssVar, value, mode);
            return;
        }

        // Handle spacing
        if (category === 'spacing') {
            const name = parts[1];
            const cssVar = `--loom-spacing-${name}`;
            document.documentElement.style.setProperty(cssVar, `${value}px`);
            return;
        }

        // Handle shapes
        if (category === 'shapes') {
            const name = parts[1];
            const cssVar = `--loom-rounded-${name}`;
            document.documentElement.style.setProperty(cssVar, `${value}px`);
            return;
        }

        // Handle typography
        if (category === 'typography') {
            const name = parts[1];
            let cssVar, cssValue;

            if (name.startsWith('size')) {
                cssVar = `--loom-size-${name.replace('size', '').toLowerCase()}`;
                cssValue = `${value}px`;
            } else if (name.startsWith('font')) {
                cssVar = `--loom-font-${name.replace('font', '').toLowerCase()}`;
                cssValue = `${value}, sans-serif`;
            } else if (name === 'lineHeight') {
                cssVar = '--loom-line-height';
                cssValue = value;
            }

            if (cssVar) {
                document.documentElement.style.setProperty(cssVar, cssValue);
            }
        }
    }

    /**
     * Update preview panel CSS variable
     */
    function updatePreviewVariable(cssVar, value, mode) {
        const previewPanel = document.getElementById('preview-panel');
        if (!previewPanel) return;

        // Only update if we're viewing that mode
        const isDarkPreview = previewPanel.classList.contains('dark-mode');
        if ((mode === 'dark' && isDarkPreview) || (mode === 'light' && !isDarkPreview)) {
            previewPanel.style.setProperty(cssVar, value);
        }
    }

    /**
     * Update token state for saving
     */
    function updateTokenState(token, value) {
        const parts = token.split('.');
        const category = parts[0];

        // Initialize category if needed
        if (!currentTokens[category]) {
            currentTokens[category] = {};
        }

        // Handle colors with light/dark mode
        if (category === 'colors' && parts.length === 3) {
            const mode = parts[1];
            const name = parts[2];

            if (!currentTokens.colors[mode]) {
                currentTokens.colors[mode] = {};
            }
            currentTokens.colors[mode][name] = value;
            markUnsaved();
            return;
        }

        // Legacy flat structure or other categories
        if (parts.length === 2) {
            currentTokens[category][parts[1]] = value;
            markUnsaved();
        }
    }

    /**
     * Update visual previews for spacing/shapes
     */
    function updatePreviews(input) {
        const token = input.dataset.token;

        if (token.startsWith('spacing.')) {
            const preview = input.parentElement.querySelector('.spacing-preview');
            if (preview) preview.style.width = `${input.value}px`;
        }

        if (token.startsWith('shapes.')) {
            const preview = input.parentElement.querySelector('.shape-preview');
            if (preview) preview.style.borderRadius = `${input.value}px`;
        }
    }

    /**
     * Initialize color input handlers
     */
    function initColorInputs() {
        const colorInputs = document.querySelectorAll('input[type="color"]');

        colorInputs.forEach(input => {
            const hexInput = document.querySelector(`[data-for="${input.id}"]`);

            if (hexInput) {
                input.addEventListener('input', () => {
                    hexInput.value = input.value;
                    updateCssVariable(input.dataset.token, input.value);
                    updateTokenState(input.dataset.token, input.value);
                });

                hexInput.addEventListener('input', () => {
                    if (/^#[0-9A-Fa-f]{6}$/.test(hexInput.value)) {
                        input.value = hexInput.value;
                        updateCssVariable(input.dataset.token, hexInput.value);
                        updateTokenState(input.dataset.token, hexInput.value);
                    }
                });

                // Also handle blur for partial hex values
                hexInput.addEventListener('blur', () => {
                    // Normalize hex if needed
                    const val = hexInput.value.trim();
                    if (val.length === 4 && val.startsWith('#')) {
                        // Expand #RGB to #RRGGBB
                        const expanded = '#' + val[1] + val[1] + val[2] + val[2] + val[3] + val[3];
                        hexInput.value = expanded;
                        input.value = expanded;
                        updateCssVariable(input.dataset.token, expanded);
                        updateTokenState(input.dataset.token, expanded);
                    }
                });
            }
        });
    }

    /**
     * Initialize all other input handlers
     */
    function initInputs() {
        const inputs = document.querySelectorAll('input[data-token], select[data-token]');

        inputs.forEach(input => {
            if (input.type === 'color') return;

            input.addEventListener('input', () => {
                const value = input.type === 'number' ? parseFloat(input.value) : input.value;
                updateCssVariable(input.dataset.token, value);
                updateTokenState(input.dataset.token, value);
                updatePreviews(input);
            });
        });
    }

    /**
     * Initialize collapsible color groups
     */
    function initCollapsibleGroups() {
        const groupHeaders = document.querySelectorAll('.color-group-header');

        groupHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const group = header.closest('.color-group');
                if (group) {
                    group.classList.toggle('collapsed');
                }
            });
        });
    }

    /**
     * Update dark mode inputs with new values
     */
    function updateDarkInputs(darkColors) {
        Object.entries(darkColors).forEach(([key, value]) => {
            const input = document.getElementById(`dark-color-${key}`);
            const hexInput = document.querySelector(`[data-for="dark-color-${key}"]`);

            if (input) {
                input.value = value;
            }
            if (hexInput) {
                hexInput.value = value;
            }
        });

        // Update state
        if (!currentTokens.colors) {
            currentTokens.colors = {};
        }
        currentTokens.colors.dark = darkColors;

        markUnsaved();

        // Update preview if viewing dark mode
        const previewPanel = document.getElementById('preview-panel');
        if (previewPanel && previewPanel.classList.contains('dark-mode')) {
            Object.entries(darkColors).forEach(([key, value]) => {
                const cssVar = `--loom-${camelToKebab(key)}`;
                previewPanel.style.setProperty(cssVar, value);
            });
        }
    }

    /**
     * Warn before leaving with unsaved changes
     */
    function initBeforeUnloadWarning() {
        window.addEventListener('beforeunload', (e) => {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    }

    // Public API
    window.TokenEditor = {
        init: () => {
            // Ensure tokens structure exists
            if (!currentTokens.colors) {
                currentTokens.colors = {};
            }
            if (!currentTokens.colors.light && tokens?.colors) {
                // Handle legacy flat structure
                if (!tokens.colors.light) {
                    currentTokens.colors = { light: tokens.colors, dark: {}, darkOverrides: {} };
                }
            }

            initColorInputs();
            initInputs();
            initCollapsibleGroups();
            initBeforeUnloadWarning();
        },
        getTokens: () => currentTokens,
        updateDarkInputs: updateDarkInputs,
        setActiveMode: (mode) => { activeColorMode = mode; },
        markSaved: markSaved,
        hasUnsavedChanges: () => hasUnsavedChanges,
        toast: toast
    };

})(window);
