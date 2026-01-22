<?php
/**
 * Admin View
 *
 * @var string $currentCategory
 * @var array $categories
 *
 * @package IconManager\Features\Admin
 * @since 3.0.0
 */



if (!defined('ABSPATH')) exit;

use IconManager\Features\Packs\IconPackManager;

$totalCategories = count($categories);
$totalIcons = 0;
$totalSize = 0;
foreach ($categories as $category) {
    $stats = IconPackManager::getPackStats($category);
    $totalIcons += $stats['count'];
    $totalSize += $stats['size'] ?? 0;
}

$currentCategory = !empty($categories) ? ($currentCategory ?: $categories[0]) : '';
$currentCategoryStats = $currentCategory ? IconPackManager::getPackStats($currentCategory) : null;

/**
 * Format bytes to human readable string
 */
function formatIconBytes(int $bytes): string {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}
?>

<div class="wrap icon-manager-wrap">
    <div class="icon-manager-header">
        <div>
            <h1><?php _e('Icon Manager', 'icon-manager'); ?></h1>
            <p class="description"><?php _e('Manage your SVG icon categories', 'icon-manager'); ?></p>
        </div>
        <div class="icon-manager-header-actions">
            <button id="import-btn" class="icon-manager-btn secondary" title="<?php esc_attr_e('Import icons from ZIP', 'icon-manager'); ?>">
                <span class="dashicons dashicons-upload"></span>
                <?php _e('Import', 'icon-manager'); ?>
            </button>
            <button id="export-btn" class="icon-manager-btn secondary" title="<?php esc_attr_e('Export category as ZIP', 'icon-manager'); ?>" <?php echo !$currentCategory ? 'disabled' : ''; ?>>
                <span class="dashicons dashicons-download"></span>
                <?php _e('Export', 'icon-manager'); ?>
            </button>
            <button id="regenerate-btn" class="icon-manager-btn secondary">
                <span class="dashicons dashicons-update"></span>
                <?php _e('Regenerate Enums', 'icon-manager'); ?>
            </button>
        </div>
    </div>

    <div class="icon-manager-layout">
        <div class="icon-manager-main">
            <!-- Stats Row -->
            <div class="icon-manager-stats">
                <div class="icon-manager-stat">
                    <span class="stat-value" id="total-categories"><?php echo $totalCategories; ?></span>
                    <span class="stat-label"><?php _e('Categories', 'icon-manager'); ?></span>
                </div>
                <div class="icon-manager-stat">
                    <span class="stat-value" id="total-icons"><?php echo $totalIcons; ?></span>
                    <span class="stat-label"><?php _e('Total Icons', 'icon-manager'); ?></span>
                </div>
                <?php if ($currentCategoryStats): ?>
                <div class="icon-manager-stat">
                    <span class="stat-value" id="category-icon-count"><?php echo $currentCategoryStats['count']; ?></span>
                    <span class="stat-label"><?php _e('In Category', 'icon-manager'); ?></span>
                </div>
                <div class="icon-manager-stat">
                    <span class="stat-value" id="category-size"><?php echo formatIconBytes($currentCategoryStats['size'] ?? 0); ?></span>
                    <span class="stat-label"><?php _e('Size', 'icon-manager'); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Category Tabs -->
            <div class="icon-manager-tabs">
                <?php foreach ($categories as $category):
                    $categoryStats = IconPackManager::getPackStats($category);
                ?>
                <a href="#" class="icon-manager-tab <?php echo $category === $currentCategory ? 'active' : ''; ?>" data-category="<?php echo esc_attr($category); ?>">
                    <?php echo esc_html($category); ?>
                    <span class="tab-count"><?php echo $categoryStats['count']; ?></span>
                </a>
                <?php endforeach; ?>
                <button id="new-category-btn" class="icon-manager-tab new-category">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php _e('New Category', 'icon-manager'); ?>
                </button>
            </div>

            <!-- Content Area -->
            <div class="icon-manager-content">
                <?php if ($currentCategory): ?>
                    <!-- Upload Zone -->
                    <div id="upload-area" class="icon-manager-upload">
                        <div class="upload-icon">
                            <span class="dashicons dashicons-cloud-upload"></span>
                        </div>
                        <div class="upload-text">
                            <strong><?php _e('Drop SVG files here', 'icon-manager'); ?></strong>
                            <span><?php _e('or click to browse', 'icon-manager'); ?></span>
                        </div>
                        <input type="file" id="icon-file-input" accept=".svg" multiple>
                    </div>

                    <!-- Upload Preview (hidden by default) -->
                    <div id="upload-preview" class="icon-manager-upload-preview" style="display: none;">
                        <div class="upload-preview-header">
                            <h3><?php _e('Preview & Rename', 'icon-manager'); ?></h3>
                            <div class="upload-preview-actions">
                                <button id="upload-cancel-btn" class="icon-manager-btn small secondary"><?php _e('Cancel', 'icon-manager'); ?></button>
                                <button id="upload-confirm-btn" class="icon-manager-btn small"><?php _e('Upload All', 'icon-manager'); ?></button>
                            </div>
                        </div>
                        <div id="upload-preview-grid" class="upload-preview-grid"></div>
                    </div>

                    <!-- Toolbar -->
                    <div class="icon-manager-toolbar">
                        <div class="toolbar-left">
                            <div class="search-box">
                                <span class="dashicons dashicons-search"></span>
                                <input type="text" id="icon-search" placeholder="<?php _e('Search icons...', 'icon-manager'); ?>">
                                <button id="search-clear" class="search-clear" style="display: none;">
                                    <span class="dashicons dashicons-no-alt"></span>
                                </button>
                            </div>
                            <div class="filter-controls">
                                <select id="sort-select" class="icon-manager-select">
                                    <option value="name-asc"><?php _e('Name A-Z', 'icon-manager'); ?></option>
                                    <option value="name-desc"><?php _e('Name Z-A', 'icon-manager'); ?></option>
                                    <option value="date-desc"><?php _e('Newest', 'icon-manager'); ?></option>
                                    <option value="date-asc"><?php _e('Oldest', 'icon-manager'); ?></option>
                                    <option value="size-desc"><?php _e('Largest', 'icon-manager'); ?></option>
                                    <option value="size-asc"><?php _e('Smallest', 'icon-manager'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="toolbar-right">
                            <span class="selection-info">
                                <span id="selected-count">0</span> <?php _e('selected', 'icon-manager'); ?>
                            </span>
                            <button id="select-all-btn" class="icon-manager-btn small secondary">
                                <?php _e('Select All', 'icon-manager'); ?>
                            </button>
                            <button id="bulk-delete-btn" class="icon-manager-btn small btn-danger" disabled>
                                <span class="dashicons dashicons-trash"></span>
                                <?php _e('Delete', 'icon-manager'); ?>
                            </button>
                            <button id="delete-category-btn" class="icon-manager-btn small btn-danger">
                                <?php _e('Delete Category', 'icon-manager'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Icon Grid -->
                    <div id="icon-content">
                        <div class="icon-manager-loading">
                            <div class="spinner"></div>
                            <span><?php _e('Loading icons...', 'icon-manager'); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="icon-manager-empty">
                        <span class="dashicons dashicons-portfolio"></span>
                        <h3><?php _e('No Icon Categories Yet', 'icon-manager'); ?></h3>
                        <p><?php _e('Create your first icon category to get started', 'icon-manager'); ?></p>
                        <button id="new-category-btn-empty" class="icon-manager-btn">
                            <span class="dashicons dashicons-plus-alt2"></span>
                            <?php _e('Create First Category', 'icon-manager'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Icon Popover Template -->
<div id="icon-popover" class="icon-popover" style="display: none;">
    <div class="popover-header">
        <span class="popover-title"></span>
        <button class="popover-close"><span class="dashicons dashicons-no-alt"></span></button>
    </div>
    <div class="popover-preview">
        <div class="popover-icon-large"></div>
        <div class="popover-controls">
            <label>
                <span><?php _e('Size', 'icon-manager'); ?></span>
                <input type="number" id="popover-size" value="24" min="8" max="512">
            </label>
            <label>
                <span><?php _e('Color', 'icon-manager'); ?></span>
                <input type="color" id="popover-color" value="#000000">
            </label>
        </div>
    </div>
    <div class="popover-copy-options">
        <button class="copy-btn" data-format="php" title="<?php esc_attr_e('Copy PHP code', 'icon-manager'); ?>">
            <span class="dashicons dashicons-editor-code"></span>
            PHP
        </button>
        <button class="copy-btn" data-format="html" title="<?php esc_attr_e('Copy HTML/SVG', 'icon-manager'); ?>">
            <span class="dashicons dashicons-media-code"></span>
            HTML
        </button>
        <button class="copy-btn" data-format="shortcode" title="<?php esc_attr_e('Copy shortcode', 'icon-manager'); ?>">
            <span class="dashicons dashicons-shortcode"></span>
            Shortcode
        </button>
        <button class="copy-btn" data-format="css" title="<?php esc_attr_e('Copy as CSS background', 'icon-manager'); ?>">
            <span class="dashicons dashicons-admin-customizer"></span>
            CSS
        </button>
    </div>
    <div class="popover-code-preview">
        <pre><code id="popover-code"></code></pre>
    </div>
    <div class="popover-actions">
        <button id="popover-copy-btn" class="icon-manager-btn small">
            <span class="dashicons dashicons-clipboard"></span>
            <?php _e('Copy to Clipboard', 'icon-manager'); ?>
        </button>
        <button id="popover-delete-btn" class="icon-manager-btn small btn-danger">
            <span class="dashicons dashicons-trash"></span>
            <?php _e('Delete', 'icon-manager'); ?>
        </button>
    </div>
</div>

<!-- Import Modal -->
<div id="import-modal" class="icon-manager-modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php _e('Import Icons', 'icon-manager'); ?></h3>
            <button class="modal-close"><span class="dashicons dashicons-no-alt"></span></button>
        </div>
        <div class="modal-body">
            <div id="import-drop-zone" class="import-drop-zone">
                <span class="dashicons dashicons-upload"></span>
                <p><?php _e('Drop ZIP file here or click to browse', 'icon-manager'); ?></p>
                <input type="file" id="import-file-input" accept=".zip">
            </div>
            <div id="import-progress" class="import-progress" style="display: none;">
                <div class="progress-bar"><div class="progress-fill"></div></div>
                <span class="progress-text"><?php _e('Importing...', 'icon-manager'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- New Category Modal -->
<div id="new-category-modal" class="icon-manager-modal" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h3><?php _e('Create New Category', 'icon-manager'); ?></h3>
            <button class="modal-close"><span class="dashicons dashicons-no-alt"></span></button>
        </div>
        <div class="modal-body">
            <label for="new-category-name"><?php _e('Category Name', 'icon-manager'); ?></label>
            <input type="text" id="new-category-name" placeholder="<?php esc_attr_e('e.g., Social, Brands, UI', 'icon-manager'); ?>" pattern="[a-zA-Z0-9_-]+" maxlength="50">
            <p class="help-text"><?php _e('Use letters, numbers, hyphens, and underscores only.', 'icon-manager'); ?></p>
        </div>
        <div class="modal-footer">
            <button class="icon-manager-btn secondary modal-cancel"><?php _e('Cancel', 'icon-manager'); ?></button>
            <button id="create-category-btn" class="icon-manager-btn"><?php _e('Create Category', 'icon-manager'); ?></button>
        </div>
    </div>
</div>

<div id="icon-manager-toast-container" class="icon-manager-toast-container"></div>
