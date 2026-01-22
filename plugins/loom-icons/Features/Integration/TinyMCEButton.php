<?php
/**
 * TinyMCE Button Integration
 *
 * Add icon button to Classic Editor.
 *
 * @package IconManager\Features\Integration
 * @since 2.1.0
 */



namespace IconManager\Features\Integration;

use IconManager\Features\Packs\IconPackManager;

class TinyMCEButton {

    public function __construct() {
        add_action('admin_head', [$this, 'addButton']);
        add_action('admin_footer', [$this, 'addModal']);
    }

    public function addButton(): void {
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }

        if (get_user_option('rich_editing') !== 'true') {
            return;
        }

        add_filter('mce_external_plugins', [$this, 'registerPlugin']);
        add_filter('mce_buttons', [$this, 'addEditorButton']);
    }

    public function registerPlugin($plugins): array {
        $plugins['icon_manager'] = ICON_MANAGER_PLUGIN_URL . 'assets/js/tinymce-plugin.js';
        return $plugins;
    }

    public function addEditorButton($buttons): array {
        array_push($buttons, 'icon_manager_button');
        return $buttons;
    }

    public function addModal(): void {
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
            return;
        }

        $packs = IconPackManager::getPackNames();
        ?>
        <div id="icon-manager-tinymce-modal" style="display:none;">
            <div class="icon-manager-modal-backdrop">
                <div class="icon-manager-modal">
                    <div class="icon-manager-modal-header">
                        <h2><?php _e('Insert Icon', 'icon-manager'); ?></h2>
                        <button class="icon-manager-modal-close">&times;</button>
                    </div>
                    <div class="icon-manager-modal-body">
                        <div class="icon-manager-form-group">
                            <label><?php _e('Icon Pack', 'icon-manager'); ?></label>
                            <select id="tinymce-icon-pack">
                                <option value=""><?php _e('Select Pack', 'icon-manager'); ?></option>
                                <?php foreach ($packs as $pack): ?>
                                    <option value="<?php echo esc_attr($pack); ?>"><?php echo esc_html($pack); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="icon-manager-form-group">
                            <label><?php _e('Icon', 'icon-manager'); ?></label>
                            <select id="tinymce-icon-name" disabled>
                                <option value=""><?php _e('Select Icon', 'icon-manager'); ?></option>
                            </select>
                        </div>
                        <div class="icon-manager-form-group">
                            <label><?php _e('Size', 'icon-manager'); ?></label>
                            <input type="number" id="tinymce-icon-size" value="24" min="8" max="128">
                        </div>
                        <div class="icon-manager-form-group">
                            <label><?php _e('Color', 'icon-manager'); ?></label>
                            <input type="color" id="tinymce-icon-color">
                        </div>
                    </div>
                    <div class="icon-manager-modal-footer">
                        <button class="icon-manager-btn secondary icon-manager-modal-close"><?php _e('Cancel', 'icon-manager'); ?></button>
                        <button class="icon-manager-btn" id="tinymce-insert-icon"><?php _e('Insert Icon', 'icon-manager'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                $('#tinymce-icon-pack').on('change', function() {
                    const pack = $(this).val();
                    const iconSelect = $('#tinymce-icon-name');
                    if (!pack) {
                        iconSelect.prop('disabled', true).html('<option value="">Select Icon</option>');
                        return;
                    }
                    fetch(iconManagerData.apiUrl + '/packs/' + pack + '/icons', {
                        headers: { 'X-WP-Nonce': iconManagerData.nonce }
                    })
                    .then(r => r.json())
                    .then(response => {
                        if (response.data) {
                            let options = '<option value="">Select Icon</option>';
                            response.data.forEach(icon => {
                                options += `<option value="${icon.name}">${icon.name}</option>`;
                            });
                            iconSelect.prop('disabled', false).html(options);
                        }
                    });
                });

                $('#tinymce-insert-icon').on('click', function() {
                    const pack = $('#tinymce-icon-pack').val();
                    const name = $('#tinymce-icon-name').val();
                    const size = $('#tinymce-icon-size').val();
                    const color = $('#tinymce-icon-color').val();
                    if (!pack || !name) {
                        alert('Please select an icon pack and icon');
                        return;
                    }
                    let shortcode = `[icon pack="${pack}" name="${name}"`;
                    if (size) shortcode += ` size="${size}"`;
                    if (color) shortcode += ` color="${color}"`;
                    shortcode += ']';
                    if (typeof tinymce !== 'undefined') {
                        tinymce.activeEditor.insertContent(shortcode);
                    }
                    $('.icon-manager-modal-backdrop').fadeOut();
                });

                $('.icon-manager-modal-close').on('click', function() {
                    $('.icon-manager-modal-backdrop').fadeOut();
                });
            });
        </script>
        <?php
    }
}
