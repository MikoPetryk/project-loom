# CLAUDE.md - Loom Framework Instructions for Claude Code

This file instructs Claude Code on how to work with the Loom framework for WordPress.

## Overview

Loom is a declarative UI framework for WordPress inspired by Jetpack Compose. It uses PHP functions to build UI components with a fluent modifier API for styling.

## Key Principles

1. **Declarative** - Describe what you want, not how to build it
2. **Composable** - Small components combine into larger ones
3. **Type-safe** - Full PHP 8.1+ type safety
4. **Token-based** - Use design tokens for consistency

## Required Imports

Always include these at the top of PHP files using Loom:

```php
<?php
declare(strict_types=1);

use Loom\Core\Components\Modifier;
use Loom\Core\Components\TextStyle;
use Loom\Core\Components\ButtonStyle;
use Loom\Core\Components\AlertType;
use Loom\Core\Tokens\Colors;
use Loom\Core\Tokens\Spacing;
use Loom\Core\Tokens\Shapes;
```

## Component Syntax

### Layout Components

```php
// Column - vertical layout
Column(
    gap: Spacing::md,
    align: 'center',      // start, center, end, stretch
    justify: 'start',     // start, center, end, between, around
    content: function() {
        // children here
    }
);

// Row - horizontal layout
Row(
    gap: Spacing::sm,
    align: 'center',
    justify: 'between',
    wrap: true,           // allow wrapping
    content: function() {
        // children here
    }
);

// Box - stacking/overlay
Box(
    align: 'center',      // top-start, center, bottom-end, etc.
    content: function() {
        // children stack on each other
    }
);
```

### Basic Components

```php
// Text
Text('Hello World');
Text('Heading', style: TextStyle::H1);
Text('Muted', color: Colors::textSecondary);

// Button
Button(text: 'Click', style: ButtonStyle::Filled);
Button(text: 'Cancel', style: ButtonStyle::Outlined);
Button(text: 'Link', style: ButtonStyle::Text);
Button(text: 'Submit', onClick: 'handleSubmit()');
Button(text: 'Go', href: '/page');

// Image
Image(src: '/path/image.jpg', alt: 'Description', width: 300, fit: 'cover');
```

### Container Components

```php
// Card
Card(
    padding: Spacing::lg,
    rounded: Shapes::lg,
    content: function() {
        Text('Card content');
    }
);

// Surface
Surface(
    elevation: 2,
    color: Colors::surface,
    content: function() {
        // content
    }
);
```

### Feedback Components

```php
// Alert
Alert(message: 'Success!', type: AlertType::Success);
Alert(message: 'Error occurred', type: AlertType::Error, title: 'Error');

// Badge
Badge(content: '5', color: Colors::error);

// Progress
Progress(value: 65);
Progress(circular: true, size: 40);

// Chip
Chip(label: 'Tag', selected: true, onClick: 'handleClick()');
```

### Input Components

```php
// TextField
TextField(
    value: $value,
    label: 'Email',
    placeholder: 'Enter email',
    name: 'email',
    type: 'email',
    required: true
);

// TextArea
TextArea(value: $text, label: 'Description', rows: 4);

// Checkbox
Checkbox(label: 'Accept terms', checked: false, name: 'terms');

// Switch (note underscore)
Switch_(label: 'Dark mode', checked: true, onChange: 'toggle()');

// Select
Select(
    options: ['a' => 'Option A', 'b' => 'Option B'],
    value: 'a',
    label: 'Choose'
);

// Slider
Slider(value: 50, min: 0, max: 100, showValue: true);
```

### Utility Components

```php
// Spacer
Spacer(size: 24);
Spacer(height: 16);
Spacer();  // flexible, fills space

// Divider
Divider();
Divider(vertical: true);
```

## Modifier API

Modifiers style components using a fluent chain:

```php
Modifier::new()
    // Size
    ->width(200)
    ->height(100)
    ->size(50)               // both width and height
    ->fillMaxWidth()         // width: 100%
    ->minWidth(100)
    ->maxWidth(600)

    // Spacing
    ->padding(16)
    ->padding(horizontal: 16, vertical: 8)
    ->paddingX(16)
    ->paddingY(8)
    ->margin(16)
    ->gap(16)

    // Colors
    ->background(Colors::primary)
    ->color(Colors::onPrimary)
    ->opacity(0.8)

    // Borders
    ->border('1px solid ' . Colors::border)
    ->rounded(8)
    ->roundedFull()

    // Shadows
    ->shadow(2)              // elevation 0-5

    // Typography
    ->fontSize(16)
    ->fontWeight(600)
    ->textAlign('center')
    ->lineClamp(2)

    // Layout
    ->flex()
    ->flexDirection('column')
    ->justifyContent('center')
    ->alignItems('center')
    ->weight(1)              // flex: 1

    // Position
    ->absolute()
    ->relative()
    ->top(0)
    ->zIndex(100)

    // Transforms
    ->rotate(45)
    ->scale(1.5)
    ->offset(10, 20)

    // Effects
    ->blur(4)
    ->backdropBlur(10)

    // Transitions
    ->transition('all 0.3s ease')

    // Interaction
    ->cursor('pointer')
    ->clickable('handleClick()')

    // Accessibility
    ->ariaLabel('Description')

    // Raw
    ->style('custom-prop', 'value')
    ->class('my-class')
    ->attr('data-id', '123')
    ->id('element-id')

    // Conditional
    ->when($condition, fn($m) => $m->background('red'))
```

## Design Tokens

Always use design tokens instead of hardcoded values:

```php
// Colors
Colors::primary
Colors::onPrimary
Colors::secondary
Colors::surface
Colors::background
Colors::text
Colors::textSecondary
Colors::error
Colors::success
Colors::warning
Colors::info
Colors::border

// Spacing (in pixels)
Spacing::none    // 0
Spacing::xxs     // 2
Spacing::xs      // 4
Spacing::sm      // 8
Spacing::md      // 16
Spacing::lg      // 24
Spacing::xl      // 32
Spacing::xxl     // 48

// Shapes (border radius)
Shapes::none     // 0
Shapes::xs       // 4
Shapes::sm       // 8
Shapes::md       // 12
Shapes::lg       // 16
Shapes::xl       // 24
Shapes::full     // 9999
```

## Common Patterns

### Page Layout

```php
Column(
    modifier: Modifier::new()->minHeight('100vh')->padding(Spacing::xl),
    gap: Spacing::lg,
    content: function() {
        // Header
        Row(justify: 'between', align: 'center', content: function() {
            Text('Site Title', style: TextStyle::H2);
            Row(gap: Spacing::sm, content: function() {
                Button(text: 'Login', style: ButtonStyle::Text);
                Button(text: 'Sign Up', style: ButtonStyle::Filled);
            });
        });

        // Main content
        Column(gap: Spacing::md, content: function() {
            // Content here
        });

        // Footer
        Text('© 2026', color: Colors::textSecondary);
    }
);
```

### Card Grid

```php
Row(
    gap: Spacing::lg,
    wrap: true,
    content: function() use ($items) {
        foreach ($items as $item) {
            Card(
                modifier: Modifier::new()->minWidth(280)->weight(1),
                padding: Spacing::lg,
                content: function() use ($item) {
                    Text($item['title'], style: TextStyle::H3);
                    Text($item['description'], color: Colors::textSecondary);
                }
            );
        }
    }
);
```

### Form

```php
Card(padding: Spacing::lg, content: function() {
    Column(gap: Spacing::md, content: function() {
        Text('Contact Form', style: TextStyle::H3);

        TextField(label: 'Name', name: 'name', required: true);
        TextField(label: 'Email', name: 'email', type: 'email', required: true);
        TextArea(label: 'Message', name: 'message', rows: 4);

        Row(gap: Spacing::sm, justify: 'end', content: function() {
            Button(text: 'Cancel', style: ButtonStyle::Text);
            Button(text: 'Send', style: ButtonStyle::Filled);
        });
    });
});
```

### Hero Section

```php
Column(
    modifier: Modifier::new()
        ->padding(Spacing::xxl)
        ->textAlign('center')
        ->background(Colors::primaryContainer),
    gap: Spacing::lg,
    align: 'center',
    content: function() {
        Text('Welcome', style: TextStyle::H1);
        Text(
            'Build beautiful WordPress sites with declarative PHP.',
            color: Colors::textSecondary,
            modifier: Modifier::new()->maxWidth(600)
        );
        Row(gap: Spacing::md, content: function() {
            Button(text: 'Get Started', style: ButtonStyle::Filled);
            Button(text: 'Learn More', style: ButtonStyle::Outlined);
        });
    }
);
```

## Best Practices for Claude

1. **Always use named parameters** for clarity
2. **Use design tokens** (Colors::, Spacing::, Shapes::) not hardcoded values
3. **Extract reusable components** as functions
4. **Keep content closures simple** - extract complex logic
5. **Use appropriate text styles** (H1-H6, Body, Caption, etc.)
6. **Apply proper spacing** between elements using gap/padding
7. **Consider accessibility** - use ariaLabel, semantic elements
8. **Use modifier chaining** for clean styling

## File Structure

For Loom themes:
```
theme-name/
├── style.css          # Theme metadata
├── functions.php      # Theme setup
├── index.php          # Main template with Loom components
└── src/               # Optional: custom components
    └── Components/
```

## Checking Loom Core

Always check if Loom Core is active:

```php
if (!class_exists('\Loom\Core\Components\Modifier')) {
    wp_die('Loom Theme requires the Loom Core plugin.');
}
```

## WordPress Integration

Loom works within WordPress templates:

```php
// In index.php or any template
<?php
use Loom\Core\Components\Modifier;
// ... other imports

// Build the entire page with Loom
Column(content: function() {
    // WordPress functions work inside
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            Card(content: function() {
                Text(get_the_title(), style: TextStyle::H2);
                Box(content: function() {
                    the_content();
                });
            });
        }
    }
});
```

---

When working with Loom, focus on:
- Declarative component composition
- Consistent use of design tokens
- Clean, readable modifier chains
- Proper content closure patterns
