# Icon Manager v2.0 - Installation & Migration Guide

## 🎯 What You're Getting

IDE autocomplete-enabled icon manager with your exact desired syntax:
```php
Icon(General::Search)->size(24)->class('icon')->render()
```

## 📦 File Structure

```
icon-manager/                  # Your plugin folder
├── icon-manager.php          # Main plugin file
├── uninstall.php             # Cleanup on uninstall
├── materials/
│   └── icons/                # Your existing icons (preserved!)
│       ├── General/
│       ├── Socials/
│       └── ...
├── data/                     # NEW - Auto-generated enums
│   ├── General.php          # Auto-generated on upload
│   ├── Socials.php          # Auto-generated on upload
│   └── ...
├── includes/
│   ├── Core/
│   │   ├── Plugin.php
│   │   ├── IconBuilder.php          # NEW - Fluent API
│   │   ├── IconRenderer.php
│   │   ├── IconPackManager.php
│   │   ├── IconUploader.php
│   │   ├── IconPackGenerator.php    # NEW - Generates enums
│   │   ├── LegacyBridge.php
│   │   └── Activator.php
│   ├── Admin/
│   │   ├── AdminMenu.php
│   │   ├── AdminAssets.php
│   │   ├── AdminNotices.php
│   │   ├── UploadHandler.php        # Updated
│   │   ├── CreateHandler.php        # Updated
│   │   ├── DeleteHandler.php        # Updated
│   │   └── Views/
│   │       └── main-page.php
│   └── IconPacks/
│       └── IconPackInterface.php    # NEW - Enum interface
└── assets/
    ├── css/admin.css
    ├── js/admin.js
    └── icons/ (symlink to materials/icons or same)
```

## 🔄 Safe Migration from v0.01

### Step 1: Backup (Important!)
```bash
# Backup your current plugin folder
cp -r wp-content/plugins/icon-manager wp-content/plugins/icon-manager-backup
```

### Step 2: Replace Files

**Option A: Keep materials/icons folder**
```bash
cd wp-content/plugins/icon-manager/

# Keep your existing icons folder!
# Just replace the PHP files

# Copy new files (they won't overwrite materials/icons/)
# Upload all files from /outputs/ EXCEPT materials/icons/
```

**Option B: Fresh install (if you have icons backed up)**
```bash
# Remove old plugin
rm -rf wp-content/plugins/icon-manager/

# Upload new plugin
# Copy materials/icons/ from backup
```

### Step 3: Activate & Auto-Generate

1. Go to WordPress Admin → Plugins
2. Deactivate (if active)
3. Activate "Icon Manager"
4. **Magic happens**: Plugin detects existing icon packs and auto-generates enum files!

Check `/data/` folder - you'll see:
- `General.php` (if you have General pack)
- `Socials.php` (if you have Socials pack)
- etc.

### Step 4: Verify

1. Go to Icon Manager admin page
2. You should see all your existing packs
3. All your icons are still there!

## ✅ Backward Compatibility

**All your v0.01 code still works!**

```php
// OLD v0.01 code - STILL WORKS
<?php echo IconsManager::GeneralSearch(24, 24, 'icon'); ?>
<?php echo IconsManager::getInstance()->SocialsFacebook(32); ?>

// NEW v2.0 code - WITH AUTOCOMPLETE
<?php echo Icon(General::Search)->size(24)->class('icon')->render(); ?>
<?php echo Icon(Socials::Facebook)->size(32)->render(); ?>
```

You can use both in the same project!

## 🎨 Using the New Syntax

### After Installation

Type in your PHP file:
```php
<?php
Icon(█
```

Your IDE shows: `General::`, `Socials::`, `Logo::`...

Select one:
```php
<?php
Icon(General::█
```

Your IDE shows: `Search`, `Cart`, `Close`, `ArrowLeft`...

Complete:
```php
<?php
echo Icon(General::Search)->size(24)->render();
```

## 🔧 Requirements

- **PHP 8.1+** (for enum support)
- WordPress 5.8+
- Existing v0.01 installation (optional - works on fresh install too)

## 📝 What Gets Auto-Generated

When you have icons in `/materials/icons/General/`:
- Search.svg
- Cart.svg
- Close.svg

Plugin creates `/data/General.php`:
```php
enum General: string implements IconPackInterface {
    case Search = 'Search';
    case Cart = 'Cart';
    case Close = 'Close';
    // ...
}
```

## 🚨 Important Notes

### DO keep these folders:
- ✅ `/materials/icons/` - Your actual icon files
- ✅ `/data/` - Auto-generated enums (but can regenerate)

### DON'T manually edit:
- ❌ Files in `/data/` folder (auto-generated)
- ❌ They regenerate when you upload/delete icons

### Safe to delete:
- Old `/data/IconManager.php` from v0.01
- Old `/data/IconPackEnum.php` from v0.01

## 🔄 If Something Goes Wrong

### Enums not generating?
```php
// In WordPress admin, add this temporarily to functions.php:
add_action('init', function() {
    if (current_user_can('manage_options')) {
        IconManager\Core\IconPackGenerator::regenerateAllEnums();
        echo 'Enums regenerated!';
    }
});
// Visit site once, then remove this code
```

### Icons not showing?
1. Check `/materials/icons/` folder exists
2. Check SVG files are there
3. Enable WP_DEBUG
4. Check error logs

### IDE not autocompleting?
1. Verify PHP 8.1+ in IDE settings
2. Check `/data/` folder has enum files
3. Clear IDE cache (File → Invalidate Caches)
4. Restart IDE

## 💡 Pro Tips

### 1. Use Import Statement
```php
<?php
use IconManager\IconPacks\General;
use IconManager\IconPacks\Socials;

// Now shorter:
echo Icon(General::Search)->size(24)->render();
echo Icon(Socials::Facebook)->size(32)->render();
```

### 2. Create Helper Function
```php
// In your theme's functions.php:
function icon($enum, $size = 24, $class = '') {
    $icon = Icon($enum)->size($size);
    if ($class) {
        $icon->class($class);
    }
    return $icon->render();
}

// Usage:
echo icon(General::Search, 24, 'search-icon');
```

### 3. Git Best Practices
```
# In .gitignore (optional):
# /data/*.php  # If you want to exclude auto-generated enums

# OR commit them (recommended):
# Your team gets autocomplete immediately!
```

## 📞 Troubleshooting

**Q: Can I use both old and new syntax?**  
A: Yes! They work together perfectly.

**Q: Will this break my existing site?**  
A: No! Full backward compatibility.

**Q: Do I need to update all my code?**  
A: No! Update when convenient.

**Q: What if I'm on PHP 8.0?**  
A: Stay on v0.01 or upgrade to PHP 8.1.

**Q: Can I switch back to v0.01?**  
A: Yes, just restore from backup.

## 🎉 Summary

1. ✅ Backup your plugin folder
2. ✅ Replace PHP files (keep /materials/icons/)
3. ✅ Activate plugin
4. ✅ Enums auto-generate
5. ✅ Start using `Icon(Pack::Name)` syntax
6. ✅ Old code still works!

That's it! You now have full IDE autocomplete while keeping all your existing functionality.
