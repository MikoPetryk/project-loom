<?php
/**
 * Foundation Components
 *
 * Basic layout primitives inspired by Jetpack Compose.
 * Column, Row, Box, Spacer - the building blocks.
 *
 * @package Loom\Core\Components
 */



namespace Loom\Core\Components;

// ════════════════════════════════════════════════════════════════════════════
// COLUMN - Vertical layout
// ════════════════════════════════════════════════════════════════════════════

class Column extends Component {

    private ?\Closure $content;

    public function __construct(
        ?\Closure $content = null,
        private int|string $gap = 0,
        private string $align = 'stretch',      // start, center, end, stretch
        private string $justify = 'start',      // start, center, end, between, around
        ?Modifier $modifier = null
    ) {
        $this->content = $content;
        $this->modifier = $modifier;
    }

    public function render(): string {
        $mod = ($this->modifier ?? Modifier::new())
            ->flex()
            ->flexDirection('column')
            ->gap($this->gap)
            ->alignItems($this->mapAlign($this->align))
            ->justifyContent($this->mapJustify($this->justify));

        return $this->tag('div', self::capture($this->content), $mod);
    }

    private function mapAlign(string $align): string {
        return match($align) {
            'start' => 'flex-start',
            'end' => 'flex-end',
            'center' => 'center',
            'stretch' => 'stretch',
            default => $align,
        };
    }

    private function mapJustify(string $justify): string {
        return match($justify) {
            'start' => 'flex-start',
            'end' => 'flex-end',
            'center' => 'center',
            'between' => 'space-between',
            'around' => 'space-around',
            'evenly' => 'space-evenly',
            default => $justify,
        };
    }
}

// ════════════════════════════════════════════════════════════════════════════
// ROW - Horizontal layout
// ════════════════════════════════════════════════════════════════════════════

class Row extends Component {

    private ?\Closure $content;

    public function __construct(
        ?\Closure $content = null,
        private int|string $gap = 0,
        private string $align = 'center',       // start, center, end, stretch
        private string $justify = 'start',      // start, center, end, between, around
        private bool $wrap = false,
        ?Modifier $modifier = null
    ) {
        $this->content = $content;
        $this->modifier = $modifier;
    }

    public function render(): string {
        $mod = ($this->modifier ?? Modifier::new())
            ->flex()
            ->flexDirection('row')
            ->gap($this->gap)
            ->alignItems($this->mapAlign($this->align))
            ->justifyContent($this->mapJustify($this->justify));

        if ($this->wrap) {
            $mod->flexWrap('wrap');
        }

        return $this->tag('div', self::capture($this->content), $mod);
    }

    private function mapAlign(string $align): string {
        return match($align) {
            'start' => 'flex-start',
            'end' => 'flex-end',
            'center' => 'center',
            'stretch' => 'stretch',
            'baseline' => 'baseline',
            default => $align,
        };
    }

    private function mapJustify(string $justify): string {
        return match($justify) {
            'start' => 'flex-start',
            'end' => 'flex-end',
            'center' => 'center',
            'between' => 'space-between',
            'around' => 'space-around',
            'evenly' => 'space-evenly',
            default => $justify,
        };
    }
}

// ════════════════════════════════════════════════════════════════════════════
// BOX - Stack/layer elements
// ════════════════════════════════════════════════════════════════════════════

class Box extends Component {

    private ?\Closure $content;

    public function __construct(
        ?\Closure $content = null,
        private string $align = 'top-start',    // top-start, top-center, center, bottom-end, etc.
        ?Modifier $modifier = null
    ) {
        $this->content = $content;
        $this->modifier = $modifier;
    }

    public function render(): string {
        $mod = ($this->modifier ?? Modifier::new())
            ->relative()
            ->style('display', 'grid');

        // Map alignment to CSS grid
        [$justify, $alignItems] = $this->mapAlignment($this->align);
        $mod->justifyContent($justify)->alignItems($alignItems);

        return $this->tag('div', self::capture($this->content), $mod);
    }

    private function mapAlignment(string $align): array {
        return match($align) {
            'top-start' => ['start', 'start'],
            'top-center' => ['center', 'start'],
            'top-end' => ['end', 'start'],
            'center-start' => ['start', 'center'],
            'center' => ['center', 'center'],
            'center-end' => ['end', 'center'],
            'bottom-start' => ['start', 'end'],
            'bottom-center' => ['center', 'end'],
            'bottom-end' => ['end', 'end'],
            default => ['start', 'start'],
        };
    }
}

// ════════════════════════════════════════════════════════════════════════════
// SPACER - Empty space
// ════════════════════════════════════════════════════════════════════════════

class Spacer extends Component {

    public function __construct(
        private int|string|null $size = null,
        private int|string|null $width = null,
        private int|string|null $height = null,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
    }

    public function render(): string {
        $mod = $this->modifier ?? Modifier::new();

        if ($this->size !== null) {
            $mod->size($this->size);
        } else {
            if ($this->width !== null) {
                $mod->width($this->width);
            }
            if ($this->height !== null) {
                $mod->height($this->height);
            }
        }

        // If no explicit size, fill available space (flex: 1)
        if ($this->size === null && $this->width === null && $this->height === null) {
            $mod->flexGrow(1);
        }

        return $this->tag('div', '', $mod);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// DIVIDER - Horizontal or vertical line
// ════════════════════════════════════════════════════════════════════════════

class Divider extends Component {

    public function __construct(
        private bool $vertical = false,
        private string $color = 'var(--loom-border, #e2e8f0)',
        private int|string $thickness = 1,
        ?Modifier $modifier = null
    ) {
        $this->modifier = $modifier;
    }

    public function render(): string {
        $mod = $this->modifier ?? Modifier::new();
        $thickness = is_int($this->thickness) ? "{$this->thickness}px" : $this->thickness;

        if ($this->vertical) {
            $mod->width($thickness)->height('100%');
        } else {
            $mod->height($thickness)->fillMaxWidth();
        }

        $mod->background($this->color);

        return $this->tag('div', '', $mod, ['role' => 'separator']);
    }
}
