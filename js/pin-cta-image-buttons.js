jQuery(document).ready(function($) {
    // Only run on single posts
    if (!$('body').hasClass('single-post')) {
        return;
    }

    // SVG for Pinterest logo
    const pinterestLogoSVG = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M12 0C5.373 0 0 5.373 0 12c0 4.99 3.657 9.132 8.48 10.099-.118-.859-.224-2.178.045-3.116.244-.834 1.577-5.313 1.577-5.313s-.402-.803-.402-1.986c0-1.863 1.08-3.254 2.42-3.254 1.142 0 1.693.859 1.693 1.89 0 1.151-.733 2.87-1.11 4.467-.318 1.322.672 2.401 1.99 2.401 2.388 0 4.224-2.516 4.224-6.14 0-3.204-2.303-5.442-5.581-5.442-3.803 0-6.037 2.849-6.037 5.794 0 1.148.438 2.382 1.054 3.049.117.13.134.244.099.377-.109.427-.354 1.35-.398 1.539-.062.248-.2.302-.461.182-1.723-.796-2.797-3.294-2.797-5.301 0-4.33 3.141-8.306 9.067-8.306 4.754 0 8.442 3.384 8.442 7.917 0 4.715-2.969 8.502-7.085 8.502-1.381 0-2.681-.712-3.123-1.557l-.847 3.216c-.306 1.156-1.14 2.6-1.699 3.487C9.19 23.414 10.573 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/>
      </svg>
    `;

    // Target WordPress post images but exclude certain elements
    $(".wp-block-image img, .entry-content img, .post-content img, article img").not(".pin-cta-container img, .pin-cta-logo img, a[class*='PIN_'] img").each(function() {
        const $image = $(this);
        
        // Skip if already wrapped
        if ($image.parent().hasClass("pin-cta-image-container")) {
            return;
        }
        
        // Skip if inside a container we want to exclude
        if ($image.parents(".pin-cta-container, .pin-cta-logo").length > 0) {
            return;
        }
        
        const imageSrc = $image.attr("src");
        $image.wrap("<div class='pin-cta-image-container'></div>");

        const $pinButton = $(`
            <a href="#" class="pin-cta-image-overlay">
                ${pinterestLogoSVG} Pin
            </a>
        `);

        $image.after($pinButton);

        $pinButton.on("click", function(event) {
            event.preventDefault();
            const pinUrl = `https://www.pinterest.com/pin/create/button/?url=${encodeURIComponent(window.location.href)}&media=${encodeURIComponent(imageSrc)}&description=${encodeURIComponent(document.title)}`;
            window.open(pinUrl, "_blank", "width=750,height=550,toolbar=0,menubar=0,location=0");
            return false;
        });
    });
});
        