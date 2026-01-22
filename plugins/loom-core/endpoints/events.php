<?php
/**
 * Server-Sent Events Endpoint
 *
 * Provides real-time updates to clients via SSE.
 *
 * @package Loom\Core
 */



// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use Loom\Core\Session\SessionManager;

// SSE Headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering

// Initialize session
SessionManager::start();
$sessionId = SessionManager::getSessionId();

if (!$sessionId) {
    echo "event: error\n";
    echo "data: " . json_encode(['error' => 'No session']) . "\n\n";
    exit;
}

// Get channels to subscribe to
$channels = isset($_GET['channels'])
    ? array_map('sanitize_text_field', explode(',', $_GET['channels']))
    : ['state'];

// Get last event ID for resumption
$lastEventId = isset($_SERVER['HTTP_LAST_EVENT_ID'])
    ? (int)$_SERVER['HTTP_LAST_EVENT_ID']
    : 0;

// Keep connection alive
set_time_limit(0);
ignore_user_abort(false);

// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}
ob_implicit_flush(true);

// Send initial connection message
echo "event: connected\n";
echo "data: " . json_encode([
    'session' => $sessionId,
    'channels' => $channels,
    'timestamp' => time(),
]) . "\n\n";
flush();

$lastCheck = $lastEventId;
$heartbeatInterval = 30; // seconds
$lastHeartbeat = time();

// Event loop
while (true) {
    // Check for new events
    global $wpdb;

    $channelPlaceholders = implode(',', array_fill(0, count($channels), '%s'));

    $events = $wpdb->get_results($wpdb->prepare(
        "SELECT id, channel, data, created_at
         FROM {$wpdb->prefix}loom_events
         WHERE id > %d
         AND channel IN ({$channelPlaceholders})
         ORDER BY id ASC
         LIMIT 50",
        array_merge([$lastCheck], $channels)
    ));

    foreach ($events as $event) {
        // Filter events for this session if needed
        $eventData = json_decode($event->data, true);

        // Check if event is relevant to this session
        if (isset($eventData['_session']) && $eventData['_session'] !== $sessionId) {
            // Skip events for other sessions (unless broadcast)
            if (!isset($eventData['_broadcast']) || !$eventData['_broadcast']) {
                $lastCheck = $event->id;
                continue;
            }
        }

        // Send event
        echo "id: {$event->id}\n";
        echo "event: {$event->channel}\n";
        echo "data: {$event->data}\n\n";

        $lastCheck = $event->id;
    }

    // Heartbeat to keep connection alive
    if (time() - $lastHeartbeat >= $heartbeatInterval) {
        echo "event: heartbeat\n";
        echo "data: " . json_encode(['timestamp' => time()]) . "\n\n";
        $lastHeartbeat = time();
    }

    // Flush output
    flush();

    // Check if client disconnected
    if (connection_aborted()) {
        break;
    }

    // Wait before next check
    sleep(1);
}

// Cleanup
exit;
