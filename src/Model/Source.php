<?php
namespace CompygoSocialFeed\Model;

use CompygoSocialFeed\Helper\Validator;
use CompygoSocialFeed\Helper\UploaderHelper;
use CompygoSocialFeed\Model\Post as PostModel;

class Source
{
    /**
     * @param $source
     * @param $api
     * @return false|int
     */
    static public function createSource($source, $api = null)
    {
        if (Validator::validateSource($source)) {
            global $wpdb;

            $pageData = [];
            if ($api) {
                $pageData = $api->getAccount($source);
            }

            if ($pageData) {
                $pageData['account']['picture'] = UploaderHelper::downloadImage(
                    CGUSF_PREFIX . 'account_logo_' . $source['id'] . '.jpg',
                    $pageData['account']['picture']);


                $item = [
                    'account_id' => $source['account_id'],
                    'account_type' => $source['account_type'],
                    'access_token' => $api->getAccessToken($source),
                    'vendor' => $source['vendor'],
                    'info' => wp_json_encode(array_merge(
                        $pageData,
                        []
                    ))
                ];
                $result = $wpdb->insert(CGUSF_DB_PREFIX . 'sources', $item);
    
                if ($result === false) {
                    Logger::addDbLog($result);
                } else {
                    return $wpdb->insert_id;
                }
            }
        }

        return false;
    }

    static public function updateSource($source)
    {
        global $wpdb;

        if (isset($source['id'])) {
            if (Validator::validateSource($source)) {
                $source['info'] = wp_json_encode($source['info']);
                $result = $wpdb->update(CGUSF_DB_PREFIX . 'sources', $source, ['id' => $source['id']]);

                if ($result === false) {
                    Logger::addDbLog($result);
                } else {
                    return $source['id'];
                }
            }
        }
    }

    /**
     * @param $id
     * @return array|false|mixed|object|void|null
     */
    static public function getSource($id)
    {
        global $wpdb;
        $results = null;

        if (is_array($id)) {
            $sql = "SELECT * FROM ". CGUSF_DB_PREFIX ."sources WHERE id IN (".implode(', ', array_fill(0, count($id), '%d')).")";
            $query = call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $id));

            $results = $wpdb->get_results($query, ARRAY_A);

            if ($results === false) {
                Logger::addDbLog($results);
            } else {
                foreach ($results as &$source) {
                    $source['info'] = json_decode($source['info'], true);
                }
            }
        } else {
            $results = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM ". CGUSF_DB_PREFIX ."sources WHERE id=%d", $id), 
                ARRAY_A
            );

            if (!$results) {
                Logger::addDbLog($results);
            } else {
                $results['info'] = json_decode($results['info'], true);
            }
        }

        return $results;
    }

    /**
     * @return array|object|null
     */
    static public function getSourceCollection($ids = [])
    {
        global $wpdb;

        if (empty($ids)) {
            $query = "SELECT * FROM ". CGUSF_DB_PREFIX."sources";
        } else {
            $ids = is_array($ids) ? $ids : [$ids];
            $inClausePlaceholder = implode(', ', array_fill(0, count($ids), '%s'));
            $query = $wpdb->prepare(
                "SELECT * FROM ".CGUSF_DB_PREFIX."sources WHERE id IN (". $inClausePlaceholder .")",
                $ids
            );
        }

        $results = $wpdb->get_results($query, ARRAY_A);
        $sources = [];

        if ($results === false) {
            Logger::addDbLog($results);
        } else {
            foreach ($results as $source) {
                $sources[$source['id']] =  $source;
                $sources[$source['id']]['info'] =  json_decode($source['info'], true);
            }
        }
        
        return $sources;
    }

    /**
     * @param $id
     * @return array[]
     */
    static public function removeSource($id)
    {       
        if (Validator::isNumber($id)) {
            global $wpdb;

            PostModel::deletePostsSourceId($id);
            $results = $wpdb->delete(CGUSF_DB_PREFIX . 'sources', ['id' => $id]);

            if ($results === false) {
                Logger::addDbLog($results);
            } else {
                return Validator::getMessage('success', 'Source have been removed');
            }
        }

        return Validator::getMessage('danger');
    }

    static public function existSource($accountId, $vendor, $type)
    {
        global $wpdb;

        if ($vendor == 'instagram') {
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM ".CGUSF_DB_PREFIX."sources"." WHERE account_id=%d AND account_type=%s",
                    $accountId, $type
                ),
                ARRAY_A
            );
        } else {
            $results = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM ".CGUSF_DB_PREFIX."sources"." WHERE account_id=%d", $accountId), ARRAY_A
            );
        }


        Logger::addDbLog($results);

        return (bool)$results;
    }
}