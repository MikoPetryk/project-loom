<?php
/**
 * Uninstall Script
 * Runs when the plugin is uninstalled
 */



// Exit if accessed directly or not uninstalling
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Define plugin directory
define('ICON_MANAGER_ICONS_DIR', plugin_dir_path(__FILE__) . 'assets/icons/');

// Delete all icon files and directories
function icon_manager_delete_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!icon_manager_delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}

// Delete icons directory
if (file_exists(ICON_MANAGER_ICONS_DIR)) {
    icon_manager_delete_directory(ICON_MANAGER_ICONS_DIR);
}

// Delete options
delete_option('icon_manager_version');
delete_option('icon_manager_max_file_size');
delete_option('icon_manager_max_icons_per_pack');

// Delete transients
delete_transient('icon_manager_upload_errors');

// Clear any cached data
wp_cache_flush();
