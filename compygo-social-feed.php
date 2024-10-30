<?php
/**
 * Plugin Name: Social Feed for WordPress by CompyGo
 * Plugin URI: https://compygo.com/wordpress-social-feed-plugin
 * Description: Display completely customizable Facebook Feed on your WordPress website. Additionally the plugin supports Instagram photos and Youtube videos. It is completely customizable, responsive, a search engine friendly, fast and reliable due to built-in cache system. 
 * Version: 2.0.0
 * Author:  Compygo
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: cgusf
 * Domain Path: /languages
 */

require 'vendor/autoload.php';

use CompygoSocialFeed\Compygo;
use CompygoSocialFeed\CompygoAdmin;
use CompygoSocialFeed\CompygoApi;
use CompygoSocialFeed\Install;
use CompygoSocialFeed\Uninstall;

global $wpdb;

# Define plugin constants
define('CGUSF_FF_DIR', plugin_dir_path( __FILE__ ));
define('CGUSF_FF_URL', plugin_dir_url( __FILE__ ));
define('CGUSF_NAME', 'compygo-social-feed');
define('CGUSF_VERSION', '2.0.0');
define('CGUSF_PREFIX', 'cgusf_');
define('CGUSF_DB_PREFIX', $wpdb->prefix . CGUSF_PREFIX);
define('CGUSF_PLACEHOLDER', 'compygo-cgusf');
define('CGUSF_AJAX_NONCE', CGUSF_PREFIX .'feed_nonce');
define('CGUSF_AJAX_ADMIN_NONCE', CGUSF_PREFIX .'admin_feed_nonce');
define('CGUSF_DEFAULT_POST_LAYOUT', 'one_col_bi');
define('CGUSF_SF_API_COUNT', 200);
define('CGUSF_SF_LIMIT', 12);
define('CGUSF_DEFAULT_LOCALE', 'en_US');
define('CGUSF_DEFAULT_DATE_FORMAT', 'Y-m-d H:i:s');

#Setup Cron Interval
add_filter('cron_schedules', function ($schedules) {
    if(!isset($schedules["7d"])){
        $schedules["7d"] = array(
            'interval' => 60*60*24*7,
            'display' => __('Every 7 days'));
    }
    return $schedules;
});

# Install
register_deactivation_hook(__FILE__, [Uninstall::class, 'uninstall']);

function cgusf_update_db_check() {
    $dbVersion = get_option(CGUSF_PREFIX.'db_version');

    if (version_compare($dbVersion, CGUSF_VERSION) < 0 ) {
        Install::install($dbVersion);
    }
}
add_action('plugins_loaded', 'cgusf_update_db_check');

# Setup Api
$compygoApi = CompygoApi::getInstance();
$compygoApi->init();

# Setup Frontend
$compygo = Compygo::getInstance();
$compygo->init();

add_action('wp_loaded', function () {
    # Setup admin
    $isAjax = defined('DOING_AJAX') && DOING_AJAX;
    if (is_admin() && !$isAjax) {
        $compygoAdmin = CompygoAdmin::getInstance();
        $compygoAdmin->init();
    }
});
