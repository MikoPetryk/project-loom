# Loom Framework Documentation

A modern, declarative UI framework for WordPress inspired by Jetpack Compose.

## Table of Contents

1. [Installation](#installation)
2. [Quick Start](#quick-start)
3. [Core Concepts](#core-concepts)
4. [Components Reference](#components-reference)
5. [Modifier API](#modifier-api)
6. [Design Tokens](#design-tokens)
7. [Best Practices](#best-practices)
8. [Plugins](#plugins)

---

## Installation

### Requirements
- WordPress 6.0+
- PHP 8.1+

### Setup
1. Upload `plugins/loom-core` to `wp-content/plugins/`
2. Upload `plugins/theme-manager` to `wp-content/plugins/` (optional, for design tokens)
3. Upload `plugins/icon-manager` to `wp-content/plugins/` (optional, for icons)
4. Upload `plugins/noti-plugin` to `wp-content/plugins/` (optional, for notifications)
5. Upload `themes/loom-theme` to `wp-content/themes/`
6. Activate Loom Core plugin first, then other plugins
7. Activate Loom Theme

---

## Quick Start

```php
<?php
use Loom\Core\Components\Modifier;
use Loom\Core\Components\TextStyle;
use Loom\Core\Components\ButtonStyle;
use Loom\Core\Tokens\Colors;
use Loom\Core\Tokens\Spacing;

// Simple column layout
Column(
    gap: Spacing::md,
    content: function() {
        Text('Hello, Loom!', style: TextStyle::H1);
        Text('Build beautiful UIs with PHP.', color: Colors::textSecondary);

        Row(gap: Spacing::sm, content: function() {
            Button(text: 'Get Started', style: ButtonStyle::Filled);
            Button(text: 'Learn More', style: ButtonStyle::Outlined);
        });
    }
);
```

---

## Core Concepts

### Declarative UI
Loom uses a declarative approach - you describe *what* you want, not *how* to build it:

```php
// Declarative: describe the UI structure
Column(content: function() {
    Text('Title');
    Text('Subtitle');
});

// NOT imperative: don't manually create HTML
// echo '<div><span>Title</span><span>Subtitle</span></div>';
```

### Components
Everything is a component. Components are functions that render UI:

```php
Text('Hello');           // Text component
Button(text: 'Click');   // Button component
Card(content: fn() => Text('Inside card'));  // Containment component
```

### Modifiers
Modifiers style and configure components using a fluent API:

```php
Text(
    'Styled text',
    modifier: Modifier::new()
        ->padding(16)
        ->background(Colors::primary)
        ->color(Colors::onPrimary)
        ->rounded(8)
);
```

### Content Closures
Container components use closures for nested content:

```php
Card(
    padding: Spacing::lg,
    content: function() {
        Text('Card Title', style: TextStyle::H3);
        Text('Card description here.');
    }
);
```

---

## Components Reference

### Foundation Components

#### Column
Vertical layout container.

```php
Column(
    gap: Spacing::md,           // Space between children
    align: 'center',            // start, center, end, stretch
    justify: 'start',           // start, center, end, between, around
    modifier: Modifier::new(),
    content: function() {
        // Children
    }
);
```

#### Row
Horizontal layout container.

```php
Row(
    gap: Spacing::sm,
    align: 'center',
    justify: 'between',
    wrap: true,                 // Allow wrapping
    modifier: Modifier::new(),
    content: function() {
        // Children
    }
);
```

#### Box
Stack/overlay container (grid-based).

```php
Box(
    align: 'center',            // top-start, center, bottom-end, etc.
    modifier: Modifier::new(),
    content: function() {
        // Children stack on top of each other
    }
);
```

#### Spacer
Empty space for layouts.

```php
Spacer(size: 24);              // Square spacer
Spacer(width: 100, height: 50); // Specific dimensions
Spacer();                       // Flexible spacer (flex: 1)
```

#### Divider
Horizontal or vertical separator line.

```php
Divider();                                    // Horizontal
Divider(vertical: true);                      // Vertical
Divider(color: Colors::border, thickness: 2); // Custom
```

---

### Basic Components

#### Text
Display text with optional styling.

```php
Text('Hello World');
Text('Heading', style: TextStyle::H1);
Text('Muted', color: Colors::textSecondary);
Text('Custom', modifier: Modifier::new()->fontSize(20)->fontWeight(600));
```

**TextStyle options:**
- `TextStyle::H1` through `TextStyle::H6`
- `TextStyle::Body`, `TextStyle::BodyLarge`, `TextStyle::BodySmall`
- `TextStyle::Caption`, `TextStyle::Label`, `TextStyle::Overline`

#### Button
Clickable action element.

```php
Button(text: 'Click Me');
Button(text: 'Submit', style: ButtonStyle::Filled);
Button(text: 'Cancel', style: ButtonStyle::Outlined);
Button(text: 'Link', style: ButtonStyle::Text);
Button(text: 'Soft', style: ButtonStyle::Tonal);

// With actions
Button(text: 'Alert', onClick: "alert('Clicked!')");
Button(text: 'Navigate', href: '/page');

// Disabled
Button(text: 'Disabled', disabled: true);

// Custom color
Button(text: 'Danger', color: Colors::error);
```

#### IconButton
Icon-only button.

```php
IconButton(icon: '⚙️');
IconButton(icon: '×', onClick: 'closeModal()');
IconButton(icon: '🔍', size: 48);
```

#### Image
Display images.

```php
Image(src: '/path/to/image.jpg', alt: 'Description');
Image(src: $url, width: 300, height: 200, fit: 'cover');
// fit options: cover, contain, fill, none
```

---

### Containment Components

#### Surface
Basic container with elevation and color.

```php
Surface(
    elevation: 2,
    color: Colors::surface,
    rounded: 12,
    content: function() {
        Text('Content');
    }
);
```

#### Card
Elevated container (Surface with defaults).

```php
Card(
    elevation: 1,
    padding: Spacing::lg,
    rounded: 12,
    onClick: 'handleClick()',  // Optional click handler
    content: function() {
        Text('Card Content');
    }
);
```

#### Dialog
Modal dialog overlay.

```php
Dialog(
    open: $isOpen,
    onClose: 'closeDialog()',
    maxWidth: 500,
    content: function() {
        Text('Dialog Title', style: TextStyle::H3);
        Text('Dialog content here.');
        Button(text: 'Close', onClick: 'closeDialog()');
    }
);
```

#### Scaffold
Page layout structure.

```php
Scaffold(
    topBar: function() {
        Row(content: function() {
            Text('App Title', style: TextStyle::H4);
        });
    },
    bottomBar: function() {
        // Bottom navigation
    },
    floatingAction: function() {
        Button(text: '+', style: ButtonStyle::Filled);
    },
    content: function() {
        // Main content
    }
);
```

---

### Feedback Components

#### Alert
Status messages.

```php
Alert(message: 'Operation successful!', type: AlertType::Success);
Alert(message: 'Something went wrong.', type: AlertType::Error);
Alert(message: 'Please note...', type: AlertType::Warning);
Alert(message: 'FYI...', type: AlertType::Info);

// With title and dismiss
Alert(
    message: 'Details here.',
    type: AlertType::Info,
    title: 'Information',
    dismissible: true,
    onDismiss: 'hideAlert()'
);
```

#### Badge
Small status indicator.

```php
Badge(content: '5');
Badge(content: 99, color: Colors::error);
Badge(dot: true);  // Dot-only badge
```

#### Progress
Progress indicators.

```php
// Linear progress
Progress(value: 65);
Progress(value: 75, color: Colors::success, height: 8);

// Indeterminate (no value)
Progress();

// Circular progress
Progress(circular: true, size: 48);
Progress(value: 50, circular: true, label: '50%');
```

#### Chip
Compact interactive elements.

```php
Chip(label: 'Tag');
Chip(label: 'Selected', selected: true);
Chip(label: 'Clickable', onClick: 'handleChip()');
Chip(label: 'Deletable', onDelete: 'removeChip()');
Chip(label: 'With Icon', icon: '⭐');
```

#### Tooltip
Hover information.

```php
Tooltip(
    text: 'Helpful information',
    position: 'top',  // top, bottom, left, right
    content: function() {
        Button(text: 'Hover me');
    }
);
```

---

### Input Components

#### TextField
Text input field.

```php
TextField(
    value: $currentValue,
    label: 'Email',
    placeholder: 'Enter your email',
    name: 'email',
    type: 'email',
    required: true,
    error: $errorMessage,
    helper: 'We won\'t share your email.',
    onChange: 'handleChange(this.value)'
);
```

#### TextArea
Multi-line text input.

```php
TextArea(
    value: $content,
    label: 'Description',
    placeholder: 'Enter description...',
    rows: 6,
    name: 'description'
);
```

#### Checkbox
Toggle checkbox.

```php
Checkbox(label: 'Accept terms', checked: false, name: 'terms');
Checkbox(
    label: 'Subscribe',
    checked: true,
    onChange: 'handleCheck(this.checked)',
    color: Colors::primary
);
```

#### Switch_
Toggle switch (note the underscore to avoid PHP keyword).

```php
Switch_(label: 'Dark Mode', checked: false, name: 'darkMode');
Switch_(
    label: 'Notifications',
    checked: true,
    onChange: 'toggleNotifications(this.checked)'
);
```

#### Slider
Range input.

```php
Slider(value: 50, min: 0, max: 100);
Slider(
    value: 75,
    min: 0,
    max: 100,
    step: 5,
    label: 'Volume',
    showValue: true,
    onChange: 'setVolume(this.value)'
);
```

#### Select
Dropdown selection.

```php
Select(
    options: [
        'small' => 'Small',
        'medium' => 'Medium',
        'large' => 'Large',
    ],
    value: 'medium',
    label: 'Size',
    name: 'size',
    onChange: 'handleSizeChange(this.value)'
);
```

---

### Head Component
Set page metadata declaratively.

```php
Head(
    title: 'Page Title',
    description: 'Page description for SEO',
    keywords: 'loom, wordpress, php',
    ogImage: '/images/og-image.jpg',
    themeColor: '#336659'
);
```

---

## Modifier API

The Modifier class provides a fluent API for styling components.

### Creating Modifiers

```php
// Method 1: Static new()
$mod = Modifier::new()->padding(16)->background('red');

// Method 2: Pass directly to component
Text('Hello', modifier: Modifier::new()->fontSize(20));
```

### Size & Dimensions

```php
->width(200)              // Fixed width (px or string)
->height(100)             // Fixed height
->size(50)                // Both width and height
->minWidth(100)           // Minimum width
->maxWidth(600)           // Maximum width
->minHeight(50)           // Minimum height
->maxHeight(400)          // Maximum height
->fillMaxWidth()          // width: 100%
->fillMaxHeight()         // height: 100%
->fillMaxSize()           // Both 100%
->wrapContentWidth()      // width: fit-content
->wrapContentHeight()     // height: fit-content
->aspectRatio(16/9)       // Aspect ratio
```

### Padding & Margin

```php
->padding(16)                              // All sides
->padding(horizontal: 16, vertical: 8)     // Horizontal/vertical
->paddingX(16)                             // Left and right
->paddingY(8)                              // Top and bottom
->paddingTop(16)                           // Individual sides
->paddingBottom(16)
->paddingLeft(16)
->paddingRight(16)

->margin(16)                               // Same pattern as padding
->marginX(16)
->marginY(8)
->marginTop(16)
// ... etc.

->gap(16)                                  // Gap between flex children
```

### Flexbox

```php
->flex()                  // display: flex
->flexDirection('column') // row, column, row-reverse, column-reverse
->justifyContent('center') // flex-start, center, flex-end, space-between, space-around
->alignItems('center')    // flex-start, center, flex-end, stretch, baseline
->alignSelf('center')     // Override parent's alignItems
->flexWrap('wrap')        // wrap, nowrap, wrap-reverse
->flexGrow(1)             // Grow factor
->flexShrink(0)           // Shrink factor
->weight(1)               // Shorthand for flex: 1
```

### Grid

```php
->grid()                  // display: grid
->gridColumns(3)          // 3 equal columns
->gridColumns('1fr 2fr')  // Custom template
->gridRows(2)             // 2 equal rows
->gridSpan(2)             // Span 2 columns
->gridSpan(2, 3)          // Span 2 columns, 3 rows
```

### Colors & Background

```php
->background(Colors::primary)
->backgroundColor('#ff0000')
->backgroundImage('/path/to/image.jpg')
->backgroundGradient('linear-gradient(45deg, red, blue)')
->backgroundSize('cover')
->backgroundPosition('center')
->color(Colors::text)
->opacity(0.5)
```

### Borders & Shapes

```php
->border('1px solid red')
->borderWidth(2)
->borderColor(Colors::border)
->borderTop('1px solid black')
->rounded(8)              // Border radius
->roundedTop(8)           // Top corners only
->roundedBottom(8)        // Bottom corners only
->roundedFull()           // Fully rounded (pill shape)
```

### Shadows & Elevation

```php
->shadow(1)               // Elevation 0-5
->shadow(3)               // Higher = more shadow
->boxShadow('0 4px 6px rgba(0,0,0,0.1)')
->dropShadow('0 4px 6px rgba(0,0,0,0.1)')
```

### Typography

```php
->fontSize(16)
->fontWeight(600)         // 100-900 or bold, normal
->fontFamily('Inter, sans-serif')
->lineHeight(1.5)
->letterSpacing(1)
->textAlign('center')     // left, center, right, justify
->textDecoration('underline')
->textTransform('uppercase')
->textOverflow('ellipsis') // With overflow: hidden
->lineClamp(3)            // Limit to 3 lines
```

### Positioning

```php
->position('relative')
->absolute()
->relative()
->fixed()
->sticky()
->top(0)
->bottom(0)
->left(0)
->right(0)
->inset(0)                // All sides at once
->zIndex(100)
```

### Transforms

```php
->offset(10, 20)          // translate(10px, 20px)
->offsetX(10)
->offsetY(20)
->rotate(45)              // 45 degrees
->scale(1.5)
->scaleX(2)
->scaleY(0.5)
->skew(10, 5)
->transformOrigin('center')
```

### Filters & Effects

```php
->blur(4)                 // Blur in pixels
->brightness(1.2)
->contrast(1.1)
->grayscale(1)            // 0-1
->saturate(1.5)
->sepia(0.5)
->invert(1)
->hueRotate(90)
->backdropBlur(10)        // Frosted glass effect
```

### Transitions & Animations

```php
->transition('all 0.3s ease')
->transitionProperty('background, color')
->transitionDuration('0.3s')
->animation('fadeIn 0.5s ease')
```

### Interaction

```php
->clickable('handleClick()')
->hoverable()             // Adds hover state class
->focusable()             // Adds tabindex and focus styles
->cursor('pointer')
->userSelect('none')
->pointerEvents('none')
```

### Visibility

```php
->visible()
->invisible()             // visibility: hidden (keeps space)
->hidden()                // display: none (removes from flow)
```

### Accessibility

```php
->ariaLabel('Description')
->ariaHidden(true)
->ariaExpanded(false)
->role('button')
->contentDescription('Screen reader text')
```

### Raw / Escape Hatch

```php
->style('custom-property', 'value')
->class('my-class', 'another-class')
->attr('data-custom', 'value')
->data('id', '123')       // data-id="123"
->id('element-id')
```

### Conditional Modifiers

```php
->when($condition, fn($m) => $m->background('red'))
->unless($condition, fn($m) => $m->opacity(0.5))
```

---

## Design Tokens

### Colors

```php
use Loom\Core\Tokens\Colors;

Colors::primary           // Primary brand color
Colors::onPrimary         // Text on primary
Colors::secondary         // Secondary color
Colors::surface           // Card/surface background
Colors::background        // Page background
Colors::text              // Primary text
Colors::textSecondary     // Muted text
Colors::error             // Error state
Colors::success           // Success state
Colors::warning           // Warning state
Colors::info              // Info state
Colors::border            // Border color
```

### Spacing

```php
use Loom\Core\Tokens\Spacing;

Spacing::none   // 0
Spacing::xxs    // 2
Spacing::xs     // 4
Spacing::sm     // 8
Spacing::md     // 16
Spacing::lg     // 24
Spacing::xl     // 32
Spacing::xxl    // 48
Spacing::xxxl   // 64
```

### Shapes

```php
use Loom\Core\Tokens\Shapes;

Shapes::none    // 0
Shapes::xs      // 4
Shapes::sm      // 8
Shapes::md      // 12
Shapes::lg      // 16
Shapes::xl      // 24
Shapes::full    // 9999 (pill shape)
```

---

## Best Practices

### 1. Use Semantic Tokens
```php
// Good - uses design tokens
Text('Hello', color: Colors::textSecondary);
Card(padding: Spacing::lg);

// Avoid - hardcoded values
Text('Hello', modifier: Modifier::new()->color('#666'));
Card(padding: 24);
```

### 2. Compose Small Components
```php
// Good - reusable component
function FeatureCard(string $title, string $description): void {
    Card(padding: Spacing::lg, content: function() use ($title, $description) {
        Text($title, style: TextStyle::H3);
        Text($description, color: Colors::textSecondary);
    });
}

// Use it
FeatureCard('Fast', 'Lightning quick performance.');
FeatureCard('Simple', 'Easy to learn and use.');
```

### 3. Keep Content Closures Clean
```php
// Good - extract logic
$items = getMenuItems();

Column(content: function() use ($items) {
    foreach ($items as $item) {
        MenuItem($item);
    }
});

// Avoid - complex logic inside closures
Column(content: function() {
    // Don't put database queries or complex logic here
});
```

### 4. Use Named Parameters
```php
// Good - clear intent
Button(
    text: 'Submit',
    style: ButtonStyle::Filled,
    onClick: 'handleSubmit()',
    disabled: $isLoading
);

// Harder to read
Button('Submit', ButtonStyle::Filled, 'handleSubmit()', null, null, $isLoading);
```

### 5. Leverage Modifier Chaining
```php
// Good - fluent chain
$cardStyle = Modifier::new()
    ->padding(Spacing::lg)
    ->rounded(Shapes::lg)
    ->shadow(2)
    ->background(Colors::surface);

// Reuse the modifier
Card(modifier: $cardStyle, content: fn() => Text('Card 1'));
Card(modifier: $cardStyle->copy()->background(Colors::primary), content: fn() => Text('Card 2'));
```

### 6. Responsive Considerations
```php
// Use percentage/relative units for responsive layouts
Row(
    wrap: true,
    gap: Spacing::md,
    content: function() {
        // Cards will wrap on small screens
        Card(modifier: Modifier::new()->minWidth(280)->weight(1));
        Card(modifier: Modifier::new()->minWidth(280)->weight(1));
        Card(modifier: Modifier::new()->minWidth(280)->weight(1));
    }
);
```

---

## Plugins

### Loom Core
The foundation - provides all components, modifiers, and base functionality.

### Loom Theme Design
Centralized design tokens with admin UI for customization.
- Custom color palettes
- Typography scales
- Spacing and shape tokens
- CSS variable output

### Loom Icons
SVG icon system with multiple packs.
```php
// With Loom Icons
echo IconsManager::UiconsRoundedHome(24, 24);
```

### Loom Notifications
Toast notification system.
```php
// PHP API
noti('success', 'Operation completed!');
noti('error', 'Something went wrong.');

// JS API
Noti.success('Saved!');
Noti.error('Failed to save.');
```

---

## License

GPL-2.0-or-later

---

*Built with Loom - A modern declarative UI framework for WordPress.*
