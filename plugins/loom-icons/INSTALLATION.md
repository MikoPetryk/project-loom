# Loom Icons - Installation Guide

## Overview

IDE autocomplete-enabled icon manager with fluent API:
```php
Icon(General::Search)->size(24)->class('icon')->render()
```

## Installation

1. Upload `loom-icons` folder to `wp-content/plugins/`
2. Activate **Loom Icons** in WordPress admin
3. Go to **Loom Icons** in admin menu to manage icon packs

## File Structure

```
loom-icons/
├── loom-icons.php        # Main plugin file
├── uninstall.php         # Cleanup on uninstall
├── materials/
│   └── icons/            # Icon storage
│       ├── General/
│       ├── Socials/
│       └── ...
├── data/                 # Auto-generated enums
│   ├── General.php
│   ├── Socials.php
│   └── ...
├── Features/             # Plugin features
├── Support/              # Helper classes
└── assets/               # Admin assets
```

## Usage

```php
// Basic usage
echo IconsManager::GeneralSearch(24, 24);

// With Icon Builder
Icon(General::Search)->size(24)->render();

// With class and style
Icon(General::Home)
    ->size(32)
    ->class('my-icon')
    ->style('color: red')
    ->render();
```

## Requirements

- WordPress 6.0+
- PHP 8.1+
- Loom Core plugin (recommended)
