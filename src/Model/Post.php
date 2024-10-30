<?php
namespace CompygoSocialFeed\Model;

use CompygoSocialFeed\Helper\DataHelper;
use CompygoSocialFeed\Helper\Validator;
use CompygoSocialFeed\Model\Logger;
use Exception;

class Post
{
    static public function getPostCollection($sourceIds = [], $limit, $date = null)
    {
        global $wpdb;

        $date = $date ?: date(CGUSF_DEFAULT_DATE_FORMAT);

        if (empty($sourceIds)) {
            $query = $wpdb->prepare(
                "SELECT id, source_id, post_value, post_date, updated_at FROM "
                    . CGUSF_DB_PREFIX."posts LIMIT %d",
                $limit
            );
        } elseif (!is_array($sourceIds)) {
            $query = $wpdb->prepare(
                "SELECT id, source_id, post_value, post_date, updated_at FROM "
                    . CGUSF_DB_PREFIX."posts WHERE source_id=%d AND post_date < %s LIMIT %d",
                $sourceIds,
                $date,
                $limit
            );
        } else {
            $inClausePlaceholder = implode(', ', array_fill(0, count($sourceIds), '%s'));
            $query = $wpdb->prepare(
                "SELECT id, source_id, post_value, post_date FROM "
                    .CGUSF_DB_PREFIX."sources WHERE source_id IN (". $inClausePlaceholder .") LIMIT %d",
                $sourceIds, $limit
            );
        }

        return self::getPostResults($query);
    }

    static public function getLastPost($sourceId)
    {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT source_id, post_value, post_date, updated_at FROM "
            . CGUSF_DB_PREFIX."posts WHERE source_id=%d ORDER BY id DESC LIMIT 1",
            $sourceId
        );

        return self::getPostResults($query);
    }

    static public function getPostResults($query)
    {
        global $wpdb;
        $results = $wpdb->get_results($query, ARRAY_A);
        Logger::addDbLog($results);

        if ($results) {
            foreach ($results as &$result) {
                $result['post_value'] = json_decode($result['post_value'], true);
            }

            return $results;
        }

        return [];
    }

    static public function savePosts($sourceId, $value)
    {
        global $wpdb;
        $multipleInsertQuery= 'INSERT INTO '. CGUSF_DB_PREFIX . 'posts (source_id, post_value, post_date, updated_at) VALUES ';

        foreach ($value as $item) {
            $multipleInsertQuery .= $wpdb->prepare(
                "(%d, %s, %s, %s),",
                $sourceId,
                wp_json_encode($item, JSON_UNESCAPED_UNICODE),
                date(CGUSF_DEFAULT_DATE_FORMAT, strtotime($item['date'])),
                date(CGUSF_DEFAULT_DATE_FORMAT)
            );
        }

        $multipleInsertQuery = rtrim($multipleInsertQuery,',') . ';';
        $result = $wpdb->query($multipleInsertQuery);

        Logger::addDbLog($result);
    }

    static public function updatePost($post)
    {
        global $wpdb;

        $result = $wpdb->update(CGUSF_DB_PREFIX . 'posts', $post, ['id' => $post['id']]);

        if ($result === false) {
            Logger::addDbLog($result);
        } else {
            return $post['id'];
        }
    }

    static public function deletePostsSourceId($sourceId)
    {
        if (Validator::isNumber($sourceId)) {
            global $wpdb;
            $results = $wpdb->delete(CGUSF_DB_PREFIX . 'posts', ['source_id' => $sourceId]);

            if ($results === false) {
                Logger::addDbLog($results);
            } else {
                return Validator::getMessage('success', 'Cache have been flushed');
            }
        }

        return Validator::getMessage('danger');
    }
}