<?php
/**
 * State Proxy
 *
 * Wraps state objects to track changes and handle actions.
 *
 * @package Loom\Core\State
 */

namespace Loom\Core\State;

use Loom\Core\Annotations\AnnotationProcessor;
use Loom\Core\Annotations\State as StateAttribute;
use Loom\Core\Annotations\Action;
use Loom\Core\Annotations\Computed;
use Loom\Core\Annotations\Observable;

class StateProxy {

    private $target;
    private $name;
    private $attribute;
    private $computedCache = [];
    private $isDirty = false;

    public function __construct($target, $name, $attribute) {
        $this->target = $target;
        $this->name = $name;
        $this->attribute = $attribute;
    }

    /**
     * Get property value
     */
    public function __get($name) {
        // Check if it's a computed property
        $computedMethods = AnnotationProcessor::getMethodsWithAttribute(
            get_class($this->target),
            Computed::class
        );

        if (isset($computedMethods[$name])) {
            return $this->getComputed($name);
        }

        return $this->target->$name ?? null;
    }

    /**
     * Set property value
     */
    public function __set($name, $value) {
        $oldValue = $this->target->$name ?? null;
        $this->target->$name = $value;

        // Check if property is observable
        $observables = AnnotationProcessor::getPropertiesWithAttribute(
            get_class($this->target),
            Observable::class
        );

        if (isset($observables[$name])) {
            $this->isDirty = true;
            $this->invalidateComputed();

            // Trigger change event
            do_action('loom_state_changed', $this->name, $name, $oldValue, $value);
        }
    }

    /**
     * Call method (action)
     */
    public function __call($method, $args) {
        // Check if it's an action
        $actions = AnnotationProcessor::getMethodsWithAttribute(
            get_class($this->target),
            Action::class
        );

        if (isset($actions[$method])) {
            return $this->executeAction($method, $args, $actions[$method]);
        }

        // Regular method call
        return $this->target->$method(...$args);
    }

    /**
     * Execute an action
     */
    public function executeAction($method, $args, $actionAttr = null) {
        if (!$actionAttr) {
            $actions = AnnotationProcessor::getMethodsWithAttribute(
                get_class($this->target),
                Action::class
            );
            $actionAttr = $actions[$method] ?? null;
        }

        // Execute the action
        $result = $this->target->$method(...$args);

        // Mark as dirty
        $this->isDirty = true;

        // Save state
        $this->persist();

        return $result;
    }

    /**
     * Get computed property value
     */
    private function getComputed($name) {
        $computed = AnnotationProcessor::getMethodsWithAttribute(
            get_class($this->target),
            Computed::class
        );

        $attr = $computed[$name];

        // Check cache
        if ($attr->cached && isset($this->computedCache[$name])) {
            return $this->computedCache[$name];
        }

        // Calculate
        $value = $this->target->$name();

        // Cache if needed
        if ($attr->cached) {
            $this->computedCache[$name] = $value;
        }

        return $value;
    }

    /**
     * Invalidate computed cache
     */
    private function invalidateComputed() {
        $this->computedCache = [];
    }

    /**
     * Persist state to storage
     */
    public function persist() {
        if (!$this->isDirty) {
            return;
        }

        $data = $this->toArray();
        StateManager::saveState($this->name, $data);
        $this->isDirty = false;
    }

    /**
     * Convert state to array
     */
    public function toArray() {
        $data = [];
        $reflection = new \ReflectionClass($this->target);

        // Get observable properties
        $observables = AnnotationProcessor::getPropertiesWithAttribute(
            get_class($this->target),
            Observable::class
        );

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            // Only include observable properties
            if (!isset($observables[$name])) {
                continue;
            }

            $property->setAccessible(true);
            $data[$name] = $property->getValue($this->target);
        }

        // Include computed values
        $computed = AnnotationProcessor::getMethodsWithAttribute(
            get_class($this->target),
            Computed::class
        );

        foreach (array_keys($computed) as $method) {
            $data[$method] = $this->getComputed($method);
        }

        return $data;
    }

    /**
     * Get underlying target
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * Check if state has a property
     */
    public function __isset($name) {
        return isset($this->target->$name);
    }
}
