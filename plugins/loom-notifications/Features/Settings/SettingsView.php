<?php
/**
 * Settings View
 *
 * @package Loom\Noti\Features\Settings
 * @since 1.0.0
 */



if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1>Noti - Notifications</h1>
    <form action="options.php" method="post">
        <?php
        settings_fields('noti_plugin_group');
        do_settings_sections('noti_plugin');
        submit_button();
        ?>
    </form>
    <h2>Usage</h2>
    <h3>PHP API</h3>
    <pre><code>use Loom\Noti\Features\Notifications\Noti;

Noti::success('Item saved!');
Noti::error('Something went wrong');
Noti::warning('Please review');
Noti::info('FYI');

// With options
Noti::success('Profile updated', [
    'icon' => 'General::Check',
    'duration' => 3000,
    'actions' => [
        ['label' => 'View', 'url' => '/profile']
    ]
]);

// Progress notification
$progress = Noti::progress('Uploading...', ['total' => 100]);
$progress->update(['current' => 50]);
$progress->complete('Upload complete!');</code></pre>
    <h3>JavaScript API</h3>
    <pre><code>Noti.success('Saved!');
Noti.error('Error occurred');
Noti.warning('Warning message');
Noti.info('Info message');

// With options
Noti.success('Done!', {
    icon: 'General::Check',
    duration: 3000,
    actions: [
        { label: 'Undo', callback: undoFunction }
    ]
});</code></pre>
</div>
