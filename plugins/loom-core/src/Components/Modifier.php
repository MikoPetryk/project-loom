<?php
/**
 * Modifier - Jetpack Compose-style styling
 *
 * Fluent API for building inline styles.
 * Chain methods to build up styles, then apply to elements.
 *
 * @package Loom\Core\Components
 */



namespace Loom\Core\Components;

class Modifier {

    private array $styles = [];
    private array $classes = [];
    private array $attributes = [];
    private array $dataAttributes = [];

    /**
     * Create a new Modifier instance.
     *
     * Use this to start a fluent chain: Modifier::new()->padding(16)->background('red')
     */
    public static function new(): self {
        return new self();
    }

    /**
     * Allow static method chaining: Modifier::padding(16)->background('red')
     *
     * Any instance method can be called statically - it will create a new
     * instance and call the method on it.
     *
     * Note: This only works for methods that don't exist as instance methods.
     * For reliable usage, prefer Modifier::new()->method() syntax.
     */
    public static function __callStatic(string $name, array $arguments): self {
        $instance = new self();
        return $instance->$name(...$arguments);
    }

    // ════════════════════════════════════════════════════════════════════════
    // LAYOUT - SIZE
    // ════════════════════════════════════════════════════════════════════════

    public function width(int|string $value): self {
        $this->styles['width'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function height(int|string $value): self {
        $this->styles['height'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function size(int|string $value): self {
        return $this->width($value)->height($value);
    }

    public function minWidth(int|string $value): self {
        $this->styles['min-width'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function maxWidth(int|string $value): self {
        $this->styles['max-width'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function minHeight(int|string $value): self {
        $this->styles['min-height'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function maxHeight(int|string $value): self {
        $this->styles['max-height'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function fillMaxWidth(): self {
        $this->styles['width'] = '100%';
        return $this;
    }

    public function fillMaxHeight(): self {
        $this->styles['height'] = '100%';
        return $this;
    }

    public function fillMaxSize(): self {
        return $this->fillMaxWidth()->fillMaxHeight();
    }

    public function wrapContentWidth(): self {
        $this->styles['width'] = 'fit-content';
        return $this;
    }

    public function wrapContentHeight(): self {
        $this->styles['height'] = 'fit-content';
        return $this;
    }

    public function wrapContentSize(): self {
        return $this->wrapContentWidth()->wrapContentHeight();
    }

    public function requiredSize(int|string $value): self {
        $val = is_int($value) ? "{$value}px" : $value;
        $this->styles['width'] = $val;
        $this->styles['height'] = $val;
        $this->styles['min-width'] = $val;
        $this->styles['min-height'] = $val;
        $this->styles['max-width'] = $val;
        $this->styles['max-height'] = $val;
        return $this;
    }

    public function aspectRatio(float $ratio): self {
        $this->styles['aspect-ratio'] = (string) $ratio;
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // SPACING - PADDING
    // ════════════════════════════════════════════════════════════════════════

    public function padding(int|string|null $all = null, int|string|null $horizontal = null, int|string|null $vertical = null): self {
        if ($all !== null && $horizontal === null && $vertical === null) {
            $this->styles['padding'] = is_int($all) ? "{$all}px" : $all;
        } elseif ($horizontal !== null && $vertical !== null) {
            $v = is_int($vertical) ? "{$vertical}px" : $vertical;
            $h = is_int($horizontal) ? "{$horizontal}px" : $horizontal;
            $this->styles['padding'] = "{$v} {$h}";
        }
        return $this;
    }

    public function paddingX(int|string $value): self {
        $val = is_int($value) ? "{$value}px" : $value;
        $this->styles['padding-left'] = $val;
        $this->styles['padding-right'] = $val;
        return $this;
    }

    public function paddingY(int|string $value): self {
        $val = is_int($value) ? "{$value}px" : $value;
        $this->styles['padding-top'] = $val;
        $this->styles['padding-bottom'] = $val;
        return $this;
    }

    public function paddingTop(int|string $value): self {
        $this->styles['padding-top'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function paddingBottom(int|string $value): self {
        $this->styles['padding-bottom'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function paddingStart(int|string $value): self {
        $this->styles['padding-inline-start'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function paddingEnd(int|string $value): self {
        $this->styles['padding-inline-end'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function paddingLeft(int|string $value): self {
        $this->styles['padding-left'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function paddingRight(int|string $value): self {
        $this->styles['padding-right'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // SPACING - MARGIN
    // ════════════════════════════════════════════════════════════════════════

    public function margin(int|string|null $all = null, int|string|null $horizontal = null, int|string|null $vertical = null): self {
        if ($all !== null && $horizontal === null && $vertical === null) {
            $this->styles['margin'] = is_int($all) ? "{$all}px" : $all;
        } elseif ($horizontal !== null && $vertical !== null) {
            $v = is_int($vertical) ? "{$vertical}px" : $vertical;
            $h = is_int($horizontal) ? "{$horizontal}px" : $horizontal;
            $this->styles['margin'] = "{$v} {$h}";
        }
        return $this;
    }

    public function marginX(int|string $value): self {
        $val = is_int($value) ? "{$value}px" : $value;
        $this->styles['margin-left'] = $val;
        $this->styles['margin-right'] = $val;
        return $this;
    }

    public function marginY(int|string $value): self {
        $val = is_int($value) ? "{$value}px" : $value;
        $this->styles['margin-top'] = $val;
        $this->styles['margin-bottom'] = $val;
        return $this;
    }

    public function marginTop(int|string $value): self {
        $this->styles['margin-top'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function marginBottom(int|string $value): self {
        $this->styles['margin-bottom'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function marginStart(int|string $value): self {
        $this->styles['margin-inline-start'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function marginEnd(int|string $value): self {
        $this->styles['margin-inline-end'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function marginLeft(int|string $value): self {
        $this->styles['margin-left'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function marginRight(int|string $value): self {
        $this->styles['margin-right'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function gap(int|string $value): self {
        $this->styles['gap'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function rowGap(int|string $value): self {
        $this->styles['row-gap'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function columnGap(int|string $value): self {
        $this->styles['column-gap'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // LAYOUT - OFFSET
    // ════════════════════════════════════════════════════════════════════════

    public function offset(int|string $x = 0, int|string $y = 0): self {
        $xVal = is_int($x) ? "{$x}px" : $x;
        $yVal = is_int($y) ? "{$y}px" : $y;
        $this->styles['transform'] = $this->appendTransform("translate({$xVal}, {$yVal})");
        return $this;
    }

    public function offsetX(int|string $value): self {
        $val = is_int($value) ? "{$value}px" : $value;
        $this->styles['transform'] = $this->appendTransform("translateX({$val})");
        return $this;
    }

    public function offsetY(int|string $value): self {
        $val = is_int($value) ? "{$value}px" : $value;
        $this->styles['transform'] = $this->appendTransform("translateY({$val})");
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // FLEXBOX
    // ════════════════════════════════════════════════════════════════════════

    public function flex(): self {
        $this->styles['display'] = 'flex';
        return $this;
    }

    public function inlineFlex(): self {
        $this->styles['display'] = 'inline-flex';
        return $this;
    }

    public function flexDirection(string $direction): self {
        $this->styles['flex-direction'] = $direction;
        return $this;
    }

    public function justifyContent(string $value): self {
        $this->styles['justify-content'] = $value;
        return $this;
    }

    public function alignItems(string $value): self {
        $this->styles['align-items'] = $value;
        return $this;
    }

    public function alignSelf(string $value): self {
        $this->styles['align-self'] = $value;
        return $this;
    }

    public function alignContent(string $value): self {
        $this->styles['align-content'] = $value;
        return $this;
    }

    public function flexWrap(string $value = 'wrap'): self {
        $this->styles['flex-wrap'] = $value;
        return $this;
    }

    public function flexGrow(int $value): self {
        $this->styles['flex-grow'] = (string) $value;
        return $this;
    }

    public function flexShrink(int $value): self {
        $this->styles['flex-shrink'] = (string) $value;
        return $this;
    }

    public function flexBasis(int|string $value): self {
        $this->styles['flex-basis'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    /**
     * Weight for Row/Column children (like Compose's weight modifier)
     */
    public function weight(float $value): self {
        $this->styles['flex'] = (string) $value;
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // GRID
    // ════════════════════════════════════════════════════════════════════════

    public function grid(): self {
        $this->styles['display'] = 'grid';
        return $this;
    }

    public function gridColumns(int|string $value): self {
        if (is_int($value)) {
            $this->styles['grid-template-columns'] = "repeat({$value}, 1fr)";
        } else {
            $this->styles['grid-template-columns'] = $value;
        }
        return $this;
    }

    public function gridRows(int|string $value): self {
        if (is_int($value)) {
            $this->styles['grid-template-rows'] = "repeat({$value}, 1fr)";
        } else {
            $this->styles['grid-template-rows'] = $value;
        }
        return $this;
    }

    public function gridColumn(string $value): self {
        $this->styles['grid-column'] = $value;
        return $this;
    }

    public function gridRow(string $value): self {
        $this->styles['grid-row'] = $value;
        return $this;
    }

    public function gridSpan(int $columns, int $rows = 1): self {
        $this->styles['grid-column'] = "span {$columns}";
        if ($rows > 1) {
            $this->styles['grid-row'] = "span {$rows}";
        }
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // BACKGROUND & COLORS
    // ════════════════════════════════════════════════════════════════════════

    public function background(string $color): self {
        $this->styles['background'] = $color;
        return $this;
    }

    public function backgroundColor(string $color): self {
        $this->styles['background-color'] = $color;
        return $this;
    }

    public function backgroundImage(string $url): self {
        $this->styles['background-image'] = "url('{$url}')";
        return $this;
    }

    public function backgroundGradient(string $gradient): self {
        $this->styles['background'] = $gradient;
        return $this;
    }

    public function backgroundSize(string $value): self {
        $this->styles['background-size'] = $value;
        return $this;
    }

    public function backgroundPosition(string $value): self {
        $this->styles['background-position'] = $value;
        return $this;
    }

    public function backgroundRepeat(string $value = 'no-repeat'): self {
        $this->styles['background-repeat'] = $value;
        return $this;
    }

    public function color(string $color): self {
        $this->styles['color'] = $color;
        return $this;
    }

    public function opacity(float $value): self {
        $this->styles['opacity'] = (string) $value;
        return $this;
    }

    public function alpha(float $value): self {
        return $this->opacity($value);
    }

    // ════════════════════════════════════════════════════════════════════════
    // BORDERS & SHAPES
    // ════════════════════════════════════════════════════════════════════════

    public function border(string $value): self {
        $this->styles['border'] = $value;
        return $this;
    }

    public function borderWidth(int|string $value): self {
        $this->styles['border-width'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function borderColor(string $color): self {
        $this->styles['border-color'] = $color;
        return $this;
    }

    public function borderStyle(string $style): self {
        $this->styles['border-style'] = $style;
        return $this;
    }

    public function borderTop(string $value): self {
        $this->styles['border-top'] = $value;
        return $this;
    }

    public function borderBottom(string $value): self {
        $this->styles['border-bottom'] = $value;
        return $this;
    }

    public function borderLeft(string $value): self {
        $this->styles['border-left'] = $value;
        return $this;
    }

    public function borderRight(string $value): self {
        $this->styles['border-right'] = $value;
        return $this;
    }

    public function borderStart(string $value): self {
        $this->styles['border-inline-start'] = $value;
        return $this;
    }

    public function borderEnd(string $value): self {
        $this->styles['border-inline-end'] = $value;
        return $this;
    }

    public function rounded(int|string $radius): self {
        $this->styles['border-radius'] = is_int($radius) ? "{$radius}px" : $radius;
        return $this;
    }

    public function roundedTop(int|string $radius): self {
        $val = is_int($radius) ? "{$radius}px" : $radius;
        $this->styles['border-top-left-radius'] = $val;
        $this->styles['border-top-right-radius'] = $val;
        return $this;
    }

    public function roundedBottom(int|string $radius): self {
        $val = is_int($radius) ? "{$radius}px" : $radius;
        $this->styles['border-bottom-left-radius'] = $val;
        $this->styles['border-bottom-right-radius'] = $val;
        return $this;
    }

    public function roundedStart(int|string $radius): self {
        $val = is_int($radius) ? "{$radius}px" : $radius;
        $this->styles['border-start-start-radius'] = $val;
        $this->styles['border-end-start-radius'] = $val;
        return $this;
    }

    public function roundedEnd(int|string $radius): self {
        $val = is_int($radius) ? "{$radius}px" : $radius;
        $this->styles['border-start-end-radius'] = $val;
        $this->styles['border-end-end-radius'] = $val;
        return $this;
    }

    public function roundedFull(): self {
        $this->styles['border-radius'] = '9999px';
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // CLIP & OVERFLOW
    // ════════════════════════════════════════════════════════════════════════

    public function clip(string $shape = 'border-box'): self {
        $this->styles['overflow'] = 'hidden';
        if ($shape !== 'border-box') {
            $this->styles['clip-path'] = $shape;
        }
        return $this;
    }

    public function clipToBounds(): self {
        $this->styles['overflow'] = 'hidden';
        return $this;
    }

    public function clipCircle(): self {
        $this->styles['clip-path'] = 'circle(50%)';
        $this->styles['overflow'] = 'hidden';
        return $this;
    }

    public function clipRounded(int|string $radius): self {
        $val = is_int($radius) ? "{$radius}px" : $radius;
        $this->styles['clip-path'] = "inset(0 round {$val})";
        $this->styles['overflow'] = 'hidden';
        return $this;
    }

    public function overflow(string $value): self {
        $this->styles['overflow'] = $value;
        return $this;
    }

    public function overflowX(string $value): self {
        $this->styles['overflow-x'] = $value;
        return $this;
    }

    public function overflowY(string $value): self {
        $this->styles['overflow-y'] = $value;
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // SCROLLING
    // ════════════════════════════════════════════════════════════════════════

    public function scrollable(): self {
        $this->styles['overflow'] = 'auto';
        return $this;
    }

    public function verticalScroll(): self {
        $this->styles['overflow-y'] = 'auto';
        $this->styles['overflow-x'] = 'hidden';
        return $this;
    }

    public function horizontalScroll(): self {
        $this->styles['overflow-x'] = 'auto';
        $this->styles['overflow-y'] = 'hidden';
        return $this;
    }

    public function scrollBehavior(string $value = 'smooth'): self {
        $this->styles['scroll-behavior'] = $value;
        return $this;
    }

    public function scrollSnapType(string $value): self {
        $this->styles['scroll-snap-type'] = $value;
        return $this;
    }

    public function scrollSnapAlign(string $value): self {
        $this->styles['scroll-snap-align'] = $value;
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // SHADOWS & ELEVATION
    // ════════════════════════════════════════════════════════════════════════

    public function shadow(int $elevation = 1): self {
        $shadows = [
            0 => 'none',
            1 => '0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24)',
            2 => '0 3px 6px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.12)',
            3 => '0 10px 20px rgba(0,0,0,0.15), 0 3px 6px rgba(0,0,0,0.10)',
            4 => '0 15px 25px rgba(0,0,0,0.15), 0 5px 10px rgba(0,0,0,0.05)',
            5 => '0 20px 40px rgba(0,0,0,0.2)',
        ];
        $this->styles['box-shadow'] = $shadows[$elevation] ?? $shadows[1];
        return $this;
    }

    public function boxShadow(string $value): self {
        $this->styles['box-shadow'] = $value;
        return $this;
    }

    public function textShadow(string $value): self {
        $this->styles['text-shadow'] = $value;
        return $this;
    }

    public function dropShadow(string $value): self {
        $this->styles['filter'] = "drop-shadow({$value})";
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // TRANSFORMS
    // ════════════════════════════════════════════════════════════════════════

    public function rotate(float $degrees): self {
        $this->styles['transform'] = $this->appendTransform("rotate({$degrees}deg)");
        return $this;
    }

    public function rotateX(float $degrees): self {
        $this->styles['transform'] = $this->appendTransform("rotateX({$degrees}deg)");
        return $this;
    }

    public function rotateY(float $degrees): self {
        $this->styles['transform'] = $this->appendTransform("rotateY({$degrees}deg)");
        return $this;
    }

    public function scale(float $value): self {
        $this->styles['transform'] = $this->appendTransform("scale({$value})");
        return $this;
    }

    public function scaleX(float $value): self {
        $this->styles['transform'] = $this->appendTransform("scaleX({$value})");
        return $this;
    }

    public function scaleY(float $value): self {
        $this->styles['transform'] = $this->appendTransform("scaleY({$value})");
        return $this;
    }

    public function skew(float $x, float $y = 0): self {
        $this->styles['transform'] = $this->appendTransform("skew({$x}deg, {$y}deg)");
        return $this;
    }

    public function transformOrigin(string $value): self {
        $this->styles['transform-origin'] = $value;
        return $this;
    }

    private function appendTransform(string $transform): string {
        $existing = $this->styles['transform'] ?? '';
        return $existing ? "{$existing} {$transform}" : $transform;
    }

    // ════════════════════════════════════════════════════════════════════════
    // FILTERS & EFFECTS
    // ════════════════════════════════════════════════════════════════════════

    public function blur(int|string $value): self {
        $val = is_int($value) ? "{$value}px" : $value;
        $this->styles['filter'] = $this->appendFilter("blur({$val})");
        return $this;
    }

    public function brightness(float $value): self {
        $this->styles['filter'] = $this->appendFilter("brightness({$value})");
        return $this;
    }

    public function contrast(float $value): self {
        $this->styles['filter'] = $this->appendFilter("contrast({$value})");
        return $this;
    }

    public function grayscale(float $value = 1): self {
        $this->styles['filter'] = $this->appendFilter("grayscale({$value})");
        return $this;
    }

    public function saturate(float $value): self {
        $this->styles['filter'] = $this->appendFilter("saturate({$value})");
        return $this;
    }

    public function sepia(float $value = 1): self {
        $this->styles['filter'] = $this->appendFilter("sepia({$value})");
        return $this;
    }

    public function invert(float $value = 1): self {
        $this->styles['filter'] = $this->appendFilter("invert({$value})");
        return $this;
    }

    public function hueRotate(float $degrees): self {
        $this->styles['filter'] = $this->appendFilter("hue-rotate({$degrees}deg)");
        return $this;
    }

    public function backdropBlur(int|string $value): self {
        $val = is_int($value) ? "{$value}px" : $value;
        $this->styles['backdrop-filter'] = "blur({$val})";
        return $this;
    }

    private function appendFilter(string $filter): string {
        $existing = $this->styles['filter'] ?? '';
        return $existing ? "{$existing} {$filter}" : $filter;
    }

    // ════════════════════════════════════════════════════════════════════════
    // TYPOGRAPHY
    // ════════════════════════════════════════════════════════════════════════

    public function fontSize(int|string $size): self {
        $this->styles['font-size'] = is_int($size) ? "{$size}px" : $size;
        return $this;
    }

    public function fontWeight(int|string $weight): self {
        $this->styles['font-weight'] = (string) $weight;
        return $this;
    }

    public function fontFamily(string $family): self {
        $this->styles['font-family'] = $family;
        return $this;
    }

    public function fontStyle(string $style): self {
        $this->styles['font-style'] = $style;
        return $this;
    }

    public function lineHeight(float|string $value): self {
        $this->styles['line-height'] = (string) $value;
        return $this;
    }

    public function letterSpacing(int|string $value): self {
        $this->styles['letter-spacing'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function textAlign(string $align): self {
        $this->styles['text-align'] = $align;
        return $this;
    }

    public function textDecoration(string $value): self {
        $this->styles['text-decoration'] = $value;
        return $this;
    }

    public function textTransform(string $value): self {
        $this->styles['text-transform'] = $value;
        return $this;
    }

    public function textOverflow(string $value = 'ellipsis'): self {
        $this->styles['text-overflow'] = $value;
        $this->styles['overflow'] = 'hidden';
        $this->styles['white-space'] = 'nowrap';
        return $this;
    }

    public function whiteSpace(string $value): self {
        $this->styles['white-space'] = $value;
        return $this;
    }

    public function wordBreak(string $value): self {
        $this->styles['word-break'] = $value;
        return $this;
    }

    public function lineClamp(int $lines): self {
        $this->styles['display'] = '-webkit-box';
        $this->styles['-webkit-line-clamp'] = (string) $lines;
        $this->styles['-webkit-box-orient'] = 'vertical';
        $this->styles['overflow'] = 'hidden';
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // POSITIONING
    // ════════════════════════════════════════════════════════════════════════

    public function position(string $value): self {
        $this->styles['position'] = $value;
        return $this;
    }

    public function absolute(): self {
        return $this->position('absolute');
    }

    public function relative(): self {
        return $this->position('relative');
    }

    public function fixed(): self {
        return $this->position('fixed');
    }

    public function sticky(): self {
        return $this->position('sticky');
    }

    public function static_(): self {
        return $this->position('static');
    }

    public function inset(int|string $value): self {
        $val = is_int($value) ? "{$value}px" : $value;
        $this->styles['inset'] = $val;
        return $this;
    }

    public function top(int|string $value): self {
        $this->styles['top'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function bottom(int|string $value): self {
        $this->styles['bottom'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function left(int|string $value): self {
        $this->styles['left'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function right(int|string $value): self {
        $this->styles['right'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function start(int|string $value): self {
        $this->styles['inset-inline-start'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function end(int|string $value): self {
        $this->styles['inset-inline-end'] = is_int($value) ? "{$value}px" : $value;
        return $this;
    }

    public function zIndex(int $value): self {
        $this->styles['z-index'] = (string) $value;
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // INTERACTION
    // ════════════════════════════════════════════════════════════════════════

    public function clickable(?string $onClick = null): self {
        $this->styles['cursor'] = 'pointer';
        $this->styles['user-select'] = 'none';
        if ($onClick !== null) {
            $this->attributes['onclick'] = $onClick;
        }
        return $this;
    }

    public function hoverable(): self {
        $this->classes[] = 'loom-hoverable';
        return $this;
    }

    public function focusable(): self {
        $this->attributes['tabindex'] = '0';
        $this->classes[] = 'loom-focusable';
        return $this;
    }

    public function draggable(bool $value = true): self {
        $this->attributes['draggable'] = $value ? 'true' : 'false';
        return $this;
    }

    public function selectable(bool $value = true): self {
        $this->styles['user-select'] = $value ? 'auto' : 'none';
        return $this;
    }

    public function pointerEvents(string $value): self {
        $this->styles['pointer-events'] = $value;
        return $this;
    }

    public function cursor(string $value): self {
        $this->styles['cursor'] = $value;
        return $this;
    }

    public function userSelect(string $value): self {
        $this->styles['user-select'] = $value;
        return $this;
    }

    /**
     * MD3 minimum interactive component size (48dp)
     */
    public function minimumInteractiveSize(): self {
        $this->styles['min-width'] = '48px';
        $this->styles['min-height'] = '48px';
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // TRANSITIONS & ANIMATIONS
    // ════════════════════════════════════════════════════════════════════════

    public function transition(string $value = 'all 0.2s ease'): self {
        $this->styles['transition'] = $value;
        return $this;
    }

    public function transitionProperty(string $value): self {
        $this->styles['transition-property'] = $value;
        return $this;
    }

    public function transitionDuration(string $value): self {
        $this->styles['transition-duration'] = $value;
        return $this;
    }

    public function transitionTimingFunction(string $value): self {
        $this->styles['transition-timing-function'] = $value;
        return $this;
    }

    public function transitionDelay(string $value): self {
        $this->styles['transition-delay'] = $value;
        return $this;
    }

    public function animation(string $value): self {
        $this->styles['animation'] = $value;
        return $this;
    }

    public function animateContentSize(): self {
        $this->styles['transition'] = 'width 0.3s ease, height 0.3s ease';
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // VISIBILITY
    // ════════════════════════════════════════════════════════════════════════

    public function visible(): self {
        $this->styles['visibility'] = 'visible';
        return $this;
    }

    public function invisible(): self {
        $this->styles['visibility'] = 'hidden';
        return $this;
    }

    public function hidden(): self {
        $this->styles['display'] = 'none';
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // SEMANTICS & ACCESSIBILITY
    // ════════════════════════════════════════════════════════════════════════

    public function testTag(string $tag): self {
        $this->dataAttributes['testid'] = $tag;
        return $this;
    }

    public function contentDescription(string $description): self {
        $this->attributes['aria-label'] = $description;
        return $this;
    }

    public function role(string $role): self {
        $this->attributes['role'] = $role;
        return $this;
    }

    public function ariaLabel(string $label): self {
        $this->attributes['aria-label'] = $label;
        return $this;
    }

    public function ariaHidden(bool $value = true): self {
        $this->attributes['aria-hidden'] = $value ? 'true' : 'false';
        return $this;
    }

    public function ariaExpanded(bool $value): self {
        $this->attributes['aria-expanded'] = $value ? 'true' : 'false';
        return $this;
    }

    public function ariaSelected(bool $value): self {
        $this->attributes['aria-selected'] = $value ? 'true' : 'false';
        return $this;
    }

    public function ariaDisabled(bool $value = true): self {
        $this->attributes['aria-disabled'] = $value ? 'true' : 'false';
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // RAW / ESCAPE HATCH
    // ════════════════════════════════════════════════════════════════════════

    public function style(string $property, string $value): self {
        $this->styles[$property] = $value;
        return $this;
    }

    public function class(string ...$classes): self {
        foreach ($classes as $class) {
            $this->classes[] = $class;
        }
        return $this;
    }

    public function attr(string $name, string $value): self {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function data(string $name, string $value): self {
        $this->dataAttributes[$name] = $value;
        return $this;
    }

    public function id(string $id): self {
        $this->attributes['id'] = $id;
        return $this;
    }

    // ════════════════════════════════════════════════════════════════════════
    // CONDITIONAL
    // ════════════════════════════════════════════════════════════════════════

    public function when(bool $condition, callable $callback): self {
        if ($condition) {
            $callback($this);
        }
        return $this;
    }

    public function unless(bool $condition, callable $callback): self {
        return $this->when(!$condition, $callback);
    }

    // ════════════════════════════════════════════════════════════════════════
    // OUTPUT
    // ════════════════════════════════════════════════════════════════════════

    public function toStyle(): string {
        $parts = [];
        foreach ($this->styles as $prop => $value) {
            $parts[] = "{$prop}: {$value}";
        }
        return implode('; ', $parts);
    }

    public function toAttributes(): string {
        $attrs = [];

        if (!empty($this->styles)) {
            $attrs[] = 'style="' . esc_attr($this->toStyle()) . '"';
        }

        if (!empty($this->classes)) {
            $attrs[] = 'class="' . esc_attr(implode(' ', array_unique($this->classes))) . '"';
        }

        foreach ($this->attributes as $name => $value) {
            $attrs[] = esc_attr($name) . '="' . esc_attr($value) . '"';
        }

        foreach ($this->dataAttributes as $name => $value) {
            $attrs[] = 'data-' . esc_attr($name) . '="' . esc_attr($value) . '"';
        }

        return implode(' ', $attrs);
    }

    public function __toString(): string {
        return $this->toAttributes();
    }

    /**
     * Merge another modifier into this one
     */
    public function merge(Modifier $other): self {
        $this->styles = array_merge($this->styles, $other->styles);
        $this->classes = array_merge($this->classes, $other->classes);
        $this->attributes = array_merge($this->attributes, $other->attributes);
        $this->dataAttributes = array_merge($this->dataAttributes, $other->dataAttributes);
        return $this;
    }

    /**
     * Clone this modifier
     */
    public function copy(): self {
        $copy = new self();
        $copy->styles = $this->styles;
        $copy->classes = $this->classes;
        $copy->attributes = $this->attributes;
        $copy->dataAttributes = $this->dataAttributes;
        return $copy;
    }

    /**
     * Get all styles as array
     */
    public function getStyles(): array {
        return $this->styles;
    }

    /**
     * Get all classes as array
     */
    public function getClasses(): array {
        return $this->classes;
    }
}
