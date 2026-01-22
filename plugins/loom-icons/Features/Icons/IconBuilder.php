<?php
/**
 * Icon Builder
 *
 * Fluent API for building icons with color support.
 *
 * @package IconManager\Features\Icons
 * @since 2.1.0
 */



namespace IconManager\Features\Icons;

use IconManager\Features\Packs\IconPackInterface;

class IconBuilder {
    private string $pack;
    private string $name;
    private ?int $width = null;
    private ?int $height = null;
    private ?string $class = null;
    private ?string $style = null;
    private ?string $id = null;
    private ?string $color = null;

    public function __construct(IconPackInterface $iconEnum) {
        $this->pack = $iconEnum->getPackName();
        $this->name = $iconEnum->getIconName();
    }

    public function size(int $size): self {
        $this->width = $size;
        $this->height = $size;
        return $this;
    }

    public function width(int $width): self {
        $this->width = $width;
        return $this;
    }

    public function height(int $height): self {
        $this->height = $height;
        return $this;
    }

    public function color(string $color): self {
        $this->color = $color;
        return $this;
    }

    public function class(string $class): self {
        $this->class = $class;
        return $this;
    }

    public function style(string $style): self {
        $this->style = $style;
        return $this;
    }

    public function id(string $id): self {
        $this->id = $id;
        return $this;
    }

    public function render(): void {
        echo $this->getHtml();
    }

    public function __toString(): string {
        return $this->getHtml();
    }

    private function getHtml(): string {
        $svg = IconRenderer::render(
            $this->pack,
            $this->name,
            $this->width,
            $this->height,
            $this->class,
            $this->style,
            $this->id,
            $this->color
        );

        // Sanitize SVG output
        return self::sanitizeSvg($svg);
    }

    /**
     * Sanitize SVG content using wp_kses with allowed SVG elements
     */
    private static function sanitizeSvg(string $svg): string {
        $allowed = [
            'svg' => [
                'xmlns' => true,
                'viewbox' => true,
                'width' => true,
                'height' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'class' => true,
                'id' => true,
                'style' => true,
                'role' => true,
                'aria-hidden' => true,
                'aria-label' => true,
                'focusable' => true,
            ],
            'path' => [
                'd' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
                'stroke-linecap' => true,
                'stroke-linejoin' => true,
                'fill-rule' => true,
                'clip-rule' => true,
                'opacity' => true,
            ],
            'circle' => [
                'cx' => true,
                'cy' => true,
                'r' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
            ],
            'rect' => [
                'x' => true,
                'y' => true,
                'width' => true,
                'height' => true,
                'rx' => true,
                'ry' => true,
                'fill' => true,
                'stroke' => true,
            ],
            'line' => [
                'x1' => true,
                'y1' => true,
                'x2' => true,
                'y2' => true,
                'stroke' => true,
                'stroke-width' => true,
            ],
            'polyline' => [
                'points' => true,
                'fill' => true,
                'stroke' => true,
                'stroke-width' => true,
            ],
            'polygon' => [
                'points' => true,
                'fill' => true,
                'stroke' => true,
            ],
            'ellipse' => [
                'cx' => true,
                'cy' => true,
                'rx' => true,
                'ry' => true,
                'fill' => true,
                'stroke' => true,
            ],
            'g' => [
                'fill' => true,
                'stroke' => true,
                'transform' => true,
                'opacity' => true,
                'id' => true,
            ],
            'defs' => [],
            'clippath' => [
                'id' => true,
            ],
            'mask' => [
                'id' => true,
            ],
            'use' => [
                'href' => true,
                'xlink:href' => true,
                'x' => true,
                'y' => true,
            ],
            'title' => [],
            'desc' => [],
        ];

        return wp_kses($svg, $allowed);
    }
}
