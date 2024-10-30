(function (blocks, element, blockEditor, apiFetch, components) {
    var el = element.createElement;
    var BlockControls = blockEditor.BlockControls;
    var useBlockProps = blockEditor.useBlockProps;
    var ToolbarButton = components.ToolbarButton;
    var ToolbarGroup = components.ToolbarGroup;

    blocks.registerBlockType( 'compygo-social-feed/social-feed-block', {
        title: 'Compygo Social Feed',
        icon: 'universal-access-alt',
        category: 'widgets',
        attributes: {
            feeds: {type: 'array'},
            isSubmit: {type: 'boolean'},
            selectedFeed: {type: 'string'},
        },
        example: {
            attributes: {
                isSubmit: false,
                selectedFeed: false,
                feeds: {},
            },
        },
        edit: function (props) {
            function setSelectedFeed(event) {
                props.setAttributes({selectedFeed: event.target.value});
            }
            function onSubmit(status) {
                props.setAttributes({isSubmit: status});

                if (!props.attributes.selectedFeed) {
                    props.setAttributes({selectedFeed: props.attributes.feeds[0].id});
                }
            }

            var feeds = props.attributes.feeds;

            if (!feeds) {
                apiFetch({
                    path: '/compygo-social-feed/v1/feed',
                    method: 'GET'
                } ).then((feeds) => {
                    props.setAttributes({feeds: feeds});
                });
            }

            var form;
            if (!props.attributes.isSubmit) {
                if (feeds && feeds.length > 0) {
                    var options = [];
                    
                    feeds.forEach(feed => {
                        options.push(el('option', {value: feed.id}, feed.name))
                    }),
                    form = el (
                        'form', 
                        {style: {flexWrap: 'nowrap'}},
                        el(
                            'select',
                            {
                                class: 'components-placeholder__select', 
                                style: { width: '100%', maxWidth: '100%', marginRight: 10},
                                value: props.attributes.selectedFeed,
                                onChange: setSelectedFeed
                            },
                            options
                        ),
                        el(
                            'sumbit',
                            {class: 'components-button is-primary', onClick: () => onSubmit(true)},
                            'Insert'
                        )
                    )
                } else {
                    form = el (
                        'div', {}, 'You do not have create a feed'
                    )
                }
    
                return el(
                    'div',
                    {class: 'components-placeholder wp-block-embed'},
                    el(
                        'div',
                        {class: 'components-placeholder__label'},
                        el('img',{style: {width: 100, marginTop: 7}, src: location.protocol + '//' + location.host + '/wp-content/plugins/compygo-social-feed/pub/img/logo-dark.svg'}),
                        el('div',{style: {fontSize: 20, marginLeft: 10, fontWeight: 400}}, '- Social Feed')
                    ),
                    el(
                        'fieldset',
                        {class: 'components-placeholder__fieldset'},
                        el(
                            'legend',
                            {class: 'components-placeholder__instructions'},
                            'Choose a feed to display social posts on your site.'),
                        form,
                        el(
                            'div',
                            {class: 'components-placeholder__learn-more'}, 
                            el(
                                'a',
                                {class:'components-external-link', target:'_blank', href: 'https://compygo.com/blog/wordpress-social-feed-plugin/guide-for-wordpress-social-feed-plugin/'},
                                'Learn more about social feeds (opens in a new tab)'
                            )
                        )
                    )
                );          
            }

            return el(
                'div',
                useBlockProps(),
                el(
                    BlockControls,
                    { key: 'controls' },
                    el(
                        ToolbarGroup, 
                        {},                    
                        el(ToolbarButton, {
                            icon: 'edit',
                            label: 'Edit',
                            onClick: () => onSubmit(false)
                        })
                    )
                ),
                el(
                    'iframe', 
                    {
                        style: {width: '100%', minHeight: '400px', marginTop: 25},
                        src: '/compygo-iframe?cg_feed_id=' + props.attributes.selectedFeed + '&cg_preview=1'
                    }
                )
            );
        },
        save: function (props) {
            if (props.attributes.selectedFeed && props.attributes.isSubmit) {
                return '[compygo-cgusf id=' + props.attributes.selectedFeed + ']';
            }

            return null;
        },
    } );
})( window.wp.blocks, window.wp.element, window.wp.blockEditor, wp.apiFetch, wp.components);