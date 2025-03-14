const { registerBlockType } = wp.blocks;
const { createElement } = wp.element;
const { InspectorControls, MediaUpload, MediaUploadCheck } = wp.blockEditor;
const { PanelBody, SelectControl, ToggleControl, Button } = wp.components;

// Pinterest SVG icon component
const PinterestIcon = () => createElement('svg', {
    className: 'pin-cta-pinterest-icon',
    viewBox: '0 0 24 24',
    width: '20',
    height: '20'
}, 
    createElement('path', {
        d: 'M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z'
    })
);

// Make sure pinCtaDefaults is defined
if (typeof pinCtaDefaults === 'undefined') {
    window.pinCtaDefaults = {
        style: 'default',
        isInline: false,
        text: 'Pin This Now to Remember It Later',
        pluginUrl: ''
    };
}

registerBlockType('pin-cta/block', {
    title: 'Pin CTA',
    icon: 'pinterest',
    category: 'widgets',
    
    attributes: {
        style: {
            type: 'string',
            default: pinCtaDefaults.style
        },
        isInline: {
            type: 'boolean',
            default: pinCtaDefaults.isInline
        },
        customText: {
            type: 'string',
            default: pinCtaDefaults.text
        },
        mediaId: {
            type: 'number'
        },
        mediaUrl: {
            type: 'string'
        }
    },
    
    edit: function(props) {
        const { attributes, setAttributes } = props;
        const containerClasses = `pin-cta-container pin-cta-${attributes.style}${attributes.isInline ? ' pin-cta-inline' : ''}`;
        
        // Create the block preview
        const blockPreview = createElement(
            'div',
            { className: containerClasses },
            [
                createElement('div', 
                    { className: 'pin-cta-logo' },
                    createElement('svg', {
                        className: 'pin-cta-pinterest-icon',
                        viewBox: '0 0 24 24',
                        width: '20',
                        height: '20'
                    }, 
                        createElement('path', {
                            d: 'M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z'
                        })
                    )
                ),
                createElement('div', 
                    { className: 'pin-cta-text' },
                    attributes.customText
                ),
                createElement('a',
                    {
                        className: 'pin-cta-pin-button',
                        href: '#'
                    },
                    [
                        createElement(PinterestIcon),
                        'Pin This'
                    ]
                )
            ]
        );

        return [
            createElement(
                InspectorControls,
                null,
                createElement(
                    PanelBody,
                    { title: 'Style Settings' },
                    [
                        createElement(SelectControl, {
                            label: 'Style',
                            value: attributes.style,
                            options: [
                                { label: 'Classic Red & White', value: 'default' },
                                { label: 'Burgundy & Gold', value: 'style1' },
                                { label: 'Fresh Green & White', value: 'style2' },
                                { label: 'Soft Pink & Rose', value: 'style3' },
                                { label: 'Navy & Gold', value: 'style4' },
                                { label: 'Sage & Cream', value: 'style5' },
                                { label: 'Royal Purple & Lavender', value: 'style6' },
                                { label: 'Ocean Teal & Coral', value: 'style7' },
                                { label: 'Midnight Blue & Silver', value: 'style8' },
                                { label: 'Autumn Orange & Cream', value: 'style9' },
                                { label: 'Forest & Mint', value: 'style10' }
                            ],
                            onChange: (newStyle) => setAttributes({ style: newStyle })
                        }),
                        createElement(ToggleControl, {
                            label: 'Inline Layout',
                            checked: attributes.isInline,
                            onChange: (isInline) => setAttributes({ isInline })
                        }),
                        createElement(MediaUploadCheck, null,
                            createElement(MediaUpload, {
                                onSelect: (media) => {
                                    setAttributes({
                                        mediaId: media.id,
                                        mediaUrl: media.url
                                    });
                                },
                                allowedTypes: ['image'],
                                value: attributes.mediaId,
                                render: ({ open }) => createElement(
                                    'div',
                                    null,
                                    [
                                        createElement('p', null, 'Custom Pinterest Image'),
                                        attributes.mediaUrl && createElement(
                                            'img',
                                            {
                                                src: attributes.mediaUrl,
                                                style: { maxWidth: '200px', display: 'block', marginBottom: '10px' }
                                            }
                                        ),
                                        createElement(
                                            Button,
                                            {
                                                onClick: open,
                                                isSecondary: true
                                            },
                                            attributes.mediaId ? 'Change Image' : 'Select Image'
                                        ),
                                        attributes.mediaId && createElement(
                                            Button,
                                            {
                                                onClick: () => setAttributes({ mediaId: null, mediaUrl: null }),
                                                isLink: true,
                                                isDestructive: true
                                            },
                                            'Remove Image'
                                        )
                                    ]
                                )
                            })
                        )
                    ]
                )
            ),
            blockPreview
        ];
    },
    
    save: function(props) {
        // Add a fallback content in case server-side rendering fails
        const { attributes } = props;
        const containerClasses = `pin-cta-container pin-cta-${attributes.style}${attributes.isInline ? ' pin-cta-inline' : ''}`;
        
        return createElement(
            'div',
            { className: containerClasses },
            [
                createElement('div', 
                    { className: 'pin-cta-logo' },
                    createElement('svg', {
                        className: 'pin-cta-pinterest-icon',
                        viewBox: '0 0 24 24',
                        width: '20',
                        height: '20'
                    }, 
                        createElement('path', {
                            d: 'M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z'
                        })
                    )
                ),
                createElement('div', 
                    { className: 'pin-cta-text' },
                    attributes.customText
                ),
                createElement('a',
                    {
                        className: 'pin-cta-pin-button',
                        href: '#'
                    },
                    [
                        createElement(PinterestIcon),
                        'Pin This'
                    ]
                )
            ]
        );
    }
});
