<?php
/**
 * Base Component
 *
 * All Loom components extend this class.
 * Provides the content closure pattern for clean nesting.
 *
 * @package Loom\Core\Components
 */



namespace Loom\Core\Components;

abstract class Component {

    protected ?Modifier $modifier = null;

    /** @var int Counter for generating unique IDs */
    private static int $idCounter = 0;

    /**
     * Generate a unique ID for form element accessibility
     *
     * @param string $prefix Component type prefix (e.g., 'textfield', 'select')
     * @return string Unique ID like 'loom-textfield-1'
     */
    protected static function generateId(string $prefix = 'field'): string {
        return 'loom-' . $prefix . '-' . (++self::$idCounter);
    }

    /**
     * Render the component to HTML
     */
    abstract public function render(): string;

    /**
     * Echo the component (for direct use in templates)
     */
    public function __toString(): string {
        return $this->render();
    }

    /**
     * Capture content from a closure
     *
     * This is the magic that makes clean nesting work.
     * Child components echo inside the closure,
     * we capture via output buffering.
     */
    protected static function capture(?callable $content): string {
        if ($content === null) {
            return '';
        }

        ob_start();
        $content();
        return ob_get_clean() ?: '';
    }

    /**
     * Build HTML tag with attributes and content
     */
    protected function tag(string $tag, string $content = '', ?Modifier $modifier = null, array $extraAttrs = []): string {
        $attrs = [];

        if ($modifier !== null) {
            $modAttrs = $modifier->toAttributes();
            if ($modAttrs) {
                $attrs[] = $modAttrs;
            }
        }

        foreach ($extraAttrs as $name => $value) {
            if ($value === true) {
                $attrs[] = esc_attr($name);
            } elseif ($value !== false && $value !== null) {
                $attrs[] = esc_attr($name) . '="' . esc_attr($value) . '"';
            }
        }

        $attrString = implode(' ', $attrs);
        $attrString = $attrString ? " {$attrString}" : '';

        // Self-closing tags
        $selfClosing = ['br', 'hr', 'img', 'input', 'meta', 'link'];
        if (in_array($tag, $selfClosing)) {
            return "<{$tag}{$attrString} />";
        }

        return "<{$tag}{$attrString}>{$content}</{$tag}>";
    }
}
