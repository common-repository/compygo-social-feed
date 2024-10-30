<?php
namespace CompygoSocialFeed\Helper\Api;

use CompygoSocialFeed\Api\Social\Facebook;
use CompygoSocialFeed\Helper\DataHelper;
use CompygoSocialFeed\Helper\Api\AbstractHelper;

class YoutubeHelper extends AbstractHelper
{
    /**
     * @param $response
     * @param $source
     * @return array
     */
    static public function sanitizePosts($response, $source)
    {
        $result = [];
        $key = 0;

        foreach($response['items'] as $post) {
            $newPost = [
                'id' => $post['id'],
                'source_id' => $source['id'],
                'vendor' => self::VENDOR_YOUTUBE,
                'message' => isset($post['snippet']['description']) ? $post['snippet']['description'] : '',
                'date' => date(CGUSF_DEFAULT_DATE_FORMAT, strtotime($post['snippet']['publishedAt'])),
                'url' => 'https://www.youtube.com/watch?v='.$post['id'],
                'view_count' => $post['statistics']['viewCount'],
                'like_count' => $post['statistics']['likeCount'],
                'comment_count' => $post['statistics']['commentCount'],
                'atch_image' => isset($post['snippet']['thumbnails']) ? $post['snippet']['thumbnails']['high']['url'] : '',
                'atch_video' => 'https://www.youtube.com/embed/' . $post['id']
            ];
            

            $newPost['paging'] = $response['nextPageToken'];

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
        $result['id'] = $value['id'];

        if ($value['snippet']['thumbnails']['medium']) {
            $result['picture'] = $value['snippet']['thumbnails']['medium']['url'];
        }

        if ($value['snippet']['title']) {
            $result['name'] = $value['snippet']['title'];
            $result['link'] = 'https://www.youtube.com/channel' . $value['snippet']['title'];
        }

        return $result;
    }
}