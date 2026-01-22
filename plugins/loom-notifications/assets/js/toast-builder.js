/**
 * Toast Builder - DOM creation for notifications
 *
 * @package Loom\Noti
 * @since 1.0.0
 */

(function(window) {
    'use strict';

    const DEFAULT_ICONS = {
        success: '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>',
        error: '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>',
        warning: '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
        info: '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>',
        log: '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/></svg>'
    };

    function getDefaultIcon(type) {
        return DEFAULT_ICONS[type] || DEFAULT_ICONS.info;
    }

    function createIcon(opts, type) {
        const iconEl = document.createElement('span');
        iconEl.className = 'noti-icon';

        if (opts.icon && opts.icon.startsWith('<svg')) {
            iconEl.innerHTML = opts.icon;
        } else {
            iconEl.innerHTML = getDefaultIcon(type);
        }
        return iconEl;
    }

    function createMessage(message, opts) {
        const msg = document.createElement('div');
        msg.className = 'noti-msg';
        if (opts.html) {
            msg.innerHTML = opts.html;
        } else {
            msg.textContent = message;
        }
        return msg;
    }

    function createProgressBar(opts) {
        const progressBar = document.createElement('div');
        progressBar.className = 'noti-progress-bar';
        const progressFill = document.createElement('div');
        progressFill.className = 'noti-progress-fill';
        progressFill.style.width = ((opts.current || 0) / (opts.total || 100) * 100) + '%';
        progressBar.appendChild(progressFill);
        return progressBar;
    }

    function createActions(opts, hideCallback) {
        const actionsEl = document.createElement('div');
        actionsEl.className = 'noti-actions';

        opts.actions.forEach(action => {
            const btn = document.createElement('button');
            btn.className = 'noti-action-btn';
            btn.textContent = action.label || 'Action';

            if (action.url) {
                btn.addEventListener('click', () => {
                    window.location.href = action.url;
                });
            } else if (action.callback && typeof action.callback === 'function') {
                btn.addEventListener('click', () => {
                    action.callback();
                    hideCallback();
                });
            }

            actionsEl.appendChild(btn);
        });

        return actionsEl;
    }

    function createCloseButton(hideCallback) {
        const close = document.createElement('button');
        close.className = 'noti-close';
        close.innerHTML = '&times;';
        close.title = 'Close';
        close.addEventListener('click', (e) => {
            e.preventDefault();
            hideCallback();
        });
        return close;
    }

    function createToast(type, message, opts, colorFn, hideCallback) {
        const el = document.createElement('div');
        el.className = 'noti-toast noti-' + type;
        el.setAttribute('role', 'status');
        el.style.background = colorFn(type);

        // Warning needs dark text
        if (type === 'warning') {
            el.style.color = '#000';
        }

        if (opts.id) {
            el.dataset.notiId = opts.id;
        }

        if (opts.icon) {
            el.appendChild(createIcon(opts, type));
        }

        el.appendChild(createMessage(message, opts));

        if (type === 'progress' || opts.progress) {
            el.appendChild(createProgressBar(opts));
        }

        if (opts.actions && Array.isArray(opts.actions) && opts.actions.length > 0) {
            el.appendChild(createActions(opts, () => hideCallback(el)));
        }

        el.appendChild(createCloseButton(() => hideCallback(el)));

        return el;
    }

    window.NotiToastBuilder = {
        create: createToast,
        getDefaultIcon: getDefaultIcon
    };

})(window);
