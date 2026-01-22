/**
 * Loom Theme Switcher
 *
 * Frontend dark mode toggle functionality.
 * Provides a simple API for switching between light and dark themes.
 *
 * @package Loom\ThemeManager
 * @since 1.1.0
 */

(function(window, document) {
    'use strict';

    const STORAGE_KEY = 'loom-theme';
    const DARK_CLASS = 'dark';

    /**
     * Get the current theme
     * @returns {'light'|'dark'}
     */
    function getTheme() {
        return document.documentElement.classList.contains(DARK_CLASS) ? 'dark' : 'light';
    }

    /**
     * Set the theme
     * @param {'light'|'dark'} mode
     */
    function setTheme(mode) {
        if (mode === 'dark') {
            document.documentElement.classList.add(DARK_CLASS);
        } else {
            document.documentElement.classList.remove(DARK_CLASS);
        }

        // Persist to localStorage
        try {
            localStorage.setItem(STORAGE_KEY, mode);
        } catch (e) {
            // localStorage might not be available
        }

        // Dispatch custom event for other scripts to listen to
        window.dispatchEvent(new CustomEvent('loom-theme-change', {
            detail: { theme: mode }
        }));
    }

    /**
     * Toggle between light and dark themes
     * @returns {'light'|'dark'} The new theme
     */
    function toggle() {
        const newTheme = getTheme() === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
        return newTheme;
    }

    /**
     * Initialize theme from stored preference
     */
    function init() {
        let savedTheme = null;

        // Try to get saved preference
        try {
            savedTheme = localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            // localStorage might not be available
        }

        // Apply saved theme if available
        if (savedTheme === 'dark' || savedTheme === 'light') {
            setTheme(savedTheme);
        }
    }

    // Public API
    window.LoomTheme = {
        /**
         * Get current theme
         * @returns {'light'|'dark'}
         */
        get: getTheme,

        /**
         * Set theme
         * @param {'light'|'dark'} mode
         */
        set: setTheme,

        /**
         * Toggle theme
         * @returns {'light'|'dark'} New theme
         */
        toggle: toggle,

        /**
         * Initialize from stored preference
         */
        init: init,

        /**
         * Check if dark mode is active
         * @returns {boolean}
         */
        isDark: function() {
            return getTheme() === 'dark';
        },

        /**
         * Check if light mode is active
         * @returns {boolean}
         */
        isLight: function() {
            return getTheme() === 'light';
        }
    };

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})(window, document);
