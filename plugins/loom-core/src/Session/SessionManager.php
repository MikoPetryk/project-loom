<?php
/**
 * Session Manager
 *
 * Handles session management for Loom, supporting both
 * authenticated users and guests.
 *
 * @package Loom\Core\Session
 */



namespace Loom\Core\Session;

class SessionManager {

    private static ?string $sessionId = null;
    private static ?int $userId = null;
    private static ?string $token = null;
    private static bool $initialized = false;

    /**
     * Start or resume session
     */
    public static function start(): void {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        // Check if table exists - skip session if not (prevents fatal errors during setup)
        if (!self::tableExists()) {
            return;
        }

        // Check for existing token
        $token = self::getTokenFromRequest();

        if ($token) {
            self::validateToken($token);
        } else {
            self::createSession();
        }

        // Link to WP user if logged in
        if (is_user_logged_in() && !self::$userId) {
            self::linkToUser(get_current_user_id());
        }
    }

    /**
     * Check if sessions table exists
     */
    private static function tableExists(): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'loom_sessions';
        $result = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
        return $result === $table;
    }

    /**
     * Get token from request
     */
    private static function getTokenFromRequest(): ?string {
        // Header first (for API calls)
        if (!empty($_SERVER['HTTP_X_LOOM_SESSION'])) {
            return sanitize_text_field($_SERVER['HTTP_X_LOOM_SESSION']);
        }

        // Cookie fallback
        if (!empty($_COOKIE['loom_session'])) {
            return sanitize_text_field($_COOKIE['loom_session']);
        }

        return null;
    }

    /**
     * Validate an existing token
     */
    private static function validateToken(string $token): void {
        global $wpdb;

        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT session_id, user_id, token
             FROM {$wpdb->prefix}loom_sessions
             WHERE token = %s AND expires_at > NOW()",
            $token
        ));

        if ($session) {
            self::$sessionId = $session->session_id;
            self::$userId = $session->user_id ? (int)$session->user_id : null;
            self::$token = $session->token;

            // Extend expiration
            self::extendSession();
        } else {
            // Token invalid, create new session
            self::createSession();
        }
    }

    /**
     * Create a new session
     */
    private static function createSession(): void {
        global $wpdb;

        self::$sessionId = self::generateSessionId();
        self::$token = self::generateToken();
        self::$userId = is_user_logged_in() ? get_current_user_id() : null;

        $wpdb->insert(
            "{$wpdb->prefix}loom_sessions",
            [
                'session_id' => self::$sessionId,
                'token' => self::$token,
                'user_id' => self::$userId,
                'data' => '{}',
                'expires_at' => date('Y-m-d H:i:s', time() + self::getSessionLifetime()),
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%d', '%s', '%s', '%s']
        );

        // Set cookie
        self::setCookie();
    }

    /**
     * Extend session expiration
     */
    private static function extendSession(): void {
        global $wpdb;

        $wpdb->update(
            "{$wpdb->prefix}loom_sessions",
            [
                'expires_at' => date('Y-m-d H:i:s', time() + self::getSessionLifetime()),
                'updated_at' => current_time('mysql'),
            ],
            ['session_id' => self::$sessionId],
            ['%s', '%s'],
            ['%s']
        );
    }

    /**
     * Link session to a user (on login)
     */
    public static function linkToUser(int $userId): void {
        global $wpdb;

        if (!self::$sessionId) {
            return;
        }

        $wpdb->update(
            "{$wpdb->prefix}loom_sessions",
            ['user_id' => $userId],
            ['session_id' => self::$sessionId],
            ['%d'],
            ['%s']
        );

        self::$userId = $userId;
    }

    /**
     * Unlink session from user (on logout)
     */
    public static function unlinkUser(): void {
        global $wpdb;

        if (!self::$sessionId) {
            return;
        }

        $wpdb->update(
            "{$wpdb->prefix}loom_sessions",
            ['user_id' => null],
            ['session_id' => self::$sessionId],
            ['%s'],
            ['%s']
        );

        self::$userId = null;
    }

    /**
     * Destroy current session
     */
    public static function destroy(): void {
        global $wpdb;

        if (self::$sessionId) {
            $wpdb->delete(
                "{$wpdb->prefix}loom_sessions",
                ['session_id' => self::$sessionId],
                ['%s']
            );
        }

        self::$sessionId = null;
        self::$userId = null;
        self::$token = null;

        // Clear cookie
        setcookie('loom_session', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => is_ssl(),
        ]);
    }

    /**
     * Get current session ID
     */
    public static function getSessionId(): ?string {
        return self::$sessionId;
    }

    /**
     * Get current user ID
     */
    public static function getUserId(): ?int {
        return self::$userId;
    }

    /**
     * Get current token
     */
    public static function getToken(): ?string {
        return self::$token;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        return self::$userId !== null;
    }

    /**
     * Generate nonce for state requests
     */
    public static function generateNonce(): string {
        $sessionId = self::$sessionId ?? '';
        $hour = date('Y-m-d H');

        return hash('sha256', $sessionId . NONCE_SALT . $hour);
    }

    /**
     * Verify nonce
     */
    public static function verifyNonce(string $nonce): bool {
        // Check current hour
        if (hash_equals(self::generateNonce(), $nonce)) {
            return true;
        }

        // Check previous hour (for edge cases)
        $sessionId = self::$sessionId ?? '';
        $prevHour = date('Y-m-d H', time() - 3600);
        $prevNonce = hash('sha256', $sessionId . NONCE_SALT . $prevHour);

        return hash_equals($prevNonce, $nonce);
    }

    /**
     * Set session cookie
     */
    private static function setCookie(): void {
        if (headers_sent()) {
            return;
        }

        setcookie('loom_session', self::$token, [
            'expires' => time() + self::getSessionLifetime(),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => is_ssl(),
        ]);
    }

    /**
     * Generate session ID
     */
    private static function generateSessionId(): string {
        return 'loom_' . bin2hex(random_bytes(16));
    }

    /**
     * Generate token
     */
    private static function generateToken(): string {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get session lifetime in seconds
     */
    private static function getSessionLifetime(): int {
        return apply_filters('loom_session_lifetime', 7 * DAY_IN_SECONDS);
    }

    /**
     * Cleanup expired sessions
     */
    public static function cleanup(): int {
        global $wpdb;

        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->prefix}loom_sessions WHERE expires_at < NOW()"
        );

        return (int)$deleted;
    }
}

// Hook into WP login/logout
add_action('wp_login', function ($user_login, $user) {
    SessionManager::linkToUser($user->ID);
}, 10, 2);

add_action('wp_logout', function () {
    SessionManager::unlinkUser();
});

// Schedule cleanup
add_action('loom_session_cleanup', [SessionManager::class, 'cleanup']);

if (!wp_next_scheduled('loom_session_cleanup')) {
    wp_schedule_event(time(), 'daily', 'loom_session_cleanup');
}
