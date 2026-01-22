<?php
/**
 * State Manager
 *
 * Manages reactive state for Loom components.
 *
 * @package Loom\Core\State
 */

namespace Loom\Core\State;

use Loom\Core\Annotations\AnnotationProcessor;
use Loom\Core\Annotations\State as StateAttribute;
use Loom\Core\Annotations\Observable;
use Loom\Core\Annotations\Computed;
use Loom\Core\Annotations\Action;
use Loom\Core\Session\SessionManager;
use Loom\Core\Container\Container;

class StateManager {

    private static $states = [];
    private static $stateData = [];
    private static $initialized = false;

    public static function init() {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        // Register state classes from container
        add_action('loom_register_state', [self::class, 'registerState']);
    }

    /**
     * Register a state class
     */
    public static function registerState($stateClass) {
        if (!class_exists($stateClass)) {
            return;
        }

        $attr = AnnotationProcessor::getAttribute($stateClass, StateAttribute::class);
        if (!$attr) {
            return;
        }

        $name = $attr->name ?? self::getStateName($stateClass);
        self::$states[$name] = [
            'class' => $stateClass,
            'attribute' => $attr,
        ];

        // Bind to container
        Container::bind($stateClass, function () use ($stateClass, $name) {
            return self::getState($name);
        });
    }

    /**
     * Get a state instance
     */
    public static function getState($name) {
        if (!isset(self::$states[$name])) {
            throw new \RuntimeException("State '{$name}' not registered");
        }

        $config = self::$states[$name];
        $stateClass = $config['class'];
        $attr = $config['attribute'];

        // Load persisted data
        $data = self::loadStateData($name, $attr);

        // Create state instance
        $state = new $stateClass();

        // Hydrate with data
        self::hydrateState($state, $data);

        // Wrap with proxy for reactivity
        return new StateProxy($state, $name, $attr);
    }

    /**
     * Load state data from storage
     */
    private static function loadStateData($name, $attr) {
        if (isset(self::$stateData[$name])) {
            return self::$stateData[$name];
        }

        $sessionId = SessionManager::getSessionId();
        $data = [];

        switch ($attr->persist) {
            case StateAttribute::PERSIST_SESSION:
                $data = StateStorage::get($sessionId, $name);
                break;
            case StateAttribute::PERSIST_DATABASE:
                $data = StateStorage::getFromDatabase($sessionId, $name);
                break;
            case StateAttribute::PERSIST_LOCAL:
                // Will be loaded from localStorage on client
                break;
        }

        self::$stateData[$name] = $data;
        return $data;
    }

    /**
     * Hydrate state object with data
     */
    private static function hydrateState($state, $data) {
        $reflection = new \ReflectionClass($state);

        foreach ($data as $prop => $value) {
            if ($reflection->hasProperty($prop)) {
                $property = $reflection->getProperty($prop);
                $property->setAccessible(true);
                $property->setValue($state, $value);
            }
        }
    }

    /**
     * Save state
     */
    public static function saveState($name, $data) {
        if (!isset(self::$states[$name])) {
            return;
        }

        $attr = self::$states[$name]['attribute'];
        $sessionId = SessionManager::getSessionId();

        self::$stateData[$name] = $data;

        switch ($attr->persist) {
            case StateAttribute::PERSIST_SESSION:
                StateStorage::save($sessionId, $name, $data);
                break;
            case StateAttribute::PERSIST_DATABASE:
                StateStorage::saveToDatabase($sessionId, $name, $data);
                break;
        }
    }

    /**
     * Get state name from class
     */
    private static function getStateName($class) {
        $parts = explode('\\', $class);
        $name = end($parts);
        return lcfirst(preg_replace('/State$/', '', $name));
    }

    /**
     * Get all registered states for hydration
     */
    public static function getHydrationData() {
        $data = [];

        foreach (self::$states as $name => $config) {
            $attr = $config['attribute'];

            // Only include states that sync to client
            if ($attr->sync) {
                $data[$name] = self::$stateData[$name] ?? [];
            }
        }

        return $data;
    }

    /**
     * Get state actions metadata for JS
     */
    public static function getActionsMetadata() {
        $metadata = [];

        foreach (self::$states as $name => $config) {
            $stateClass = $config['class'];
            $actions = AnnotationProcessor::getMethodsWithAttribute($stateClass, Action::class);

            $metadata[$name] = [];
            foreach ($actions as $method => $actionAttr) {
                $metadata[$name][$method] = [
                    'mode' => $actionAttr->mode,
                    'debounce' => $actionAttr->debounce,
                    'confirm' => $actionAttr->confirm,
                ];
            }
        }

        return $metadata;
    }
}
