<?php
namespace CompygoSocialFeed;

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
use CompygoSocialFeed\Core\Singleton;
use CompygoSocialFeed\Model\Stats as StatsModel;

class Uninstall extends Singleton 
{
    static public function uninstall() 
    {
        global $wpdb;

        $wpdb->query('DROP TABLE IF EXISTS '.CGUSF_DB_PREFIX.'posts');
        $wpdb->query('DROP TABLE IF EXISTS '.CGUSF_DB_PREFIX.'feeds');
        $wpdb->query('DROP TABLE IF EXISTS '.CGUSF_DB_PREFIX.'sources');

        delete_option(CGUSF_PREFIX.'db_version');

        wp_clear_scheduled_hook(CGUSF_PREFIX . 'refreshAccessTokens');
    }
}