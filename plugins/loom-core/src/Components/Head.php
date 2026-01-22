<?php
/**
 * Head Component
 *
 * Manages page metadata declaratively.
 * Components can add metadata that gets collected and rendered in <head>.
 *
 * Usage:
 *   Head(description: 'Page description', ogImage: '/path/to/image.jpg');
 *
 * @package Loom\Core\Components
 */



namespace Loom\Core\Components;

class Head {

    /** @var array<string, mixed> Collected metadata */
    private static array $metadata = [];

    /** @var array<string> Custom meta tags */
    private static array $customMeta = [];

    /** @var array<string> Link tags (preload, prefetch, etc.) */
    private static array $links = [];

    /** @var bool Whether metadata has been rendered */
    private static bool $rendered = false;

    /**
     * Set page metadata
     */
    public static function set(
        ?string $title = null,
        ?string $description = null,
        ?string $keywords = null,
        ?string $author = null,
        ?string $robots = null,
        ?string $canonical = null,
        ?string $ogTitle = null,
        ?string $ogDescription = null,
        ?string $ogImage = null,
        ?string $ogType = null,
        ?string $ogUrl = null,
        ?string $twitterCard = null,
        ?string $twitterSite = null,
        ?string $themeColor = null
    ): void {
        if ($title !== null) self::$metadata['title'] = $title;
        if ($description !== null) self::$metadata['description'] = $description;
        if ($keywords !== null) self::$metadata['keywords'] = $keywords;
        if ($author !== null) self::$metadata['author'] = $author;
        if ($robots !== null) self::$metadata['robots'] = $robots;
        if ($canonical !== null) self::$metadata['canonical'] = $canonical;
        if ($ogTitle !== null) self::$metadata['og:title'] = $ogTitle;
        if ($ogDescription !== null) self::$metadata['og:description'] = $ogDescription;
        if ($ogImage !== null) self::$metadata['og:image'] = $ogImage;
        if ($ogType !== null) self::$metadata['og:type'] = $ogType;
        if ($ogUrl !== null) self::$metadata['og:url'] = $ogUrl;
        if ($twitterCard !== null) self::$metadata['twitter:card'] = $twitterCard;
        if ($twitterSite !== null) self::$metadata['twitter:site'] = $twitterSite;
        if ($themeColor !== null) self::$metadata['theme-color'] = $themeColor;
    }

    /**
     * Add a custom meta tag
     */
    public static function addMeta(string $name, string $content, bool $isProperty = false): void {
        $attr = $isProperty ? 'property' : 'name';
        self::$customMeta[] = '<meta ' . $attr . '="' . esc_attr($name) . '" content="' . esc_attr($content) . '">';
    }

    /**
     * Add a link tag (preload, prefetch, etc.)
     */
    public static function addLink(string $rel, string $href, ?string $as = null, ?string $type = null, bool $crossorigin = false): void {
        $attrs = 'rel="' . esc_attr($rel) . '" href="' . esc_attr($href) . '"';
        if ($as !== null) $attrs .= ' as="' . esc_attr($as) . '"';
        if ($type !== null) $attrs .= ' type="' . esc_attr($type) . '"';
        if ($crossorigin) $attrs .= ' crossorigin';
        self::$links[] = '<link ' . $attrs . '>';
    }

    /**
     * Preload a resource
     */
    public static function preload(string $href, string $as, ?string $type = null): void {
        self::addLink('preload', $href, $as, $type);
    }

    /**
     * Prefetch a resource
     */
    public static function prefetch(string $href): void {
        self::addLink('prefetch', $href);
    }

    /**
     * Get a specific metadata value
     */
    public static function get(string $key, mixed $default = null): mixed {
        return self::$metadata[$key] ?? $default;
    }

    /**
     * Check if metadata has been set
     */
    public static function has(string $key): bool {
        return isset(self::$metadata[$key]);
    }

    /**
     * Render all metadata as HTML
     * Call this in your layout's <head> section
     */
    public static function render(): string {
        if (self::$rendered) {
            return '';
        }
        self::$rendered = true;

        $output = [];

        // Basic meta tags
        if (isset(self::$metadata['description'])) {
            $output[] = '<meta name="description" content="' . esc_attr(self::$metadata['description']) . '">';
        }
        if (isset(self::$metadata['keywords'])) {
            $output[] = '<meta name="keywords" content="' . esc_attr(self::$metadata['keywords']) . '">';
        }
        if (isset(self::$metadata['author'])) {
            $output[] = '<meta name="author" content="' . esc_attr(self::$metadata['author']) . '">';
        }
        if (isset(self::$metadata['robots'])) {
            $output[] = '<meta name="robots" content="' . esc_attr(self::$metadata['robots']) . '">';
        }
        if (isset(self::$metadata['theme-color'])) {
            $output[] = '<meta name="theme-color" content="' . esc_attr(self::$metadata['theme-color']) . '">';
        }

        // Canonical URL
        if (isset(self::$metadata['canonical'])) {
            $output[] = '<link rel="canonical" href="' . esc_url(self::$metadata['canonical']) . '">';
        }

        // Open Graph
        $ogTags = ['og:title', 'og:description', 'og:image', 'og:type', 'og:url'];
        foreach ($ogTags as $tag) {
            if (isset(self::$metadata[$tag])) {
                $value = $tag === 'og:url' || $tag === 'og:image'
                    ? esc_url(self::$metadata[$tag])
                    : esc_attr(self::$metadata[$tag]);
                $output[] = '<meta property="' . $tag . '" content="' . $value . '">';
            }
        }

        // Twitter Card
        $twitterTags = ['twitter:card', 'twitter:site'];
        foreach ($twitterTags as $tag) {
            if (isset(self::$metadata[$tag])) {
                $output[] = '<meta name="' . $tag . '" content="' . esc_attr(self::$metadata[$tag]) . '">';
            }
        }

        // Custom meta tags
        $output = array_merge($output, self::$customMeta);

        // Link tags
        $output = array_merge($output, self::$links);

        return implode("\n    ", $output);
    }

    /**
     * Reset metadata (useful for testing)
     */
    public static function reset(): void {
        self::$metadata = [];
        self::$customMeta = [];
        self::$links = [];
        self::$rendered = false;
    }

    /**
     * Get the page title with optional suffix
     */
    public static function getTitle(?string $suffix = null): string {
        $title = self::$metadata['title'] ?? '';
        if ($suffix && $title) {
            return $title . ' - ' . $suffix;
        }
        return $title ?: $suffix ?: '';
    }
}
