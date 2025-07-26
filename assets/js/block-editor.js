/**
 * Block Editor Integration for Roadmap Meta Fields
 */
(function(wp) {
    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { PanelRow, TextControl, SelectControl, RangeControl, DatePicker, ExternalLink } = wp.components;
    const { useSelect, useDispatch } = wp.data;
    const { createElement: el, Fragment } = wp.element;
    const { __ } = wp.i18n;

    const RoadmapMetaPanel = () => {
        const postMeta = useSelect(select => 
            select('core/editor').getEditedPostAttribute('meta') || {}
        );
        
        const { editPost } = useDispatch('core/editor');
        const fields = window.roadmapMeta?.fields || [];
        const isPro = window.roadmapMeta?.isPro || false;

        const updateMeta = (key, value) => {
            editPost({ meta: { [key]: value } });
        };

        const renderField = (field) => {
            const value = postMeta[field.id] || field.default || '';
            const isProField = field.pro && !isPro;

            if (isProField) {
                return el(PanelRow, { key: field.id },
                    el('div', { className: 'roadmap-pro-field-wrapper' },
                        el('label', {}, field.label),
                        el('div', { className: 'roadmap-pro-notice' },
                            el('span', { className: 'roadmap-pro-badge' }, 'PRO'),
                            el('span', {}, __('This feature requires the Pro addon', 'roadmap-plugin'))
                        )
                    )
                );
            }

            switch (field.type) {
                case 'select':
                    return el(PanelRow, { key: field.id },
                        el(SelectControl, {
                            label: field.label,
                            help: field.description,
                            value: value,
                            options: Object.entries(field.options || {}).map(([val, label]) => ({
                                value: val,
                                label: label
                            })),
                            onChange: (newValue) => updateMeta(field.id, newValue),
                            disabled: field.readonly
                        })
                    );

                case 'range':
                    return el(PanelRow, { key: field.id },
                        el('div', { className: 'roadmap-range-field' },
                            el(RangeControl, {
                                label: field.label + ': ' + value + '%',
                                help: field.description,
                                value: parseInt(value) || 0,
                                min: field.min || 0,
                                max: field.max || 100,
                                step: field.step || 1,
                                onChange: (newValue) => updateMeta(field.id, newValue),
                                disabled: field.readonly,
                                withInputField: false
                            })
                        )
                    );

                case 'date':
                    return el(PanelRow, { key: field.id },
                        el('div', { className: 'roadmap-date-field' },
                            el('label', {}, field.label),
                            el(TextControl, {
                                type: 'date',
                                help: field.description,
                                value: value,
                                onChange: (newValue) => updateMeta(field.id, newValue),
                                disabled: field.readonly
                            })
                        )
                    );

                case 'url':
                    return el(PanelRow, { key: field.id },
                        el(TextControl, {
                            label: field.label,
                            help: field.description,
                            type: 'url',
                            placeholder: field.placeholder,
                            value: value,
                            onChange: (newValue) => updateMeta(field.id, newValue),
                            disabled: field.readonly
                        })
                    );

                case 'number':
                    return el(PanelRow, { key: field.id },
                        el(TextControl, {
                            label: field.label,
                            help: field.description,
                            type: 'number',
                            value: value,
                            onChange: (newValue) => updateMeta(field.id, newValue),
                            disabled: field.readonly
                        })
                    );

                default:
                    return el(PanelRow, { key: field.id },
                        el(TextControl, {
                            label: field.label,
                            help: field.description,
                            value: value,
                            onChange: (newValue) => updateMeta(field.id, newValue),
                            disabled: field.readonly
                        })
                    );
            }
        };

        // Group fields by type
        const basicFields = fields.filter(f => f.free !== false && !f.pro);
        const proFields = fields.filter(f => f.pro);

        return el(Fragment, {},
            el(PluginDocumentSettingPanel, {
                name: 'roadmap-meta-basic',
                title: __('Roadmap Details', 'roadmap-plugin'),
                className: 'roadmap-meta-panel'
            },
                basicFields.map(field => renderField(field))
            ),
            
            proFields.length > 0 && el(PluginDocumentSettingPanel, {
                name: 'roadmap-meta-pro',
                title: __('Pro Features', 'roadmap-plugin'),
                className: 'roadmap-meta-panel roadmap-meta-pro'
            },
                proFields.map(field => renderField(field))
            )
        );
    };

    // Register the plugin
    registerPlugin('roadmap-meta-fields', {
        render: RoadmapMetaPanel,
        icon: 'chart-line'
    });

})(window.wp);