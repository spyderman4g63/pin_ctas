<?php
/*
Plugin Name: Pin CTA
Description: Adds a Pinterest CTA shortcode and block with multiple design templates for the Gutenberg editor.
Version: 1.5
Author: John Ward
Author URI: https://johnathanward.com
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register the shortcode
function pin_cta_shortcode($atts) {
    // Handle both block attributes and shortcode attributes
    if (is_array($atts) && isset($atts['isInline'])) {
        // Coming from block editor
        $inline = $atts['isInline'];
        $style = isset($atts['style']) ? $atts['style'] : 'default';
        $media_id = isset($atts['mediaId']) ? $atts['mediaId'] : null;
    } else {
        // Coming from shortcode
        $atts = shortcode_atts(array(
            'style' => 'default',
            'inline' => false,
            'media_id' => null,
        ), $atts, 'pin_cta_button');
        $inline = filter_var($atts['inline'], FILTER_VALIDATE_BOOLEAN);
        $style = $atts['style'];
        $media_id = $atts['media_id'];
    }

    $inline_class = $inline ? ' pin-cta-inline' : '';
    
    // Determine which image URL to use
    $image_url = '';
    if ($media_id) {
        $image_url = wp_get_attachment_url($media_id);
    } elseif (has_post_thumbnail()) {
        $image_url = get_the_post_thumbnail_url();
    }
    
    ob_start();
    ?>
    <div class="pin-cta-container pin-cta-<?php echo esc_attr($style); ?><?php echo $inline_class; ?>">
        <div class="pin-cta-logo">
            <img src="https://upload.wikimedia.org/wikipedia/commons/0/08/Pinterest-logo.png" alt="Pinterest">
        </div>
        <div class="pin-cta-text">Pin This Now to Remember It Later</div>
        <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink()); ?>&media=<?php echo esc_url($image_url); ?>&description=<?php echo urlencode(get_the_title()); ?>" target="_blank" class="pin-button">
            <svg class="pinterest-icon" viewBox="0 0 24 24" width="20" height="20">
                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/>
            </svg>
            Pin This
        </a>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('pin_cta_button', 'pin_cta_shortcode');

function pin_cta_register_block() {
    // Register the block's script
    wp_register_script(
        'pin-cta-block',
        plugins_url('blocks/block.js', __FILE__),
        array('wp-blocks', 'wp-editor', 'wp-element', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/block.js')
    );

    // Register the editor-specific CSS for the block
    wp_register_style(
        'pin-cta-editor-style',
        plugins_url('blocks/editor.css', __FILE__),
        array('wp-edit-blocks'),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/editor.css')
    );

    // Register the frontend CSS for the block
    wp_register_style(
        'pin-cta-style',
        plugins_url('blocks/style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'blocks/style.css')
    );

    // Register the block with all assets
    register_block_type('pin-cta/block', array(
        'editor_script'    => 'pin-cta-block',
        'editor_style'     => 'pin-cta-editor-style',
        'style'            => 'pin-cta-style', // Frontend style
        'render_callback'  => 'pin_cta_shortcode',
        'attributes'       => array(
            'style' => array(
                'type' => 'string',
                'default' => 'default'
            ),
            'isInline' => array(
                'type' => 'boolean',
                'default' => false
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
add_action('init', 'pin_cta_register_block');
