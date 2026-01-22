<p align="center">
  <img src="https://via.placeholder.com/120x120/336659/ffffff?text=L" alt="Loom Logo" width="120" height="120">
</p>

<h1 align="center">Loom</h1>

<p align="center">
  <strong>A modern, declarative UI framework for WordPress</strong>
</p>

<p align="center">
  Build beautiful WordPress interfaces with composable PHP components.<br>
  Inspired by Jetpack Compose. Powered by design tokens.
</p>

<p align="center">
  <a href="#installation">Installation</a> •
  <a href="#quick-start">Quick Start</a> •
  <a href="#components">Components</a> •
  <a href="#documentation">Documentation</a> •
  <a href="#license">License</a>
</p>

---

## Why Loom?

Traditional WordPress theming mixes PHP logic with HTML strings. Loom takes a different approach:

```php
// ❌ Traditional WordPress
echo '<div class="card" style="padding: 24px; background: #fff;">';
echo '<h2>' . esc_html($title) . '</h2>';
echo '<p style="color: #666;">' . esc_html($description) . '</p>';
echo '</div>';

// ✅ With Loom
Card(padding: Spacing::lg, content: function() use ($title, $description) {
    Text($title, style: TextStyle::H2);
    Text($description, color: Colors::textSecondary);
});
```

**Declarative** — Describe what you want, not how to build it
**Composable** — Small components combine into complex UIs
**Type-safe** — Full PHP 8.1+ support with IDE autocomplete
**Consistent** — Design tokens ensure visual harmony

---

## Installation

### Requirements

- WordPress 6.0+
- PHP 8.1+

### Setup

1. Download the latest release
2. Upload plugins to `wp-content/plugins/`:
   - `loom-core` (required)
   - `loom-theme-design` (recommended)
   - `loom-icons` (optional)
   - `loom-notifications` (optional)
3. Upload theme to `wp-content/themes/`:
   - `loom-theme`
4. Activate **Loom Core** plugin first, then others
5. Activate **Loom Theme**

---

## Quick Start

```php
<?php
declare(strict_types=1);

use Loom\Core\Components\Modifier;
use Loom\Core\Components\TextStyle;
use Loom\Core\Components\ButtonStyle;
use Loom\Core\Tokens\Colors;
use Loom\Core\Tokens\Spacing;

// Create a hero section
Column(
    modifier: Modifier::new()->padding(Spacing::xxl)->textAlign('center'),
    gap: Spacing::lg,
    align: 'center',
    content: function() {
        Text('Welcome to Loom', style: TextStyle::H1);

        Text(
            'Build WordPress sites the modern way.',
            color: Colors::textSecondary,
            modifier: Modifier::new()->fontSize(18)
        );

        Row(gap: Spacing::md, content: function() {
            Button(text: 'Get Started', style: ButtonStyle::Filled);
            Button(text: 'Learn More', style: ButtonStyle::Outlined);
        });
    }
);
```

---

## Components

### Layout

| Component | Description |
|-----------|-------------|
| `Column` | Vertical flex container |
| `Row` | Horizontal flex container |
| `Box` | Stack/overlay container |
| `Spacer` | Flexible empty space |
| `Divider` | Horizontal/vertical line |

### Basic

| Component | Description |
|-----------|-------------|
| `Text` | Typography with style presets |
| `Button` | Filled, outlined, text variants |
| `IconButton` | Icon-only button |
| `Image` | Responsive images |

### Containment

| Component | Description |
|-----------|-------------|
| `Card` | Elevated container |
| `Surface` | Basic styled container |
| `Dialog` | Modal overlay |
| `Scaffold` | Page structure |

### Feedback

| Component | Description |
|-----------|-------------|
| `Alert` | Success, error, warning, info |
| `Badge` | Status indicators |
| `Progress` | Linear and circular |
| `Chip` | Compact interactive elements |
| `Tooltip` | Hover information |

### Input

| Component | Description |
|-----------|-------------|
| `TextField` | Text input with label |
| `TextArea` | Multi-line input |
| `Checkbox` | Toggle checkbox |
| `Switch_` | Toggle switch |
| `Slider` | Range input |
| `Select` | Dropdown selection |

---

## Modifier API

Style components with a fluent chainable API:

```php
Modifier::new()
    ->padding(Spacing::lg)
    ->background(Colors::surface)
    ->rounded(Shapes::lg)
    ->shadow(2)
    ->transition('all 0.2s ease');
```

### Available Methods

**Layout:** `width`, `height`, `size`, `fillMaxWidth`, `minWidth`, `maxWidth`
**Spacing:** `padding`, `paddingX`, `paddingY`, `margin`, `gap`
**Flexbox:** `flex`, `justifyContent`, `alignItems`, `weight`, `flexWrap`
**Colors:** `background`, `color`, `opacity`
**Borders:** `border`, `rounded`, `roundedFull`
**Effects:** `shadow`, `blur`, `backdropBlur`
**Typography:** `fontSize`, `fontWeight`, `textAlign`, `lineClamp`
**Position:** `absolute`, `relative`, `fixed`, `top`, `zIndex`
**Transform:** `rotate`, `scale`, `offset`
**Interaction:** `cursor`, `clickable`, `hoverable`

[See full Modifier reference →](DOCUMENTATION.md#modifier-api)

---

## Design Tokens

Consistent design with built-in tokens:

```php
// Colors
Colors::primary
Colors::surface
Colors::text
Colors::textSecondary
Colors::error
Colors::success

// Spacing (px)
Spacing::sm   // 8
Spacing::md   // 16
Spacing::lg   // 24
Spacing::xl   // 32

// Shapes (border-radius)
Shapes::sm    // 8
Shapes::md    // 12
Shapes::lg    // 16
Shapes::full  // 9999
```

---

## Plugins

### Loom Core
The foundation. Provides all components, modifiers, and base functionality.

### Loom Theme Design
Customize design tokens through the WordPress admin. Edit colors, typography, spacing, and more.

### Loom Icons
SVG icon system with multiple icon packs and IDE autocomplete support.

### Loom Notifications
Toast notification system with PHP and JavaScript APIs.

```php
noti('success', 'Changes saved!');
```

---

## Documentation

- [Full Documentation](DOCUMENTATION.md) — Complete component and API reference
- [Claude Code Guide](CLAUDE.md) — AI assistant instructions for Loom

---

## Examples

### Card Grid

```php
Row(gap: Spacing::lg, wrap: true, content: function() {
    foreach ($features as $feature) {
        Card(
            modifier: Modifier::new()->minWidth(280)->weight(1),
            padding: Spacing::lg,
            content: function() use ($feature) {
                Text($feature['icon'], modifier: Modifier::new()->fontSize(32));
                Text($feature['title'], style: TextStyle::H4);
                Text($feature['desc'], color: Colors::textSecondary);
            }
        );
    }
});
```

### Form

```php
Card(padding: Spacing::lg, content: function() {
    Column(gap: Spacing::md, content: function() {
        Text('Contact Us', style: TextStyle::H3);
        TextField(label: 'Name', name: 'name', required: true);
        TextField(label: 'Email', name: 'email', type: 'email');
        TextArea(label: 'Message', name: 'message', rows: 4);
        Button(text: 'Send Message', style: ButtonStyle::Filled);
    });
});
```

### Alert States

```php
Column(gap: Spacing::sm, content: function() {
    Alert(message: 'Operation completed successfully!', type: AlertType::Success);
    Alert(message: 'Please check your input.', type: AlertType::Warning);
    Alert(message: 'An error occurred.', type: AlertType::Error);
    Alert(message: 'New updates available.', type: AlertType::Info);
});
```

---

## Browser Support

Loom outputs modern CSS and works in all evergreen browsers:

- Chrome / Edge (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)

---

## Contributing

Contributions are welcome! Please read our contributing guidelines before submitting PRs.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## License

GPL-2.0-or-later — See [LICENSE](LICENSE) for details.

---

<p align="center">
  <strong>Built with ❤️ for the WordPress community</strong>
</p>

<p align="center">
  <a href="https://github.com/MikoPetryk/project-loom">GitHub</a> •
  <a href="DOCUMENTATION.md">Docs</a> •
  <a href="https://github.com/MikoPetryk/project-loom/issues">Issues</a>
</p>
