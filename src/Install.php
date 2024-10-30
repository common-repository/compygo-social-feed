<?php
namespace CompygoSocialFeed;

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
use CompygoSocialFeed\Core\Singleton;
use CompygoSocialFeed\Model\Stats as StatsModel;
use CompygoSocialFeed\Model\Options as OptionsModel;

class Install extends Singleton
{
    static public function install($version)
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        // Initial version
        if ($version === false) {
            if(!self::isTable(CGUSF_DB_PREFIX.'sources')) {
                self::createSourceTable(CGUSF_DB_PREFIX . 'sources', $charsetCollate);
            }

            if(!self::isTable(CGUSF_DB_PREFIX.'feeds')) {
                self::createFeedsTable(CGUSF_DB_PREFIX . 'feeds', $charsetCollate);
            }

            self::setPluginOptions();
            update_option(CGUSF_PREFIX.'db_version', '1.0.0');
        }

        // 1.0.1 version
        if (version_compare($version, '1.0.1' ) < 0 ) {
            if(!self::isTable(CGUSF_DB_PREFIX.'posts')) {
                self::createPostTable(CGUSF_DB_PREFIX . 'posts', $charsetCollate);
            }

            update_option(CGUSF_PREFIX.'db_version', '1.0.1');
        }

        // 1.1.0 version
        if (version_compare($version, '1.1.0' ) < 0 ) {
            self::removeTable(CGUSF_DB_PREFIX . 'cache');
            update_option(CGUSF_PREFIX.'db_version', '1.1.0');
        }

        self::installCron();

        //Send stats to Compygo
        StatsModel::setStats('install');
    }

    private static function createSourceTable($tableName, $charsetCollate)
    {
        $sql = "CREATE TABLE {$tableName} (
            id bigint(5) unsigned NOT NULL AUTO_INCREMENT,
            account_id text NOT NULL,
            vendor text(3) NOT NULL,
            account_type text NOT NULL,
            access_token text NOT NULL,
            info text NOT NULL,
            PRIMARY KEY (id)
        ) {$charsetCollate}";

        dbDelta($sql);
    }

    private static function removeTable($tableName)
    {
        global $wpdb;
        $sql = "DROP TABLE IF EXISTS $tableName";
        $wpdb->query($sql);
    }

    private static function createFeedsTable($tableName, $charsetCollate)
    {
        $sql = "CREATE TABLE {$tableName} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name text NOT NULL,
            config text NOT NULL,
            PRIMARY KEY (id)
        ) {$charsetCollate}";

        dbDelta($sql);
    }

    private static function createPostTable($tableName, $charsetCollate)
    {
        global $wpdb;
        $sourceTable = CGUSF_DB_PREFIX . 'sources';

        $sql = "CREATE TABLE {$tableName} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            source_id bigint(20) unsigned NOT NULL,
            post_value mediumtext NOT NULL,
            post_date datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (source_id) REFERENCES ".$sourceTable."(id)
        ) {$charsetCollate}";

        dbDelta($sql);
    }

    private static function isTable($tableName)
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $tableName)) == $tableName;
    }

    /**
     * @return void
     */
    private static function setPluginOptions()
    {
        add_option(CGUSF_PREFIX.'db_version', '1.0.0');
        add_option(CGUSF_PREFIX.'strings', [
            'See More' => [
                'string' => 'See More'
            ],
            'See Less' => [
                'string' => 'See Less'
            ],
            'Share' => [
                'string' => 'Share'
            ],
            'View on Facebook' => [
                'string' => 'View'
            ],
            'View on Instagram' => [
                'string' => 'View'
            ],
            'View on Youtube' => [
                'string' => 'View'
            ],
            'Load More' => [
                'string' => 'Load More'
            ]
        ]);
        add_option(CGUSF_PREFIX.'cache_time', '1');
        add_option(CGUSF_PREFIX.'cache_unit', 'h');
        add_option(CGUSF_PREFIX.'custom_css', '');
        add_option(CGUSF_PREFIX.'license_key', '');
        add_option(CGUSF_PREFIX.'api_call_count', 0);
        add_option(CGUSF_PREFIX.'facebook_app_id', '');
        add_option(CGUSF_PREFIX.'facebook_app_secret', '');
        add_option(CGUSF_PREFIX.'youtube_api_key', '');
        add_option(
            CGUSF_PREFIX.'locale',
            in_array(get_locale(), array_keys(OptionsModel::getLocales())) ? get_locale() : CGUSF_DEFAULT_LOCALE
        );
        add_option(CGUSF_PREFIX.'stats', []);
        add_option(CGUSF_PREFIX.'logs', []);
    }



    public static function installCron()
    {
        $hook = CGUSF_PREFIX. 'refreshAccessTokens';

        wp_clear_scheduled_hook($hook);
        wp_schedule_event(time(), '7d', $hook, []);
    }
}