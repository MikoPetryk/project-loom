<?php
/**
 * Icon Manager v2.0 - Quick Usage Guide
 * 
 * This plugin provides IDE autocomplete through PHP 8.1 enums
 */

// =============================================================================
// BASIC USAGE - Simple syntax with IDE autocomplete!
// =============================================================================

// Just use the pack name directly - Type "Icon(General::" and see all icons
echo Icon(General::Search)->render();

// With size
echo Icon(General::ArrowLeft)->size(24)->render();

// With size and class  
echo Icon(General::Cart)->size(32)->class('cart-icon')->render();

// Full chain
echo Icon(Socials::Facebook)
    ->size(24)
    ->class('social-icon')
    ->style('color: #1877f2;')
    ->id('fb-icon')
    ->render();

// Direct output (no echo needed)
Icon(General::Upload)->size(20)->output();


// =============================================================================
// NAVIGATION EXAMPLE
// =============================================================================
?>
<nav>
    <a href="/">
        <?php Icon(General::Home)->size(20)->output(); ?>
        Home
    </a>
    <a href="/cart">
        <?php Icon(General::Cart)->size(20)->output(); ?>
        Cart
    </a>
</nav>

<?php
// =============================================================================
// SOCIAL ICONS
// =============================================================================
?>
<div class="social-links">
    <a href="#"><?php echo Icon(Socials::Facebook)->size(32); ?></a>
    <a href="#"><?php echo Icon(Socials::Twitter)->size(32); ?></a>
    <a href="#"><?php echo Icon(Socials::Instagram)->size(32); ?></a>
</div>

<?php
// =============================================================================
// BUTTONS
// =============================================================================
?>
<button class="btn">
    <?php Icon(General::Upload)->size(18)->output(); ?>
    Upload File
</button>

<?php
// =============================================================================
// DYNAMIC SELECTION WITH MATCH
// =============================================================================

$status = 'success';

$icon = match($status) {
    'success' => Icon(General::Success)->size(20),
    'error' => Icon(General::Error)->size(20),
    'pending' => Icon(General::Clock)->size(20),
    default => Icon(General::Info)->size(20)
};

echo $icon;


// =============================================================================
// BACKWARD COMPATIBILITY - Old code still works!
// =============================================================================

// Legacy API from v0.01 still works
echo IconsManager::GeneralSearch(24, 24, 'search-icon');
echo IconsManager::getInstance()->GeneralArrowLeft(24);


// =============================================================================
// INSTALLATION & SETUP
// =============================================================================

/*
1. Upload plugin to wp-content/plugins/icon-manager/
2. Activate plugin
3. Go to Icon Manager in admin menu
4. Create icon pack (e.g., "General")
5. Upload SVG files
6. Enum file auto-generates in /data/ folder
7. Use in your theme: Icon(General::IconName)->size(24)->render()

Your IDE will now autocomplete:
- Icon( ← shows all packs (General, Socials, Logo, etc.)
- Icon(General:: ← shows all icons in General pack
- ->size( ← shows method signature
*/


// =============================================================================
// MIGRATION FROM v0.01
// =============================================================================

/*
All your existing icons in /materials/icons/ will work!

The plugin will:
1. Detect existing icon packs on activation
2. Auto-generate enum files for each pack
3. Keep all your existing icons
4. Keep backward compatibility with old IconsManager:: calls

Your v0.01 code will continue working:
- IconsManager::GeneralSearch(24, 24, 'icon')
- IconsManager::getInstance()->SocialsFacebook(32)

New v2.0 syntax with autocomplete:
- Icon(General::Search)->size(24)->class('icon')->render()
- Icon(Socials::Facebook)->size(32)->render()
*/
