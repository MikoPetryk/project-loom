/**
 * Icon Manager Admin
 *
 * Complete admin interface with popover, search, upload preview, export/import.
 *
 * @package IconManager
 * @since 3.0.0
 */

(function() {
    'use strict';

    const { nonce, apiUrl, pluginUrl, currentCategory, i18n } = window.iconManagerData || {};

    // State
    let activeCategory = currentCategory || '';
    let selectedIcons = new Set();
    let allIcons = [];
    let filteredIcons = [];
    let currentSort = 'name-asc';
    let searchQuery = '';
    let pendingUploads = [];

    // ===========================================
    // API Client
    // ===========================================

    const api = {
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

                const data = await response.json();

                if (!response.ok) {
                    let msg = data.message || data.error?.message || 'Request failed';
                    if (data.code === 'rest_cookie_invalid_nonce') {
                        msg = i18n?.sessionExpired || 'Session expired. Please refresh the page.';
                    }
                    throw new Error(msg);
                }

                return data;
            } catch (error) {
                if (error.name === 'TypeError') {
                    throw new Error(i18n?.networkError || 'Network error. Please check your connection.');
                }
                throw error;
            }
        },

        getCategories: () => api.request('/packs'),
        getIcons: (category) => api.request(`/packs/${encodeURIComponent(category)}/icons`),
        createCategory: (name) => api.request('/packs', { method: 'POST', body: JSON.stringify({ name }) }),
        deleteCategory: (name) => api.request(`/packs/${encodeURIComponent(name)}`, { method: 'DELETE' }),
        deleteIcons: (category, icons) => api.request(`/packs/${encodeURIComponent(category)}/icons/batch`, {
            method: 'DELETE',
            body: JSON.stringify({ icons })
        }),
        regenerateEnums: () => api.request('/regenerate', { method: 'POST' }),
        getIconSvg: (category, icon) => api.request(`/packs/${encodeURIComponent(category)}/icons/${encodeURIComponent(icon)}`),

        async uploadIcons(category, files) {
            const formData = new FormData();
            files.forEach(file => formData.append('icons[]', file.blob, file.name));

            const response = await fetch(`${apiUrl}/packs/${encodeURIComponent(category)}/icons`, {
                method: 'POST',
                headers: { 'X-WP-Nonce': nonce },
                credentials: 'same-origin',
                body: formData,
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Upload failed');
            return data;
        },

        async exportCategory(category) {
            const response = await fetch(`${apiUrl}/packs/${encodeURIComponent(category)}/export`, {
                headers: {
                    'X-WP-Nonce': nonce,
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.error?.message || 'Export failed');

            // Convert base64 to blob and download
            const binaryString = atob(result.data.content);
            const bytes = new Uint8Array(binaryString.length);
            for (let i = 0; i < binaryString.length; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            const blob = new Blob([bytes], { type: 'application/zip' });

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = result.data.filename || `${category}-icons.zip`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        },

        async importCategory(file) {
            const formData = new FormData();
            formData.append('file', file);

            const response = await fetch(`${apiUrl}/packs/import`, {
                method: 'POST',
                headers: { 'X-WP-Nonce': nonce },
                credentials: 'same-origin',
                body: formData,
            });

            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Import failed');
            return data;
        }
    };

    // ===========================================
    // Toast Notifications
    // ===========================================

    function toast(message, type = 'success', duration = 4000) {
        const container = document.getElementById('icon-manager-toast-container');
        if (!container) return;

        // Use is- prefix to avoid WordPress admin CSS conflicts
        const typeClass = type.startsWith('is-') ? type : `is-${type}`;

        const el = document.createElement('div');
        el.className = `icon-manager-toast ${typeClass}`;
        el.innerHTML = `
            <span class="icon-manager-toast-message">${message}</span>
            <button class="icon-manager-toast-close" onclick="this.parentElement.remove()">&times;</button>
        `;
        container.appendChild(el);

        if (duration > 0) {
            setTimeout(() => {
                el.classList.add('leaving');
                setTimeout(() => el.remove(), 200);
            }, duration);
        }
    }

    // ===========================================
    // Icon Grid
    // ===========================================

    async function loadIcons() {
        if (!activeCategory) return;

        const content = document.getElementById('icon-content');
        if (!content) return;

        content.innerHTML = '<div class="icon-manager-loading"><div class="spinner"></div><span>Loading icons...</span></div>';

        try {
            const response = await api.getIcons(activeCategory);
            allIcons = response.data || [];
            applyFilters();
        } catch (error) {
            content.innerHTML = `<div class="icon-manager-empty-grid"><span class="dashicons dashicons-warning"></span><p>${error.message}</p></div>`;
        }
    }

    function applyFilters() {
        // Filter by search
        filteredIcons = allIcons.filter(icon => {
            const name = icon.name.toLowerCase();
            return name.includes(searchQuery.toLowerCase());
        });

        // Sort
        filteredIcons.sort((a, b) => {
            switch (currentSort) {
                case 'name-asc': return a.name.localeCompare(b.name);
                case 'name-desc': return b.name.localeCompare(a.name);
                case 'size-asc': return (a.size || 0) - (b.size || 0);
                case 'size-desc': return (b.size || 0) - (a.size || 0);
                case 'date-asc': return new Date(a.modified || 0) - new Date(b.modified || 0);
                case 'date-desc': return new Date(b.modified || 0) - new Date(a.modified || 0);
                default: return 0;
            }
        });

        renderIconGrid();
    }

    function renderIconGrid() {
        const content = document.getElementById('icon-content');
        if (!content) return;

        if (filteredIcons.length === 0) {
            content.innerHTML = `
                <div class="icon-manager-no-icons">
                    <span class="dashicons dashicons-format-image"></span>
                    <p>${searchQuery ? 'No icons match your search' : 'No icons in this category yet'}</p>
                </div>
            `;
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'icon-manager-grid';

        filteredIcons.forEach(icon => {
            const item = document.createElement('div');
            item.className = `icon-manager-icon-card${selectedIcons.has(icon.name) ? ' selected' : ''}`;
            item.dataset.name = icon.name;

            item.innerHTML = `
                <input type="checkbox" class="icon-manager-icon-checkbox" ${selectedIcons.has(icon.name) ? 'checked' : ''}>
                <div class="icon-manager-icon-preview">${icon.svg || ''}</div>
                <div class="icon-manager-icon-name">${escapeHtml(icon.name.replace('.svg', ''))}</div>
            `;

            item.addEventListener('click', (e) => {
                if (e.target.type === 'checkbox') {
                    toggleSelection(icon.name, e.target.checked);
                } else {
                    showPopover(icon, item);
                }
            });

            grid.appendChild(item);
        });

        content.innerHTML = '';
        content.appendChild(grid);
        updateSelectionUI();
    }

    function toggleSelection(name, selected) {
        if (selected) {
            selectedIcons.add(name);
        } else {
            selectedIcons.delete(name);
        }

        const item = document.querySelector(`.icon-manager-icon-card[data-name="${name}"]`);
        if (item) {
            item.classList.toggle('selected', selected);
        }

        updateSelectionUI();
    }

    function updateSelectionUI() {
        const countEl = document.getElementById('selected-count');
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        const selectAllBtn = document.getElementById('select-all-btn');

        if (countEl) countEl.textContent = selectedIcons.size;
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = selectedIcons.size === 0;
        if (selectAllBtn) {
            selectAllBtn.textContent = selectedIcons.size === filteredIcons.length && filteredIcons.length > 0 ? 'Deselect All' : 'Select All';
        }
    }

    // ===========================================
    // Popover (replaces modal)
    // ===========================================

    let currentPopoverIcon = null;

    function showPopover(icon, targetElement) {
        const popover = document.getElementById('icon-popover');
        if (!popover) return;

        currentPopoverIcon = icon;

        // Update popover content
        popover.querySelector('.popover-title').textContent = icon.name.replace('.svg', '');
        popover.querySelector('.popover-icon-large').innerHTML = icon.svg || '';

        // Reset controls
        document.getElementById('popover-size').value = 24;
        document.getElementById('popover-color').value = '#000000';

        // Update code preview
        updateCodePreview('php');

        // Position popover
        const rect = targetElement.getBoundingClientRect();

        let left = rect.right + 10;
        let top = rect.top;

        // Adjust if off-screen
        if (left + 320 > window.innerWidth) {
            left = rect.left - 330;
        }
        if (left < 10) {
            left = 10;
        }
        if (top + 450 > window.innerHeight) {
            top = window.innerHeight - 460;
        }

        popover.style.left = `${left}px`;
        popover.style.top = `${Math.max(10, top)}px`;
        popover.style.display = 'block';

        // Mark active format button
        popover.querySelectorAll('.copy-btn').forEach(btn => btn.classList.remove('active'));
        popover.querySelector('.copy-btn[data-format="php"]').classList.add('active');

        // Close on outside click
        setTimeout(() => {
            document.addEventListener('click', closePopoverOnOutsideClick);
        }, 10);
    }

    function closePopover() {
        const popover = document.getElementById('icon-popover');
        if (popover) popover.style.display = 'none';
        currentPopoverIcon = null;
        document.removeEventListener('click', closePopoverOnOutsideClick);
    }

    function closePopoverOnOutsideClick(e) {
        const popover = document.getElementById('icon-popover');
        if (popover && !popover.contains(e.target) && !e.target.closest('.icon-item')) {
            closePopover();
        }
    }

    function updateCodePreview(format) {
        if (!currentPopoverIcon) return;

        const size = document.getElementById('popover-size').value;
        const color = document.getElementById('popover-color').value;
        const iconName = currentPopoverIcon.name.replace('.svg', '');
        const enumName = toPascalCase(activeCategory);

        let code = '';
        switch (format) {
            case 'php':
                code = `Icon(${enumName}::${toPascalCase(iconName)})->size(${size})->color('${color}')->render();`;
                break;
            case 'html':
                code = currentPopoverIcon.svg || '';
                break;
            case 'shortcode':
                code = `[icon category="${activeCategory}" name="${iconName}" size="${size}" color="${color}"]`;
                break;
            case 'css':
                const base64 = btoa(unescape(encodeURIComponent(currentPopoverIcon.svg || '')));
                code = `background-image: url("data:image/svg+xml;base64,${base64}");`;
                break;
        }

        document.getElementById('popover-code').textContent = code;
    }

    async function copyToClipboard() {
        const code = document.getElementById('popover-code').textContent;
        try {
            await navigator.clipboard.writeText(code);
            toast(i18n?.copied || 'Copied to clipboard!', 'success', 2000);
        } catch (error) {
            // Fallback
            const textarea = document.createElement('textarea');
            textarea.value = code;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            toast(i18n?.copied || 'Copied to clipboard!', 'success', 2000);
        }
    }

    // ===========================================
    // Upload with Preview
    // ===========================================

    function handleFiles(files) {
        pendingUploads = [];
        const previewGrid = document.getElementById('upload-preview-grid');
        if (!previewGrid) return;

        previewGrid.innerHTML = '';

        Array.from(files).forEach((file, index) => {
            if (file.type !== 'image/svg+xml' && !file.name.endsWith('.svg')) {
                toast(`Skipped ${file.name}: not an SVG file`, 'is-warning');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const svgContent = e.target.result;
                const baseName = file.name.replace('.svg', '');

                pendingUploads.push({
                    originalName: file.name,
                    name: baseName + '.svg',
                    svg: svgContent,
                    blob: file
                });

                const item = document.createElement('div');
                item.className = 'upload-preview-item';
                item.dataset.index = pendingUploads.length - 1;
                item.innerHTML = `
                    <div class="preview-icon">${svgContent}</div>
                    <input type="text" class="preview-name" value="${escapeHtml(baseName)}" data-index="${pendingUploads.length - 1}">
                    <button class="preview-remove" data-index="${pendingUploads.length - 1}">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                `;
                previewGrid.appendChild(item);
            };
            reader.readAsText(file);
        });

        // Show preview panel
        setTimeout(() => {
            if (pendingUploads.length > 0) {
                document.getElementById('upload-area').style.display = 'none';
                document.getElementById('upload-preview').style.display = 'block';
            }
        }, 100);
    }

    async function confirmUpload() {
        if (pendingUploads.length === 0) return;

        const uploadBtn = document.getElementById('upload-confirm-btn');
        if (uploadBtn) {
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';
        }

        // Update names from inputs
        document.querySelectorAll('.preview-name').forEach(input => {
            const index = parseInt(input.dataset.index);
            if (pendingUploads[index]) {
                pendingUploads[index].name = input.value.trim() + '.svg';
            }
        });

        // Filter out removed items
        const uploads = pendingUploads.filter(u => u !== null);

        try {
            await api.uploadIcons(activeCategory, uploads);
            toast(i18n?.uploadSuccess || 'Icons uploaded successfully!', 'success');
            cancelUpload();
            loadIcons();
            await api.regenerateEnums();
        } catch (error) {
            toast(error.message, 'is-error');
        } finally {
            if (uploadBtn) {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Upload All';
            }
        }
    }

    function cancelUpload() {
        pendingUploads = [];
        const uploadPreview = document.getElementById('upload-preview');
        const uploadArea = document.getElementById('upload-area');
        const previewGrid = document.getElementById('upload-preview-grid');

        if (uploadPreview) uploadPreview.style.display = 'none';
        if (uploadArea) uploadArea.style.display = 'flex';
        if (previewGrid) previewGrid.innerHTML = '';
    }

    // ===========================================
    // Category Management
    // ===========================================

    function showNewCategoryModal() {
        const modal = document.getElementById('new-category-modal');
        if (modal) {
            modal.style.display = 'flex';
            const input = document.getElementById('new-category-name');
            if (input) {
                input.value = '';
                input.focus();
            }
        }
    }

    function closeModal(modalId) {
        const modal = typeof modalId === 'string' ? document.getElementById(modalId) : modalId;
        if (modal) modal.style.display = 'none';
    }

    async function createCategory() {
        const input = document.getElementById('new-category-name');
        const name = input?.value.trim();

        if (!name || !/^[a-zA-Z0-9_-]+$/.test(name)) {
            toast('Invalid category name. Use only letters, numbers, hyphens, and underscores.', 'is-error');
            return;
        }

        try {
            await api.createCategory(name);
            toast(i18n?.categoryCreated || 'Category created!', 'success');
            closeModal('new-category-modal');
            window.location.href = `?page=icon-manager&category=${encodeURIComponent(name)}`;
        } catch (error) {
            toast(error.message, 'is-error');
        }
    }

    async function deleteCategory() {
        if (!activeCategory) return;
        if (!confirm(i18n?.confirmDeleteCategory || 'Are you sure you want to delete this category and all its icons?')) return;

        try {
            await api.deleteCategory(activeCategory);
            toast(i18n?.categoryDeleted || 'Category deleted!', 'success');
            window.location.href = '?page=icon-manager';
        } catch (error) {
            toast(error.message, 'is-error');
        }
    }

    async function bulkDeleteIcons() {
        if (selectedIcons.size === 0) return;
        if (!confirm(i18n?.confirmBulkDelete || 'Are you sure you want to delete the selected icons?')) return;

        try {
            await api.deleteIcons(activeCategory, Array.from(selectedIcons));
            toast(i18n?.iconsDeleted || 'Icons deleted!', 'success');
            selectedIcons.clear();
            loadIcons();
            await api.regenerateEnums();
        } catch (error) {
            toast(error.message, 'is-error');
        }
    }

    async function deleteIconFromPopover() {
        if (!currentPopoverIcon) return;
        if (!confirm(i18n?.confirmDelete || 'Are you sure you want to delete this icon?')) return;

        try {
            await api.deleteIcons(activeCategory, [currentPopoverIcon.name]);
            toast('Icon deleted', 'success');
            closePopover();
            loadIcons();
            await api.regenerateEnums();
        } catch (error) {
            toast(error.message, 'is-error');
        }
    }

    // ===========================================
    // Export / Import
    // ===========================================

    async function exportCategory() {
        if (!activeCategory) return;

        try {
            toast(i18n?.exportStarted || 'Export started...', 'info');
            await api.exportCategory(activeCategory);
        } catch (error) {
            toast(error.message, 'is-error');
        }
    }

    function showImportModal() {
        const modal = document.getElementById('import-modal');
        if (modal) modal.style.display = 'flex';
    }

    async function handleImportFile(file) {
        if (!file.name.endsWith('.zip')) {
            toast('Please select a ZIP file', 'is-error');
            return;
        }

        const progress = document.getElementById('import-progress');
        const dropZone = document.getElementById('import-drop-zone');

        if (dropZone) dropZone.style.display = 'none';
        if (progress) progress.style.display = 'block';

        try {
            await api.importCategory(file);
            toast(i18n?.importSuccess || 'Import successful!', 'success');
            closeModal('import-modal');
            window.location.reload();
        } catch (error) {
            toast(error.message, 'is-error');
            if (dropZone) dropZone.style.display = 'flex';
            if (progress) progress.style.display = 'none';
        }
    }

    // ===========================================
    // Search & Filters
    // ===========================================

    function initSearch() {
        const searchInput = document.getElementById('icon-search');
        const searchClear = document.getElementById('search-clear');
        const sortSelect = document.getElementById('sort-select');

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                searchQuery = e.target.value;
                if (searchClear) searchClear.style.display = searchQuery ? 'block' : 'none';
                applyFilters();
            });
        }

        if (searchClear) {
            searchClear.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                searchQuery = '';
                searchClear.style.display = 'none';
                applyFilters();
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                currentSort = e.target.value;
                applyFilters();
            });
        }
    }

    // ===========================================
    // Keyboard Shortcuts
    // ===========================================

    function initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Escape to close popover/modal
            if (e.key === 'Escape') {
                closePopover();
                document.querySelectorAll('.icon-manager-modal').forEach(m => m.style.display = 'none');
            }

            // Ctrl+C to copy when popover is open
            if ((e.ctrlKey || e.metaKey) && e.key === 'c' && currentPopoverIcon) {
                e.preventDefault();
                copyToClipboard();
            }

            // Ctrl+A to select all icons (when not in input)
            if ((e.ctrlKey || e.metaKey) && e.key === 'a' && !e.target.matches('input, textarea')) {
                e.preventDefault();
                selectAllIcons();
            }

            // Delete key for selected icons
            if (e.key === 'Delete' && selectedIcons.size > 0 && !e.target.matches('input, textarea')) {
                e.preventDefault();
                bulkDeleteIcons();
            }
        });
    }

    function selectAllIcons() {
        if (selectedIcons.size === filteredIcons.length) {
            selectedIcons.clear();
        } else {
            filteredIcons.forEach(icon => selectedIcons.add(icon.name));
        }
        renderIconGrid();
    }

    // ===========================================
    // Utilities
    // ===========================================

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function toPascalCase(str) {
        return str
            .replace(/[-_](.)/g, (_, c) => c.toUpperCase())
            .replace(/^(.)/, (_, c) => c.toUpperCase());
    }

    // ===========================================
    // Initialization
    // ===========================================

    function init() {
        // Get active category from tabs
        const activeTab = document.querySelector('.icon-manager-tab.active[data-category]');
        if (activeTab) {
            activeCategory = activeTab.dataset.category;
        }

        // Category tabs
        document.querySelectorAll('.icon-manager-tab[data-category]').forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const category = tab.dataset.category;
                window.location.href = `?page=icon-manager&category=${encodeURIComponent(category)}`;
            });
        });

        // New category buttons
        document.getElementById('new-category-btn')?.addEventListener('click', showNewCategoryModal);
        document.getElementById('new-category-btn-empty')?.addEventListener('click', showNewCategoryModal);
        document.getElementById('create-category-btn')?.addEventListener('click', createCategory);

        // Delete category
        document.getElementById('delete-category-btn')?.addEventListener('click', deleteCategory);

        // Bulk delete
        document.getElementById('bulk-delete-btn')?.addEventListener('click', bulkDeleteIcons);

        // Select all
        document.getElementById('select-all-btn')?.addEventListener('click', selectAllIcons);

        // Regenerate enums
        document.getElementById('regenerate-btn')?.addEventListener('click', async () => {
            try {
                await api.regenerateEnums();
                toast(i18n?.enumsRegenerated || 'Enums regenerated!', 'success');
            } catch (error) {
                toast(error.message, 'is-error');
            }
        });

        // Export/Import
        document.getElementById('export-btn')?.addEventListener('click', exportCategory);
        document.getElementById('import-btn')?.addEventListener('click', showImportModal);

        // Import file handling
        const importInput = document.getElementById('import-file-input');
        const importDropZone = document.getElementById('import-drop-zone');

        if (importInput) {
            importInput.addEventListener('change', (e) => {
                if (e.target.files[0]) handleImportFile(e.target.files[0]);
            });
        }

        if (importDropZone) {
            importDropZone.addEventListener('click', () => importInput?.click());
            importDropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                importDropZone.classList.add('dragover');
            });
            importDropZone.addEventListener('dragleave', () => {
                importDropZone.classList.remove('dragover');
            });
            importDropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                importDropZone.classList.remove('dragover');
                if (e.dataTransfer.files[0]) handleImportFile(e.dataTransfer.files[0]);
            });
        }

        // Upload area
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('icon-file-input');

        if (uploadArea && fileInput) {
            uploadArea.addEventListener('click', () => fileInput.click());
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                handleFiles(e.dataTransfer.files);
            });
            fileInput.addEventListener('change', (e) => {
                handleFiles(e.target.files);
                fileInput.value = '';
            });
        }

        // Upload preview
        document.getElementById('upload-confirm-btn')?.addEventListener('click', confirmUpload);
        document.getElementById('upload-cancel-btn')?.addEventListener('click', cancelUpload);

        // Remove from preview
        document.getElementById('upload-preview-grid')?.addEventListener('click', (e) => {
            if (e.target.closest('.preview-remove')) {
                const index = parseInt(e.target.closest('.preview-remove').dataset.index);
                pendingUploads[index] = null;
                e.target.closest('.upload-preview-item')?.remove();
                if (pendingUploads.filter(u => u !== null).length === 0) {
                    cancelUpload();
                }
            }
        });

        // Popover
        const popover = document.getElementById('icon-popover');
        if (popover) {
            popover.querySelector('.popover-close')?.addEventListener('click', closePopover);

            popover.querySelectorAll('.copy-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    popover.querySelectorAll('.copy-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    updateCodePreview(btn.dataset.format);
                });
            });

            document.getElementById('popover-size')?.addEventListener('input', () => {
                const activeFormat = popover.querySelector('.copy-btn.active')?.dataset.format || 'php';
                updateCodePreview(activeFormat);
            });

            document.getElementById('popover-color')?.addEventListener('input', () => {
                const activeFormat = popover.querySelector('.copy-btn.active')?.dataset.format || 'php';
                updateCodePreview(activeFormat);
            });

            document.getElementById('popover-copy-btn')?.addEventListener('click', copyToClipboard);
            document.getElementById('popover-delete-btn')?.addEventListener('click', deleteIconFromPopover);
        }

        // Modal close buttons
        document.querySelectorAll('.modal-close, .modal-cancel, .modal-backdrop').forEach(el => {
            el.addEventListener('click', (e) => {
                if (e.target === el) {
                    const modal = el.closest('.icon-manager-modal');
                    if (modal) modal.style.display = 'none';
                }
            });
        });

        // Enter key in new category input
        document.getElementById('new-category-name')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') createCategory();
        });

        // Init search
        initSearch();

        // Init keyboard shortcuts
        initKeyboardShortcuts();

        // Load icons
        if (activeCategory) {
            loadIcons();
        }
    }

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
