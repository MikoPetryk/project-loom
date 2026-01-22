<?php
/**
 * Feedback Components
 *
 * Alert, Badge, Progress, Snackbar - user feedback elements.
 *
 * @package Loom\Core\Components
 */



namespace Loom\Core\Components;

use Loom\Core\Tokens\Colors;

// ════════════════════════════════════════════════════════════════════════════
// ALERT TYPE - Enum for alert variants
// ════════════════════════════════════════════════════════════════════════════

enum AlertType: string {
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Error = 'error';
}

// ════════════════════════════════════════════════════════════════════════════
// ALERT - Informational message
// ════════════════════════════════════════════════════════════════════════════

class Alert extends Component {

    public function __construct(
        private string $message,
        private AlertType $type = AlertType::Info,
        private ?string $title = null,
        private bool $dismissible = false,
        private ?string $onDismiss = null,
        private ?string $icon = null,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
    }

    public function render(): string {
        [$bgColor, $textColor, $borderColor] = $this->getColors();

        $mod = ($this->modifier ?? Modifier::new())
            ->flex()
            ->gap(12)
            ->padding(16)
            ->rounded(8)
            ->background($bgColor)
            ->color($textColor)
            ->border("1px solid {$borderColor}");

        $content = '';

        // Icon
        if ($this->icon) {
            $content .= $this->tag('span', $this->icon, Modifier::new()->flexShrink(0));
        } else {
            $content .= $this->tag('span', $this->getDefaultIcon(), Modifier::new()->flexShrink(0));
        }

        // Text content
        $textContent = '';
        if ($this->title) {
            $textContent .= $this->tag('strong', esc_html($this->title), Modifier::new()->style('display', 'block')->style('margin-bottom', '4px'));
        }
        $textContent .= esc_html($this->message);
        $content .= $this->tag('div', $textContent, Modifier::new()->flexGrow(1));

        // Dismiss button
        if ($this->dismissible) {
            $dismissMod = Modifier::new()
                ->background('transparent')
                ->style('border', 'none')
                ->color($textColor)
                ->cursor('pointer')
                ->opacity(0.7)
                ->padding(0);
            $dismissAttrs = ['onclick' => $this->onDismiss ?? 'this.closest(\'div\').remove()'];
            $content .= $this->tag('button', '&times;', $dismissMod, $dismissAttrs);
        }

        return $this->tag('div', $content, $mod, ['role' => 'alert']);
    }

    private function getColors(): array {
        // Returns [background, text, border] using CSS variables with fallbacks
        return match($this->type) {
            AlertType::Info => [
                'var(--loom-info-container, #eff6ff)',
                'var(--loom-on-info-container, #1e40af)',
                'var(--loom-info-border, #bfdbfe)'
            ],
            AlertType::Success => [
                'var(--loom-success-container, #f0fdf4)',
                'var(--loom-on-success-container, #166534)',
                'var(--loom-success-border, #bbf7d0)'
            ],
            AlertType::Warning => [
                'var(--loom-warning-container, #fffbeb)',
                'var(--loom-on-warning-container, #92400e)',
                'var(--loom-warning-border, #fde68a)'
            ],
            AlertType::Error => [
                Colors::errorContainer(),
                Colors::onErrorContainer(),
                'var(--loom-error-border, #fecaca)'
            ],
        };
    }

    private function getDefaultIcon(): string {
        return match($this->type) {
            AlertType::Info => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>',
            AlertType::Success => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
            AlertType::Warning => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>',
            AlertType::Error => '<svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
        };
    }
}

// ════════════════════════════════════════════════════════════════════════════
// BADGE - Small status indicator
// ════════════════════════════════════════════════════════════════════════════

class Badge extends Component {

    public function __construct(
        private string|int $content,
        private ?string $color = null,
        private ?string $textColor = null,
        private bool $dot = false,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
        $this->color = $color ?? Colors::primary();
        $this->textColor = $textColor ?? Colors::onPrimary();
    }

    public function render(): string {
        $mod = ($this->modifier ?? Modifier::new())
            ->style('display', 'inline-flex')
            ->alignItems('center')
            ->justifyContent('center')
            ->background($this->color)
            ->color($this->textColor)
            ->fontWeight(500);

        if ($this->dot) {
            $mod->size(8)->roundedFull();
            return $this->tag('span', '', $mod);
        }

        $mod->fontSize(12)
            ->padding(horizontal: 8, vertical: 2)
            ->roundedFull()
            ->style('min-width', '20px');

        return $this->tag('span', esc_html((string) $this->content), $mod);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// PROGRESS - Loading indicator
// ════════════════════════════════════════════════════════════════════════════

class Progress extends Component {

    public function __construct(
        private ?int $value = null,             // null = indeterminate
        private int $max = 100,
        private ?string $color = null,
        private int|string $height = 4,
        private bool $circular = false,
        private int $size = 40,
        private ?string $label = null,          // Accessible label for screen readers
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
        $this->color = $color ?? Colors::primary();
    }

    public function render(): string {
        if ($this->circular) {
            return $this->renderCircular();
        }
        return $this->renderLinear();
    }

    /**
     * Get ARIA attributes for accessibility
     */
    private function getAriaAttrs(): array {
        $attrs = [
            'role' => 'progressbar',
            'aria-valuemin' => '0',
            'aria-valuemax' => (string) $this->max,
        ];

        // Accessible label
        if ($this->label) {
            $attrs['aria-label'] = $this->label;
        } else {
            // Default label based on state
            $attrs['aria-label'] = $this->value !== null ? 'Progress' : 'Loading';
        }

        // Current value (omit for indeterminate)
        if ($this->value !== null) {
            $attrs['aria-valuenow'] = (string) $this->value;
        }

        return $attrs;
    }

    private function renderLinear(): string {
        $trackMod = ($this->modifier ?? Modifier::new())
            ->fillMaxWidth()
            ->height($this->height)
            ->rounded($this->height)
            ->background(Colors::border())
            ->style('overflow', 'hidden');

        $fillPercent = $this->value !== null
            ? min(100, max(0, ($this->value / $this->max) * 100))
            : 30;

        $fillMod = Modifier::new()
            ->height('100%')
            ->background($this->color)
            ->rounded($this->height)
            ->width("{$fillPercent}%");

        if ($this->value === null) {
            $fillMod->style('animation', 'loom-progress-indeterminate 1.5s ease-in-out infinite');
        }

        $fill = $this->tag('div', '', $fillMod);

        return $this->tag('div', $fill, $trackMod, $this->getAriaAttrs());
    }

    private function renderCircular(): string {
        $mod = ($this->modifier ?? Modifier::new())
            ->size($this->size);

        $strokeWidth = max(2, (int)($this->size / 10));
        $radius = ($this->size - $strokeWidth) / 2;
        $circumference = 2 * M_PI * $radius;

        $fillPercent = $this->value !== null
            ? min(100, max(0, ($this->value / $this->max) * 100))
            : 25;

        $offset = $circumference * (1 - $fillPercent / 100);

        $animation = $this->value === null
            ? 'animation="loom-progress-spin 1s linear infinite"'
            : '';

        $borderColor = Colors::border();
        $svg = <<<SVG
<svg width="{$this->size}" height="{$this->size}" viewBox="0 0 {$this->size} {$this->size}" {$animation}>
    <circle
        cx="{($this->size / 2)}"
        cy="{($this->size / 2)}"
        r="{$radius}"
        fill="none"
        stroke="{$borderColor}"
        stroke-width="{$strokeWidth}"
    />
    <circle
        cx="{($this->size / 2)}"
        cy="{($this->size / 2)}"
        r="{$radius}"
        fill="none"
        stroke="{$this->color}"
        stroke-width="{$strokeWidth}"
        stroke-linecap="round"
        stroke-dasharray="{$circumference}"
        stroke-dashoffset="{$offset}"
        transform="rotate(-90 {($this->size / 2)} {($this->size / 2)})"
    />
</svg>
SVG;

        return $this->tag('div', $svg, $mod, $this->getAriaAttrs());
    }
}

// ════════════════════════════════════════════════════════════════════════════
// CHIP - Compact element for selections/tags
// ════════════════════════════════════════════════════════════════════════════

class Chip extends Component {

    public function __construct(
        private string $label,
        private bool $selected = false,
        private ?string $onClick = null,
        private ?string $onDelete = null,
        private ?string $icon = null,
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
            ->gap(6)
            ->padding(horizontal: 12, vertical: 6)
            ->rounded(16)
            ->fontSize(14)
            ->fontWeight(500)
            ->transition('all 0.2s ease');

        if ($this->selected) {
            $mod->background($this->color)->color(Colors::onPrimary());
        } else {
            $mod->background('transparent')
                ->border("1px solid " . Colors::border())
                ->color(Colors::text());
        }

        if ($this->onClick) {
            $mod->cursor('pointer');
        }

        $content = '';

        // Icon
        if ($this->icon) {
            $content .= $this->tag('span', $this->icon, Modifier::new()->style('display', 'flex'));
        }

        // Label
        $content .= esc_html($this->label);

        // Delete button
        if ($this->onDelete) {
            $deleteMod = Modifier::new()
                ->style('display', 'flex')
                ->style('margin-left', '4px')
                ->cursor('pointer')
                ->opacity(0.7);
            $deleteIcon = '<svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';
            $content .= $this->tag('span', $deleteIcon, $deleteMod, ['onclick' => "event.stopPropagation(); {$this->onDelete}"]);
        }

        $attrs = [];
        if ($this->onClick) {
            $attrs['onclick'] = $this->onClick;
        }

        return $this->tag('span', $content, $mod, $attrs);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// TOOLTIP - Hover hint (requires JS for positioning)
// ════════════════════════════════════════════════════════════════════════════

class Tooltip extends Component {

    private ?\Closure $content;

    public function __construct(
        private string $text,
        ?\Closure $content = null,
        private string $position = 'top',       // top, bottom, left, right
        ?Modifier $modifier = null
    ) {
        $this->content = $content;
        $this->modifier = $modifier;
    }

    public function render(): string {
        $wrapperMod = ($this->modifier ?? Modifier::new())
            ->relative()
            ->style('display', 'inline-block')
            ->class('loom-tooltip-wrapper');

        $tooltipMod = Modifier::new()
            ->absolute()
            ->background(Colors::inverseSurface())
            ->color(Colors::inverseOnSurface())
            ->padding(horizontal: 8, vertical: 4)
            ->rounded(4)
            ->fontSize(12)
            ->style('white-space', 'nowrap')
            ->style('pointer-events', 'none')
            ->opacity(0)
            ->transition('opacity 0.2s ease')
            ->zIndex(1000)
            ->class('loom-tooltip');

        // Position
        $tooltipMod = match($this->position) {
            'top' => $tooltipMod->style('bottom', '100%')->style('left', '50%')->style('transform', 'translateX(-50%)')->style('margin-bottom', '8px'),
            'bottom' => $tooltipMod->style('top', '100%')->style('left', '50%')->style('transform', 'translateX(-50%)')->style('margin-top', '8px'),
            'left' => $tooltipMod->style('right', '100%')->style('top', '50%')->style('transform', 'translateY(-50%)')->style('margin-right', '8px'),
            'right' => $tooltipMod->style('left', '100%')->style('top', '50%')->style('transform', 'translateY(-50%)')->style('margin-left', '8px'),
            default => $tooltipMod,
        };

        $tooltip = $this->tag('span', esc_html($this->text), $tooltipMod);
        $childContent = self::capture($this->content);

        return $this->tag('span', $childContent . $tooltip, $wrapperMod);
    }
}
