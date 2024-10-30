<?php
namespace CompygoSocialFeed\Model;

use CompygoSocialFeed\Api\Social\Facebook as FacebookApi;
use CompygoSocialFeed\Api\Social\Instagram as InstagramApi;
use CompygoSocialFeed\Api\Social\Youtube as YoutubeApi;

class Api
{
    static function getApiByVendor($feed, $source)
    {
        $vendor = strtolower($source['vendor']);

        if ($vendor === 'youtube') {
            $api = new YoutubeApi($feed, $source);
        } elseif ($vendor === 'instagram') {
            $api = new InstagramApi($feed, $source);
        } else {
            $api = new FacebookApi($feed, $source);
        }

        return $api;
    }
}