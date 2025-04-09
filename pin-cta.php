<?php
/*
Plugin Name: Pin CTA
Description: Adds a Pinterest Share shortcode and block.
Version: 1.2.0
Author: John Ward
Author URI: https://johnathanward.com
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register the shortcode
function pin_cta_shortcode($atts) {
    // For debugging
    error_log('Pin CTA shortcode called with: ' . print_r($atts, true));
    
    // Handle both block attributes and shortcode attributes
    if (is_array($atts)) {
        $style = isset($atts['style']) ? $atts['style'] : 'default';
        $inline = isset($atts['isInline']) ? $atts['isInline'] : false;
        $media_id = isset($atts['mediaId']) ? $atts['mediaId'] : null;
        $media_url = isset($atts['mediaUrl']) ? $atts['mediaUrl'] : '';
        $custom_text = isset($atts['customText']) ? $atts['customText'] : 'Pin This Now to Remember It Later';
        
        // Debug the inline value
        error_log('Pin CTA shortcode isInline (array): ' . var_export($inline, true) . ' (type: ' . gettype($inline) . ')');
        
        // Convert string 'false'/'true' to boolean if needed
        if (is_string($inline)) {
            $inline = filter_var($inline, FILTER_VALIDATE_BOOLEAN);
            error_log('Pin CTA shortcode isInline converted from string: ' . var_export($inline, true));
        }
    } else {
        $atts = shortcode_atts(array(
            'style' => 'default',
            'inline' => false,
            'media_id' => null,
            'media_url' => '',
            'custom_text' => 'Pin This Now to Remember It Later'
        ), $atts, 'pin_cta_button');
        $style = $atts['style'];
        $inline = filter_var($atts['inline'], FILTER_VALIDATE_BOOLEAN);
        $media_id = $atts['media_id'];
        $media_url = $atts['media_url'];
        $custom_text = $atts['custom_text'];
        
        // Debug the inline value
        error_log('Pin CTA shortcode inline (string): ' . var_export($inline, true) . ' (type: ' . gettype($inline) . ')');
    }

    // Ensure $inline is a boolean
    $inline = (bool)$inline;
    error_log('Pin CTA shortcode final inline value: ' . var_export($inline, true));

    $inline_class = $inline ? esc_attr(' pin-cta-inline') : '';
    error_log('Pin CTA shortcode inline_class: ' . $inline_class);
    
    // Determine which image URL to use
    $image_url = '';
    if (!empty($media_url)) {
        // First priority: Use image set in block/shortcode
        $image_url = $media_url;
    } else {
        // Second priority: Look for first image in post content
        $post_content = get_post_field('post_content', get_the_ID());
        preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $post_content, $image);
        if (!empty($image[1])) {
            $image_url = $image[1];
        } elseif (has_post_thumbnail()) {
            // Third priority: Use featured image
            $image_url = get_the_post_thumbnail_url();
        } else {
            // Finally: Check for SEO meta image (keeping this as fallback)
            $og_image = get_post_meta(get_the_ID(), '_yoast_wpseo_opengraph-image', true);
            if (!$og_image) {
                $og_image = get_post_meta(get_the_ID(), '_rank_math_facebook_image', true);
            }
            if ($og_image) {
                $image_url = $og_image;
            }
        }
    }
    
    ob_start();
    ?>
    <div class="pin-cta-container pin-cta-<?php echo esc_attr($style); ?><?php echo esc_attr($inline_class); ?>">
        <div class="pin-cta-logo">
            <svg class="pin-cta-pinterest-icon" viewBox="0 0 24 24" width="20" height="20">
                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/>
            </svg>
        </div>
        <div class="pin-cta-text"><?php echo esc_html($custom_text); ?></div>
        <a href="<?php echo esc_url("https://pinterest.com/pin/create/button/?url=" . urlencode(get_permalink()) . "&media=" . urlencode($image_url) . "&description=" . urlencode(get_the_title())); ?>" target="_blank" class="pin-cta-pin-button">
            <svg class="pin-cta-pinterest-icon" viewBox="0 0 24 24" width="20" height="20">
                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/>
            </svg>
            Pin This
        </a>
    </div>
    <?php
    return ob_get_clean();
}

function pin_cta_register_block() {
    // Get default options
    $options = get_option('pin_cta_options', array(
        'pin_cta_default_style' => 'default',
        'pin_cta_default_layout' => 'block',
        'pin_cta_default_text' => 'Pin This Now to Remember It Later'
    ));

    // Register block script
    wp_register_script(
        'pin-cta-block',
        plugins_url('blocks/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/block.js'),
        true // Load script in the footer
    );

    // Pass defaults to JavaScript
    wp_localize_script('pin-cta-block', 'pinCtaDefaults', array(
        'style' => $options['pin_cta_default_style'],
        'isInline' => $options['pin_cta_default_layout'] === 'inline',
        'text' => $options['pin_cta_default_text'],
        'pluginUrl' => plugins_url('', __FILE__)
    ));

    wp_register_style(
        'pin-cta-style',
        plugins_url('blocks/style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/style.css')
    );
    
    // Ensure the style is enqueued for the frontend
    wp_enqueue_style('pin-cta-style');

    // Register the block
    register_block_type('pin-cta/block', array(
        'editor_script' => 'pin-cta-block',
        'editor_style' => 'pin-cta-editor-style',
        'style' => 'pin-cta-style',
        'render_callback' => function($attributes, $content) {
            // For debugging
            error_log('Pin CTA block render callback called with: ' . print_r($attributes, true));
            return pin_cta_shortcode($attributes);
        },
        'attributes' => array(
            'style' => array(
                'type' => 'string',
                'default' => $options['pin_cta_default_style']
            ),
            'isInline' => array(
                'type' => 'boolean',
                'default' => $options['pin_cta_default_layout'] === 'inline'
            ),
            'customText' => array(
                'type' => 'string',
                'default' => $options['pin_cta_default_text']
            ),
            'mediaId' => array(
                'type' => 'number'
            ),
            'mediaUrl' => array(
                'type' => 'string'
            )
        )
    ));
}

// Ensure styles are loaded on the frontend
function pin_cta_enqueue_frontend_styles() {
    if (has_block('pin-cta/block') || is_singular()) {
        wp_enqueue_style(
            'pin-cta-style',
            plugins_url('blocks/style.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'blocks/style.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'pin_cta_enqueue_frontend_styles');

// Ensure styles are loaded in the admin area for the preview
function pin_cta_enqueue_admin_styles($hook) {
    // Only load on our settings page
    if ($hook !== 'toplevel_page_pin_cta_settings') {
        return;
    }
    
    wp_enqueue_style(
        'pin-cta-style',
        plugins_url('blocks/style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/style.css')
    );
    
    // Add some admin-specific styles
    wp_add_inline_style('pin-cta-style', '
        .pin-cta-preview-section {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .pin-cta-preview-section h3 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
    ');
}
add_action('admin_enqueue_scripts', 'pin_cta_enqueue_admin_styles');

// Add these functions after the existing code but before the closing PHP tag

function pin_cta_add_admin_menu() {
    add_menu_page(
        'Pin CTA Settings', // Page title
        'Pin CTA',         // Menu title
        'manage_options',   // Capability required
        'pin_cta_settings', // Menu slug
        'pin_cta_options_page', // Callback function
        'dashicons-pinterest', // Icon (using WordPress Pinterest dashicon)
        30 // Position in menu (lower numbers = higher in sidebar)
    );
}
// add_action('admin_menu', 'pin_cta_add_admin_menu'); // Removed duplicate, now handled in pin_cta_init()

function pin_cta_settings_init() {
    // Define default options
    $defaults = array(
        'pin_cta_auto_placement' => 'disabled',
        'pin_cta_default_style' => 'default',
        'pin_cta_default_layout' => 'block',
        'pin_cta_default_text' => 'Pin This Now to Remember It Later',
        'pin_cta_positions' => array('after_content'),
        'pin_cta_enable_image_buttons' => 'disabled',
        'pin_cta_image_button_text' => 'Pin This',
        'pin_cta_image_button_style' => 'default'
    );

    // Set default options if they don't exist
    if (!get_option('pin_cta_options')) {
        update_option('pin_cta_options', $defaults);
    }

    // Register the settings
    register_setting(
        'pin_cta_settings',
        'pin_cta_options',
        'pin_cta_sanitize_options'
    );

    // Pin CTA Settings Section
    add_settings_section(
        'pin_cta_settings_section',
        'Pin CTA Settings',
        null,
        'pin_cta_settings'
    );

    // Add setting for default style
    add_settings_field(
        'pin_cta_default_style',
        'CTA Box Style',
        'pin_cta_style_field_callback',
        'pin_cta_settings',
        'pin_cta_settings_section'
    );

    // Add setting for default layout
    add_settings_field(
        'pin_cta_default_layout',
        'CTA Box Layout',
        'pin_cta_layout_field_callback',
        'pin_cta_settings',
        'pin_cta_settings_section'
    );

    // Add setting for default text
    add_settings_field(
        'pin_cta_default_text',
        'CTA Box Text',
        'pin_cta_text_field_callback',
        'pin_cta_settings',
        'pin_cta_settings_section'
    );

    // Add setting for automatic placement
    add_settings_field(
        'pin_cta_auto_placement',
        'Automatic Placement',
        'pin_cta_auto_placement_callback',
        'pin_cta_settings',
        'pin_cta_settings_section'
    );

    // Add setting for placement positions
    add_settings_field(
        'pin_cta_positions',
        'Placement Positions',
        'pin_cta_positions_callback',
        'pin_cta_settings',
        'pin_cta_settings_section'
    );

    // Pin It Button Settings Section
    add_settings_section(
        'pin_cta_image_button_section',
        'Pin It Button Settings',
        null,
        'pin_cta_settings'
    );

    // Add setting for image buttons enable/disable
    add_settings_field(
        'pin_cta_enable_image_buttons',
        'Enable Pin It Buttons',
        'pin_cta_image_buttons_callback',
        'pin_cta_settings',
        'pin_cta_image_button_section'
    );

    // Add setting for image button style
    add_settings_field(
        'pin_cta_image_button_style',
        'Button Style',
        'pin_cta_image_button_style_callback',
        'pin_cta_settings',
        'pin_cta_image_button_section'
    );
    // Add setting for image button text
    add_settings_field(
        'pin_cta_image_button_text',
        'Button Text',
        'pin_cta_image_button_text_callback',
        'pin_cta_settings',
        'pin_cta_image_button_section'
    );
}

/**
 * Sanitize plugin options
 *
 * @param array $options The options array to sanitize
 * @return array Sanitized options
 */
function pin_cta_sanitize_options($options) {        
    // For debugging
    error_log('Pin CTA sanitizing options (raw input): ' . print_r($options, true));
    
    // Ensure we have an array
    if (!is_array($options)) {
        error_log('Pin CTA options is not an array');
        $options = array();
    }

    // Set defaults if options are missing
    $defaults = array(
        'pin_cta_auto_placement' => 'disabled',
        'pin_cta_default_style' => 'default',
        'pin_cta_default_layout' => 'block',
        'pin_cta_default_text' => 'Pin This Now to Remember It Later',
        'pin_cta_positions' => array('after_content'),
        'pin_cta_enable_image_buttons' => 'disabled',
        'pin_cta_image_button_text' => 'Pin This',
        'pin_cta_image_button_style' => 'default'
    );

    // Merge with defaults, but don't overwrite existing values
    foreach ($defaults as $key => $default_value) {
        if (!isset($options[$key])) {
            $options[$key] = $default_value;
        }
    }
    
    error_log('Pin CTA options after merging with defaults: ' . print_r($options, true));

    // Sanitize auto placement (must be one of: disabled, enabled)
    $valid_auto_placements = array('disabled', 'enabled');
    if (!isset($options['pin_cta_auto_placement']) || !in_array($options['pin_cta_auto_placement'], $valid_auto_placements)) {
        error_log('Pin CTA invalid auto_placement value: ' . (isset($options['pin_cta_auto_placement']) ? $options['pin_cta_auto_placement'] : 'not set'));
        $options['pin_cta_auto_placement'] = 'disabled';
    } else {
        error_log('Pin CTA auto_placement set to: ' . $options['pin_cta_auto_placement']);
    }
    
    // Sanitize default style (must be one of: default, classic, style1, style2, etc.)
    $valid_styles = array('default', 'classic', 'style1', 'style2', 'style3', 'style4', 'style5', 
                         'style6', 'style7', 'style8', 'style9', 'style10');
    
    if (!isset($options['pin_cta_image_button_style']) || !in_array($options['pin_cta_image_button_style'], $valid_styles)) {
        $options['pin_cta_image_button_style'] = 'default';
    }
    
    // Sanitize default layout (must be one of: block, inline)
    $valid_layouts = array('block', 'inline');
    if (!in_array($options['pin_cta_default_layout'], $valid_layouts)) {
        $options['pin_cta_default_layout'] = 'block';
    }
    
    // Sanitize default text
    $options['pin_cta_default_text'] = sanitize_text_field($options['pin_cta_default_text']);
    
    // Ensure positions is an array and contains only valid positions
    if (!isset($options['pin_cta_positions']) || !is_array($options['pin_cta_positions']) || (is_array($options['pin_cta_positions']) && count($options['pin_cta_positions']) === 1 && $options['pin_cta_positions'][0] === '')) {
        $options['pin_cta_positions'] = array('after_content');
    } else {
        $valid_positions = array('after_title', 'after_first_header', 'after_first_paragraph', 'middle_content', 'after_content');
        $options['pin_cta_positions'] = array_intersect($options['pin_cta_positions'], $valid_positions);
        
        // If no valid positions remain, set default
        if (empty($options['pin_cta_positions'])) {
            $options['pin_cta_positions'] = array('after_content');
        }
    }
    
    // Sanitize image buttons setting (must be one of: disabled, enabled)
    $valid_image_buttons = array('disabled', 'enabled');
    if (!isset($options['pin_cta_enable_image_buttons']) || !in_array($options['pin_cta_enable_image_buttons'], $valid_image_buttons)) {
        $options['pin_cta_enable_image_buttons'] = 'disabled';
    }
    
    // Sanitize image button text
    if (isset($options['pin_cta_image_button_text'])) {
        $options['pin_cta_image_button_text'] = sanitize_text_field($options['pin_cta_image_button_text']);
    } else {
        $options['pin_cta_image_button_text'] = 'Pin This';
    }

    error_log('Pin CTA final sanitized options: ' . print_r($options, true));
    return $options;
}

// Add this right after the initial plugin checks
// add_action('admin_init', 'pin_cta_settings_init'); // Removed duplicate, now handled in pin_cta_init()

function pin_cta_style_field_callback() {
    $options = get_option('pin_cta_options', array('pin_cta_default_style' => 'default'));
    ?>
    <select name="pin_cta_options[pin_cta_default_style]">
        <option value="default" <?php selected($options['pin_cta_default_style'], 'default'); ?>>Classic Red & White</option>
        <option value="style1" <?php selected($options['pin_cta_default_style'], 'style1'); ?>>Burgundy & Gold</option>
        <option value="style2" <?php selected($options['pin_cta_default_style'], 'style2'); ?>>Fresh Green & White</option>
        <option value="style3" <?php selected($options['pin_cta_default_style'], 'style3'); ?>>Soft Pink & Rose</option>
        <option value="style4" <?php selected($options['pin_cta_default_style'], 'style4'); ?>>Navy & Gold</option>
        <option value="style5" <?php selected($options['pin_cta_default_style'], 'style5'); ?>>Sage & Cream</option>
        <option value="style6" <?php selected($options['pin_cta_default_style'], 'style6'); ?>>Royal Purple & Lavender</option>
        <option value="style7" <?php selected($options['pin_cta_default_style'], 'style7'); ?>>Ocean Teal & Coral</option>
        <option value="style8" <?php selected($options['pin_cta_default_style'], 'style8'); ?>>Midnight Blue & Silver</option>
        <option value="style9" <?php selected($options['pin_cta_default_style'], 'style9'); ?>>Autumn Orange & Cream</option>
        <option value="style10" <?php selected($options['pin_cta_default_style'], 'style10'); ?>>Forest & Mint</option>
    </select>
    <?php
}

function pin_cta_layout_field_callback() {
    $options = get_option('pin_cta_options', array('pin_cta_default_layout' => 'block'));
    ?>
    <select name="pin_cta_options[pin_cta_default_layout]">
        <option value="block" <?php selected($options['pin_cta_default_layout'], 'block'); ?>>Block</option>
        <option value="inline" <?php selected($options['pin_cta_default_layout'], 'inline'); ?>>Inline</option>
    </select>
    <?php
}

function pin_cta_text_field_callback() {
    $options = get_option('pin_cta_options', array('pin_cta_default_text' => 'Pin This Now to Remember It Later'));
    ?>
    <input type="text" name="pin_cta_options[pin_cta_default_text]" value="<?php echo esc_attr($options['pin_cta_default_text']); ?>" class="regular-text">
    <?php
}

function pin_cta_auto_placement_callback() {
    $options = get_option('pin_cta_options', array('pin_cta_auto_placement' => 'disabled'));
    
    // For debugging
    error_log('Pin CTA auto placement options: ' . print_r($options, true));
    ?>
    <select name="pin_cta_options[pin_cta_auto_placement]">
        <option value="disabled" <?php selected($options['pin_cta_auto_placement'], 'disabled'); ?>>Disabled</option>
        <option value="enabled" <?php selected($options['pin_cta_auto_placement'], 'enabled'); ?>>Enabled</option>
    </select>
    <p class="description">Enable automatic placement of Pin CTA widgets in your content.</p>
    <?php
}

function pin_cta_positions_callback() {
    $defaults = array(
        'pin_cta_positions' => array('after_content')
    );
    $options = get_option('pin_cta_options', $defaults);
    
    // For debugging
    error_log('Pin CTA positions options: ' . print_r($options, true));
    
    if (!isset($options['pin_cta_positions'])) {
        $options['pin_cta_positions'] = $defaults['pin_cta_positions'];
    }
    
    // Add a hidden field to ensure the array is always sent, even when no checkboxes are selected
    echo '<input type="hidden" name="pin_cta_options[pin_cta_positions]" value="">';
    
    $positions = array(
        'after_title' => 'After Title',
        'after_first_header' => 'After First Header (H2-H6)',
        'after_first_paragraph' => 'After First Paragraph',
        'middle_content' => 'Middle of Content',
        'after_content' => 'After Content'
    );

    foreach ($positions as $key => $label) {
        $checked = in_array($key, (array)$options['pin_cta_positions']);
        ?>
        <label style="display: block; margin-bottom: 5px;">
            <input type="checkbox" name="pin_cta_options[pin_cta_positions][]" 
                   value="<?php echo esc_attr($key); ?>"
                   <?php checked($checked); ?>>
            <?php echo esc_html($label); ?>
        </label>
        <?php
    }
}

function pin_cta_image_buttons_callback() {
    $options = get_option('pin_cta_options');
    ?>
    <select name="pin_cta_options[pin_cta_enable_image_buttons]" id="pin_cta_enable_image_buttons">
        <option value="disabled" <?php selected($options['pin_cta_enable_image_buttons'], 'disabled'); ?>>Disabled</option>
        <option value="enabled" <?php selected($options['pin_cta_enable_image_buttons'], 'enabled'); ?>>Enabled</option>
    </select>
    <p class="description">When enabled, Pinterest buttons will appear in the top right corner of images in your posts.</p>
    <?php
}

function pin_cta_image_button_text_callback() {
    $options = get_option('pin_cta_options', array('pin_cta_image_button_text' => 'Pin This'));
    ?>
    <input type="text" name="pin_cta_options[pin_cta_image_button_text]" value="<?php echo esc_attr($options['pin_cta_image_button_text']); ?>" class="regular-text">
    <p class="description">Customize the text shown when hovering over the Pinterest image button. Default is "Pin This".</p>
    <?php
}

function pin_cta_image_button_style_callback() {
    $options = get_option('pin_cta_options');
    $current_style = isset($options['pin_cta_image_button_style']) ? $options['pin_cta_image_button_style'] : 'default';
    ?>
    <select name="pin_cta_options[pin_cta_image_button_style]">
        <option value="default" <?php selected($current_style, 'default'); ?>>Default (White & Red)</option>
        <option value="classic" <?php selected($current_style, 'classic'); ?>>Classic (Red Background)</option>
        <option value="style1" <?php selected($current_style, 'style1'); ?>>Burgundy & Gold</option>
        <option value="style2" <?php selected($current_style, 'style2'); ?>>Fresh Green & White</option>
        <option value="style3" <?php selected($current_style, 'style3'); ?>>Soft Pink & Rose</option>
        <option value="style4" <?php selected($current_style, 'style4'); ?>>Navy & Gold</option>
        <option value="style5" <?php selected($current_style, 'style5'); ?>>Sage & Cream</option>
        <option value="style6" <?php selected($current_style, 'style6'); ?>>Royal Purple & Lavender</option>
        <option value="style7" <?php selected($current_style, 'style7'); ?>>Ocean Teal & Coral</option>
        <option value="style8" <?php selected($current_style, 'style8'); ?>>Midnight Blue & Silver</option>
        <option value="style9" <?php selected($current_style, 'style9'); ?>>Autumn Orange & Cream</option>
        <option value="style10" <?php selected($current_style, 'style10'); ?>>Forest & Mint</option>
    </select>
    <p class="description">Choose the style for the Pin It buttons that appear over images.</p>
    <?php
}

function pin_cta_options_page() {
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-pinterest" style="font-size: 30px; width: 30px; height: 30px; margin-right: 10px;"></span><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="pin-cta-admin-layout" style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
            <!-- Settings Column -->
            <div class="pin-cta-settings-column" style="flex: 1; min-width: 300px; max-width: 800px;">
                <div class="card" style="margin-bottom: 20px;">
                    <form action="options.php" method="post">
                        <?php
                        settings_fields('pin_cta_settings');
                        do_settings_sections('pin_cta_settings');
                        submit_button();
                        ?>
                    </form>
                </div>

                <div class="card" style="margin-bottom: 20px;">
                    <h2>Join Our Community</h2>
                    <p>Want to learn more about growing your Pinterest traffic and maximizing your social media presence?</p>
                    <p>Join our <a href="https://traffic-alchemy.beehiiv.com/subscribe?utm_source=pin_cta" target="_blank">Traffic Alchemy community</a> to discover proven strategies for growing and monetizing your websites and social media profiles. Get insider tips on:</p>
                    <ul style="list-style-type: disc; margin-left: 20px;">
                        <li>Driving organic traffic</li>
                        <li>Maximizing ad revenue</li>
                        <li>Mastering affiliate marketing</li>
                        <li>Social media growth tactics</li>
                        <li>Content optimization strategies</li>
                    </ul>
                    <p style="margin-top: 15px;"><a href="https://traffic-alchemy.beehiiv.com/subscribe?utm_source=pin_cta" target="_blank" class="button button-primary">Join Traffic Alchemy Now</a></p>
                </div>
            </div>

            <!-- Preview Column -->
            <div class="pin-cta-preview-column" style="flex: 1; min-width: 300px; max-width: 800px;">
                <div class="card pin-cta-preview-section" style="margin-bottom: 20px;">
                    <h2>CTA Preview</h2>
                    <p>This shows how your Pin CTA will look with current settings:</p>
                    
                    <?php 
                    $options = get_option('pin_cta_options');
                    
                    // Block version
                    echo '<div class="pin-cta-preview-block">';
                    echo '<h3>Block Layout</h3>';
                    echo pin_cta_shortcode(array(
                        'style' => $options['pin_cta_default_style'],
                        'isInline' => false,
                        'customText' => $options['pin_cta_default_text']
                    ));
                    echo '</div>';
                    
                    // Inline version
                    echo '<div class="pin-cta-preview-inline">';
                    echo '<h3>Inline Layout</h3>';
                    echo pin_cta_shortcode(array(
                        'style' => $options['pin_cta_default_style'],
                        'isInline' => true,
                        'customText' => $options['pin_cta_default_text']
                    ));
                    echo '</div>';
                    ?>
                </div>

                <div class="card pin-cta-image-button-preview">
                    <h2>Pin Button Preview</h2>
                    <p>When enabled, Pinterest buttons will appear in the top right corner of images in your posts:</p>
                    
                    <div class="pin-cta-image-carousel" style="position: relative; margin: 20px 0;">
                        <div class="pin-cta-carousel-container" style="position: relative; display: inline-block;">
                            <img src="<?php echo plugins_url('assets/preview-image-01.jpeg', __FILE__); ?>" 
                                 alt="Preview Image 1" 
                                 class="pin-cta-preview-image active"
                                 style="max-width: 100%; height: auto; display: block;" 
                                 data-index="0">
                            <img src="<?php echo plugins_url('assets/preview-image-02.jpeg', __FILE__); ?>" 
                                 alt="Preview Image 2" 
                                 class="pin-cta-preview-image"
                                 style="max-width: 100%; height: auto; display: none;" 
                                 data-index="1">
                            <img src="<?php echo plugins_url('assets/preview-image-03.jpeg', __FILE__); ?>" 
                                 alt="Preview Image 3" 
                                 class="pin-cta-preview-image"
                                 style="max-width: 100%; height: auto; display: none;" 
                                 data-index="2">
                            
                            <a href="#" class="pin-cta-image-button" style="position: absolute; top: 10px; right: 10px; border-radius: 24px; padding: 8px 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);text-decoration: none !important;">
                                <span style="margin-right: 6px; font-size: 14px; font-weight: 600;"><?php echo esc_html($options['pin_cta_image_button_text']); ?></span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="16" height="16">
                                    <path fill="currentColor" d="M204 6.5C101.4 6.5 0 74.9 0 185.6 0 256 39.6 296 63.6 296c9.9 0 15.6-27.6 15.6-35.4 0-9.3-23.7-29.1-23.7-67.8 0-80.4 61.2-137.4 140.4-137.4 68.1 0 118.5 38.7 118.5 109.8 0 53.1-21.3 152.7-90.3 152.7-24.9 0-46.2-18-46.2-43.8 0-37.8 26.4-74.4 26.4-113.4 0-66.2-93.9-54.2-93.9 25.8 0 16.8 2.1 35.4 9.6 50.7-13.8 59.4-42 147.9-42 209.1 0 18.9 2.7 37.5 4.5 56.4 3.4 3.8 1.7 3.4 6.9 1.5 50.4-69 48.6-82.5 71.4-172.8 12.3 23.4 44.1 36 69.3 36 106.2 0 153.9-103.5 153.9-196.8C384 71.3 298.2 6.5 204 6.5z"></path>
                                </svg>
                            </a>
                        </div>
                        
                        <div class="pin-cta-carousel-nav" style="text-align: center; margin-top: 10px;">
                            <button type="button" class="pin-cta-carousel-prev" style="background: #e60023; color: white; border: none; padding: 5px 10px; margin: 0 5px; cursor: pointer; border-radius: 3px;">&laquo; Previous</button>
                            <span class="pin-cta-carousel-indicator" style="display: inline-block; margin: 0 10px;">Image 1 of 3</span>
                            <button type="button" class="pin-cta-carousel-next" style="background: #e60023; color: white; border: none; padding: 5px 10px; margin: 0 5px; cursor: pointer; border-radius: 3px;">Next &raquo;</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Theme selection change handler
        $('select[name="pin_cta_options[pin_cta_default_style]"]').on('change', function() {
            var selectedStyle = $(this).val();
            
            // Update block layout preview
            var $blockPreview = $('.pin-cta-preview-block .pin-cta-container');
            // Remove existing style classes
            $blockPreview.removeClass(function(index, className) {
                return (className.match(/(^|\s)pin-cta-\S+/g) || []).join(' ');
            });
            $blockPreview.addClass('pin-cta-container pin-cta-' + selectedStyle);
            
            // Update inline layout preview
            var $inlinePreview = $('.pin-cta-preview-inline .pin-cta-container');
            // Remove existing style classes
            $inlinePreview.removeClass(function(index, className) {
                return (className.match(/(^|\s)pin-cta-\S+/g) || []).join(' ');
            });
            $inlinePreview.addClass('pin-cta-container pin-cta-' + selectedStyle + ' pin-cta-inline');
        });

        // Image button style change handler
        $('select[name="pin_cta_options[pin_cta_image_button_style]"]').on('change', function() {
            var selectedStyle = $(this).val();
            
            // Update Pin It button preview
            var $pinButton = $('.pin-cta-image-button');
            
            // First remove any existing pin-cta-* classes
            $pinButton.removeClass(function(index, className) {
                return (className.match(/(^|\s)pin-cta-\S+/g) || []).join(' ');
            });
            
            // Add base classes and new style
            $pinButton.addClass('pin-cta-image-button pin-cta-' + selectedStyle);
            
            // Update button style properties based on theme
            if (selectedStyle === 'default') {
                $pinButton.css({
                    'background-color': '#ffffff',
                    'color': '#e60023'
                });
            } else if (selectedStyle === 'classic') {
                $pinButton.css({
                    'background-color': '#e60023',
                    'color': '#ffffff'
                });
            } else {
                // Remove inline styles to let CSS classes handle other themes
                $pinButton.css({
                    'background-color': '',
                    'color': ''
                });
            }
        });

        // Carousel functionality
        var $images = $('.pin-cta-preview-image');
        var $indicator = $('.pin-cta-carousel-indicator');
        var totalImages = $images.length;
        var currentIndex = 0;
        
        // Update the indicator
        function updateIndicator() {
            $indicator.text('Image ' + (currentIndex + 1) + ' of ' + totalImages);
        }
        
        // Show the current image
        function showImage(index) {
            $images.hide().removeClass('active');
            $images.eq(index).show().addClass('active');
            currentIndex = index;
            updateIndicator();
        }
        
        // Previous button click
        $('.pin-cta-carousel-prev').click(function(e) {
            e.preventDefault();
            var newIndex = currentIndex - 1;
            if (newIndex < 0) {
                newIndex = totalImages - 1;
            }
            showImage(newIndex);
        });
        
        // Next button click
        $('.pin-cta-carousel-next').click(function(e) {
            e.preventDefault();
            var newIndex = currentIndex + 1;
            if (newIndex >= totalImages) {
                newIndex = 0;
            }
            showImage(newIndex);
        });
        
        // Initialize
        showImage(0);

        // Set initial Pin It button style
        var initialStyle = $('select[name="pin_cta_options[pin_cta_image_button_style]"]').val();
        var $pinButton = $('.pin-cta-image-button');
        
        // Remove any existing pin-cta-* classes
        $pinButton.removeClass(function(index, className) {
            return (className.match(/(^|\s)pin-cta-\S+/g) || []).join(' ');
        });
        
        // Add base classes and initial style
        $pinButton.addClass('pin-cta-image-button pin-cta-' + initialStyle);
        
        // Set initial button style properties
        if (initialStyle === 'default') {
            $pinButton.css({
                'background-color': '#ffffff',
                'color': '#e60023'
            });
        } else if (initialStyle === 'classic') {
            $pinButton.css({
                'background-color': '#e60023',
                'color': '#ffffff'
            });
        } else {
            // Remove inline styles to let CSS classes handle other themes
            $pinButton.css({
                'background-color': '',
                'color': ''
            });
        }
    });
    </script>

    <?php
}

/**
 * Helper function to check if we're on a single post page
 * 
 * @return bool Whether we're on a single post page
 */
function pin_cta_is_single_post() {
    // Only check if we're on a single post
    return is_singular('post');
}

// Function to check if we should apply the CTA to this post
function pin_cta_should_apply_to_post($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // For debugging
    error_log('Pin CTA checking if should apply to post: ' . $post_id);
    
    // First check if we're on a single post page
    if (!pin_cta_is_single_post()) {
        error_log('Pin CTA: Not on a single post page, skipping');
        return false;
    }
    
    // Get options
    $options = get_option('pin_cta_options');
    error_log('Pin CTA options: ' . print_r($options, true));
    
    // Check if automatic placement is enabled
    if (empty($options['pin_cta_auto_placement']) || $options['pin_cta_auto_placement'] !== 'enabled') {
        error_log('Pin CTA: Automatic placement is disabled');
        return false;
    }
    
    error_log('Pin CTA: Should apply to post ' . $post_id);
    return true;
}

// Main content filter function
function pin_cta_add_to_content($content) {
    // Only proceed if we have content to work with
    if (empty($content)) {
        error_log('Pin CTA: Empty content, skipping');
        return $content;
    }
    
    // Check if we're on a single post page
    if (!pin_cta_is_single_post()) {
        error_log('Pin CTA: Not on a single post page, skipping');
        return $content;
    }

    // For debugging
    $post_id = get_the_ID();
    error_log('Pin CTA content filter running on post: ' . $post_id);

    // Check if we should apply the CTA to this post
    if (!pin_cta_should_apply_to_post($post_id)) {
        error_log('Pin CTA should not be applied to this post');
        return $content;
    }

    // Get options
    $options = get_option('pin_cta_options');

    // Generate the Pin CTA HTML
    $pin_cta = pin_cta_shortcode(array(
        'style' => $options['pin_cta_default_style'],
        'isInline' => ($options['pin_cta_default_layout'] === 'inline'),
        'customText' => $options['pin_cta_default_text']
    ));

    // For debugging
    error_log('Pin CTA layout setting: ' . $options['pin_cta_default_layout']);
    error_log('Pin CTA isInline value passed to shortcode: ' . var_export(($options['pin_cta_default_layout'] === 'inline'), true));

    // Get selected positions
    $positions = (array)$options['pin_cta_positions'];
    
    // For debugging
    error_log('Pin CTA positions to apply: ' . print_r($positions, true));
    
    // Store original content
    $modified_content = $content;
    $original_content_length = strlen($content);
    error_log('Pin CTA original content length: ' . $original_content_length);

    // Handle each position
    foreach ($positions as $position) {
        switch ($position) {
            case 'after_title':
                // Add at the beginning of content
                error_log('Pin CTA applying after_title position');
                $modified_content = $pin_cta . $modified_content;
                break;

            case 'after_first_header':
                if (preg_match('/<h[2-6][^>]*>.*?<\/h[2-6]>/is', $modified_content)) {
                    error_log('Pin CTA applying after_first_header position');
                    $modified_content = preg_replace(
                        '/(<h[2-6][^>]*>.*?<\/h[2-6]>)/is',
                        '$1' . $pin_cta,
                        $modified_content,
                        1
                    );
                } else {
                    error_log('Pin CTA no headers found for after_first_header position');
                }
                break;

            case 'after_first_paragraph':
                if (strpos($modified_content, '</p>') !== false) {
                    error_log('Pin CTA applying after_first_paragraph position');
                    $pos = strpos($modified_content, '</p>');
                    $modified_content = substr_replace($modified_content, '</p>' . $pin_cta, $pos, 4);
                } else {
                    error_log('Pin CTA no paragraphs found for after_first_paragraph position');
                }
                break;

            case 'middle_content':
                $parts = explode('</p>', $modified_content);
                if (count($parts) > 1) {
                    error_log('Pin CTA applying middle_content position');
                    $middle = ceil(count($parts) / 2);
                    $parts[$middle - 1] .= $pin_cta;
                    $modified_content = implode('</p>', $parts);
                } else {
                    error_log('Pin CTA not enough paragraphs for middle_content position');
                }
                break;

            case 'after_content':
                error_log('Pin CTA applying after_content position');
                $modified_content .= $pin_cta;
                break;

            default:
                error_log('Pin CTA unknown position: ' . $position);
                break;
        }
    }

    // Check if content was modified
    $modified_content_length = strlen($modified_content);
    error_log('Pin CTA modified content length: ' . $modified_content_length);
    
    if ($original_content_length == $modified_content_length) {
        error_log('Pin CTA WARNING: Content length unchanged, widget may not have been inserted');
    }

    return $modified_content;
}

// Make sure the filter is properly registered with the right priority
function pin_cta_register_filters() {
    // Remove any existing filters first to avoid duplicates
    remove_filter('the_content', 'pin_cta_add_to_content', 20);
    
    // Add the filter with a high priority to ensure it runs after other plugins
    add_filter('the_content', 'pin_cta_add_to_content', 99);
    
    error_log('Pin CTA filters registered');
}

// Call this function during initialization
add_action('wp', 'pin_cta_register_filters');

// Initialize the plugin
/**
 * Initialize the Pin CTA plugin
 * 
 * This function sets up all the necessary hooks and filters for the plugin to work.
 * It's called at the end of the plugin file to ensure everything is properly initialized.
 * 
 * @since 1.0.3
 */
function pin_cta_init() {
    // Register the shortcode
    add_shortcode('pin_cta_button', 'pin_cta_shortcode');
    
    // Register the block
    add_action('init', 'pin_cta_register_block');
    
    // Add the admin menu
    add_action('admin_menu', 'pin_cta_add_admin_menu');
    
    // Initialize settings
    add_action('admin_init', 'pin_cta_settings_init');
    
    // Add the content filter
    add_filter('the_content', 'pin_cta_add_to_content', 20);
}

// Run the initialization
pin_cta_init();

// Remove any existing hooks to prevent duplicates
remove_filter('the_content', 'pin_cta_add_to_content', 20);
remove_filter('the_title', 'pin_cta_after_post_title');

// Function to check if the CSS for the inline style is being properly loaded
function pin_cta_check_css_loading() {
    // Only run on admin pages
    if (!is_admin()) {
        return;
    }
    
    // Check if we're on the Pin CTA settings page
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'toplevel_page_pin_cta_settings') {
        return;
    }
    
    // Check if the CSS file exists
    $css_file = plugin_dir_path(__FILE__) . 'blocks/style.css';
    if (!file_exists($css_file)) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>Pin CTA: CSS file not found. This may cause display issues with the inline layout.</p></div>';
        });
        return;
    }
    
    // Check if the CSS file contains the inline styles
    $css_content = file_get_contents($css_file);
    if (strpos($css_content, '.pin-cta-inline') === false) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>Pin CTA: Inline styles may be missing from the CSS file. This could affect the display of inline CTAs.</p></div>';
        });
    }
}
add_action('admin_init', 'pin_cta_check_css_loading');

/**
 * Check if image buttons should be applied to a post
 *
 * @param int|null $post_id The post ID to check
 * @return bool Whether image buttons should be applied
 */
function pin_cta_should_apply_image_buttons($post_id = null) {
    // Get options
    $options = get_option('pin_cta_options');
    
    // Check if image buttons are enabled
    if (empty($options['pin_cta_enable_image_buttons']) || $options['pin_cta_enable_image_buttons'] !== 'enabled') {
        return false;
    }
    
    return true;
}

/**
 * Enqueue scripts and styles for the Pinterest image buttons
 */
function pin_cta_enqueue_image_buttons_assets() {
    // Check if we're on a single post or page
    if (!is_admin() && (is_singular('post') || is_singular('page'))) {
        // Get options for button text
        $options = get_option('pin_cta_options', array(
            'pin_cta_image_button_text' => 'Pin This',
            'pin_cta_image_button_style' => 'default'
        ));
        
        // Enqueue the JavaScript
        wp_enqueue_script(
            'pin-cta-image-buttons',
            plugins_url('pin-cta-image-buttons.js', __FILE__),
            array('jquery'),
            filemtime(plugin_dir_path(__FILE__) . 'pin-cta-image-buttons.js'),
            true
        );
        
        // Pass the options to JavaScript
        wp_localize_script(
            'pin-cta-image-buttons',
            'pinCtaOptions',
            array(
                'buttonText' => $options['pin_cta_image_button_text'],
                'buttonStyle' => $options['pin_cta_image_button_style']
            )
        );
        
        // Enqueue the CSS
        wp_enqueue_style(
            'pin-cta-image-buttons',
            plugins_url('pin-cta-image-buttons.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'pin-cta-image-buttons.css')
        );
    }
}
add_action('wp_enqueue_scripts', 'pin_cta_enqueue_image_buttons_assets', 10);

// Update the preview section to use the saved style
function pin_cta_update_preview_script() {
    $options = get_option('pin_cta_options');
    $current_style = isset($options['pin_cta_image_button_style']) ? $options['pin_cta_image_button_style'] : 'default';
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Set initial style
        $('.pin-cta-image-button').addClass('pin-cta-<?php echo esc_js($current_style); ?>');
    });
    </script>
    <?php
}
add_action('admin_footer', 'pin_cta_update_preview_script');

