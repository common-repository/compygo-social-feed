<?php
namespace CompygoSocialFeed;

use CompygoSocialFeed\View\AdminHtml;
use CompygoSocialFeed\Core\Singleton;

class CompygoAdmin extends Singleton {
    public $initiated = false;
    public $adminHtml;

    public function __construct()
    {
        $this->adminHtml = new AdminHtml();
    }

    public function init() {            

        if (!$this->initiated) {
            $this->initHooks();
            add_action('init', function() {
                load_plugin_textdomain('cgusf', false, 'compygo-social-feed/languages');
            });
            add_action('enqueue_block_editor_assets', [$this, 'initGutenbergBlock']);
		}
    }

    public function initHooks() 
    {
		$this->initiated = true;
        $this->initAssets();
        $this->initMenu();
        $this->initPluginListMenu();

    }

    public function initGutenbergBlock()
    {
        $blockname = 'compygo-social-feed/social-feed-block';

        wp_enqueue_script(
            $blockname,
            trailingslashit(CGUSF_FF_URL) . 'src/web/gutenberg/block/social-feed.js',
            ['wp-blocks', 'wp-editor', 'wp-i18n'],
            '1.0.0'
        );

        add_action('init', function() {
            register_block_type($blockname, [
                'api_version' => 1,
                'editor_script' => $blockname,
                'render_callback' => [$this, 'renderCallbackGutenberBlock']
            ]);
        });
    }

    public function renderCallbackGutenberBlock()
    {
        return 123;
    }

    public function initMenu()
    {
		add_action('admin_menu', function() {
            add_menu_page(
                'CG Social Feed',
                'CG Social Feed',
                'manage_options',
                CGUSF_PREFIX.'feeds',
                [$this->adminHtml, 'getFeedsHtml'],
                CGUSF_FF_URL . 'pub/img/menu_logo.svg'
            );
    
            add_submenu_page( 
                CGUSF_PREFIX.'feeds', 
                'CG Social Feed - ' . __('Feeds', 'cgusf'), 
                __('Feeds', 'cgusf'), 
                'manage_options', 
                CGUSF_PREFIX.'feeds', 
                [$this->adminHtml, 'getFeedsHtml']
            );
    
            add_submenu_page( 
                CGUSF_PREFIX.'feeds', 
                'CG Social Feed - ' . __('Settings', 'cgusf'), 
                __('Settings', 'cgusf'), 
                'manage_options', 
                CGUSF_PREFIX.'settings', 
                [$this->adminHtml, 'getSettingsHtml']
            );
        });
    }

    public function initPluginListMenu()
    {
        add_filter('plugin_action_links_compygo-social-feed/compygo-social-feed.php', function ($links) {
            $url = esc_url(add_query_arg(
                'page',
                'cgusf_settings',
                get_admin_url() . 'admin.php'
            ));

            $settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
            $pro_link = "<a target='_blank' style='font-weight: bold; color: #2e9c0a' href='https://www.buymeacoffee.com/compygo'>"
                . __( 'Donate' )
                . '</a>';

            array_unshift( $links, $settings_link);
            array_unshift($links, $pro_link);

            return $links;
        });
    }

    public function initAssets() 
    {
        add_action('current_screen', function() {
            wp_enqueue_style(
                CGUSF_PREFIX . 'admin_style_common',
                trailingslashit(CGUSF_FF_URL) . 'pub/admin_common.css',
                [],
                CGUSF_VERSION
            );
            if (strpos(get_current_screen()->id, CGUSF_PREFIX) !== false) {
                add_action('admin_enqueue_scripts', function() {
                    wp_enqueue_style(
                        CGUSF_PREFIX . 'admin_style',
                        trailingslashit(CGUSF_FF_URL) . 'pub/admin.css',
                        [],
                        CGUSF_VERSION
                    );
                    wp_enqueue_script(
                        CGUSF_PREFIX.'admin_script',
                        trailingslashit(CGUSF_FF_URL) . 'pub/admin.main.js',
                        ['jquery'],
                        CGUSF_VERSION,
                        true
                    );
                    wp_localize_script(
                        CGUSF_PREFIX.'admin_script',
                        CGUSF_PREFIX.'admin_ajax_obj',
                        [
                            'ajax_url' => admin_url('admin-ajax.php'),
                            'nonce' => wp_create_nonce(CGUSF_AJAX_ADMIN_NONCE),
                        ]
                    );
                });
            }
        });
    }
}