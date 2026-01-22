/**
 * Theme Manager API Client
 *
 * @package Loom\ThemeManager
 * @since 1.0.0
 */

(function(window) {
    'use strict';

    const config = window.themeManagerData || {};
    const { nonce, apiUrl } = config;

    // Debug: Log configuration
    if (!nonce || !apiUrl) {
        console.error('Theme Manager: Missing configuration', { nonce: !!nonce, apiUrl: !!apiUrl });
    }

    const ThemeManagerApi = {
        async request(endpoint, options = {}) {
            const url = `${apiUrl}${endpoint}`;

            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce,
                        ...options.headers,
                    },
                    credentials: 'same-origin',
                });

                // Try to parse JSON, but handle non-JSON responses
                let data;
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    throw new Error(`Server error (${response.status}): Invalid response format`);
                }

                if (!response.ok) {
                    let errorMessage = data.error?.message || data.message || `Request failed (${response.status})`;

                    // Handle expired nonce/session
                    if (data.code === 'rest_cookie_invalid_nonce' || errorMessage.includes('Cookie check failed')) {
                        errorMessage = 'Session expired. Please refresh the page and try again.';
                    }

                    throw new Error(errorMessage);
                }

                return data;
            } catch (error) {
                if (error.name === 'TypeError') {
                    throw new Error('Network error: Could not connect to server');
                }
                throw error;
            }
        },

        async saveTokens(tokenData) {
            return this.request('/tokens', {
                method: 'POST',
                body: JSON.stringify(tokenData),
            });
        },

        async resetTokens() {
            return this.request('/tokens/reset', {
                method: 'POST',
            });
        },

        /**
         * Generate dark theme from saved light colors (server-side)
         * @deprecated Use generateDarkFromLight instead
         */
        async generateDark() {
            return this.request('/tokens/generate-dark', {
                method: 'POST',
            });
        },

        /**
         * Generate dark theme from provided light colors (client-side values)
         * This allows generating dark theme from unsaved changes
         */
        async generateDarkFromLight(lightColors) {
            return this.request('/tokens/generate-dark', {
                method: 'POST',
                body: JSON.stringify({ lightColors }),
            });
        },

        async getColorGroups() {
            return this.request('/tokens/color-groups', {
                method: 'GET',
            });
        }
    };

    window.ThemeManagerApi = ThemeManagerApi;

})(window);
