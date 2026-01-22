<?php
/**
 * Containment Components
 *
 * Card, Surface - containers for content.
 *
 * @package Loom\Core\Components
 */



namespace Loom\Core\Components;

// ════════════════════════════════════════════════════════════════════════════
// SURFACE - Basic material surface
// ════════════════════════════════════════════════════════════════════════════

class Surface extends Component {

    private ?\Closure $content;

    public function __construct(
        ?\Closure $content = null,
        private int $elevation = 0,
        private string $color = 'var(--loom-surface, #ffffff)',
        private int|string $rounded = 0,
        ?Modifier $modifier = null
    ) {
        $this->content = $content;
        $this->modifier = $modifier;
    }

    public function render(): string {
        $mod = ($this->modifier ?? Modifier::new())
            ->background($this->color)
            ->rounded($this->rounded);

        if ($this->elevation > 0) {
            $mod->shadow($this->elevation);
        }

        return $this->tag('div', self::capture($this->content), $mod);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// CARD - Elevated content container
// ════════════════════════════════════════════════════════════════════════════

class Card extends Component {

    private ?\Closure $content;

    public function __construct(
        ?\Closure $content = null,
        private int $elevation = 1,
        private int|string $padding = 16,
        private int|string $rounded = 12,
        private ?string $onClick = null,
        ?Modifier $modifier = null
    ) {
        $this->content = $content;
        $this->modifier = $modifier;
    }

    public function render(): string {
        $mod = ($this->modifier ?? Modifier::new())
            ->background('var(--loom-surface, #ffffff)')
            ->rounded($this->rounded)
            ->padding($this->padding)
            ->shadow($this->elevation)
            ->transition('box-shadow 0.2s ease');

        $attrs = [];
        if ($this->onClick) {
            $mod->cursor('pointer');
            $attrs['onclick'] = $this->onClick;
        }

        return $this->tag('div', self::capture($this->content), $mod, $attrs);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// DIALOG - Modal dialog overlay
// ════════════════════════════════════════════════════════════════════════════

class Dialog extends Component {

    private ?\Closure $content;

    public function __construct(
        ?\Closure $content = null,
        private bool $open = false,
        private ?string $onClose = null,
        private int|string $maxWidth = 400,
        ?Modifier $modifier = null
    ) {
        $this->content = $content;
        $this->modifier = $modifier;
    }

    public function render(): string {
        if (!$this->open) {
            return '';
        }

        // Backdrop
        $backdropMod = Modifier::new()
            ->fixed()
            ->inset(0)
            ->background('rgba(0, 0, 0, 0.5)')
            ->flex()
            ->alignItems('center')
            ->justifyContent('center')
            ->zIndex(1000);

        $backdropAttrs = [];
        if ($this->onClose) {
            $backdropAttrs['onclick'] = $this->onClose;
        }

        // Dialog
        $dialogMod = ($this->modifier ?? Modifier::new())
            ->background('var(--loom-surface, #ffffff)')
            ->rounded(16)
            ->padding(24)
            ->maxWidth($this->maxWidth)
            ->width('90%')
            ->shadow(4);

        // Stop click propagation on dialog
        $dialogAttrs = ['onclick' => 'event.stopPropagation()'];

        $dialogContent = $this->tag('div', self::capture($this->content), $dialogMod, $dialogAttrs);

        return $this->tag('div', $dialogContent, $backdropMod, $backdropAttrs);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// SCAFFOLD - App structure
// ════════════════════════════════════════════════════════════════════════════

class Scaffold extends Component {

    private ?\Closure $content;
    private ?\Closure $topBar;
    private ?\Closure $bottomBar;
    private ?\Closure $floatingAction;

    public function __construct(
        ?\Closure $content = null,
        ?\Closure $topBar = null,
        ?\Closure $bottomBar = null,
        ?\Closure $floatingAction = null,
        ?Modifier $modifier = null
    ) {
        $this->content = $content;
        $this->topBar = $topBar;
        $this->bottomBar = $bottomBar;
        $this->floatingAction = $floatingAction;
        $this->modifier = $modifier;
    }

    public function render(): string {
        $mod = ($this->modifier ?? Modifier::new())
            ->flex()
            ->flexDirection('column')
            ->style('min-height', '100vh');

        $html = '';

        // Top bar
        if ($this->topBar) {
            $html .= self::capture($this->topBar);
        }

        // Main content (flex-grow)
        $contentMod = Modifier::new()->flexGrow(1)->relative();
        $html .= $this->tag('main', self::capture($this->content), $contentMod);

        // Floating action button
        if ($this->floatingAction) {
            $fabMod = Modifier::new()
                ->fixed()
                ->style('bottom', '24px')
                ->style('right', '24px')
                ->zIndex(100);
            $html .= $this->tag('div', self::capture($this->floatingAction), $fabMod);
        }

        // Bottom bar
        if ($this->bottomBar) {
            $html .= self::capture($this->bottomBar);
        }

        return $this->tag('div', $html, $mod);
    }
}
