/**
 * Icon Manager REST API Client
 *
 * Provides a fetch-based API client for Icon Manager operations.
 *
 * @package IconManager
 * @since 2.1.0
 */

const IconManagerApi = {
    baseUrl: '',
    nonce: '',

    init(apiUrl, nonce) {
        this.baseUrl = apiUrl;
        this.nonce = nonce;
    },

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;

        const config = {
            ...options,
            headers: {
                'X-WP-Nonce': this.nonce,
                ...options.headers,
            },
            credentials: 'same-origin',
        };

        // Don't set Content-Type for FormData (browser sets it with boundary)
        if (!(options.body instanceof FormData)) {
            config.headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(url, config);

        // Handle 204 No Content
        if (response.status === 204) {
            return { success: true };
        }

        const data = await response.json();

        if (!response.ok) {
            throw new IconManagerApiError(
                data.error?.message || 'Request failed',
                data.error?.code || 'unknown_error',
                response.status
            );
        }

        return data;
    },

    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },

    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    },

    async delete(endpoint, data = null) {
        const options = { method: 'DELETE' };
        if (data) {
            options.body = JSON.stringify(data);
        }
        return this.request(endpoint, options);
    },

    async upload(endpoint, formData) {
        return this.request(endpoint, {
            method: 'POST',
            body: formData,
        });
    },

    // API Methods
    async getPacks() {
        return this.get('/packs');
    },

    async getPack(packName) {
        return this.get(`/packs/${packName}`);
    },

    async getPackIcons(packName) {
        return this.get(`/packs/${packName}/icons`);
    },

    async createPack(name) {
        return this.post('/packs', { name });
    },

    async deletePack(packName) {
        return this.delete(`/packs/${packName}`);
    },

    async uploadIcons(packName, files) {
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('icons[]', files[i]);
        }
        return this.upload(`/packs/${packName}/icons`, formData);
    },

    async deleteIcons(packName, icons) {
        return this.delete(`/packs/${packName}/icons/batch`, { icons });
    },

    async getIconSvg(packName, iconName, size = null, color = null) {
        let endpoint = `/packs/${packName}/icons/${iconName}`;
        const params = new URLSearchParams();
        if (size) params.append('size', size);
        if (color) params.append('color', color);
        if (params.toString()) endpoint += `?${params}`;
        return this.get(endpoint);
    },

    async getStats() {
        return this.get('/stats');
    },

    async regenerate() {
        return this.post('/regenerate', {});
    },
};

class IconManagerApiError extends Error {
    constructor(message, code, status) {
        super(message);
        this.name = 'IconManagerApiError';
        this.code = code;
        this.status = status;
    }
}

// Export for use in other modules
window.IconManagerApi = IconManagerApi;
