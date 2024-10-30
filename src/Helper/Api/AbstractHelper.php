<?php
namespace CompygoSocialFeed\Helper\Api;

abstract class AbstractHelper
{
    const VENDOR_FACEBOOK = 'facebook';
    const VENDOR_INSTAGRAM = 'instagram';
    const VENDOR_YOUTUBE = 'youtube';

    abstract static public function sanitizePosts($response, $source);
    abstract static public function sanitizeAccountData($value);
}