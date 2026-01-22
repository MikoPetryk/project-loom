/**
 * Theme Manager Admin - Main Entry
 *
 * @package Loom\ThemeManager
 * @since 1.0.0
 */

(function() {
    'use strict';

    let activeMode = 'light';

    /**
     * Initialize main tab navigation
     */
    function initTabs() {
        const tabs = document.querySelectorAll('.tab-btn');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.dataset.tab;

                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                tab.classList.add('active');
                document.getElementById(`tab-${target}`).classList.add('active');
            });
        });
    }

    /**
     * Initialize mode toggle (Light/Dark switch in header)
     */
    function initModeToggle() {
        const modeBtns = document.querySelectorAll('.mode-switch-btn');
        const lightSection = document.querySelector('.color-mode-section[data-mode="light"]');
        const darkSection = document.querySelector('.color-mode-section[data-mode="dark"]');
        const previewPanel = document.getElementById('preview-panel');

        modeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const mode = btn.dataset.mode;
                activeMode = mode;

                // Update button states
                modeBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Show/hide color sections
                if (lightSection && darkSection) {
                    lightSection.style.display = mode === 'light' ? 'block' : 'none';
                    darkSection.style.display = mode === 'dark' ? 'block' : 'none';
                }

                // Update preview panel
                if (previewPanel) {
                    previewPanel.classList.toggle('dark-mode', mode === 'dark');
                }

                // Apply colors to preview
                applyPreviewColors(mode);

                // Update TokenEditor
                if (window.TokenEditor) {
                    window.TokenEditor.setActiveMode(mode);
                }
            });
        });
    }

    /**
     * Apply colors to preview panel based on mode
     */
    function applyPreviewColors(mode) {
        const previewPanel = document.getElementById('preview-panel');
        if (!previewPanel || !window.TokenEditor) return;

        const tokens = window.TokenEditor.getTokens();
        const colors = mode === 'dark' ? tokens.colors?.dark : tokens.colors?.light;
        if (!colors) return;

        // Apply all color variables to preview panel
        Object.entries(colors).forEach(([key, value]) => {
            const cssVar = `--loom-${camelToKebab(key)}`;
            previewPanel.style.setProperty(cssVar, value);
        });
    }

    /**
     * Convert camelCase to kebab-case
     */
    function camelToKebab(str) {
        return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
    }

    /**
     * Save all tokens to the server
     */
    async function saveTokens() {
        const saveBtn = document.getElementById('save-tokens');
        const status = document.getElementById('save-status');

        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
        status.textContent = '';
        status.className = '';

        try {
            await window.ThemeManagerApi.saveTokens(window.TokenEditor.getTokens());
            status.textContent = 'Saved successfully!';

            // Mark as saved - THIS WAS MISSING
            if (window.TokenEditor && window.TokenEditor.markSaved) {
                window.TokenEditor.markSaved();
            }
        } catch (error) {
            status.textContent = `Error: ${error.message}`;
            status.className = 'error';
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Changes';
        }
    }

    /**
     * Reset all tokens to defaults
     */
    async function resetTokens() {
        if (!confirm('Are you sure you want to reset all tokens to defaults? This will reset both light and dark mode colors.')) {
            return;
        }

        const resetBtn = document.getElementById('reset-tokens');
        const status = document.getElementById('save-status');

        resetBtn.disabled = true;
        resetBtn.textContent = 'Resetting...';

        try {
            await window.ThemeManagerApi.resetTokens();
            status.textContent = 'Reset successful! Reloading...';
            setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            status.textContent = `Error: ${error.message}`;
            status.className = 'error';
            resetBtn.disabled = false;
            resetBtn.textContent = 'Reset to Defaults';
        }
    }

    /**
     * Generate dark theme from current light colors (including unsaved changes)
     */
    async function generateDarkTheme() {
        const generateBtn = document.getElementById('generate-dark');
        const status = document.getElementById('save-status');

        if (!confirm('This will regenerate all dark mode colors from your current light theme colors. Any manual dark mode customizations will be lost. Continue?')) {
            return;
        }

        generateBtn.disabled = true;
        const originalText = generateBtn.innerHTML;
        generateBtn.innerHTML = '<span class="dashicons dashicons-update" style="animation: spin 1s linear infinite;"></span> Generating...';
        status.textContent = '';
        status.className = '';

        try {
            // Get current light colors from the UI (including unsaved changes)
            const tokens = window.TokenEditor.getTokens();
            const lightColors = tokens.colors?.light || {};

            // Send light colors to server for dark generation
            const response = await window.ThemeManagerApi.generateDarkFromLight(lightColors);

            if (response.data && response.data.dark) {
                // Update the dark mode inputs with new values
                window.TokenEditor.updateDarkInputs(response.data.dark);
                status.textContent = 'Dark theme generated! Review and save changes.';

                // Show toast
                if (window.TokenEditor.toast) {
                    window.TokenEditor.toast('Dark palette generated from current light colors', 'success');
                }
            }
        } catch (error) {
            status.textContent = `Error: ${error.message}`;
            status.className = 'error';
        } finally {
            generateBtn.disabled = false;
            generateBtn.innerHTML = originalText;
        }
    }

    /**
     * Initialize the admin page
     */
    function init() {
        initTabs();
        initModeToggle();

        if (window.TokenEditor) {
            window.TokenEditor.init();
        }

        // Bind button handlers
        document.getElementById('save-tokens')?.addEventListener('click', saveTokens);
        document.getElementById('reset-tokens')?.addEventListener('click', resetTokens);
        document.getElementById('generate-dark')?.addEventListener('click', generateDarkTheme);

        // Initial preview colors
        setTimeout(() => applyPreviewColors('light'), 100);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
