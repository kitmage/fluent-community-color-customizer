<?php
/**
 * Plugin Name: FluentCommunity Color Customizer
 * Plugin URI: https://kitmage.com
 * Description: Provides admin control over FluentCommunity's GUTENBERG BLOCK EDITOR color palette.
 * Version: 1.0.3
 * Author: KitMage
 * Author URI: https://kitmage.com
 * License: GPL v2 or later
 * Text Domain: fluent-community-color-customizer
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FCCC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FCCC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FCCC_VERSION', '1.0.3');

class FluentCommunityColorCustomizer {
    
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Check if FluentCommunity is active
        if (!$this->isFluentCommunityActive()) {
            add_action('admin_notices', array($this, 'fluentCommunityNotActiveNotice'));
            return;
        }
        
        // Initialize plugin functionality
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
        
        // Hook into FluentCommunity's color system
        add_filter('fluent_community/color_schmea_config', array($this, 'overrideColorConfig'), 10, 2);
        add_filter('fluent_community/block_editor_settings', array($this, 'overrideBlockEditorColors'), 10, 1);
        
        // Inject custom CSS
        add_action('wp_enqueue_scripts', array($this, 'injectCustomColors'), 20);
        add_action('admin_enqueue_scripts', array($this, 'injectCustomColorsAdmin'), 20);
        add_action('wp_head', array($this, 'outputCustomCSS'), 999);
        add_action('admin_head', array($this, 'outputCustomCSS'), 999);
        
        // Hook into FluentCommunity block editor specifically
        add_action('fluent_community/block_editor_head', array($this, 'outputBlockEditorCSS'), 999);
        
        // Hook into FluentCommunity portal frontend specifically
        add_action('fluent_community/portal_head', array($this, 'outputPortalCSS'), 999);
    }
    
    private function isFluentCommunityActive() {
        return class_exists('FluentCommunity\App\App');
    }
    
    public function fluentCommunityNotActiveNotice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('FluentCommunity Color Customizer requires FluentCommunity plugin to be active.', 'fluent-community-color-customizer'); ?></p>
        </div>
        <?php
    }
    
    public function addAdminMenu() {
        add_options_page(
            __('FluentCommunity Colors', 'fluent-community-color-customizer'),
            __('FluentCommunity Colors', 'fluent-community-color-customizer'),
            'manage_options',
            'fluent-community-colors',
            array($this, 'adminPage')
        );
    }
    
    public function registerSettings() {
        register_setting('fccc_colors', 'fccc_custom_colors', array(
            'sanitize_callback' => array($this, 'sanitizeColors')
        ));
    }
    
    public function enqueueAdminScripts($hook) {
        if ($hook !== 'settings_page_fluent-community-colors') {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        wp_add_inline_script('wp-color-picker', '
            jQuery(document).ready(function($) {
                // Initialize color pickers
                $(".fccc-color-picker").wpColorPicker({
                    change: function(event, ui) {
                        // Update the original input field value
                        $(this).val(ui.color.toString()).trigger("change");
                        
                        // Update preview in real-time
                        var colorKey = $(this).data("color-key");
                        var theme = $(this).data("theme");
                        var newColor = ui.color.toString();
                        $(".fccc-preview-" + theme + "-" + colorKey).css("background-color", newColor);
                    },
                    clear: function(event) {
                        // Handle clear button
                        var colorKey = $(this).data("color-key");
                        var theme = $(this).data("theme");
                        $(this).val("").trigger("change");
                        $(".fccc-preview-" + theme + "-" + colorKey).css("background-color", "transparent");
                    }
                });
                
                // Tab switching functionality
                $(".fccc-tab-button").click(function(e) {
                    e.preventDefault();
                    var targetTab = $(this).data("tab");
                    
                    // Update active tab button
                    $(".fccc-tab-button").removeClass("nav-tab-active");
                    $(this).addClass("nav-tab-active");
                    
                    // Show/hide tab content
                    $(".fccc-tab-content").hide();
                    $("#fccc-tab-" + targetTab).show();
                    
                    // Update preview section
                    $(".fccc-preview-section").hide();
                    $("#fccc-preview-" + targetTab).show();
                });
                
                // Initialize first tab as active
                $(".fccc-tab-button:first").addClass("nav-tab-active");
                $(".fccc-tab-content:first").show();
                $(".fccc-preview-section:first").show();
            });
        ');
        
        wp_add_inline_style('wp-color-picker', '
            .fccc-tabs {
                margin-bottom: 20px;
            }
            .fccc-tab-content {
                display: none;
                padding: 20px 0;
            }
            .fccc-color-field {
                margin-bottom: 20px;
            }
            .fccc-color-description {
                font-style: italic;
                color: #666;
                margin-top: 5px;
            }
            .fccc-preview-section {
                background: #f9f9f9;
                border-radius: 8px;
                display: none;
            }
            .fccc-color-swatches {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }
            .fccc-color-swatch {
                text-align: center;
            }
            .fccc-color-preview {
                width: 60px;
                height: 60px;
                border-radius: 8px;
                border: 2px solid #ddd;
                margin: 0 auto 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .fccc-theme-indicator {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
                margin-left: 8px;
            }
            .fccc-light-indicator {
                background: #fff3cd;
                color: #856404;
                border: 1px solid #ffeaa7;
            }
            .fccc-dark-indicator {
                background: #d1ecf1;
                color: #0c5460;
                border: 1px solid #bee5eb;
            }
        ');
    }
    
    public function getDefaultColors() {
        return array(
            '1' => array(
                'label' => __('Primary Color', 'fluent-community-color-customizer'),
                'default' => '#4F46E5',
                'description' => __('Main accent color used for buttons and links', 'fluent-community-color-customizer')
            ),
            '2' => array(
                'label' => __('Secondary Color', 'fluent-community-color-customizer'),
                'default' => '#7C3AED',
                'description' => __('Secondary accent color for hover states', 'fluent-community-color-customizer')
            ),
            '3' => array(
                'label' => __('Strongest Text', 'fluent-community-color-customizer'),
                'default' => '#1F2937',
                'description' => __('Darkest text color for headings', 'fluent-community-color-customizer')
            ),
            '4' => array(
                'label' => __('Strong Text', 'fluent-community-color-customizer'),
                'default' => '#374151',
                'description' => __('Strong text color for important content', 'fluent-community-color-customizer')
            ),
            '5' => array(
                'label' => __('Medium Text', 'fluent-community-color-customizer'),
                'default' => '#6B7280',
                'description' => __('Medium text color for regular content', 'fluent-community-color-customizer')
            ),
            '6' => array(
                'label' => __('Subtle Text', 'fluent-community-color-customizer'),
                'default' => '#9CA3AF',
                'description' => __('Subtle text color for secondary information', 'fluent-community-color-customizer')
            ),
            '7' => array(
                'label' => __('Subtle Background', 'fluent-community-color-customizer'),
                'default' => '#E5E7EB',
                'description' => __('Light background color for sections', 'fluent-community-color-customizer')
            ),
            '8' => array(
                'label' => __('Lighter Background', 'fluent-community-color-customizer'),
                'default' => '#FFFFFF',
                'description' => __('Lightest background color', 'fluent-community-color-customizer')
            )
        );
    }
    
    public function getDarkThemeDefaults() {
        return array(
            '1' => '#6366F1', // Slightly lighter primary for better contrast
            '2' => '#8B5CF6', // Slightly lighter secondary
            '3' => '#F9FAFB', // Lightest text (inverted from strongest)
            '4' => '#F3F4F6', // Light text (inverted from strong)
            '5' => '#D1D5DB', // Medium-light text (inverted from medium)
            '6' => '#9CA3AF', // Subtle text (same as light theme)
            '7' => '#374151', // Dark background
            '8' => '#1F2937'  // Darkest background (inverted from lightest)
        );
    }
    
    private function generateDarkVariant($lightColor) {
        // Convert hex to RGB
        $hex = ltrim($lightColor, '#');
        if (strlen($hex) !== 6) {
            return $lightColor; // Return original if invalid
        }
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Calculate luminance to determine if it's a light or dark color
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        if ($luminance > 0.5) {
            // Light color - make it darker for dark theme
            $factor = 0.3; // Make it 30% darker
            $r = max(0, $r * $factor);
            $g = max(0, $g * $factor);
            $b = max(0, $b * $factor);
        } else {
            // Dark color - make it lighter for dark theme
            $factor = 1.7; // Make it 70% lighter
            $r = min(255, $r * $factor);
            $g = min(255, $g * $factor);
            $b = min(255, $b * $factor);
        }
        
        return sprintf('#%02x%02x%02x', round($r), round($g), round($b));
    }
    
    private function migrateExistingColors() {
        $existingColors = get_option('fccc_custom_colors', array());
        
        // Check if we need to migrate (old format is flat array, new format has 'light' and 'dark' keys)
        if (!empty($existingColors) && !isset($existingColors['light']) && !isset($existingColors['dark'])) {
            $lightDefaults = $this->getDefaultColors();
            $darkDefaults = $this->getDarkThemeDefaults();
            
            $newStructure = array(
                'light' => array(),
                'dark' => array()
            );
            
            // Migrate existing colors to light theme
            foreach ($existingColors as $key => $color) {
                if ($color) {
                    $newStructure['light'][$key] = $color;
                    // Auto-generate dark variant
                    $newStructure['dark'][$key] = $this->generateDarkVariant($color);
                }
            }
            
            // Fill in any missing colors with defaults
            foreach ($lightDefaults as $key => $colorData) {
                if (!isset($newStructure['light'][$key])) {
                    $newStructure['light'][$key] = $colorData['default'];
                }
                if (!isset($newStructure['dark'][$key])) {
                    $newStructure['dark'][$key] = $darkDefaults[$key];
                }
            }
            
            // Update the option with new structure
            update_option('fccc_custom_colors', $newStructure);
            
            return $newStructure;
        }
        
        // If already in new format or empty, return as-is
        return $existingColors;
    }
    
    private function getColors() {
        $colors = $this->migrateExistingColors();
        
        // If empty, initialize with defaults
        if (empty($colors)) {
            $lightDefaults = $this->getDefaultColors();
            $darkDefaults = $this->getDarkThemeDefaults();
            
            $colors = array(
                'light' => array(),
                'dark' => array()
            );
            
            foreach ($lightDefaults as $key => $colorData) {
                $colors['light'][$key] = $colorData['default'];
                $colors['dark'][$key] = $darkDefaults[$key];
            }
        }
        
        return $colors;
    }
    
    
    public function sanitizeColors($input) {
        $sanitized = array();
        if (is_array($input)) {
            // Handle new nested structure (light/dark themes)
            if (isset($input['light']) || isset($input['dark'])) {
                foreach (['light', 'dark'] as $theme) {
                    if (isset($input[$theme]) && is_array($input[$theme])) {
                        $sanitized[$theme] = array();
                        foreach ($input[$theme] as $key => $value) {
                            $sanitized[$theme][$key] = sanitize_hex_color($value);
                        }
                    }
                }
            } else {
                // Handle old flat structure for backward compatibility
                foreach ($input as $key => $value) {
                    $sanitized[$key] = sanitize_hex_color($value);
                }
            }
        }
        return $sanitized;
    }
    
    public function adminPage() {
        $colors = $this->getColors();
        $lightDefaults = $this->getDefaultColors();
        $darkDefaults = $this->getDarkThemeDefaults();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="notice notice-info">
                <p><?php _e('These color settings will override the default FluentCommunity color palette for both light and dark themes. Changes will be applied immediately after saving to both the frontend and block editor.', 'fluent-community-color-customizer'); ?></p>
            </div>
            
            <div class="notice notice-warning">
                <p><strong><?php _e('Known Issue:', 'fluent-community-color-customizer'); ?></strong> <?php _e('Colors selected in the block editor for Links may be overridden by the CSS for ".feed_md_content a". This is a known limitation of the current implementation.', 'fluent-community-color-customizer'); ?></p>
            </div>
            
            <!-- Tab Navigation -->
            <div class="fccc-tabs">
                <h2 class="nav-tab-wrapper">
                    <a href="#" class="nav-tab fccc-tab-button" data-tab="light">
                        <?php _e('Light Theme', 'fluent-community-color-customizer'); ?>
                        <span class="fccc-theme-indicator fccc-light-indicator"><?php _e('Light', 'fluent-community-color-customizer'); ?></span>
                    </a>
                    <a href="#" class="nav-tab fccc-tab-button" data-tab="dark">
                        <?php _e('Dark Theme', 'fluent-community-color-customizer'); ?>
                        <span class="fccc-theme-indicator fccc-dark-indicator"><?php _e('Dark', 'fluent-community-color-customizer'); ?></span>
                    </a>
                </h2>
            </div>
            
            <form method="post" action="options.php">
                <?php settings_fields('fccc_colors'); ?>
                
                <!-- Light Theme Tab -->
                <div id="fccc-tab-light" class="fccc-tab-content">
                    <h3><?php _e('Light Theme Colors', 'fluent-community-color-customizer'); ?></h3>
                    <p><?php _e('These colors will be used when FluentCommunity is in light theme mode.', 'fluent-community-color-customizer'); ?></p>
                    
                    <?php foreach ($lightDefaults as $key => $colorData): ?>
                        <?php 
                        $currentColor = isset($colors['light'][$key]) ? $colors['light'][$key] : $colorData['default'];
                        ?>
                        <div class="fccc-color-field">
                            <label for="fccc_light_<?php echo $key; ?>">
                                <strong><?php echo esc_html($colorData['label']); ?></strong>
                            </label>
                            <br>
                            <input type="text" 
                                   id="fccc_light_<?php echo $key; ?>"
                                   name="fccc_custom_colors[light][<?php echo $key; ?>]" 
                                   value="<?php echo esc_attr($currentColor); ?>" 
                                   class="fccc-color-picker" 
                                   data-color-key="<?php echo $key; ?>"
                                   data-theme="light" />
                            <?php if (!empty($colorData['description'])): ?>
                                <div class="fccc-color-description"><?php echo esc_html($colorData['description']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Dark Theme Tab -->
                <div id="fccc-tab-dark" class="fccc-tab-content">
                    <h3><?php _e('Dark Theme Colors', 'fluent-community-color-customizer'); ?></h3>
                    <p><?php _e('These colors will be used when FluentCommunity is in dark theme mode.', 'fluent-community-color-customizer'); ?></p>
                    
                    <?php foreach ($lightDefaults as $key => $colorData): ?>
                        <?php 
                        $currentColor = isset($colors['dark'][$key]) ? $colors['dark'][$key] : $darkDefaults[$key];
                        ?>
                        <div class="fccc-color-field">
                            <label for="fccc_dark_<?php echo $key; ?>">
                                <strong><?php echo esc_html($colorData['label']); ?></strong>
                            </label>
                            <br>
                            <input type="text" 
                                   id="fccc_dark_<?php echo $key; ?>"
                                   name="fccc_custom_colors[dark][<?php echo $key; ?>]" 
                                   value="<?php echo esc_attr($currentColor); ?>" 
                                   class="fccc-color-picker" 
                                   data-color-key="<?php echo $key; ?>"
                                   data-theme="dark" />
                            <?php if (!empty($colorData['description'])): ?>
                                <div class="fccc-color-description"><?php echo esc_html($colorData['description']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php submit_button(); ?>
            </form>
            
            <!-- Light Theme Preview -->
            <div id="fccc-preview-light" class="fccc-preview-section" style="margin-top: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3><?php _e('Light Theme Preview', 'fluent-community-color-customizer'); ?></h3>
                <div class="fccc-color-swatches">
                    <?php foreach ($lightDefaults as $key => $colorData): ?>
                        <?php $currentColor = isset($colors['light'][$key]) ? $colors['light'][$key] : $colorData['default']; ?>
                        <div class="fccc-color-swatch">
                            <div class="fccc-color-preview fccc-preview-light-<?php echo $key; ?>" 
                                 style="background-color: <?php echo esc_attr($currentColor); ?>;"></div>
                            <small><strong><?php echo esc_html($colorData['label']); ?></strong><br><?php echo esc_html($currentColor); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Dark Theme Preview -->
            <div id="fccc-preview-dark" class="fccc-preview-section" style="margin-top: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3><?php _e('Dark Theme Preview', 'fluent-community-color-customizer'); ?></h3>
                <div class="fccc-color-swatches">
                    <?php foreach ($lightDefaults as $key => $colorData): ?>
                        <?php $currentColor = isset($colors['dark'][$key]) ? $colors['dark'][$key] : $darkDefaults[$key]; ?>
                        <div class="fccc-color-swatch">
                            <div class="fccc-color-preview fccc-preview-dark-<?php echo $key; ?>" 
                                 style="background-color: <?php echo esc_attr($currentColor); ?>;"></div>
                            <small><strong><?php echo esc_html($colorData['label']); ?></strong><br><?php echo esc_html($currentColor); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function overrideColorConfig($config, $context = 'view') {
        $customColors = $this->getColors();
        
        if (empty($customColors)) {
            return $config;
        }
        
        // Override light theme colors
        if (isset($config['light_config']['body']) && isset($customColors['light'])) {
            foreach ($customColors['light'] as $key => $color) {
                if ($color) {
                    $config['light_config']['body']['theme_palette_color_' . $key] = $color;
                }
            }
        }
        
        // Override dark theme colors
        if (isset($config['dark_config']['body']) && isset($customColors['dark'])) {
            foreach ($customColors['dark'] as $key => $color) {
                if ($color) {
                    $config['dark_config']['body']['theme_palette_color_' . $key] = $color;
                }
            }
        }
        
        return $config;
    }
    
    public function overrideBlockEditorColors($settings) {
        $customColors = $this->getColors();
        
        if (empty($customColors)) {
            return $settings;
        }
        
        // Override the colors array in block editor settings
        if (isset($settings['colors'])) {
            foreach ($settings['colors'] as &$colorSetting) {
                $slug = $colorSetting['slug'];
                // Extract the color number from slug like 'theme-palette-color-1'
                if (preg_match('/theme-palette-color-(\d+)/', $slug, $matches)) {
                    $colorKey = $matches[1];
                    
                    // For block editor, we'll use light theme colors as the default
                    // The CSS will handle theme switching via CSS variables
                    if (isset($customColors['light'][$colorKey]) && $customColors['light'][$colorKey]) {
                        $colorSetting['color'] = $customColors['light'][$colorKey];
                    }
                    // Fallback to old flat structure for backward compatibility
                    elseif (isset($customColors[$colorKey]) && $customColors[$colorKey]) {
                        $colorSetting['color'] = $customColors[$colorKey];
                    }
                }
            }
        }
        
        return $settings;
    }
    
    public function injectCustomColors() {
        $this->enqueueCustomCSS();
    }
    
    public function injectCustomColorsAdmin() {
        $this->enqueueCustomCSS();
    }
    
    private function enqueueCustomCSS() {
        $customColors = $this->getColors();
        
        if (empty($customColors)) {
            return;
        }
        
        $css = $this->generateThemeAwareCSS($customColors);
        wp_add_inline_style('wp-block-library', $css);
    }
    
    public function outputCustomCSS() {
        $customColors = $this->getColors();
        
        if (empty($customColors)) {
            return;
        }
        
        $css = $this->generateThemeAwareCSS($customColors);
        echo '<style type="text/css" id="fccc-custom-colors">' . $css . '</style>' . "\n";
    }
    
    public function outputBlockEditorCSS() {
        $customColors = $this->getColors();
        
        if (empty($customColors)) {
            return;
        }
        
        $css = $this->generateThemeAwareCSS($customColors, true);
        echo '<style type="text/css" id="fccc-block-editor-colors">' . $css . '</style>' . "\n";
    }
    
    public function outputPortalCSS() {
        $customColors = $this->getColors();
        
        if (empty($customColors)) {
            return;
        }
        
        $css = $this->generateThemeAwarePortalCSS($customColors);
        echo '<style type="text/css" id="fccc-portal-colors">' . $css . '</style>' . "\n";
    }
    
    private function generateThemeAwareCSS($customColors, $isBlockEditor = false) {
        $css = '';
        
        // Generate CSS for both light and dark themes
        if (isset($customColors['light']) && is_array($customColors['light'])) {
            // Light theme CSS variables (default state)
            $css .= ':root { ';
            foreach ($customColors['light'] as $key => $color) {
                if ($color) {
                    $css .= '--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--wp--preset--color--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--fcom-theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                }
            }
            $css .= '} ';
            
            // Body level CSS variables for light theme
            $css .= 'body { ';
            foreach ($customColors['light'] as $key => $color) {
                if ($color) {
                    $css .= '--fcom-theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                }
            }
            $css .= '} ';
        }
        
        if (isset($customColors['dark']) && is_array($customColors['dark'])) {
            // Dark theme CSS variables (when html has .dark class)
            $css .= 'html.dark { ';
            foreach ($customColors['dark'] as $key => $color) {
                if ($color) {
                    $css .= '--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--wp--preset--color--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--fcom-theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                }
            }
            $css .= '} ';
            
            // Body level CSS variables for dark theme
            $css .= 'html.dark body { ';
            foreach ($customColors['dark'] as $key => $color) {
                if ($color) {
                    $css .= '--fcom-theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                }
            }
            $css .= '} ';
        }
        
        // Block editor specific overrides
        if ($isBlockEditor) {
            if (isset($customColors['light'])) {
                $css .= '.editor-styles-wrapper { ';
                foreach ($customColors['light'] as $key => $color) {
                    if ($color) {
                        $css .= '--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    }
                }
                $css .= '} ';
                
                // Override color classes for light theme
                foreach ($customColors['light'] as $key => $color) {
                    if ($color) {
                        $css .= '.editor-styles-wrapper .has-theme-palette-color-' . $key . '-color { ';
                        $css .= 'color: ' . $color . ' !important; } ';
                        
                        $css .= '.editor-styles-wrapper .has-theme-palette-color-' . $key . '-background-color { ';
                        $css .= 'background-color: ' . $color . ' !important; } ';
                    }
                }
            }
            
            if (isset($customColors['dark'])) {
                $css .= 'html.dark .editor-styles-wrapper { ';
                foreach ($customColors['dark'] as $key => $color) {
                    if ($color) {
                        $css .= '--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    }
                }
                $css .= '} ';
                
                // Override color classes for dark theme
                foreach ($customColors['dark'] as $key => $color) {
                    if ($color) {
                        $css .= 'html.dark .editor-styles-wrapper .has-theme-palette-color-' . $key . '-color { ';
                        $css .= 'color: ' . $color . ' !important; } ';
                        
                        $css .= 'html.dark .editor-styles-wrapper .has-theme-palette-color-' . $key . '-background-color { ';
                        $css .= 'background-color: ' . $color . ' !important; } ';
                    }
                }
            }
        }
        
        return $css;
    }
    
    private function generateThemeAwarePortalCSS($customColors) {
        $css = '';
        
        // Generate portal CSS for both light and dark themes
        if (isset($customColors['light']) && is_array($customColors['light'])) {
            // Light theme portal CSS (default state)
            $css .= ':root, .fcom_portal_wrapper { ';
            foreach ($customColors['light'] as $key => $color) {
                if ($color) {
                    $css .= '--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--fcom-theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--wp--preset--color--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                }
            }
            $css .= '} ';
            
            // Body level overrides for light theme
            $css .= 'body.fcom_portal_body { ';
            foreach ($customColors['light'] as $key => $color) {
                if ($color) {
                    $css .= '--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--fcom-theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                }
            }
            $css .= '} ';
            
            // FluentCommunity portal container overrides for light theme
            $css .= '.fcom_portal_wrapper, .fcom_portal_wrapper * { ';
            foreach ($customColors['light'] as $key => $color) {
                if ($color) {
                    $css .= '--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--fcom-theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                }
            }
            $css .= '} ';
            
            // Direct color overrides for light theme
            $this->generatePortalColorOverrides($css, $customColors['light'], 'light');
        }
        
        if (isset($customColors['dark']) && is_array($customColors['dark'])) {
            // Dark theme portal CSS (when html has .dark class)
            $css .= 'html.dark { ';
            foreach ($customColors['dark'] as $key => $color) {
                if ($color) {
                    $css .= '--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--fcom-theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--wp--preset--color--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                }
            }
            $css .= '} ';
            
            // Body level overrides for dark theme
            $css .= 'html.dark body.fcom_portal_body { ';
            foreach ($customColors['dark'] as $key => $color) {
                if ($color) {
                    $css .= '--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--fcom-theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                }
            }
            $css .= '} ';
            
            // FluentCommunity portal container overrides for dark theme
            $css .= 'html.dark .fcom_portal_wrapper, html.dark .fcom_portal_wrapper * { ';
            foreach ($customColors['dark'] as $key => $color) {
                if ($color) {
                    $css .= '--theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                    $css .= '--fcom-theme-palette-color-' . $key . ': ' . $color . ' !important; ';
                }
            }
            $css .= '} ';
            
            // Direct color overrides for dark theme
            $this->generatePortalColorOverrides($css, $customColors['dark'], 'dark');
        }
        
        return $css;
    }
    
    private function generatePortalColorOverrides(&$css, $colors, $theme) {
        $wrapperSelector = $theme === 'dark' ? 'html.dark .fcom_portal_wrapper' : '.fcom_portal_wrapper';
        
        foreach ($colors as $key => $color) {
            if ($color) {
                // Button and link colors
                if ($key == '1') { // Primary color
                    $css .= $wrapperSelector . ' .fcom_btn_primary, ';
                    $css .= $wrapperSelector . ' .fcom_primary_color, ';
                    $css .= $wrapperSelector . ' a.fcom_link { ';
                    $css .= 'background-color: ' . $color . ' !important; ';
                    $css .= 'border-color: ' . $color . ' !important; ';
                    $css .= '} ';
                    
                    $css .= $wrapperSelector . ' .fcom_text_primary, ';
                    $css .= $wrapperSelector . ' .fcom_link_color { ';
                    $css .= 'color: ' . $color . ' !important; ';
                    $css .= '} ';
                }
                
                // Text colors
                if (in_array($key, ['3', '4', '5', '6'])) {
                    $css .= $wrapperSelector . ' .fcom_text_' . $key . ', ';
                    $css .= $wrapperSelector . ' .has-theme-palette-color-' . $key . '-color { ';
                    $css .= 'color: ' . $color . ' !important; ';
                    $css .= '} ';
                }
                
                // Background colors
                if (in_array($key, ['7', '8'])) {
                    $css .= $wrapperSelector . ' .fcom_bg_' . $key . ', ';
                    $css .= $wrapperSelector . ' .has-theme-palette-color-' . $key . '-background-color { ';
                    $css .= 'background-color: ' . $color . ' !important; ';
                    $css .= '} ';
                }
                
                // Generic color classes
                $css .= $wrapperSelector . ' .has-theme-palette-color-' . $key . '-color { ';
                $css .= 'color: ' . $color . ' !important; ';
                $css .= '} ';
                
                $css .= $wrapperSelector . ' .has-theme-palette-color-' . $key . '-background-color { ';
                $css .= 'background-color: ' . $color . ' !important; ';
                $css .= '} ';
                
                $css .= $wrapperSelector . ' .has-theme-palette-color-' . $key . '-border-color { ';
                $css .= 'border-color: ' . $color . ' !important; ';
                $css .= '} ';
            }
        }
    }
}

// Initialize the plugin
FluentCommunityColorCustomizer::getInstance();

