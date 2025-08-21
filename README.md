# FluentCommunity Color Customizer

A WordPress plugin that provides admin control over the FluentCommunity color palette without modifying core plugin files. This plugin is update-safe and allows you to customize the 8 main theme colors used in the FluentCommunity block editor.

## Features

- **Update-Safe**: No modifications to FluentCommunity core files
- **Easy Color Management**: WordPress color picker interface for all 8 theme colors
- **Real-time Preview**: See color swatches in the admin interface with live updates
- **Block Editor Integration**: Colors work seamlessly in FluentCommunity's Gutenberg editor
- **Light & Dark Theme Support**: Separate color customization for both themes
- **Automatic Integration**: Hooks into FluentCommunity's existing color system
- **Admin-Only Access**: Requires `manage_options` capability
- **Enhanced UI**: Improved admin interface with better color previews

## Installation

### Method 1: Manual Installation

1. **Download the Plugin**
   - Download or clone this repository
   - Upload the entire `fluent-community-color-customizer` folder to `/wp-content/plugins/`

2. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "FluentCommunity Color Customizer" and click "Activate"

### Method 2: ZIP Installation

1. **Create ZIP File**
   - Compress the `fluent-community-color-customizer` folder into a ZIP file
   
2. **Upload via WordPress Admin**
   - Go to WordPress Admin → Plugins → Add New → Upload Plugin
   - Choose the ZIP file and click "Install Now"
   - Activate the plugin

### Requirements

- WordPress 5.0 or higher
- FluentCommunity plugin must be active
- PHP 7.0 or higher

## Usage

### Accessing the Settings

1. Go to WordPress Admin → Settings → FluentCommunity Colors
2. You'll see 8 color picker fields for the theme palette

### Color Palette Explanation

The plugin allows you to customize these 8 colors that are used throughout FluentCommunity:

1. **Primary Color** (`#4F46E5`) - Main accent color used for buttons and links
2. **Secondary Color** (`#7C3AED`) - Secondary accent color for hover states
3. **Strongest Text** (`#1F2937`) - Darkest text color for headings
4. **Strong Text** (`#374151`) - Strong text color for important content
5. **Medium Text** (`#6B7280`) - Medium text color for regular content
6. **Subtle Text** (`#9CA3AF`) - Subtle text color for secondary information
7. **Subtle Background** (`#E5E7EB`) - Light background color for sections
8. **Lighter Background** (`#FFFFFF`) - Lightest background color

### Making Changes

1. **Select Colors**: Click on any color field to open the WordPress color picker
2. **Live Preview**: Watch the preview swatches update in real-time as you change colors
3. **Reset Individual Colors**: Use the "Clear" button next to each field to restore the default color
4. **Save Changes**: Click "Save Changes" to apply your customizations
5. **Immediate Effect**: Changes are applied immediately across your FluentCommunity installation, including the block editor

### Enhanced Features

- **Real-time Preview**: Color swatches update immediately as you change colors
- **Better Layout**: Grid-based color preview with larger swatches
- **Block Editor Support**: Custom colors appear correctly in FluentCommunity's Gutenberg editor
- **Improved Styling**: Enhanced admin interface with better visual feedback

## How It Works

### Technical Implementation

The plugin uses multiple WordPress and FluentCommunity hooks for comprehensive integration:

- **`fluent_community/color_schmea_config`** - Overrides the color configuration
- **`fluent_community/block_editor_settings`** - Modifies block editor color settings
- **`fluent_community/block_editor_head`** - Injects CSS specifically for the block editor
- **`wp_enqueue_scripts`** - Injects custom CSS on frontend
- **`admin_enqueue_scripts`** - Injects custom CSS in admin area

### CSS Override Method

The plugin generates comprehensive CSS that overrides FluentCommunity's default colors:

```css
:root {
    --theme-palette-color-1: #your-custom-color !important;
    --wp--preset--color--theme-palette-color-1: #your-custom-color !important;
}

body {
    --fcom-theme-palette-color-1: #your-custom-color !important;
}

.editor-styles-wrapper {
    --theme-palette-color-1: #your-custom-color !important;
}
```

### Block Editor Integration

The plugin specifically targets FluentCommunity's block editor by:
- Hooking into `fluent_community/block_editor_settings` to modify color palette
- Using `fluent_community/block_editor_head` to inject editor-specific CSS
- Overriding both CSS variables and color classes for complete coverage

### Database Storage

Custom colors are stored in the WordPress options table as `fccc_custom_colors`, making them:
- Persistent across plugin updates
- Included in WordPress backups
- Easily exportable/importable
- Site-specific (works with multisite)

## Compatibility

- **FluentCommunity Updates**: ✅ Fully compatible - no core files modified
- **WordPress Updates**: ✅ Uses standard WordPress APIs
- **Block Editor**: ✅ Full integration with FluentCommunity's Gutenberg editor
- **Theme Changes**: ✅ Works with any WordPress theme
- **Multisite**: ✅ Works on multisite installations (per-site settings)
- **Caching Plugins**: ✅ Compatible with caching plugins

## Troubleshooting

### Plugin Not Working

1. **Check FluentCommunity**: Ensure FluentCommunity plugin is active and up to date
2. **Check Permissions**: Ensure you have `manage_options` capability
3. **Clear Cache**: If using caching plugins, clear cache after making changes
4. **Check PHP Version**: Ensure PHP 7.0 or higher

### Colors Not Applying

1. **Hard Refresh**: Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
2. **Check CSS Priority**: The plugin uses `!important` declarations
3. **Theme Conflicts**: Test with a default WordPress theme
4. **Plugin Conflicts**: Temporarily deactivate other plugins to test

### Block Editor Issues

1. **Clear Editor Cache**: Refresh the FluentCommunity block editor page
2. **Check Hook Priority**: The plugin uses high priority hooks
3. **Verify Integration**: Ensure you're using the FluentCommunity block editor, not standard WordPress editor

### Reset to Defaults

To reset all colors to defaults:
1. Go to Settings → FluentCommunity Colors
2. Click "Clear" next to each color field
3. Click "Save Changes"

Alternatively, you can delete the `fccc_custom_colors` option from the database.

## Development

### File Structure
```
fluent-community-color-customizer/
├── fluent-community-color-customizer.php  # Main plugin file
└── README.md                              # This documentation
```

### Hooks Available

The plugin provides these hooks for developers:

```php
// Filter custom colors before applying
add_filter('fccc_custom_colors', function($colors) {
    // Modify $colors array
    return $colors;
});

// Modify color configuration
add_filter('fccc_color_config', function($config) {
    // Modify configuration
    return $config;
});
```

### Extending the Plugin

You can extend the plugin by:
1. Adding more color fields in `getDefaultColors()`
2. Modifying the CSS output in `generateCustomCSS()`
3. Adding additional color schemes or themes
4. Creating preset color combinations

### Code Quality

The plugin follows WordPress coding standards:
- Proper sanitization and escaping
- Nonce verification for forms
- Capability checks for admin access
- Internationalization ready
- Object-oriented design pattern

## Advanced Usage

### Programmatic Color Setting

You can set colors programmatically:

```php
$colors = array(
    '1' => '#FF0000', // Primary Color
    '2' => '#00FF00', // Secondary Color
    // ... etc
);
update_option('fccc_custom_colors', $colors);
```

### Integration with Other Plugins

The plugin can be integrated with other customization plugins by hooking into its filters and using the stored color values.

## License

GPL v2 or later - same as WordPress

## Known Issues

- **Link Color Override**: Colors selected in the block editor for Links may be overridden by the CSS for ".feed_md_content a". This is a known limitation of the current implementation.

## Author

**KitMage** - [https://kitmage.com](https://kitmage.com)

## Changelog

### Version 1.0.3
- Added known issues documentation
- Cleaned up code and removed unused functionality
- Prepared for WordPress directory submission

### Version 1.0.0
- Initial release
- 8 customizable color palette options
- WordPress color picker integration
- Real-time preview functionality
- Light and dark theme support
- Update-safe implementation
- Full block editor integration
- Enhanced admin interface
- Comprehensive CSS override system
- Multi-hook integration with FluentCommunity
