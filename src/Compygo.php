<?php
namespace CompygoSocialFeed;

use CompygoSocialFeed\Helper\RequestHelper;
use CompygoSocialFeed\Model\Feed as FeedModel;
use CompygoSocialFeed\Model\Source as SourceModel;
use CompygoSocialFeed\View\FrontendHtml;
use CompygoSocialFeed\Core\Singleton;
use CompygoSocialFeed\Helper\HtmlHelper;
use CompygoSocialFeed\Helper\StyleHelper;
use CompygoSocialFeed\Api\Social\Instagram as InstagramApi;
use League\Plates\Engine as PlatesEngine;

class Compygo extends Singleton {
    public $initiated = false;

    public function init() {
        if (!$this->initiated) {
			$this->initHooks();
		}
    }

    public function initHooks() {
		$this->initiated = true;
        $this->initShortcode();
        $this->initAssets();
        $this->setupCron();
        add_filter('template_include', [$this, 'initIframe'], 90);
    }

    public function initShortcode()
    {
        add_shortcode(CGUSF_PLACEHOLDER, [$this, 'getHtmlFeed']);
    }

    public function initAssets()
    {
        add_action('wp_loaded', function () {
            self::registerStyles();
            self::registerScripts();
        });

        add_action('wp_enqueue_scripts', function () {
            wp_add_inline_script(CGUSF_PREFIX . 'script', self::addFooterScripts());
        });
    }

    static function enqueueStyles($feed)
    {
        if (self::isSlickAssets($feed))  {
            wp_enqueue_style(CGUSF_PREFIX . 'slick');
        }

        wp_enqueue_style('dashicons');
        wp_enqueue_style(CGUSF_PREFIX . 'style');

        if (self::isSlickAssets($feed))  {
            wp_enqueue_style(CGUSF_PREFIX . 'slick-theme');
        }
    }

    static function isMasonryAssets($feed)
    {
        return $feed['config']['feed_layout'] === 'masonry';
    }

    static function isSlickAssets($feed)
    {
        return ($feed['config']['feed_layout'] === 'grid' && $feed['config']['slider']) || $feed['config']['lightbox']['enable'];
    }

    static function registerStyles()
    {
        wp_register_style(
            CGUSF_PREFIX . 'style',
            trailingslashit(CGUSF_FF_URL) . 'pub/frontend.css',
            [],
            CGUSF_VERSION
        );
        wp_register_style(
            CGUSF_PREFIX . 'slick',
            trailingslashit(CGUSF_FF_URL) . 'pub/slick.css',
            [],
            CGUSF_VERSION
        );
        wp_register_style(
            CGUSF_PREFIX . 'slick-theme',
            trailingslashit(CGUSF_FF_URL) . 'pub/slick-theme.css',
            [],
            CGUSF_VERSION
        );
    }
    static function enqueueScripts($feed)
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script(CGUSF_PREFIX . 'script');

        if (self::isMasonryAssets($feed)) {
            wp_enqueue_script(CGUSF_PREFIX . 'masonry');
            wp_enqueue_script(CGUSF_PREFIX . 'image_loader');
        }

        if (self::isSlickAssets($feed))  {
            wp_enqueue_script(CGUSF_PREFIX . 'slick');
        }
    }
    static function registerScripts()
    {
        wp_register_script(
            CGUSF_PREFIX . 'script',
            trailingslashit(CGUSF_FF_URL) . 'pub/frontend.main.js',
            ['jquery'],
            CGUSF_VERSION,
            false 
        );
        wp_localize_script(
            CGUSF_PREFIX . 'script',
            CGUSF_PREFIX . 'ajax_obj',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce(CGUSF_AJAX_NONCE),
            ]
        );

        wp_register_script(
            CGUSF_PREFIX . 'masonry',
            trailingslashit(CGUSF_FF_URL) . 'pub/library/masonry.pkgd.min.js',
            ['jquery', CGUSF_PREFIX . 'script'],
            1.1,
            true
        );
        wp_register_script(
            CGUSF_PREFIX . 'image_loader',
            trailingslashit(CGUSF_FF_URL) . 'pub/library/image-loader.min.js',
            ['jquery', CGUSF_PREFIX . 'script', CGUSF_PREFIX . 'masonry'],
            1.1,
            true
        );
        wp_register_script(
            CGUSF_PREFIX . 'slick',
            trailingslashit(CGUSF_FF_URL) . 'pub/library/slick.min.js',
            [CGUSF_PREFIX . 'script'],
            1.1,
            true
        );
    }

    static function addFooterScripts() 
    {
        $templates = new PlatesEngine(CGUSF_FF_DIR . '/pub/js', 'js');
        return $templates->render('script-footer');
    }

    public function getHtmlFeed($atts = [], $content = null) 
    {
        if (isset($atts['id'])) {
            $feed = FeedModel::getFeed($atts['id']);

            if (!isset($feed['id'])) {
                return false;
            }

            // Add styles and scripts
            $this->renderAssets($feed);

            if (RequestHelper::isAdminPreview('get')) {
                $feed['config']['lightbox']['enable'] = false;
            }

            return FrontendHtml::getHtml($feed);
        }

        return false;
    }

    public function renderAssets($feed)
    {
        $this->enqueueScripts($feed);
        $this->enqueueStyles($feed);
        $customCss = StyleHelper::getCssVariable($feed);
        wp_add_inline_style(CGUSF_PREFIX . 'style', esc_html($customCss));
    }

    public function initIframe($template)
    {
        if (strpos($_SERVER['REQUEST_URI'], 'compygo-iframe') !== false) {
            status_header(200);

            $paramCgFeedId = RequestHelper::getRequestParam('cg_feed_id', 'key', 'get');
            $paramCgPreview = RequestHelper::getRequestParam('cg_preview', 'bool', 'get');

            if ($paramCgFeedId && $paramCgPreview) {
                $this->renderAssets(FeedModel::getFeed($paramCgFeedId));
                wp_add_inline_script(CGUSF_PREFIX . 'script', self::addFooterScripts());
                wp_enqueue_script(
                    CGUSF_PREFIX . 'smooth-scrollbar',
                    trailingslashit(CGUSF_FF_URL) . 'pub/library/smooth-scrollbar.js',
                    []
                );

                return  __DIR__ . './../src/web/templates/frontend/iframe.php';
            }

            wp_die();
        }

        return $template;
    }


    public function setupCron()
    {
        $hook = CGUSF_PREFIX . 'refreshAccessTokens';
        add_action($hook, [$this, 'refreshAccessTokens']);
    }

    public function refreshAccessTokens()
    {
        $sources = SourceModel::getSourceCollection();

        foreach ($sources as $source) {
            $accessToken = '';

            if ($source['vendor'] == 'instagram') {
                $accessToken = InstagramApi::refreshToken($source);
            }

            if ($accessToken) {
                $source['access_token'] = $accessToken;
                SourceModel::updateSource($source);
            }
        }
    }
}