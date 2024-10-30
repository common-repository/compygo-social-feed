<?php
namespace CompygoSocialFeed\Helper\Api;

class CoreHelper
{
    /**
     * @param $post
     * @return int
     */
    static public function commentCount($post)
    {
        return $post['comments'] ? count($post['comments']['data']) : 0;
    }

    /**
     * @param $post
     * @return int
     */
    static public function likeCount($post)
    {
        return $post['likes'] ? count($post['likes']['data']) : 0;
    }

    /**
     * @param $post
     * @return int
     */
    static public function shareCount($post)
    {
        $count = isset($post['shares']) ? $post['shares']['count'] : 0;
        unset($post['shares']['count']);

        return $count;
    }
}