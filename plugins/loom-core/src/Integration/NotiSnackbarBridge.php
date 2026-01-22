<?php
/**
 * Noti Snackbar Bridge
 *
 * Bridges Noti notification queue to Snackbar UI component.
 * Allows Noti::success() etc. to render using Loom Core Snackbar
 * for visual consistency.
 *
 * @package Loom\Core\Integration
 */

namespace Loom\Core\Integration;

/**
 * Bridge between Noti notifications and Snackbar UI component.
 */
class NotiSnackbarBridge {

    /** @var bool Whether the bridge is enabled */
    private static $enabled = false;

    /** @var bool Whether hooks have been modified */
    private static $hooksModified = false;

    /**
     * Enable the bridge (Noti will render via Snackbar)
     */
    public static function enable() {
        if (!PluginRegistry::hasNoti()) {
            return;
        }

        if (self::$enabled) {
            return;
        }

        self::$enabled = true;

        // Replace Noti's default render with Snackbar rendering
        if (!self::$hooksModified) {
            remove_action('wp_footer', [\Loom\Noti\Features\Notifications\Noti::class, 'renderQueue']);
            remove_action('admin_footer', [\Loom\Noti\Features\Notifications\Noti::class, 'renderQueue']);

            add_action('wp_footer', [self::class, 'renderQueue'], 99);
            add_action('admin_footer', [self::class, 'renderQueue'], 99);

            self::$hooksModified = true;
        }
    }

    /**
     * Disable the bridge (use default Noti rendering)
     */
    public static function disable() {
        if (!PluginRegistry::hasNoti()) {
            return;
        }

        if (!self::$enabled) {
            return;
        }

        self::$enabled = false;

        if (self::$hooksModified) {
            remove_action('wp_footer', [self::class, 'renderQueue'], 99);
            remove_action('admin_footer', [self::class, 'renderQueue'], 99);

            add_action('wp_footer', [\Loom\Noti\Features\Notifications\Noti::class, 'renderQueue']);
            add_action('admin_footer', [\Loom\Noti\Features\Notifications\Noti::class, 'renderQueue']);

            self::$hooksModified = false;
        }
    }

    /**
     * Check if bridge is enabled
     *
     * @return bool True if bridge is active
     */
    public static function isEnabled() {
        return self::$enabled;
    }

    /**
     * Render notification queue as Snackbar components
     */
    public static function renderQueue() {
        if (!PluginRegistry::hasNoti()) {
            return;
        }

        $notifications = \Loom\Noti\Features\Notifications\Noti::getQueue();

        if (empty($notifications)) {
            return;
        }

        \Loom\Noti\Features\Notifications\Noti::clearQueue();

        // Transform notifications to Snackbar-compatible format
        $transformed = array_map([self::class, 'transformNotification'], $notifications);
        $json = wp_json_encode($transformed);

        // Output container and JavaScript
        echo self::getContainerHtml();
        echo self::getSnackbarScript($json);
    }

    /**
     * Transform Noti notification to Snackbar-compatible format
     *
     * @param array $notification Noti notification data
     * @return array Snackbar-compatible data
     */
    private static function transformNotification($notification) {
        $options = $notification['options'] ?? [];

        return [
            'message' => $notification['message'] ?? '',
            'type' => $notification['type'] ?? 'info',
            'duration' => $options['duration'] ?? 5000,
            'action' => $options['action'] ?? null,
            'onAction' => $options['onAction'] ?? null,
            'position' => $options['position'] ?? 'bottom',
        ];
    }

    /**
     * Get Snackbar container HTML
     *
     * @return string Container HTML
     */
    private static function getContainerHtml() {
        return '<div id="loom-snackbar-container" style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:1100;display:flex;flex-direction:column;gap:8px;pointer-events:none;"></div>';
    }

    /**
     * Get JavaScript for showing Snackbars
     *
     * @param string $notificationsJson JSON encoded notifications
     * @return string Script HTML
     */
    private static function getSnackbarScript($notificationsJson) {
        return <<<HTML
<script>
(function() {
    'use strict';

    var LoomSnackbar = {
        container: null,
        queue: [],
        isShowing: false,

        init: function() {
            this.container = document.getElementById('loom-snackbar-container');
        },

        showQueued: function(notifications) {
            this.queue = notifications;
            this.showNext();
        },

        showNext: function() {
            if (this.isShowing || this.queue.length === 0) return;

            this.isShowing = true;
            var notification = this.queue.shift();
            this.render(notification);
        },

        render: function(notification) {
            var self = this;
            var colors = this.getTypeColors(notification.type);

            var snackbar = document.createElement('div');
            snackbar.style.cssText = 'display:flex;align-items:center;gap:16px;padding:14px 16px;background:' + colors.bg + ';color:' + colors.text + ';border-radius:8px;box-shadow:0 3px 5px rgba(0,0,0,0.2);animation:loom-snackbar-in 0.2s ease;pointer-events:auto;max-width:calc(100vw - 48px);';
            snackbar.setAttribute('role', 'alert');

            // Message
            var message = document.createElement('span');
            message.style.cssText = 'font-size:14px;flex:1;';
            message.textContent = notification.message;
            snackbar.appendChild(message);

            // Action button
            if (notification.action) {
                var actionBtn = document.createElement('button');
                actionBtn.type = 'button';
                actionBtn.style.cssText = 'background:transparent;border:none;color:inherit;font-size:14px;font-weight:500;cursor:pointer;padding:0;text-transform:uppercase;opacity:0.9;';
                actionBtn.textContent = notification.action;
                if (notification.onAction) {
                    // Dispatch custom event instead of eval() for security
                    actionBtn.onclick = function() {
                        var event = new CustomEvent('loom:snackbar:action', {
                            detail: {
                                action: notification.onAction,
                                message: notification.message,
                                type: notification.type
                            },
                            bubbles: true
                        });
                        document.dispatchEvent(event);
                        // Also try to call if it's a registered global function name
                        if (typeof window[notification.onAction] === 'function') {
                            window[notification.onAction]();
                        }
                    };
                }
                snackbar.appendChild(actionBtn);
            }

            // Close button
            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.style.cssText = 'background:transparent;border:none;color:inherit;cursor:pointer;padding:4px;margin:-4px;margin-left:0;opacity:0.7;display:flex;';
            closeBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
            closeBtn.onclick = function() { self.dismiss(snackbar); };
            snackbar.appendChild(closeBtn);

            this.container.appendChild(snackbar);

            // Auto-dismiss
            if (notification.duration > 0) {
                setTimeout(function() {
                    self.dismiss(snackbar);
                }, notification.duration);
            }
        },

        dismiss: function(snackbar) {
            var self = this;
            snackbar.style.animation = 'loom-snackbar-out 0.2s ease forwards';
            setTimeout(function() {
                if (snackbar.parentNode) {
                    snackbar.parentNode.removeChild(snackbar);
                }
                self.isShowing = false;
                self.showNext();
            }, 200);
        },

        getTypeColors: function(type) {
            var colors = {
                success: { bg: 'var(--loom-success, #2ecc71)', text: '#fff' },
                error: { bg: 'var(--loom-error, #e74c3c)', text: '#fff' },
                warning: { bg: 'var(--loom-warning, #f39c12)', text: '#000' },
                info: { bg: 'var(--loom-info, #3498db)', text: '#fff' },
                log: { bg: 'var(--loom-surface, #f8f9fa)', text: 'var(--loom-text, #1a1a1a)' }
            };
            return colors[type] || { bg: 'var(--loom-inverse-surface, #323232)', text: 'var(--loom-inverse-on-surface, #fff)' };
        }
    };

    // Add animations
    var style = document.createElement('style');
    style.textContent = '@keyframes loom-snackbar-in { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } } @keyframes loom-snackbar-out { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(20px); } }';
    document.head.appendChild(style);

    // Initialize and show queued notifications
    document.addEventListener('DOMContentLoaded', function() {
        LoomSnackbar.init();
        LoomSnackbar.showQueued({$notificationsJson});
    });

    // Expose globally
    window.LoomSnackbar = LoomSnackbar;
})();
</script>
HTML;
    }

    /**
     * Create a notification directly (helper method)
     *
     * Uses Noti queue if available, otherwise shows Snackbar directly.
     *
     * @param string $message Notification message
     * @param string $type Notification type (success, error, warning, info)
     * @param array $options Additional options
     */
    public static function notify(
        $message,
        $type = 'info',
        $options = []
    ) {
        if (PluginRegistry::hasNoti()) {
            // Use Noti's queue (will be rendered via bridge if enabled)
            \Loom\Noti\Features\Notifications\Noti::enqueue($type, $message, $options);
        } else {
            // Fallback: render Snackbar directly via JS
            $data = [
                'message' => $message,
                'type' => $type,
                'duration' => $options['duration'] ?? 5000,
                'action' => $options['action'] ?? null,
            ];
            $json = wp_json_encode($data);

            add_action('wp_footer', function() use ($json) {
                echo self::getContainerHtml();
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof LoomSnackbar !== 'undefined') {
                            LoomSnackbar.init();
                            LoomSnackbar.showQueued([{$json}]);
                        }
                    });
                </script>";
            }, 100);
        }
    }
}
