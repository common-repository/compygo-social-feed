<?php
namespace CompygoSocialFeed\Helper\Api;

use CompygoSocialFeed\Api\Social\Instagram;
use CompygoSocialFeed\Helper\DataHelper;

class InstagramHelper extends AbstractHelper
{
    public const MEDIA_TYPE_IMAGE = "image";
    public const MEDIA_TYPE_CAROUSEL_ALBUM = "carousel_album";
    public const MEDIA_TYPE_VIDEO = "video";
    public const ACCOUNT_TYPE_BUSINESS = "business";

    /**
     * @param $posts
     * @return array
     */
    static public function sanitizePosts($response, $source)
    {
        $result = [];
        $key = 0;

        foreach($response['data'] as $post) {
            $newPost = [
                'id' => $post['id'],
                'source_id' => $source['id'],
                'status' => 'post',
                'vendor' => self::VENDOR_INSTAGRAM,
                'message' => isset($post['caption']) ? $post['caption'] : '',
                'date' => date(CGUSF_DEFAULT_DATE_FORMAT, strtotime($post['timestamp'])),
                'url' => isset($post['permalink']) ? $post['permalink'] : '',
                'like_count' => isset($post['like_count']) ? $post['like_count'] : 0,
                'comment_count' =>  isset($post['comments_count']) ? $post['comments_count'] : 0,
                'atch_type' => isset($post['media_type']) ? strtolower($post['media_type']) : '',
                'atch_image' => isset($post['thumbnail_url']) ? $post['thumbnail_url'] : $post['media_url'],
            ];

            if ( isset($post['atch_type']) && strtolower($newPost['atch_type']) === InstagramHelper::MEDIA_TYPE_VIDEO) {
                $newPost['atch_video'] = isset($post['media_url']) ? $post['media_url'] : '';
            }

            if(isset($post['children'])) {
                $newPost['children'] = [];
                
                foreach($post['children']['data'] as $index => $child) {
                    $newPost['children'][$index] = [];
                    $newPost['children'][$index]['media_url'] = $child['media_url'];
                }
            }

            $newPost['paging'] = $response['paging']['cursors']['after'];

            $result[$key] = $newPost;
            $key++;
        }

        return $result;
    }

    /**
     * @param $value
     * @return mixed
     */
    static public function sanitizeAccountData($value) 
    {
        $value['name'] = $value['username'];
        $value['link'] = Instagram::PROFILE_URL . $value['username'];
        $value['picture'] = isset($value['profile_picture_url']) ? $value['profile_picture_url'] : '';
        unset($value['username']);
        unset($value['profile_picture_url']);

        return $value;
    }
}