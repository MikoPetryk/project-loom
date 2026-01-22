<?php
/**
 * Lightweight State Endpoint
 *
 * Handles state updates with minimal WordPress loading.
 * ~20-50ms response time vs 200-500ms for full WP.
 *
 * @package Loom\Core
 */



// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

use Loom\Core\Session\SessionManager;
use Loom\Core\State\StateManager;
use Loom\Core\State\StateStorage;
use Loom\Core\Annotations\AnnotationProcessor;
use Loom\Core\Annotations\Action;
use Loom\Core\Container\Container;

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

/**
 * Send JSON response and exit
 */
function loom_response(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 */
function loom_error(string $message, int $status = 400): void {
    loom_response([
        'success' => false,
        'error' => $message,
        'timestamp' => time(),
    ], $status);
}

// ═══════════════════════════════════════════════════════════════════════════
// REQUEST VALIDATION
// ═══════════════════════════════════════════════════════════════════════════

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    loom_error('Method not allowed', 405);
}

// Initialize session
SessionManager::start();

// Verify nonce
$nonce = $_SERVER['HTTP_X_LOOM_NONCE'] ?? '';
if (!SessionManager::verifyNonce($nonce)) {
    loom_error('Invalid or expired nonce', 403);
}

// Parse request body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    loom_error('Invalid JSON body', 400);
}

$stateName = $input['state'] ?? null;
$actionName = $input['action'] ?? null;
$payload = $input['payload'] ?? [];

if (!$stateName || !$actionName) {
    loom_error('Missing state or action parameter', 400);
}

// ═══════════════════════════════════════════════════════════════════════════
// LOAD STATE CLASS
// ═══════════════════════════════════════════════════════════════════════════

// Get state class from registry
$stateRegistry = get_option('loom_state_registry', []);
$stateClass = $stateRegistry[$stateName] ?? null;

if (!$stateClass || !class_exists($stateClass)) {
    loom_error("State '{$stateName}' not found", 404);
}

// ═══════════════════════════════════════════════════════════════════════════
// VALIDATE ACTION
// ═══════════════════════════════════════════════════════════════════════════

$actions = AnnotationProcessor::getMethodsWithAttribute($stateClass, Action::class);

if (!isset($actions[$actionName])) {
    loom_error("Action '{$actionName}' not found on state '{$stateName}'", 404);
}

$actionAttr = $actions[$actionName];

// Check if action requires server-side execution
if ($actionAttr->mode === Action::MODE_CLIENT) {
    loom_error("Action '{$actionName}' is client-side only", 400);
}

// ═══════════════════════════════════════════════════════════════════════════
// LOAD STATE DATA
// ═══════════════════════════════════════════════════════════════════════════

$sessionId = SessionManager::getSessionId();
$stateData = StateStorage::get($sessionId, $stateName);

// Create state instance
$state = new $stateClass();

// Hydrate with existing data
$reflection = new ReflectionClass($state);
foreach ($stateData as $prop => $value) {
    if ($reflection->hasProperty($prop)) {
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        $property->setValue($state, $value);
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// EXECUTE ACTION
// ═══════════════════════════════════════════════════════════════════════════

try {
    // Build action arguments
    $method = $reflection->getMethod($actionName);
    $params = $method->getParameters();
    $args = [];

    foreach ($params as $param) {
        $paramName = $param->getName();

        if (isset($payload[$paramName])) {
            $args[] = $payload[$paramName];
        } elseif ($param->isDefaultValueAvailable()) {
            $args[] = $param->getDefaultValue();
        } elseif ($param->allowsNull()) {
            $args[] = null;
        } else {
            loom_error("Missing required parameter: {$paramName}", 400);
        }
    }

    // Execute
    $result = $method->invoke($state, ...$args);

    // Collect new state
    $newStateData = [];
    foreach ($reflection->getProperties() as $property) {
        $property->setAccessible(true);
        $newStateData[$property->getName()] = $property->getValue($state);
    }

    // Save state
    StateStorage::save($sessionId, $stateName, $newStateData);

    // Broadcast event for real-time updates
    do_action('loom_state_updated', $stateName, $newStateData, $sessionId);

    // Success response
    loom_response([
        'success' => true,
        'state' => $newStateData,
        'result' => $result,
        'timestamp' => time(),
    ]);

} catch (\Throwable $e) {
    loom_error($e->getMessage(), 500);
}
