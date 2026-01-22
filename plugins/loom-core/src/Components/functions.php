<?php
/**
 * Loom Component Functions
 *
 * Global functions for clean component API.
 * All functions auto-echo by default.
 *
 * Usage:
 *   Column(gap: 16, content: function() {
 *       Text('Hello', style: TextStyle::H1);
 *       Button('Click me');
 *   });
 *
 * @package Loom\Core\Components
 */



use Loom\Core\Components\Modifier;
use Loom\Core\Components\TextStyle;
use Loom\Core\Components\ButtonStyle;
use Loom\Core\Components\AlertType;

// ════════════════════════════════════════════════════════════════════════════
// FOUNDATION
// ════════════════════════════════════════════════════════════════════════════

function Column(
    ?\Closure $content = null,
    int|string $gap = 0,
    string $align = 'stretch',
    string $justify = 'start',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Column($content, $gap, $align, $justify, $modifier);
}

function Row(
    ?\Closure $content = null,
    int|string $gap = 0,
    string $align = 'center',
    string $justify = 'start',
    bool $wrap = false,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Row($content, $gap, $align, $justify, $wrap, $modifier);
}

function Box(
    ?\Closure $content = null,
    string $align = 'top-start',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Box($content, $align, $modifier);
}

function Spacer(
    int|string|null $size = null,
    int|string|null $width = null,
    int|string|null $height = null,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Spacer($size, $width, $height, $modifier);
}

function Divider(
    bool $vertical = false,
    string $color = 'var(--loom-border, #e2e8f0)',
    int|string $thickness = 1,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Divider($vertical, $color, $thickness, $modifier);
}

// ════════════════════════════════════════════════════════════════════════════
// BASIC
// ════════════════════════════════════════════════════════════════════════════

function Text(
    string $text,
    ?TextStyle $style = null,
    ?string $color = null,
    string $tag = 'span',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Text($text, $style, $color, $tag, $modifier);
}

function Button(
    string $text,
    ButtonStyle $style = ButtonStyle::Filled,
    ?string $onClick = null,
    ?string $href = null,
    ?string $icon = null,
    bool $disabled = false,
    string $color = 'var(--loom-primary, #6366f1)',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Button($text, $style, $onClick, $href, $icon, $disabled, $color, $modifier);
}

function IconButton(
    string $icon,
    ?string $onClick = null,
    ?string $href = null,
    int $size = 40,
    bool $disabled = false,
    string $color = 'var(--loom-text, #1a1a1a)',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\IconButton($icon, $onClick, $href, $size, $disabled, $color, $modifier);
}

function Image(
    string $src,
    string $alt = '',
    int|string|null $width = null,
    int|string|null $height = null,
    string $fit = 'cover',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Image($src, $alt, $width, $height, $fit, $modifier);
}

// ════════════════════════════════════════════════════════════════════════════
// CONTAINMENT
// ════════════════════════════════════════════════════════════════════════════

function Surface(
    ?\Closure $content = null,
    int $elevation = 0,
    string $color = 'var(--loom-surface, #ffffff)',
    int|string $rounded = 0,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Surface($content, $elevation, $color, $rounded, $modifier);
}

function Card(
    ?\Closure $content = null,
    int $elevation = 1,
    int|string $padding = 16,
    int|string $rounded = 12,
    ?string $onClick = null,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Card($content, $elevation, $padding, $rounded, $onClick, $modifier);
}

function Dialog(
    ?\Closure $content = null,
    bool $open = false,
    ?string $onClose = null,
    int|string $maxWidth = 400,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Dialog($content, $open, $onClose, $maxWidth, $modifier);
}

function Scaffold(
    ?\Closure $content = null,
    ?\Closure $topBar = null,
    ?\Closure $bottomBar = null,
    ?\Closure $floatingAction = null,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Scaffold($content, $topBar, $bottomBar, $floatingAction, $modifier);
}

// ════════════════════════════════════════════════════════════════════════════
// FEEDBACK
// ════════════════════════════════════════════════════════════════════════════

function Alert(
    string $message,
    AlertType $type = AlertType::Info,
    ?string $title = null,
    bool $dismissible = false,
    ?string $onDismiss = null,
    ?string $icon = null,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Alert($message, $type, $title, $dismissible, $onDismiss, $icon, $modifier);
}

function Badge(
    string|int $content,
    string $color = 'var(--loom-primary, #6366f1)',
    string $textColor = 'white',
    bool $dot = false,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Badge($content, $color, $textColor, $dot, $modifier);
}

function Progress(
    ?int $value = null,
    int $max = 100,
    string $color = 'var(--loom-primary, #6366f1)',
    int|string $height = 4,
    bool $circular = false,
    int $size = 40,
    ?string $label = null,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Progress($value, $max, $color, $height, $circular, $size, $label, $modifier);
}

function Chip(
    string $label,
    bool $selected = false,
    ?string $onClick = null,
    ?string $onDelete = null,
    ?string $icon = null,
    string $color = 'var(--loom-primary, #6366f1)',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Chip($label, $selected, $onClick, $onDelete, $icon, $color, $modifier);
}

function Tooltip(
    string $text,
    ?\Closure $content = null,
    string $position = 'top',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Tooltip($text, $content, $position, $modifier);
}

// ════════════════════════════════════════════════════════════════════════════
// INPUT
// ════════════════════════════════════════════════════════════════════════════

function TextField(
    ?string $value = null,
    ?string $label = null,
    ?string $placeholder = null,
    ?string $name = null,
    ?string $id = null,
    string $type = 'text',
    bool $required = false,
    bool $disabled = false,
    ?string $error = null,
    ?string $helper = null,
    ?string $onChange = null,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\TextField($value, $label, $placeholder, $name, $id, $type, $required, $disabled, $error, $helper, $onChange, $modifier);
}

function TextArea(
    ?string $value = null,
    ?string $label = null,
    ?string $placeholder = null,
    ?string $name = null,
    ?string $id = null,
    int $rows = 4,
    bool $required = false,
    bool $disabled = false,
    ?string $error = null,
    ?string $helper = null,
    ?string $onChange = null,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\TextArea($value, $label, $placeholder, $name, $id, $rows, $required, $disabled, $error, $helper, $onChange, $modifier);
}

function Checkbox(
    ?string $label = null,
    bool $checked = false,
    ?string $name = null,
    ?string $value = null,
    bool $disabled = false,
    ?string $onChange = null,
    string $color = 'var(--loom-primary, #6366f1)',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Checkbox($label, $checked, $name, $value, $disabled, $onChange, $color, $modifier);
}

function Switch_(
    ?string $label = null,
    bool $checked = false,
    ?string $name = null,
    ?string $id = null,
    bool $disabled = false,
    ?string $onChange = null,
    string $color = 'var(--loom-primary, #6366f1)',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Switch_($label, $checked, $name, $id, $disabled, $onChange, $color, $modifier);
}

function Slider(
    int|float $value = 50,
    int|float $min = 0,
    int|float $max = 100,
    int|float $step = 1,
    ?string $label = null,
    ?string $name = null,
    ?string $id = null,
    bool $disabled = false,
    bool $showValue = false,
    ?string $onChange = null,
    string $color = 'var(--loom-primary, #6366f1)',
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Slider($value, $min, $max, $step, $label, $name, $id, $disabled, $showValue, $onChange, $color, $modifier);
}

function Select(
    array $options,
    ?string $value = null,
    ?string $label = null,
    ?string $placeholder = null,
    ?string $name = null,
    ?string $id = null,
    bool $required = false,
    bool $disabled = false,
    ?string $error = null,
    ?string $onChange = null,
    ?Modifier $modifier = null
): void {
    echo new \Loom\Core\Components\Select($options, $value, $label, $placeholder, $name, $id, $required, $disabled, $error, $onChange, $modifier);
}

// ════════════════════════════════════════════════════════════════════════════
// HEAD - Page metadata
// ════════════════════════════════════════════════════════════════════════════

/**
 * Set page metadata declaratively
 *
 * @param string|null $description Page description for SEO
 * @param string|null $title Override page title
 * @param string|null $keywords Meta keywords
 * @param string|null $author Page author
 * @param string|null $robots Robots directives
 * @param string|null $canonical Canonical URL
 * @param string|null $ogTitle Open Graph title
 * @param string|null $ogDescription Open Graph description
 * @param string|null $ogImage Open Graph image URL
 * @param string|null $ogType Open Graph type (website, article, etc.)
 * @param string|null $twitterCard Twitter card type
 * @param string|null $themeColor Theme color for mobile browsers
 */
function Head(
    ?string $description = null,
    ?string $title = null,
    ?string $keywords = null,
    ?string $author = null,
    ?string $robots = null,
    ?string $canonical = null,
    ?string $ogTitle = null,
    ?string $ogDescription = null,
    ?string $ogImage = null,
    ?string $ogType = null,
    ?string $twitterCard = null,
    ?string $themeColor = null
): void {
    \Loom\Core\Components\Head::set(
        title: $title,
        description: $description,
        keywords: $keywords,
        author: $author,
        robots: $robots,
        canonical: $canonical,
        ogTitle: $ogTitle,
        ogDescription: $ogDescription,
        ogImage: $ogImage,
        ogType: $ogType,
        twitterCard: $twitterCard,
        themeColor: $themeColor
    );
}
