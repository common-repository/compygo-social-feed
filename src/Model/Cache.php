<?php
namespace CompygoSocialFeed\Model;

use CompygoSocialFeed\Helper\Validator;
use CompygoSocialFeed\Model\Logger;
use Exception;

class Cache
{
    /**
     * @param $key
     * @return false|array|object|null|void
     */
    static public function getByKey($key)
    {
        global $wpdb;
        $result = null;

        if (Validator::isString($key)) {
            $result = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM ". COMPYGO_DB_PREFIX ."cache WHERE cache_key=%s", $key),
                ARRAY_A
            );

            if ($result === false) {
                Logger::addDBLog($result);
            } else {
                if (isset($result['cache_value'])) {
                    $result['cache_value'] = json_decode($result['cache_value'], true);
                }
            }
        }

        return $result;
    }

    /**
     * @param $key
     * @param $value
     * @param $feedId
     * @param $refresh
     * @return void
     */
    static public function setByKey($key, $value, $feedId, $refresh = false)
    {
        global $wpdb;
        $cache = [
            'feed_id' => $feedId,
            'cache_key' => $key,
            'cache_value' => wp_json_encode($value),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($refresh) {
            $result = $wpdb->update(COMPYGO_DB_PREFIX . 'cache', $cache, ['cache_key' => $key]);
        } else {
            $result = $wpdb->insert(COMPYGO_DB_PREFIX . 'cache', $cache);
        }

        Logger::addDbLog($result);
    }

    /**
     * @param $id
     * @return array[]
     */
    static public function removeCacheByFeedId($id)
    {       
        if (Validator::isNumber($id)) {
            global $wpdb;
            $results = $wpdb->delete(COMPYGO_DB_PREFIX . 'cache', ['feed_id' => $id]);

            if ($results === false) {
                Logger::addDbLog($results);
            } else {
                return Validator::getMessage('success', 'Cache have been flushed');
            }
        }

        return Validator::getMessage('danger');
    }

    /**
     * @param $cacheResponse
     * @return bool
     * @throws Exception
     */
    static public function isExpired($cacheResponse)
    {
        $cacheTime = (int)get_option(COMPYGO_PREFIX .'cache_time');
        $cacheUnit = get_option(COMPYGO_PREFIX .'cache_unit');
        $now = new \DateTime();
        $cacheDate = new \DateTime($cacheResponse['updated_at']);
        $diff = $now->diff($cacheDate);


        $diffMin = ($diff->d*24*60)+($diff->h*24)+$diff->i;
        $isExpired = false;

        if ($cacheUnit == 'm') {
            $isExpired = $diffMin > $cacheTime;
        }

        if ($cacheUnit == 'h') {
            $isExpired = $diffMin > ($cacheTime * 60);
        }

        if ($cacheUnit == 'd') {
            $isExpired = $diffMin > ($cacheTime * 24*60);
        }

        return $isExpired;
    }

    /**
     * @param $feedId
     * @param $sourceId
     * @param $paginationToken
     * @param $function
     * @return string
     */
    static public function getCacheKey($feedId, $sourceId, $paginationToken, $function)
    {
        $key = $feedId . '_' . $sourceId . '_' . $function;
        $key .= $paginationToken ? '_' . substr($paginationToken, 0, 25) : '';

        return $key;
    }
}