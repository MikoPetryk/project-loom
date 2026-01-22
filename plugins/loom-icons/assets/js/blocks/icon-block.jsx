import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, TextControl } from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

registerBlockType('icon-manager/icon', {
    apiVersion: 2,
    title: __('Icon', 'icon-manager'),
    description: __('Insert an icon from Icon Manager', 'icon-manager'),
    category: 'media',
    icon: 'art',
    supports: {
        html: false,
        align: true
    },
    attributes: {
        pack: {
            type: 'string',
            default: ''
        },
        iconName: {
            type: 'string',
            default: ''
        },
        size: {
            type: 'number',
            default: 24
        },
        color: {
            type: 'string',
            default: ''
        },
        cssClass: {
            type: 'string',
            default: ''
        }
    },

    edit: ({ attributes, setAttributes }) => {
        const { pack, iconName, size, color, cssClass } = attributes;
        const [packs, setPacks] = useState([]);
        const [icons, setIcons] = useState([]);
        const [loading, setLoading] = useState(false);
        const [baseSvg, setBaseSvg] = useState(''); // Store original SVG
        const blockProps = useBlockProps();

        // Load packs on mount
        useEffect(() => {
            setLoading(true);
            apiFetch({
                path: '/icon-manager/v1/packs'
            })
                .then(data => {
                    setPacks(data.packs || []);
                    setLoading(false);
                })
                .catch(error => {
                    console.error('Error loading packs:', error);
                    setLoading(false);
                });
        }, []);

        // Load icons when pack changes
        useEffect(() => {
            if (pack) {
                setLoading(true);
                apiFetch({
                    path: `/icon-manager/v1/packs/${pack}/icons`
                })
                    .then(data => {
                        setIcons(data.icons || []);
                        setLoading(false);
                    })
                    .catch(error => {
                        console.error('Error loading icons:', error);
                        setLoading(false);
                    });
            } else {
                setIcons([]);
            }
        }, [pack]);

        // Load base SVG only when pack or icon changes (not size/color)
        useEffect(() => {
            if (!pack || !iconName) {
                setBaseSvg('');
                return;
            }

            apiFetch({
                path: '/icon-manager/v1/render',
                method: 'POST',
                data: {
                    pack: pack,
                    icon: iconName,
                    size: 24, // Get at default size
                    color: '' // No color
                }
            })
                .then(data => {
                    setBaseSvg(data.svg || '');
                })
                .catch(error => {
                    console.error('Error loading icon:', error);
                    setBaseSvg('');
                });
        }, [pack, iconName]); // Only re-fetch when icon changes, not size/color

        // Apply size and color client-side for instant updates
        const getStyledSvg = () => {
            if (!baseSvg) return '';

            // Parse SVG
            const parser = new DOMParser();
            const doc = parser.parseFromString(baseSvg, 'image/svg+xml');
            const svg = doc.querySelector('svg');

            if (!svg) return baseSvg;

            // Apply size
            svg.setAttribute('width', size);
            svg.setAttribute('height', size);

            // Apply color to all paths, circles, rects, etc.
            if (color) {
                const fillableElements = svg.querySelectorAll('path, circle, rect, ellipse, polygon, polyline, line');
                fillableElements.forEach(el => {
                    if (el.getAttribute('fill') !== 'none') {
                        el.setAttribute('fill', color);
                    }
                    if (el.getAttribute('stroke') && el.getAttribute('stroke') !== 'none') {
                        el.setAttribute('stroke', color);
                    }
                });
            }

            return svg.outerHTML;
        };

        const packOptions = [
            { label: __('Select Pack', 'icon-manager'), value: '' },
            ...packs.map(p => ({ label: p, value: p }))
        ];

        const iconOptions = [
            { label: __('Select Icon', 'icon-manager'), value: '' },
            ...icons.map(i => ({ label: i.name, value: i.name }))
        ];

        const styledSvg = getStyledSvg();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Icon Settings', 'icon-manager')} initialOpen={true}>
                        <SelectControl
                            label={__('Icon Pack', 'icon-manager')}
                            value={pack}
                            options={packOptions}
                            onChange={(value) => {
                                setAttributes({ pack: value, iconName: '' });
                            }}
                            disabled={loading}
                        />

                        {pack && (
                            <SelectControl
                                label={__('Icon', 'icon-manager')}
                                value={iconName}
                                options={iconOptions}
                                onChange={(value) => setAttributes({ iconName: value })}
                                disabled={loading}
                            />
                        )}

                        {pack && iconName && (
                            <>
                                <RangeControl
                                    label={__('Size', 'icon-manager') + ': ' + size + 'px'}
                                    value={size}
                                    onChange={(value) => setAttributes({ size: value })}
                                    min={8}
                                    max={256}
                                    step={1}
                                />

                                <div style={{ marginBottom: '16px' }}>
                                    <label style={{ display: 'block', marginBottom: '8px', fontWeight: 500 }}>
                                        {__('Color', 'icon-manager')}
                                    </label>
                                    <input
                                        type="color"
                                        value={color || '#000000'}
                                        onChange={(e) => setAttributes({ color: e.target.value })}
                                        style={{ width: '100%', height: '40px', cursor: 'pointer' }}
                                    />
                                    <button
                                        onClick={() => setAttributes({ color: '' })}
                                        style={{
                                            marginTop: '8px',
                                            padding: '4px 12px',
                                            fontSize: '12px',
                                            cursor: 'pointer'
                                        }}
                                    >
                                        {__('Reset Color', 'icon-manager')}
                                    </button>
                                </div>

                                <TextControl
                                    label={__('CSS Class', 'icon-manager')}
                                    value={cssClass}
                                    onChange={(value) => setAttributes({ cssClass: value })}
                                    help={__('Add custom CSS classes', 'icon-manager')}
                                />
                            </>
                        )}
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    {loading && (
                        <div style={{ padding: '20px', textAlign: 'center' }}>
                            {__('Loading...', 'icon-manager')}
                        </div>
                    )}
                    {!loading && (!pack || !iconName) && (
                        <div style={{
                            padding: '40px 20px',
                            border: '2px dashed #ddd',
                            borderRadius: '4px',
                            textAlign: 'center',
                            color: '#666',
                            backgroundColor: '#f9f9f9'
                        }}>
                            <span style={{ fontSize: '48px' }}>🎨</span>
                            <p style={{ margin: '12px 0 0' }}>
                                {__('Select an icon pack and icon from the sidebar →', 'icon-manager')}
                            </p>
                        </div>
                    )}
                    {!loading && pack && iconName && (
                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                padding: '20px',
                                border: '1px solid #ddd',
                                borderRadius: '4px',
                                backgroundColor: '#fff',
                                minHeight: '100px'
                            }}
                        >
                            {styledSvg ? (
                                <div dangerouslySetInnerHTML={{ __html: styledSvg }} />
                            ) : (
                                <div style={{ color: '#999' }}>
                                    {__('Loading icon...', 'icon-manager')}
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </>
        );
    },

    save: ({ attributes }) => {
        // Return null - render on frontend via PHP
        return null;
    }
});