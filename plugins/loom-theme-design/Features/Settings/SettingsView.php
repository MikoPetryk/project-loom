<?php
/**
 * Settings View
 *
 * @package Loom\ThemeManager\Features\Settings
 * @since 1.2.0
 */



use Loom\ThemeManager\Features\Tokens\Colors;
use Loom\ThemeManager\Features\Tokens\Typography;
use Loom\ThemeManager\Features\Tokens\Spacing;
use Loom\ThemeManager\Features\Tokens\Shapes;

if (!defined('ABSPATH')) exit;

$colorGroups = Colors::getColorGroups();
$colorLabels = Colors::getLabels();
$lightColors = Colors::toArray();
$darkColors = Colors::toDarkArray();
?>
<div class="wrap theme-manager-wrap">
    <div class="theme-manager-header">
        <div>
            <h1>Theme Manager</h1>
            <p class="description">Design tokens for your theme with light and dark mode support.</p>
        </div>
        <div class="theme-manager-mode-toggle">
            <span class="mode-label">Preview Mode:</span>
            <div class="mode-switch">
                <button type="button" class="mode-switch-btn active" data-mode="light">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    Light
                </button>
                <button type="button" class="mode-switch-btn" data-mode="dark">
                    <span class="dashicons dashicons-welcome-view-site"></span>
                    Dark
                </button>
            </div>
            <button type="button" id="generate-dark" class="generate-dark-btn" title="Generate dark palette from light colors">
                <span class="dashicons dashicons-update"></span>
                Generate Dark
            </button>
        </div>
    </div>

    <div id="theme-manager-app" class="theme-manager-layout">
        <!-- Left Column: Settings -->
        <div class="theme-manager-settings">
            <div class="theme-manager-tabs">
                <button class="tab-btn active" data-tab="colors">Colors</button>
                <button class="tab-btn" data-tab="typography">Typography</button>
                <button class="tab-btn" data-tab="spacing">Spacing</button>
                <button class="tab-btn" data-tab="shapes">Shapes</button>
            </div>

            <!-- Colors Tab -->
            <div class="tab-content active" id="tab-colors">
                <!-- Light Mode Colors -->
                <div class="color-mode-section" data-mode="light">
                    <div class="section-header">
                        <h3>Light Mode Colors</h3>
                    </div>
                    <?php foreach ($colorGroups as $groupKey => $group):
                        $colorCount = array_sum(array_map('count', $group['colors']));
                    ?>
                    <div class="color-group" data-group="<?php echo esc_attr($groupKey); ?>">
                        <div class="color-group-header">
                            <h4>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                <?php echo esc_html($group['label']); ?>
                            </h4>
                            <span class="color-group-count"><?php echo $colorCount; ?></span>
                        </div>
                        <div class="color-group-body">
                            <?php foreach ($group['colors'] as $rowKey => $colorKeys): ?>
                            <div class="color-row">
                                <?php foreach ($colorKeys as $key): ?>
                                <div class="color-item">
                                    <label for="light-color-<?php echo esc_attr($key); ?>"><?php echo esc_html($colorLabels[$key] ?? $key); ?></label>
                                    <div class="color-input-wrap">
                                        <input type="color"
                                            id="light-color-<?php echo esc_attr($key); ?>"
                                            name="colors[light][<?php echo esc_attr($key); ?>]"
                                            value="<?php echo esc_attr($lightColors[$key] ?? '#000000'); ?>"
                                            data-token="colors.light.<?php echo esc_attr($key); ?>">
                                        <input type="text"
                                            class="color-hex"
                                            value="<?php echo esc_attr($lightColors[$key] ?? '#000000'); ?>"
                                            data-for="light-color-<?php echo esc_attr($key); ?>">
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Dark Mode Colors -->
                <div class="color-mode-section" data-mode="dark" style="display: none;">
                    <div class="section-header">
                        <h3>Dark Mode Colors</h3>
                        <p class="section-description">These colors are auto-generated from light mode. Customize to override.</p>
                    </div>
                    <?php foreach ($colorGroups as $groupKey => $group):
                        $colorCount = array_sum(array_map('count', $group['colors']));
                    ?>
                    <div class="color-group" data-group="<?php echo esc_attr($groupKey); ?>">
                        <div class="color-group-header">
                            <h4>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                <?php echo esc_html($group['label']); ?>
                            </h4>
                            <span class="color-group-count"><?php echo $colorCount; ?></span>
                        </div>
                        <div class="color-group-body">
                            <?php foreach ($group['colors'] as $rowKey => $colorKeys): ?>
                            <div class="color-row">
                                <?php foreach ($colorKeys as $key): ?>
                                <div class="color-item">
                                    <label for="dark-color-<?php echo esc_attr($key); ?>"><?php echo esc_html($colorLabels[$key] ?? $key); ?></label>
                                    <div class="color-input-wrap">
                                        <input type="color"
                                            id="dark-color-<?php echo esc_attr($key); ?>"
                                            name="colors[dark][<?php echo esc_attr($key); ?>]"
                                            value="<?php echo esc_attr($darkColors[$key] ?? '#000000'); ?>"
                                            data-token="colors.dark.<?php echo esc_attr($key); ?>">
                                        <input type="text"
                                            class="color-hex"
                                            value="<?php echo esc_attr($darkColors[$key] ?? '#000000'); ?>"
                                            data-for="dark-color-<?php echo esc_attr($key); ?>">
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Typography Tab -->
            <div class="tab-content" id="tab-typography">
                <div class="token-grid">
                    <div class="token-item">
                        <label>Heading Font</label>
                        <select data-token="typography.fontHeading">
                            <?php foreach (Typography::getFontOptions() as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected(Typography::fontHeading(), $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="token-item">
                        <label>Body Font</label>
                        <select data-token="typography.fontBody">
                            <?php foreach (Typography::getFontOptions() as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected(Typography::fontBody(), $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php foreach (['sizeBase', 'sizeH1', 'sizeH2', 'sizeH3', 'sizeH4', 'sizeH5', 'sizeH6'] as $key): ?>
                    <div class="token-item">
                        <label><?php echo esc_html(Typography::getLabels()[$key]); ?> (px)</label>
                        <input type="number" value="<?php echo esc_attr((string) Typography::toArray()[$key]); ?>" min="12" max="96" data-token="typography.<?php echo esc_attr($key); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Spacing Tab -->
            <div class="tab-content" id="tab-spacing">
                <div class="token-grid">
                    <?php foreach (Spacing::getLabels() as $key => $label): ?>
                    <div class="token-item">
                        <label><?php echo esc_html($label); ?> (px)</label>
                        <input type="number" value="<?php echo esc_attr((string) Spacing::toArray()[$key]); ?>" min="0" max="128" data-token="spacing.<?php echo esc_attr($key); ?>">
                        <div class="spacing-preview" style="width: <?php echo esc_attr((string) Spacing::toArray()[$key]); ?>px;"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Shapes Tab -->
            <div class="tab-content" id="tab-shapes">
                <div class="token-grid">
                    <?php foreach (Shapes::getLabels() as $key => $label): ?>
                    <div class="token-item">
                        <label><?php echo esc_html($label); ?> (px)</label>
                        <input type="number" value="<?php echo esc_attr((string) Shapes::toArray()[$key]); ?>" min="0" max="9999" data-token="shapes.<?php echo esc_attr($key); ?>">
                        <div class="shape-preview" style="border-radius: <?php echo esc_attr((string) Shapes::toArray()[$key]); ?>px;"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Actions Bar -->
            <div class="actions-bar" id="actions-bar">
                <button type="button" id="save-tokens" class="button button-primary">Save Changes</button>
                <button type="button" id="reset-tokens" class="button button-secondary">Reset</button>
                <span id="save-status"></span>
            </div>
        </div>

        <!-- Right Column: Live Preview -->
        <div class="theme-manager-preview">
            <div class="preview-header">
                <h3>Live Preview</h3>
            </div>
            <div class="preview-panel" id="preview-panel">
                <div class="preview-section-inner">
                    <h4 class="preview-title">Buttons</h4>
                    <div class="button-row">
                        <button class="preview-btn primary">Primary</button>
                        <button class="preview-btn secondary">Secondary</button>
                        <button class="preview-btn tertiary">Tertiary</button>
                    </div>
                    <div class="button-row">
                        <button class="preview-btn btn-success">Success</button>
                        <button class="preview-btn btn-warning">Warning</button>
                        <button class="preview-btn btn-error">Error</button>
                    </div>
                </div>

                <div class="preview-section-inner">
                    <h4 class="preview-title">Surfaces</h4>
                    <div class="preview-cards">
                        <div class="preview-card surface">
                            <h5>Surface</h5>
                            <p>Content on surface background.</p>
                        </div>
                        <div class="preview-card surface-variant">
                            <h5>Surface Variant</h5>
                            <p>Alternative surface style.</p>
                        </div>
                        <div class="preview-card primary-container">
                            <h5>Primary Container</h5>
                            <p>Highlighted content area.</p>
                        </div>
                    </div>
                </div>

                <div class="preview-section-inner">
                    <h4 class="preview-title">Text Hierarchy</h4>
                    <div class="text-preview">
                        <p class="text-primary">Primary text - most prominent content</p>
                        <p class="text-secondary">Secondary text - supporting information</p>
                        <p class="text-disabled">Disabled text - inactive elements</p>
                    </div>
                </div>

                <div class="preview-section-inner">
                    <h4 class="preview-title">Alerts</h4>
                    <div class="alert-preview">
                        <div class="preview-alert is-success">Success alert style</div>
                        <div class="preview-alert is-warning">Warning alert style</div>
                        <div class="preview-alert is-error">Error alert style</div>
                        <div class="preview-alert is-info">Info alert style</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="theme-manager-toast-container" class="theme-manager-toast-container"></div>
