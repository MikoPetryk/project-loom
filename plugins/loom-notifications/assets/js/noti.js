/**
 * Noti - Modern Notification System
 *
 * Toast notifications with icon, action, and progress support.
 *
 * @package Loom\Noti
 * @since 1.0.0
 */

(function(window) {
    'use strict';

    const settings = window.NotiSettings || {};
    const TYPES = { Success: 'success', Info: 'info', Warning: 'warning', Error: 'error', Log: 'log' };

    let container = null;
    let loomContainer = null;
    const progressToasts = new Map();

    // Check if Loom Core Snackbar is available
    function hasLoomSnackbar() {
        return typeof window.LoomSnackbar !== 'undefined' ||
               document.documentElement.style.getPropertyValue('--loom-primary') ||
               document.getElementById('loom-snackbar-container');
    }

    function ensureLoomContainer() {
        if (loomContainer) return loomContainer;
        loomContainer = document.getElementById('loom-snackbar-container');
        if (!loomContainer) {
            loomContainer = document.createElement('div');
            loomContainer.id = 'loom-snackbar-container';
            loomContainer.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:999999;display:flex;flex-direction:column;gap:8px;pointer-events:none;';
            document.body.appendChild(loomContainer);
        }
        return loomContainer;
    }

    function ensureContainer() {
        if (container) return container;
        container = document.createElement('div');
        container.className = 'noti-container ' + (settings.position || 'top-right');
        document.body.appendChild(container);
        return container;
    }

    function colorFor(type) {
        // Use CSS variables (with fallbacks for when Theme Manager isn't active)
        const colors = {
            success: 'var(--loom-success, #10b981)',
            info: 'var(--loom-info, #3b82f6)',
            warning: 'var(--loom-warning, #f59e0b)',
            error: 'var(--loom-error, #ef4444)',
            log: 'var(--loom-surface, #f8fafc)'
        };

        return colors[type] || colors.log;
    }

    function hideToast(toast) {
        if (!toast) return;
        if (toast._hideTimeout) {
            clearTimeout(toast._hideTimeout);
            toast._hideTimeout = null;
        }
        toast.classList.remove('show');
        toast.style.animation = 'loom-snackbar-out 0.2s ease forwards';
        setTimeout(() => {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
            if (toast.dataset.notiId) progressToasts.delete(toast.dataset.notiId);
        }, 220);
    }

    // Loom-style Snackbar rendering
    function showLoomSnackbar(type, message, opts = {}) {
        const cont = ensureLoomContainer();

        const colors = {
            success: { bg: 'var(--loom-success, #10b981)', text: '#fff' },
            error: { bg: 'var(--loom-error, #ef4444)', text: '#fff' },
            warning: { bg: 'var(--loom-warning, #f59e0b)', text: '#000' },
            info: { bg: 'var(--loom-info, #3b82f6)', text: '#fff' },
            log: { bg: 'var(--loom-surface, #f8fafc)', text: 'var(--loom-text, #1a1a1a)' }
        };
        const c = colors[type] || { bg: 'var(--loom-inverse-surface, #323232)', text: '#fff' };

        const snackbar = document.createElement('div');
        snackbar.className = 'loom-snackbar';
        snackbar.style.cssText = `display:flex;align-items:center;gap:12px;padding:14px 16px;background:${c.bg};color:${c.text};border-radius:var(--loom-rounded-md, 8px);box-shadow:0 3px 5px -1px rgba(0,0,0,0.2),0 6px 10px 0 rgba(0,0,0,0.14),0 1px 18px 0 rgba(0,0,0,0.12);animation:loom-snackbar-in 0.2s ease;pointer-events:auto;max-width:400px;min-width:280px;font-family:var(--loom-font-body, system-ui, sans-serif);font-size:14px;`;
        snackbar.setAttribute('role', 'alert');

        if (opts.id) snackbar.dataset.notiId = opts.id;

        // Message
        const msg = document.createElement('span');
        msg.className = 'loom-snackbar-message';
        msg.style.cssText = 'flex:1;line-height:1.5;';
        msg.textContent = message;
        snackbar.appendChild(msg);

        // Action button
        if (opts.action) {
            const actionBtn = document.createElement('button');
            actionBtn.type = 'button';
            actionBtn.style.cssText = 'background:transparent;border:none;color:inherit;font-size:14px;font-weight:500;cursor:pointer;padding:6px 8px;margin:-6px -8px;text-transform:uppercase;letter-spacing:0.5px;border-radius:4px;opacity:0.95;transition:background 0.15s;';
            actionBtn.textContent = opts.action;
            actionBtn.onmouseover = function() { this.style.background = 'rgba(255,255,255,0.15)'; };
            actionBtn.onmouseout = function() { this.style.background = 'transparent'; };
            if (opts.onAction && typeof opts.onAction === 'function') {
                actionBtn.onclick = () => { opts.onAction(); hideToast(snackbar); };
            }
            snackbar.appendChild(actionBtn);
        }

        // Close button
        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.style.cssText = 'background:transparent;border:none;color:inherit;cursor:pointer;padding:4px;margin:-4px;margin-left:4px;opacity:0.7;display:flex;transition:opacity 0.15s;';
        closeBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
        closeBtn.onmouseover = function() { this.style.opacity = '1'; };
        closeBtn.onmouseout = function() { this.style.opacity = '0.7'; };
        closeBtn.onclick = () => hideToast(snackbar);
        snackbar.appendChild(closeBtn);

        cont.appendChild(snackbar);

        if (opts.id && (type === 'progress' || opts.progress)) {
            progressToasts.set(opts.id, snackbar);
        }

        const duration = opts.duration || 5000;
        if (duration > 0 && type !== 'progress') {
            snackbar._hideTimeout = setTimeout(() => hideToast(snackbar), duration);
        }

        return {
            element: snackbar,
            close: () => hideToast(snackbar),
            update: (data) => updateProgress(opts.id, data)
        };
    }

    // Legacy toast rendering (fallback)
    function showLegacyToast(type, message, opts = {}) {
        const cont = ensureContainer();
        const toast = window.NotiToastBuilder.create(type, message, opts, colorFor, hideToast);

        if (cont.className.indexOf('bottom') !== -1) {
            cont.appendChild(toast);
        } else {
            cont.insertBefore(toast, cont.firstChild);
        }

        requestAnimationFrame(() => toast.classList.add('show'));

        if (opts.id && (type === 'progress' || opts.progress)) {
            progressToasts.set(opts.id, toast);
        }

        const autohide = opts.duration || opts.autohide || (type === 'progress' ? 0 : settings.autohide) || 5000;
        if (autohide > 0) {
            toast._hideTimeout = setTimeout(() => hideToast(toast), autohide);
        }

        return {
            element: toast,
            close: () => hideToast(toast),
            update: (data) => updateProgress(opts.id, data)
        };
    }

    function showToast(type, message, opts = {}) {
        // Always use Loom Snackbar style (unified design)
        // Set useLoomStyle: false in NotiSettings to use legacy style
        if (settings.useLoomStyle === false) {
            return showLegacyToast(type, message, opts);
        }
        return showLoomSnackbar(type, message, opts);
    }

    function updateProgress(id, data) {
        const toast = progressToasts.get(id);
        if (!toast) return;
        if (data.message) {
            const msg = toast.querySelector('.noti-msg');
            if (msg) msg.textContent = data.message;
        }
        if (data.current !== undefined && data.total !== undefined) {
            const fill = toast.querySelector('.noti-progress-fill');
            if (fill) fill.style.width = (data.current / data.total * 100) + '%';
        }
    }

    function completeProgress(id, message) {
        const toast = progressToasts.get(id);
        if (toast) hideToast(toast);
        if (message) showToast('success', message);
    }

    const Noti = {
        Types: TYPES,
        Success: (msg, opts) => showToast(TYPES.Success, msg, opts),
        Info: (msg, opts) => showToast(TYPES.Info, msg, opts),
        Warning: (msg, opts) => showToast(TYPES.Warning, msg, opts),
        Error: (msg, opts) => showToast(TYPES.Error, msg, opts),
        Log: (msg, opts) => showToast(TYPES.Log, msg, opts),
        success: (msg, opts) => showToast(TYPES.Success, msg, opts),
        info: (msg, opts) => showToast(TYPES.Info, msg, opts),
        warning: (msg, opts) => showToast(TYPES.Warning, msg, opts),
        error: (msg, opts) => showToast(TYPES.Error, msg, opts),
        log: (msg, opts) => showToast(TYPES.Log, msg, opts),

        progress: (msg, opts = {}) => {
            opts.id = opts.id || 'progress_' + Date.now();
            return showToast('progress', msg, { ...opts, progress: true });
        },

        show: (type, message, opts) => {
            const t = (typeof type === 'string') ? type.toLowerCase() : 'log';
            return showToast(t, message, opts);
        },

        showQueued: (notifications) => {
            if (!Array.isArray(notifications)) return;
            notifications.forEach(n => {
                if (n.type === 'progress_update') updateProgress(n.id, n);
                else if (n.type === 'progress_complete') completeProgress(n.id, n.message);
                else if (n.type === 'progress_fail') {
                    const toast = progressToasts.get(n.id);
                    if (toast) hideToast(toast);
                    showToast('error', n.message);
                } else {
                    showToast(n.type, n.message, n.options || {});
                }
            });
        },

        setOptions: (opts) => {
            if (!opts) return;
            Object.assign(settings, opts);
            if (container && opts.position) {
                container.className = 'noti-container ' + opts.position;
            }
        }
    };

    window.Noti = Noti;

})(window);
