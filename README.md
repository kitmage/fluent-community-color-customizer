=== FluentCommunity Color Customizer ===
Contributors: kitmage
Tags: fluentcommunity, colors, customization, block-editor, themes
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Provides admin control over FluentCommunity's GUTENBERG BLOCK EDITOR color palette.

== Description ==

A WordPress plugin that provides admin control over the FluentCommunity color palette without modifying core plugin files. This plugin is update-safe and allows you to customize the 8 main theme colors used in the FluentCommunity block editor.

= Features =

* **Update-Safe**: No modifications to FluentCommunity core files
* **Easy Color Management**: WordPress color picker interface for all 8 theme colors
* **Real-time Preview**: See color swatches in the admin interface with live updates
* **Block Editor Integration**: Colors work seamlessly in FluentCommunity's Gutenberg editor
* **Light & Dark Theme Support**: Separate color customization for both themes
* **Automatic Integration**: Hooks into FluentCommunity's existing color system
* **Admin-Only Access**: Requires `manage_options` capability
* **Enhanced UI**: Improved admin interface with better color previews

= Color Palette Explanation =

The plugin allows you to customize these 8 colors that are used throughout FluentCommunity:

1. **Primary Color** (`#4F46E5`) - Main accent color used for buttons and links
2. **Secondary Color** (`#7C3AED`) - Secondary accent color for hover states
3. **Strongest Text** (`#1F2937`) - Darkest text color for headings
4. **Strong Text** (`#374151`) - Strong text color for important content
5. **Medium Text** (`#6B7280`) - Medium text color for regular content
6. **Subtle Text** (`#9CA3AF`) - Subtle text color for secondary information
7. **Subtle Background** (`#E5E7EB`) - Light background color for sections
8. **Lighter Background** (`#FFFFFF`) - Lightest background color

= Requirements =

* WordPress 5.0 or higher
* FluentCommunity plugin must be active
* PHP 7.0 or higher

== Installation ==

= Method 1: WordPress Admin =

1. Go to WordPress Admin → Plugins → Add New
2. Search for "FluentCommunity Color Customizer"
3. Click "Install Now" and then "Activate"

= Method 2: Manual Installation =

1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Activate the plugin

= Method 3: FTP Installation =

1. Download and extract the plugin files
2. Upload the entire `fluent-community-color-customizer` folder to `/wp-content/plugins/`
3. Go to WordPress Admin → Plugins and activate the plugin

== Usage ==

= Accessing the Settings =

1. Go to WordPress Admin → Settings → FluentCommunity Colors
2. You'll see 8 color picker fields for the theme palette organized in Light and Dark theme tabs

= Making Changes =

1. **Select Colors**: Click on any color field to open the WordPress color picker
2. **Live Preview**: Watch the preview swatches update in real-time as you change colors
3. **Reset Individual Colors**: Use the "Clear" button next to each field to restore the default color
4. **Save Changes**: Click "Save Changes" to apply your customizations
5. **Immediate Effect**: Changes are applied immediately across your FluentCommunity installation, including the block editor

== How It Works ==

= Technical Implementation =

The plugin uses multiple WordPress and FluentCommunity hooks for comprehensive integration:

* **`fluent_community/color_schmea_config`** - Overrides the color configuration
* **`fluent_community/block_editor_settings`** - Modifies block editor color settings
* **`fluent_community/block_editor_head`** - Injects CSS specifically for the block editor
* **`wp_enqueue_scripts`** - Injects custom CSS on frontend
* **`admin_enqueue_scripts`** - Injects custom CSS in admin area

= CSS Override Method =

The plugin generates comprehensive CSS that overrides FluentCommunity's default colors using CSS variables and direct class overrides for maximum compatibility.

= Database Storage =

Custom colors are stored in the WordPress options table as `fccc_custom_colors`, making them:

* Persistent across plugin updates
* Included in WordPress backups
* Easily exportable/importable
* Site-specific (works with multisite)

== Compatibility ==

* **FluentCommunity Updates**: ✅ Fully compatible - no core files modified
* **WordPress Updates**: ✅ Uses standard WordPress APIs
* **Block Editor**: ✅ Full integration with FluentCommunity's Gutenberg editor
* **Theme Changes**: ✅ Works with any WordPress theme
* **Multisite**: ✅ Works on multisite installations (per-site settings)
* **Caching Plugins**: ✅ Compatible with caching plugins

== Frequently Asked Questions ==

= Does this plugin modify FluentCommunity core files? =

No, this plugin uses WordPress hooks and filters to override colors without modifying any FluentCommunity core files. This makes it completely update-safe.

= Will my custom colors be lost when FluentCommunity updates? =

No, since we don't modify core files, your custom colors will persist through FluentCommunity updates.

= Can I use different colors for light and dark themes? =

Yes! The plugin provides separate color customization for both light and dark themes.

= Do the colors work in the block editor? =

Yes, the plugin fully integrates with FluentCommunity's block editor, so your custom colors will appear in the color palette when editing posts.

= Can I reset colors to defaults? =

Yes, you can reset individual colors using the "Clear" button next to each color field, or you can delete the `fccc_custom_colors` option from your database to reset all colors.

== Troubleshooting ==

= Plugin Not Working =

1. **Check FluentCommunity**: Ensure FluentCommunity plugin is active and up to date
2. **Check Permissions**: Ensure you have `manage_options` capability
3. **Clear Cache**: If using caching plugins, clear cache after making changes
4. **Check PHP Version**: Ensure PHP 7.0 or higher

= Colors Not Applying =

1. **Hard Refresh**: Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
2. **Check CSS Priority**: The plugin uses `!important` declarations
3. **Theme Conflicts**: Test with a default WordPress theme
4. **Plugin Conflicts**: Temporarily deactivate other plugins to test

= Block Editor Issues =

1. **Clear Editor Cache**: Refresh the FluentCommunity block editor page
2. **Check Hook Priority**: The plugin uses high priority hooks
3. **Verify Integration**: Ensure you're using the FluentCommunity block editor, not standard WordPress editor

== Screenshots ==

1. Admin interface showing color picker fields for light theme
2. Dark theme color customization tab
3. Real-time color preview swatches
4. Colors applied in FluentCommunity block editor

== Changelog ==

= 1.0.3 =
* Fixed security escaping issues for WordPress directory compliance
* Added proper readme.txt format
* Improved code documentation
* Enhanced security with proper output escaping

= 1.0.2 =
* Added light and dark theme support
* Enhanced admin interface with tabbed layout
* Improved real-time preview functionality
* Better CSS variable handling

= 1.0.1 =
* Improved block editor integration
* Enhanced CSS override system
* Better compatibility with caching plugins

= 1.0.0 =
* Initial release
* 8 customizable color palette options
* WordPress color picker integration
* Real-time preview functionality
* Update-safe implementation
* Full block editor integration
* Enhanced admin interface
* Comprehensive CSS override system
* Multi-hook integration with FluentCommunity

== Known Issues ==

* Colors selected in the block editor for Links may be overridden by the CSS for ".feed_md_content a". This is a known limitation of the current implementation.

== Upgrade Notice ==

= 1.0.3 =
Security and compliance update. Recommended for all users planning to submit to WordPress directory.

== Developer Information ==

= Hooks Available =

The plugin provides these hooks for developers:

`
// Filter custom colors before applying
add_filter('fccc_custom_colors', function($colors) {
    // Modify $colors array
    return $colors;
});
`

= Extending the Plugin =

You can extend the plugin by:

1. Adding more color fields in `getDefaultColors()`
2. Modifying the CSS output in `generateCustomCSS()`
3. Adding additional color schemes or themes
4. Creating preset color combinations

= Code Quality =

The plugin follows WordPress coding standards:

* Proper sanitization and escaping
* Nonce verification for forms
* Capability checks for admin access
* Internationalization ready
* Object-oriented design pattern
