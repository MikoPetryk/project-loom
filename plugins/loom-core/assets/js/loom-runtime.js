/**
 * Loom Runtime
 *
 * Client-side runtime for Loom Core. Handles:
 * - State management and hydration
 * - Action execution (client and server)
 * - Real-time updates via SSE
 * - UI bindings and reactivity
 */

(function() {
    'use strict';

    // ═══════════════════════════════════════════════════════════════════════
    // CONFIGURATION
    // ═══════════════════════════════════════════════════════════════════════

    const config = window.LoomConfig || {
        stateUrl: '/loom/state/',
        eventsUrl: '/loom/events/',
        nonce: '',
        session: '',
        debug: false
    };

    function log(...args) {
        if (config.debug) {
            console.log('[Loom]', ...args);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // STATE MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════

    const LoomState = {
        _states: {},
        _actions: {},
        _listeners: {},
        _pendingActions: new Set(),

        /**
         * Hydrate state from server
         */
        hydrate() {
            const el = document.getElementById('loom-hydration');
            if (!el) return;

            try {
                const data = JSON.parse(el.textContent);
                this._states = data.states || {};
                this._actions = data.actions || {};
                log('State hydrated:', this._states);
            } catch (e) {
                console.error('[Loom] Hydration failed:', e);
            }
        },

        /**
         * Get state value
         */
        get(stateName, key = null) {
            const state = this._states[stateName];
            if (!state) return null;
            return key ? state[key] : state;
        },

        /**
         * Set state value (triggers update)
         */
        set(stateName, key, value) {
            if (!this._states[stateName]) {
                this._states[stateName] = {};
            }

            const oldValue = this._states[stateName][key];
            this._states[stateName][key] = value;

            this._notifyListeners(stateName, key, value, oldValue);
            this._updateBindings(stateName, key, value);
        },

        /**
         * Execute an action
         */
        async action(stateName, actionName, payload = {}) {
            const actionConfig = this._actions[stateName]?.[actionName];

            if (!actionConfig) {
                console.error(`[Loom] Action ${stateName}.${actionName} not found`);
                return;
            }

            // Client-side action
            if (actionConfig.mode === 'client') {
                return this._executeClientAction(stateName, actionName, payload);
            }

            // Confirmation required?
            if (actionConfig.confirm) {
                if (!confirm(actionConfig.confirm)) {
                    return;
                }
            }

            // Debounce?
            const actionKey = `${stateName}.${actionName}`;
            if (actionConfig.debounce) {
                return this._debounce(actionKey, () => {
                    return this._executeServerAction(stateName, actionName, payload);
                }, actionConfig.debounce);
            }

            return this._executeServerAction(stateName, actionName, payload);
        },

        /**
         * Execute client-side action
         */
        _executeClientAction(stateName, actionName, payload) {
            // Client actions are pre-defined JavaScript functions
            const fn = window[`loom_${stateName}_${actionName}`];
            if (typeof fn === 'function') {
                return fn(this._states[stateName], payload);
            }
            log(`Client action ${stateName}.${actionName} not implemented`);
        },

        /**
         * Execute server-side action
         */
        async _executeServerAction(stateName, actionName, payload) {
            const actionKey = `${stateName}.${actionName}`;
            this._pendingActions.add(actionKey);
            this._updatePendingUI(actionKey, true);

            try {
                const response = await fetch(config.stateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Loom-Nonce': config.nonce,
                        'X-Loom-Session': config.session
                    },
                    body: JSON.stringify({
                        state: stateName,
                        action: actionName,
                        payload: payload
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Update local state
                    this._states[stateName] = result.state;
                    this._updateAllBindings(stateName, result.state);
                    log(`Action ${actionKey} succeeded:`, result);
                    return result.result;
                } else {
                    console.error(`[Loom] Action failed:`, result.error);
                    throw new Error(result.error);
                }
            } finally {
                this._pendingActions.delete(actionKey);
                this._updatePendingUI(actionKey, false);
            }
        },

        /**
         * Debounce helper
         */
        _debounceTimers: {},
        _debounce(key, fn, delay) {
            if (this._debounceTimers[key]) {
                clearTimeout(this._debounceTimers[key]);
            }
            return new Promise((resolve) => {
                this._debounceTimers[key] = setTimeout(async () => {
                    resolve(await fn());
                }, delay);
            });
        },

        /**
         * Check if action is pending
         */
        isPending(actionKey) {
            return this._pendingActions.has(actionKey);
        },

        /**
         * Subscribe to state changes
         */
        subscribe(stateName, callback) {
            if (!this._listeners[stateName]) {
                this._listeners[stateName] = [];
            }
            this._listeners[stateName].push(callback);

            // Return unsubscribe function
            return () => {
                const idx = this._listeners[stateName].indexOf(callback);
                if (idx > -1) {
                    this._listeners[stateName].splice(idx, 1);
                }
            };
        },

        /**
         * Notify listeners of state change
         */
        _notifyListeners(stateName, key, value, oldValue) {
            const listeners = this._listeners[stateName] || [];
            listeners.forEach(fn => fn(key, value, oldValue, this._states[stateName]));
        },

        /**
         * Update UI bindings for a specific key
         */
        _updateBindings(stateName, key, value) {
            const selector = `[data-loom-bind="${stateName}.${key}"]`;
            document.querySelectorAll(selector).forEach(el => {
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    el.value = value;
                } else {
                    el.textContent = value;
                }
            });
        },

        /**
         * Update all bindings for a state
         */
        _updateAllBindings(stateName, state) {
            Object.entries(state).forEach(([key, value]) => {
                this._updateBindings(stateName, key, value);
            });
        },

        /**
         * Update pending UI state
         */
        _updatePendingUI(actionKey, isPending) {
            const selector = `[data-loom-loading="${actionKey}"]`;
            document.querySelectorAll(selector).forEach(el => {
                el.classList.toggle('loom-loading', isPending);
                if (el.tagName === 'BUTTON') {
                    el.disabled = isPending;
                }
            });
        }
    };

    // ═══════════════════════════════════════════════════════════════════════
    // REAL-TIME UPDATES (SSE)
    // ═══════════════════════════════════════════════════════════════════════

    const LoomRealtime = {
        _eventSource: null,
        _reconnectTimeout: null,
        _reconnectDelay: 1000,

        /**
         * Connect to SSE endpoint
         */
        connect(channels = ['state', 'products']) {
            if (this._eventSource) {
                this._eventSource.close();
            }

            const url = `${config.eventsUrl}?channels=${channels.join(',')}`;
            this._eventSource = new EventSource(url);

            this._eventSource.onopen = () => {
                log('SSE connected');
                this._reconnectDelay = 1000;
            };

            this._eventSource.onerror = () => {
                log('SSE error, reconnecting...');
                this._eventSource.close();
                this._scheduleReconnect(channels);
            };

            // Handle state updates
            this._eventSource.addEventListener('state.updated', (e) => {
                const data = JSON.parse(e.data);
                LoomState._states[data.state] = data.data;
                LoomState._updateAllBindings(data.state, data.data);
                log('State updated via SSE:', data);
            });

            // Handle product updates
            this._eventSource.addEventListener('products.updated', (e) => {
                const data = JSON.parse(e.data);
                this._handleProductUpdate(data);
            });

            this._eventSource.addEventListener('products.created', (e) => {
                const data = JSON.parse(e.data);
                this._handleProductCreated(data);
            });

            // Heartbeat
            this._eventSource.addEventListener('heartbeat', () => {
                log('SSE heartbeat');
            });
        },

        /**
         * Schedule reconnection
         */
        _scheduleReconnect(channels) {
            if (this._reconnectTimeout) {
                clearTimeout(this._reconnectTimeout);
            }

            this._reconnectTimeout = setTimeout(() => {
                this.connect(channels);
            }, this._reconnectDelay);

            // Exponential backoff (max 30 seconds)
            this._reconnectDelay = Math.min(this._reconnectDelay * 2, 30000);
        },

        /**
         * Handle product update
         */
        _handleProductUpdate(data) {
            const elements = document.querySelectorAll(`[data-loom-product="${data.id}"]`);

            elements.forEach(el => {
                // Update bound fields
                el.querySelectorAll('[data-loom-bind]').forEach(binding => {
                    const field = binding.dataset.loomBind;
                    if (data[field] !== undefined) {
                        binding.textContent = data[field];
                    }
                });

                // Full re-render if needed
                if (el.dataset.loomRerender === 'true') {
                    this._refreshElement(el);
                }
            });

            log('Product updated:', data);
        },

        /**
         * Handle product created
         */
        _handleProductCreated(data) {
            document.querySelectorAll('[data-loom-list="products"]').forEach(el => {
                this._refreshElement(el);
            });
            log('Product created:', data);
        },

        /**
         * Refresh element via AJAX
         */
        async _refreshElement(el) {
            const refreshUrl = el.dataset.loomRefreshUrl;
            if (!refreshUrl) return;

            try {
                const response = await fetch(refreshUrl);
                const html = await response.text();
                el.outerHTML = html;
            } catch (e) {
                console.error('[Loom] Refresh failed:', e);
            }
        },

        /**
         * Disconnect SSE
         */
        disconnect() {
            if (this._eventSource) {
                this._eventSource.close();
                this._eventSource = null;
            }
            if (this._reconnectTimeout) {
                clearTimeout(this._reconnectTimeout);
            }
        }
    };

    // ═══════════════════════════════════════════════════════════════════════
    // ACTION HANDLERS
    // ═══════════════════════════════════════════════════════════════════════

    const LoomActions = {
        /**
         * Handle click actions
         */
        init() {
            document.addEventListener('click', (e) => {
                const actionEl = e.target.closest('[data-loom-action]');
                if (!actionEl) return;

                e.preventDefault();

                const [stateName, actionName] = actionEl.dataset.loomAction.split('.');
                const payload = actionEl.dataset.loomPayload
                    ? JSON.parse(actionEl.dataset.loomPayload)
                    : {};

                LoomState.action(stateName, actionName, payload);
            });
        },

        /**
         * Navigate to URL
         */
        navigate(url) {
            window.location.href = url;
        },

        /**
         * Toggle element visibility
         */
        toggle(elementId) {
            const el = document.getElementById(elementId);
            if (el) {
                el.hidden = !el.hidden;
            }
        },

        /**
         * Show modal
         */
        showModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('loom-modal-open');
                modal.hidden = false;
            }
        },

        /**
         * Hide modal
         */
        hideModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('loom-modal-open');
                modal.hidden = true;
            }
        }
    };

    // ═══════════════════════════════════════════════════════════════════════
    // INITIALIZATION
    // ═══════════════════════════════════════════════════════════════════════

    // Expose globals
    window.LoomState = LoomState;
    window.LoomRealtime = LoomRealtime;
    window.LoomActions = LoomActions;
    window.Loom = {
        state: LoomState,
        realtime: LoomRealtime,
        actions: LoomActions
    };

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', () => {
        LoomState.hydrate();
        LoomActions.init();

        // Auto-connect SSE if not disabled
        if (!config.disableSSE) {
            LoomRealtime.connect();
        }

        log('Loom runtime initialized');
    });

})();
