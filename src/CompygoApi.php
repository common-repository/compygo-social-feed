<?php
namespace CompygoSocialFeed;

use CompygoSocialFeed\Api\Admin as AdminApi;
use CompygoSocialFeed\Api\Frontend as FrontendApi;
use CompygoSocialFeed\Core\Singleton;

class CompygoApi extends Singleton {
    public $initiated = false;
    public $apiFrontend;
    public $apiAdmin;

    public function __construct()
    {
        $this->apiAdmin = new AdminApi();
        $this->apiFrontend = new FrontendApi();
    }

    public function init() {
        if (!$this->initiated) {
			$this->initHooks();
		}
    }

    public function initHooks() {
		$this->initiated = true;

        $this->initApiActions();
        $this->initRestActions();
    }

    public function initRestActions() 
    {
        add_action('rest_api_init', function () {
            register_rest_route(CGUSF_NAME . '/v1', '/feed/', [
                'methods' => 'GET',
                'callback' => [$this->apiAdmin, 'restFetchFeedAjax'],
                'permission_callback' => function () {
                    return current_user_can('edit_others_posts');
                }
            ]);
        });
    }

    public function initApiActions()
    {
        // Posts ajax requests
        add_action('wp_ajax_nopriv_fetch_feed_posts', [$this->apiFrontend, 'fetchFeedPostsAjax']);
        add_action('wp_ajax_fetch_feed_posts', [$this->apiFrontend, 'fetchFeedPostsAjax']);
        add_action('wp_ajax_nopriv_fetch_feed_sources', [$this->apiFrontend, 'fetchFeedSourcesAjax']);
        add_action('wp_ajax_fetch_feed_sources', [$this->apiFrontend, 'fetchFeedSourcesAjax']);

        // Logs ajax requests
        add_action('wp_ajax_fetch_logs', [$this->apiAdmin, 'fetchLogsAjax']);

        // Options ajax requests
        add_action('wp_ajax_fetch_options', [$this->apiAdmin, 'fetchOptionsAjax']);
        add_action('wp_ajax_update_options', [$this->apiAdmin, 'updateOptionsAjax']);

        // Feed ajax requests
        add_action('wp_ajax_create_or_update_feed', [$this->apiAdmin, 'createOrUpdateFeedAjax']);
        add_action('wp_ajax_fetch_feed', [$this->apiAdmin, 'fetchFeedAjax']);
        add_action('wp_ajax_remove_feed', [$this->apiAdmin, 'removeFeedAjax']);
        add_action('wp_ajax_duplicate_feed', [$this->apiAdmin, 'duplicateFeedAjax']);
        add_action('wp_ajax_save_feed_logo', [$this->apiAdmin, 'saveFeedLogo']);
        add_action('wp_ajax_remove_feed_logo', [$this->apiAdmin, 'removeFeedLogo']);

        // Source ajax requests
        add_action('wp_ajax_fetch_source', [$this->apiAdmin, 'fetchSourceAjax']);
        add_action('wp_ajax_create_or_update_source', [$this->apiAdmin, 'createOrUpdateSourceAjax']);
        add_action('wp_ajax_remove_source', [$this->apiAdmin, 'removeSourceAjax']);

        // Cache requests
        add_action('wp_ajax_flush_cache', [$this->apiAdmin, 'flushCache']);

        // Statistics requests
        add_action('wp_ajax_stats', [$this->apiAdmin, 'sendStats']);
    }
}