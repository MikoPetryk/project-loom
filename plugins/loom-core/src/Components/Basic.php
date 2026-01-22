<?php
/**
 * Basic Components
 *
 * Text, Button, Icon - fundamental UI elements.
 *
 * @package Loom\Core\Components
 */



namespace Loom\Core\Components;

use Loom\Core\Tokens\Colors;

// ════════════════════════════════════════════════════════════════════════════
// TEXT STYLE - Enum for typography presets
// ════════════════════════════════════════════════════════════════════════════

enum TextStyle: string {
    case H1 = 'h1';
    case H2 = 'h2';
    case H3 = 'h3';
    case H4 = 'h4';
    case H5 = 'h5';
    case H6 = 'h6';
    case Body = 'body';
    case BodyLarge = 'body-lg';
    case BodySmall = 'body-sm';
    case Caption = 'caption';
    case Label = 'label';
    case Overline = 'overline';
}

// ════════════════════════════════════════════════════════════════════════════
// TEXT - Display text with optional styling
// ════════════════════════════════════════════════════════════════════════════

class Text extends Component {

    public function __construct(
        private string $text,
        private ?TextStyle $style = null,
        private ?string $color = null,
        private string $tag = 'span',
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
    }

    public function render(): string {
        $mod = $this->modifier ?? Modifier::new();

        // Apply text style
        if ($this->style !== null) {
            $mod = $this->applyStyle($mod, $this->style);
        }

        // Apply color
        if ($this->color !== null) {
            $mod->color($this->color);
        }

        // Determine tag from style if not set
        $tag = $this->tag;
        if ($this->style !== null && $this->tag === 'span') {
            $tag = $this->getTagForStyle($this->style);
        }

        return $this->tag($tag, esc_html($this->text), $mod);
    }

    private function applyStyle(Modifier $mod, TextStyle $style): Modifier {
        return match($style) {
            TextStyle::H1 => $mod->fontSize(36)->fontWeight(700)->lineHeight(1.2),
            TextStyle::H2 => $mod->fontSize(30)->fontWeight(600)->lineHeight(1.25),
            TextStyle::H3 => $mod->fontSize(24)->fontWeight(600)->lineHeight(1.3),
            TextStyle::H4 => $mod->fontSize(20)->fontWeight(500)->lineHeight(1.35),
            TextStyle::H5 => $mod->fontSize(18)->fontWeight(500)->lineHeight(1.4),
            TextStyle::H6 => $mod->fontSize(16)->fontWeight(500)->lineHeight(1.4),
            TextStyle::Body => $mod->fontSize(16)->fontWeight(400)->lineHeight(1.5),
            TextStyle::BodyLarge => $mod->fontSize(18)->fontWeight(400)->lineHeight(1.5),
            TextStyle::BodySmall => $mod->fontSize(14)->fontWeight(400)->lineHeight(1.5),
            TextStyle::Caption => $mod->fontSize(12)->fontWeight(400)->lineHeight(1.4),
            TextStyle::Label => $mod->fontSize(14)->fontWeight(500)->lineHeight(1.4),
            TextStyle::Overline => $mod->fontSize(12)->fontWeight(500)->lineHeight(1.4)
                ->style('text-transform', 'uppercase')
                ->style('letter-spacing', '0.5px'),
        };
    }

    private function getTagForStyle(TextStyle $style): string {
        return match($style) {
            TextStyle::H1 => 'h1',
            TextStyle::H2 => 'h2',
            TextStyle::H3 => 'h3',
            TextStyle::H4 => 'h4',
            TextStyle::H5 => 'h5',
            TextStyle::H6 => 'h6',
            default => 'p',
        };
    }
}

// ════════════════════════════════════════════════════════════════════════════
// BUTTON STYLE - Enum for button variants
// ════════════════════════════════════════════════════════════════════════════

enum ButtonStyle: string {
    case Filled = 'filled';
    case Outlined = 'outlined';
    case Text = 'text';
    case Tonal = 'tonal';
}

// ════════════════════════════════════════════════════════════════════════════
// BUTTON - Clickable action element
// ════════════════════════════════════════════════════════════════════════════

class Button extends Component {

    public function __construct(
        private string $text,
        private ButtonStyle $style = ButtonStyle::Filled,
        private ?string $onClick = null,
        private ?string $href = null,
        private ?string $icon = null,
        private bool $disabled = false,
        private ?string $color = null,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
        $this->color = $color ?? Colors::primary();
    }

    public function render(): string {
        $mod = ($this->modifier ?? Modifier::new())
            ->style('display', 'inline-flex')
            ->alignItems('center')
            ->justifyContent('center')
            ->gap(8)
            ->padding(horizontal: 16, vertical: 10)
            ->rounded(8)
            ->fontSize(14)
            ->fontWeight(500)
            ->cursor($this->disabled ? 'not-allowed' : 'pointer')
            ->transition('all 0.2s ease')
            ->style('border', 'none')
            ->style('outline', 'none')
            ->style('text-decoration', 'none');

        // Apply style variant
        $mod = $this->applyButtonStyle($mod);

        // Disabled state
        if ($this->disabled) {
            $mod->opacity(0.5);
        }

        // Build content
        $content = '';
        if ($this->icon) {
            $content .= $this->icon;
        }
        $content .= esc_html($this->text);

        // Attributes
        $attrs = [];
        if ($this->disabled) {
            $attrs['disabled'] = true;
        }
        if ($this->onClick && !$this->disabled) {
            $attrs['onclick'] = $this->onClick;
        }

        // Link or button?
        if ($this->href && !$this->disabled) {
            $attrs['href'] = $this->href;
            return $this->tag('a', $content, $mod, $attrs);
        }

        $attrs['type'] = 'button';
        return $this->tag('button', $content, $mod, $attrs);
    }

    private function applyButtonStyle(Modifier $mod): Modifier {
        return match($this->style) {
            ButtonStyle::Filled => $mod
                ->background($this->color)
                ->color(Colors::onPrimary()),

            ButtonStyle::Outlined => $mod
                ->background('transparent')
                ->color($this->color)
                ->border("2px solid {$this->color}"),

            ButtonStyle::Text => $mod
                ->background('transparent')
                ->color($this->color)
                ->padding(horizontal: 8, vertical: 6),

            ButtonStyle::Tonal => $mod
                ->background("{$this->color}20")
                ->color($this->color),
        };
    }
}

// ════════════════════════════════════════════════════════════════════════════
// ICON BUTTON - Icon-only button
// ════════════════════════════════════════════════════════════════════════════

class IconButton extends Component {

    public function __construct(
        private string $icon,
        private ?string $onClick = null,
        private ?string $href = null,
        private int $size = 40,
        private bool $disabled = false,
        private ?string $color = null,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
        $this->color = $color ?? Colors::text();
    }

    public function render(): string {
        $mod = ($this->modifier ?? Modifier::new())
            ->style('display', 'inline-flex')
            ->alignItems('center')
            ->justifyContent('center')
            ->size($this->size)
            ->roundedFull()
            ->background('transparent')
            ->color($this->color)
            ->cursor($this->disabled ? 'not-allowed' : 'pointer')
            ->transition('background 0.2s ease')
            ->style('border', 'none')
            ->style('outline', 'none');

        if ($this->disabled) {
            $mod->opacity(0.5);
        }

        $attrs = [];
        if ($this->disabled) {
            $attrs['disabled'] = true;
        }
        if ($this->onClick && !$this->disabled) {
            $attrs['onclick'] = $this->onClick;
        }
        $attrs['aria-label'] = 'icon button';

        if ($this->href && !$this->disabled) {
            $attrs['href'] = $this->href;
            return $this->tag('a', $this->icon, $mod, $attrs);
        }

        $attrs['type'] = 'button';
        return $this->tag('button', $this->icon, $mod, $attrs);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// IMAGE - Display images
// ════════════════════════════════════════════════════════════════════════════

class Image extends Component {

    public function __construct(
        private string $src,
        private string $alt = '',
        private int|string|null $width = null,
        private int|string|null $height = null,
        private string $fit = 'cover',          // cover, contain, fill, none
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
    }

    public function render(): string {
        $mod = $this->modifier ?? Modifier::new();

        if ($this->width !== null) {
            $mod->width($this->width);
        }
        if ($this->height !== null) {
            $mod->height($this->height);
        }

        $mod->style('object-fit', $this->fit);

        $attrs = [
            'src' => $this->src,
            'alt' => $this->alt,
            'loading' => 'lazy',
        ];

        return $this->tag('img', '', $mod, $attrs);
    }
}
