<?php
/*
Plugin Name: Pin CTA
Description: Adds a Pinterest Share shortcode and block.
Version: 1.0.3
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
    }

    $inline_class = $inline ? esc_attr(' pin-cta-inline') : '';
    
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
        'pin_cta_positions' => array('after_content')
    );

    // Set default options if they don't exist
    if (!get_option('pin_cta_options')) {
        update_option('pin_cta_options', $defaults);
    }

    // Register the settings with simpler format to avoid dynamic argument warning
    register_setting(
        'pin_cta_settings',  // Option group
        'pin_cta_options',   // Option name
        'pin_cta_sanitize_options' // Sanitization callback
    );

    // Default Settings Section
    add_settings_section(
        'pin_cta_settings_section',
        'Default Settings',
        null,
        'pin_cta_settings'
    );

    // Add setting for default style
    add_settings_field(
        'pin_cta_default_style',
        'Default Style',
        'pin_cta_style_field_callback',
        'pin_cta_settings',
        'pin_cta_settings_section'
    );

    // Add setting for default layout
    add_settings_field(
        'pin_cta_default_layout',
        'Default Layout',
        'pin_cta_layout_field_callback',
        'pin_cta_settings',
        'pin_cta_settings_section'
    );

    // Add setting for default text
    add_settings_field(
        'pin_cta_default_text',
        'Default Text',
        'pin_cta_text_field_callback',
        'pin_cta_settings',
        'pin_cta_settings_section'
    );

    // Automatic Placement Section
    add_settings_section(
        'pin_cta_placement_section',
        'Automatic Placement Settings',
        null,
        'pin_cta_settings'
    );

    // Add setting for automatic placement
    add_settings_field(
        'pin_cta_auto_placement',
        'Automatic Placement',
        'pin_cta_auto_placement_callback',
        'pin_cta_settings',
        'pin_cta_placement_section'
    );

    // Add setting for placement positions
    add_settings_field(
        'pin_cta_positions',
        'Placement Positions',
        'pin_cta_positions_callback',
        'pin_cta_settings',
        'pin_cta_placement_section'
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
    error_log('Pin CTA sanitizing options: ' . print_r($options, true));
    
    // Ensure we have an array
    if (!is_array($options)) {
        $options = array();
    }

    // Set defaults if options are missing
    $defaults = array(
        'pin_cta_auto_placement' => 'disabled',
        'pin_cta_default_style' => 'default',
        'pin_cta_default_layout' => 'block',
        'pin_cta_default_text' => 'Pin This Now to Remember It Later',
        'pin_cta_positions' => array('after_content')
    );

    // Merge with defaults
    $options = wp_parse_args($options, $defaults);

    // Sanitize auto placement (must be one of: disabled, enabled)
    $valid_auto_placements = array('disabled', 'enabled');
    if (!in_array($options['pin_cta_auto_placement'], $valid_auto_placements)) {
        $options['pin_cta_auto_placement'] = 'disabled';
    }
    
    // Sanitize default style (must be one of: default, style1, style2, etc.)
    $valid_styles = array('default', 'style1', 'style2', 'style3', 'style4', 'style5', 
                         'style6', 'style7', 'style8', 'style9', 'style10');
    if (!in_array($options['pin_cta_default_style'], $valid_styles)) {
        $options['pin_cta_default_style'] = 'default';
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

function pin_cta_options_page() {
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-pinterest" style="font-size: 30px; width: 30px; height: 30px; margin-right: 10px;"></span><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <form action="options.php" method="post">
                <?php
                settings_fields('pin_cta_settings');
                do_settings_sections('pin_cta_settings');
                submit_button();
                ?>
            </form>
        </div>
    </div>
    <?php
}

// Update the content filter to check only for posts
function pin_cta_add_to_content($content) {
    // Only proceed if we're on a single post
    // This ensures it only runs on individual post pages, not archives, home, etc.
    if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    // For debugging
    error_log('Pin CTA content filter running on post: ' . get_the_ID());

    // Check if we should apply the CTA to this post
    if (!pin_cta_should_apply_to_post()) {
        error_log('Pin CTA should not be applied to this post');
        return $content;
    }

    // Get options
    $options = get_option('pin_cta_options');

    // Generate the Pin CTA HTML
    $pin_cta = pin_cta_shortcode(array(
        'style' => $options['pin_cta_default_style'],
        'isInline' => $options['pin_cta_default_layout'] === 'inline',
        'customText' => $options['pin_cta_default_text']
    ));

    // Get selected positions
    $positions = (array)$options['pin_cta_positions'];
    
    // For debugging
    error_log('Pin CTA positions to apply: ' . print_r($positions, true));
    
    // Store original content
    $modified_content = $content;

    // Handle each position
    foreach ($positions as $position) {
        // Check if this position is valid for this content
        if (!pin_cta_is_valid_position($position, $modified_content)) {
            error_log('Pin CTA position ' . $position . ' is not valid for this content');
            continue;
        }
        
        switch ($position) {
            case 'after_title':
                // Add at the beginning of content
                error_log('Pin CTA applying after_title position');
                $modified_content = $pin_cta . $modified_content;
                break;

            case 'after_first_header':
                error_log('Pin CTA applying after_first_header position');
                $modified_content = preg_replace(
                    '/(<h[2-6][^>]*>.*?<\/h[2-6]>)/is',
                    '$1' . $pin_cta,
                    $modified_content,
                    1
                );
                break;

            case 'after_first_paragraph':
                error_log('Pin CTA applying after_first_paragraph position');
                $modified_content = preg_replace('/<\/p>/', '</p>' . $pin_cta, $modified_content, 1);
                break;

            case 'middle_content':
                error_log('Pin CTA applying middle_content position');
                $parts = explode('</p>', $modified_content);
                $middle = ceil(count($parts) / 2);
                $parts[$middle - 1] .= $pin_cta;
                $modified_content = implode('</p>', $parts);
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

    return $modified_content;
}

// Remove all existing hooks
remove_filter('the_content', 'pin_cta_add_to_content', 20);
remove_filter('the_title', 'pin_cta_after_post_title');

// Add only the content filter
// add_filter('the_content', 'pin_cta_add_to_content', 20); // Removed duplicate, now handled in pin_cta_init()

// Add a function to check if we should apply the CTA to this post
function pin_cta_should_apply_to_post($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // Only apply to posts, not pages or other post types
    if (get_post_type($post_id) !== 'post') {
        return false;
    }
    
    // Get options
    $options = get_option('pin_cta_options');
    
    // Check if automatic placement is enabled
    if (empty($options['pin_cta_auto_placement']) || $options['pin_cta_auto_placement'] !== 'enabled') {
        return false;
    }
    
    return true;
}

// Add a function to test if a position is valid and should be applied
function pin_cta_is_valid_position($position, $content) {
    switch ($position) {
        case 'after_title':
            // Always valid
            return true;
            
        case 'after_first_header':
            // Check if there's at least one header
            return preg_match('/<h[2-6][^>]*>.*?<\/h[2-6]>/is', $content) > 0;
            
        case 'after_first_paragraph':
            // Check if there's at least one paragraph
            return preg_match('/<\/p>/', $content) > 0;
            
        case 'middle_content':
            // Check if there are at least two paragraphs
            $parts = explode('</p>', $content);
            return count($parts) > 1;
            
        case 'after_content':
            // Always valid
            return true;
            
        default:
            return false;
    }
}

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

