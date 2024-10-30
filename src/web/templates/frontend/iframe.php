<?php
use CompygoSocialFeed\Helper\RequestHelper;
?>
<html style="margin: 0">
    <head>
        <?php
            wp_print_scripts();

            global $wp_styles;
            foreach( $wp_styles->queue as $style ) {
                if (in_array($style, ['dashicons', 'cgusf_style', 'cgusf_slick', 'cgusf_slick-theme', 'cgusf_smooth-scrollbar'])) {
                    echo '<link type="text/css" rel="stylesheet" href="' 
                        . esc_url($wp_styles->registered[$style]->src) 
                        . '?ver=1.1.0"></link>';

                    if ($wp_styles->registered[$style]->extra) {
                        echo '<style>' . esc_html($wp_styles->registered[$style]->extra['after'][0]) . '</style>';
                    }
                }
            }
        ?>
        <script>
            jQuery(document).ready(function($) {
                let wrapper = $('.cgusf__wrapper'),
                    body = $('body'),
                    Scrollbar = window.Scrollbar;

                let initScrollbar = function () {
                    Scrollbar.init(document.querySelector('body'), {
                        damping: 1,
                        alwaysShowTracks: true
                    });
                }

                initScrollbar();

                body.on('cgusf:fetch:posts:after', function (e) {
                    e.preventDefault();
                    initScrollbar();
                });
            });
        </script>
    </head>
    <body class="cgusf__iframe" style="height: 100%; padding: 10px 20px; margin: 0">
        <?php
            $paramFeedId = RequestHelper::getRequestParam('feed_id', 'key');
            $paramCgFeedId = RequestHelper::getRequestParam('cg_feed_id', 'key', 'get');
            $id = (int)(!empty($paramFeedId) ?: $paramCgFeedId);
            echo do_shortcode('[compygo-cgusf id='. esc_attr($id) .']');
        ?>
    </body>
</html>