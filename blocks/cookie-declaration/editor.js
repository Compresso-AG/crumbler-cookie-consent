(function() {
    'use strict';

    var el = wp.element.createElement;
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var Placeholder = wp.components.Placeholder;
    var __ = wp.i18n.__;

    registerBlockType('crumbler/cookie-declaration', {
        title: __('Cookie Declaration', 'crumbler-cookie-consent'),
        description: __('Displays the Crumbler cookie declaration.', 'crumbler-cookie-consent'),
        icon: 'shield',
        category: 'widgets',
        supports: {
            html: false,
            multiple: false
        },
        attributes: {
            lang: {
                type: 'string',
                default: ''
            }
        },

        edit: function(props) {
            var lang = props.attributes.lang;

            return el('div', { className: props.className },
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Settings', 'crumbler-cookie-consent'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Language', 'crumbler-cookie-consent'),
                            help: __('Leave empty to use the plugin setting.', 'crumbler-cookie-consent'),
                            value: lang,
                            options: [
                                { label: __('Use plugin setting', 'crumbler-cookie-consent'), value: '' },
                                { label: 'Deutsch', value: 'de' },
                                { label: 'Fran\u00e7ais', value: 'fr' },
                                { label: 'Italiano', value: 'it' },
                                { label: 'English', value: 'en' }
                            ],
                            onChange: function(value) {
                                props.setAttributes({ lang: value });
                            }
                        })
                    )
                ),
                el(Placeholder, {
                    icon: 'shield',
                    label: __('Cookie Declaration', 'crumbler-cookie-consent') + ' (Crumbler)',
                    instructions: __('Displays the cookie declaration with all detected services and cookies. Data is automatically loaded from the Crumbler API on the frontend.', 'crumbler-cookie-consent')
                })
            );
        },

        save: function() {
            return null;
        }
    });
})();
