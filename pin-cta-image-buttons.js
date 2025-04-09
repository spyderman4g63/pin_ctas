/**
 * Pinterest CTA Image Buttons
 * Adds Pinterest save buttons to post images
 */
jQuery(document).ready(function($) {
    // Only proceed if we're on a single post page
    if (!$('body').hasClass('single-post') && !$('body').hasClass('single-page')) {
        return;
    }

    // Get the button style from WordPress settings
    var buttonStyle = (typeof pinCtaOptions !== 'undefined' && pinCtaOptions.buttonStyle) ? pinCtaOptions.buttonStyle : 'default';
    var buttonText = (typeof pinCtaOptions !== 'undefined' && pinCtaOptions.buttonText) ? pinCtaOptions.buttonText : 'Pin This';

    // Function to create the Pin It button
    function createPinButton(imageUrl, pageUrl, description) {
        var button = $('<a>', {
            href: 'https://pinterest.com/pin/create/button/' +
                  '?url=' + encodeURIComponent(pageUrl) +
                  '&media=' + encodeURIComponent(imageUrl) +
                  '&description=' + encodeURIComponent(description),
            class: 'pin-cta-image-button ' + buttonStyle,
            target: '_blank',
            rel: 'noopener'
        });

        // Use a simple string for the button content including SVG
        var buttonHtml = '<span class="pin-cta-button-text">' + buttonText + '</span>' +
                        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="16" height="16" style="fill: currentColor;">' +
                        '<path d="M204 6.5C101.4 6.5 0 74.9 0 185.6 0 256 39.6 296 63.6 296c9.9 0 15.6-27.6 15.6-35.4 0-9.3-23.7-29.1-23.7-67.8 0-80.4 61.2-137.4 140.4-137.4 68.1 0 118.5 38.7 118.5 109.8 0 53.1-21.3 152.7-90.3 152.7-24.9 0-46.2-18-46.2-43.8 0-37.8 26.4-74.4 26.4-113.4 0-66.2-93.9-54.2-93.9 25.8 0 16.8 2.1 35.4 9.6 50.7-13.8 59.4-42 147.9-42 209.1 0 18.9 2.7 37.5 4.5 56.4 3.4 3.8 1.7 3.4 6.9 1.5 50.4-69 48.6-82.5 71.4-172.8 12.3 23.4 44.1 36 69.3 36 106.2 0 153.9-103.5 153.9-196.8C384 71.3 298.2 6.5 204 6.5z"/>' +
                        '</svg>';

        button.html(buttonHtml);
        return button;
    }

    // Process each image in the content
    $('.entry-content img, .post-content img, .content img, article img').each(function() {
        var $img = $(this);
        
        // Skip small images and already processed images
        if ($img.width() < 150 || $img.height() < 150 || $img.closest('.pin-cta-image-wrapper').length > 0) {
            return;
        }

        // Skip images in navigation areas
        if ($img.closest('nav, header, footer, .sidebar, .site-header, .site-footer').length > 0) {
            return;
        }

        var $wrapper = $('<div>', {
            class: 'pin-cta-image-wrapper',
            style: 'position: relative; display: inline-block; max-width: 100%;'
        });

        // Get image details
        var imageUrl = $img.prop('src');
        var pageUrl = window.location.href;
        var description = $img.attr('alt') || document.title;

        // Create and add the Pin button
        var $pinButton = createPinButton(imageUrl, pageUrl, description);
        $img.wrap($wrapper);
        $img.after($pinButton);
    });
}); 