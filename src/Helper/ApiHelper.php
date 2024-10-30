<?php
namespace CompygoSocialFeed\Helper;

use CompygoSocialFeed\Api\Social\Facebook as FacebookApi;
use CompygoSocialFeed\Api\Social\Instagram as InstagramApi;

class ApiHelper
{
    /**
     * @param $vendor
     * @return object
     */
    static function getApiByVendor($feed, $source)
    {
        $vendor = strtolower($source['vendor']);

        if ($vendor === 'instagram') {
            $api = new InstagramApi($feed, $source);
        } else {
            $api = new FacebookApi($feed, $source);
        }

        return $api;
    }
}