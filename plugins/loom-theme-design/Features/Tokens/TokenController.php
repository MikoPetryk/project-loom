<?php
/**
 * Token Controller
 *
 * REST API controller for design token operations.
 *
 * @package Loom\ThemeManager\Features\Tokens
 * @since 1.0.0
 */



namespace Loom\ThemeManager\Features\Tokens;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class TokenController extends WP_REST_Controller {

    protected $namespace = 'theme-manager/v1';
    protected $rest_base = 'tokens';

    public function register_routes(): void {
        // Get/update all tokens
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            ['methods' => WP_REST_Server::READABLE, 'callback' => [$this, 'get_tokens'], 'permission_callback' => [$this, 'check_read_permission']],
            ['methods' => WP_REST_Server::EDITABLE, 'callback' => [$this, 'update_tokens'], 'permission_callback' => [$this, 'admin_check']],
        ]);

        // Reset to defaults
        register_rest_route($this->namespace, '/' . $this->rest_base . '/reset', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'reset_tokens'],
            'permission_callback' => [$this, 'admin_check'],
        ]);

        // Generate dark palette from light
        register_rest_route($this->namespace, '/' . $this->rest_base . '/generate-dark', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'generate_dark'],
            'permission_callback' => [$this, 'admin_check'],
        ]);

        // Get generated CSS
        register_rest_route($this->namespace, '/css', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_css'],
            'permission_callback' => [$this, 'check_read_permission'],
        ]);

        // Get color groups for UI
        register_rest_route($this->namespace, '/' . $this->rest_base . '/color-groups', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_color_groups'],
            'permission_callback' => [$this, 'check_read_permission'],
        ]);
    }

    /**
     * Check read permission - allow logged-in users or valid nonce
     */
    public function check_read_permission($request): bool {
        return is_user_logged_in() || wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
    }

    /**
     * Check admin permission
     */
    public function admin_check($request): bool {
        return current_user_can('manage_options');
    }

    /**
     * Get all tokens
     */
    public function get_tokens($request): WP_REST_Response {
        $tokens = TokenRegistry::getAll();

        // Ensure colors has the new structure
        if (!isset($tokens['colors']['light'])) {
            $tokens['colors'] = Colors::toFullArray();
        }

        return new WP_REST_Response([
            'data' => $tokens,
            'meta' => [
                'colorGroups' => Colors::getColorGroups(),
                'colorLabels' => Colors::getLabels(),
            ],
        ], 200);
    }

    /**
     * Update tokens
     */
    public function update_tokens($request): WP_REST_Response {
        try {
            $input = $request->get_json_params();

            if (empty($input) || !is_array($input)) {
                return new WP_REST_Response([
                    'error' => ['code' => 'invalid_input', 'message' => 'No data received']
                ], 400);
            }

            $tokens = $this->sanitize($input);

            if (!TokenRegistry::save($tokens)) {
                return new WP_REST_Response([
                    'error' => ['code' => 'save_failed', 'message' => 'Failed to save to database']
                ], 500);
            }

            return new WP_REST_Response([
                'data' => TokenRegistry::getAll(),
                'message' => 'Tokens updated successfully'
            ], 200);

        } catch (\Throwable $e) {
            error_log('Theme Manager - Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return new WP_REST_Response([
                'error' => ['code' => 'server_error', 'message' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Reset all tokens to defaults
     */
    public function reset_tokens($request): WP_REST_Response {
        $defaults = [
            'colors' => Colors::getDefaults(),
            'typography' => Typography::getDefaults(),
            'spacing' => Spacing::getDefaults(),
            'shapes' => Shapes::getDefaults(),
        ];

        update_option('loom_theme_tokens', $defaults);
        Colors::load($defaults['colors']);
        Typography::load($defaults['typography']);
        Spacing::load($defaults['spacing']);
        Shapes::load($defaults['shapes']);

        return new WP_REST_Response([
            'data' => $defaults,
            'message' => 'Reset to defaults'
        ], 200);
    }

    /**
     * Generate dark palette from light colors
     *
     * Can accept light colors from client (for unsaved changes) or use saved colors
     */
    public function generate_dark($request): WP_REST_Response {
        try {
            $input = $request->get_json_params();

            // Use provided light colors from client, or fall back to saved colors
            if (!empty($input['lightColors']) && is_array($input['lightColors'])) {
                // Sanitize the provided colors
                $lightColors = [];
                $defaults = Colors::toArray();
                foreach ($input['lightColors'] as $key => $value) {
                    if (array_key_exists($key, $defaults)) {
                        $sanitized = sanitize_hex_color($value);
                        $lightColors[$key] = $sanitized ?: $defaults[$key];
                    }
                }
                // Fill in any missing colors with defaults
                $lightColors = array_merge($defaults, $lightColors);
            } else {
                // Fall back to saved light colors
                $lightColors = Colors::toArray();
            }

            // Generate dark palette
            $darkColors = ColorGenerator::generateDarkPalette($lightColors);

            // Note: We don't save here - just return the generated colors
            // The user can review and save when ready

            return new WP_REST_Response([
                'data' => [
                    'dark' => $darkColors,
                    'light' => $lightColors,
                ],
                'message' => 'Dark palette generated successfully'
            ], 200);

        } catch (\Throwable $e) {
            error_log('Theme Manager - Generate Dark Error: ' . $e->getMessage());
            return new WP_REST_Response([
                'error' => ['code' => 'generation_failed', 'message' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Get generated CSS
     */
    public function get_css($request): WP_REST_Response {
        return new WP_REST_Response([
            'data' => ['css' => CssGenerator::generate()]
        ], 200);
    }

    /**
     * Get color groups for UI organization
     */
    public function get_color_groups($request): WP_REST_Response {
        return new WP_REST_Response([
            'data' => [
                'groups' => Colors::getColorGroups(),
                'labels' => Colors::getLabels(),
            ]
        ], 200);
    }

    /**
     * Sanitize input tokens
     */
    private function sanitize(array $input): array {
        // Start with current values to preserve what's not being updated
        $tokens = [
            'colors' => Colors::toFullArray(),
            'typography' => Typography::getDefaults(),
            'spacing' => Spacing::getDefaults(),
            'shapes' => Shapes::getDefaults(),
        ];

        // Handle colors with new nested structure
        if (isset($input['colors']) && is_array($input['colors'])) {
            // New structure: colors.light, colors.dark
            if (isset($input['colors']['light']) && is_array($input['colors']['light'])) {
                foreach ($input['colors']['light'] as $key => $value) {
                    if (array_key_exists($key, $tokens['colors']['light'])) {
                        $sanitized = sanitize_hex_color($value);
                        if ($sanitized) {
                            $tokens['colors']['light'][$key] = $sanitized;
                        }
                    }
                }
            }

            if (isset($input['colors']['dark']) && is_array($input['colors']['dark'])) {
                foreach ($input['colors']['dark'] as $key => $value) {
                    if (array_key_exists($key, $tokens['colors']['dark'])) {
                        $sanitized = sanitize_hex_color($value);
                        if ($sanitized) {
                            $tokens['colors']['dark'][$key] = $sanitized;
                        }
                    }
                }
            }

            if (isset($input['colors']['darkOverrides']) && is_array($input['colors']['darkOverrides'])) {
                $tokens['colors']['darkOverrides'] = [];
                foreach ($input['colors']['darkOverrides'] as $key => $value) {
                    if (array_key_exists($key, $tokens['colors']['light'])) {
                        $sanitized = sanitize_hex_color($value);
                        if ($sanitized) {
                            $tokens['colors']['darkOverrides'][$key] = $sanitized;
                        }
                    }
                }
            }

            // Legacy flat structure support
            if (!isset($input['colors']['light']) && !isset($input['colors']['dark'])) {
                foreach ($input['colors'] as $key => $value) {
                    if (is_string($value) && array_key_exists($key, $tokens['colors']['light'])) {
                        $sanitized = sanitize_hex_color($value);
                        if ($sanitized) {
                            $tokens['colors']['light'][$key] = $sanitized;
                        }
                    }
                }
                // Regenerate dark from light
                $tokens['colors']['dark'] = ColorGenerator::generateDarkPalette($tokens['colors']['light']);
            }
        }

        // Typography
        if (isset($input['typography']) && is_array($input['typography'])) {
            foreach ($input['typography'] as $key => $value) {
                if (array_key_exists($key, $tokens['typography'])) {
                    if (in_array($key, ['fontHeading', 'fontBody'])) {
                        $tokens['typography'][$key] = sanitize_text_field($value);
                    } elseif ($key === 'lineHeight') {
                        $tokens['typography'][$key] = (float) $value;
                    } else {
                        $tokens['typography'][$key] = absint($value);
                    }
                }
            }
        }

        // Spacing
        if (isset($input['spacing']) && is_array($input['spacing'])) {
            foreach ($input['spacing'] as $key => $value) {
                if (array_key_exists($key, $tokens['spacing'])) {
                    $tokens['spacing'][$key] = absint($value);
                }
            }
        }

        // Shapes
        if (isset($input['shapes']) && is_array($input['shapes'])) {
            foreach ($input['shapes'] as $key => $value) {
                if (array_key_exists($key, $tokens['shapes'])) {
                    $tokens['shapes'][$key] = absint($value);
                }
            }
        }

        return $tokens;
    }
}
