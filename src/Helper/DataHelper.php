<?php
namespace CompygoSocialFeed\Helper;

use DateTimeZone;
use CompygoSocialFeed\Helper\Api\FacebookHelper;

class DataHelper
{
    /**
     * @return false|string
     */
    static public function getTimezoneArray()
    {        
        return json_encode(DateTimeZone::listIdentifiers(DateTimeZone::ALL));
    }

    /**
     * @return false|string
     */
    static public function getLocalArray() 
    {
        return self::getTimezoneArray();
    }

    /**
     * @param $feed
     * @return string
     */
    static public function getPostLayoutPath($feed)
    {
        $layoutDir = 'post_layout/';
        $feedLayout = $feed['config']['post_layout'];


        if ($feed['config']['type'] == 'post') {
            return $layoutDir . $feedLayout;
        }

        if ($feed['config']['type'] == 'photo') {
            return 'post_layout/photo';
        }

        return '';
    }

    /**
     * @param $postUrl
     * @return string
     */
    static public function getFacebookShareUrl($postUrl)
    {
        return 'https://www.facebook.com/sharer/sharer.php?u=' . $postUrl;
    }

    /**
     * @param $label
     * @return string
     */
    static public function getString($label)
    {
        $strings = get_option(CGUSF_PREFIX .'strings');
        $string = $label;

        if (isset($strings[$label])) {
            $string = $strings[$label]['string'];
        }

        return $string;
    }

    /**
     * @param $url
     * @return string
     */
    static public function getDomainByUrl($url)
    {
        $domain = parse_url($url);
        return $domain['host'];
    }

    /**
     * @param $array
     * @return void
     */
    static public function prepareJson(&$array)
    {

        if (is_array($array)) {
            array_walk_recursive($array, function (&$item, $key) {
                if ($item === 'true') {
                    $item = true;
                } else if ($item === 'false') {
                    $item = false;
                }

                if (is_numeric($item)) {
                    $item = intval($item);
                }
            });
        }
    }

    static public function sanitizeAccountData($data, $feed)
    {
        $authorCustomName = $feed['config']['design']['author']['name'];
        $authorCustomPicture = $feed['config']['design']['logo']['file'];

        if ($data) {
            if ($authorCustomName) {
                $data['account']['name'] = $authorCustomName;
            }
            if ($authorCustomPicture) {
                $data['account']['picture_custom'] = $authorCustomPicture;
            }
        }

        return $data;
    }

    static public function getViewOnString($source)
    {
        if($source['vendor'] === 'instagram') {
            $string = self::getString('View on Instagram');
        } elseif($source['vendor'] === 'youtube') {
            $string = self::getString('View on Youtube');
        } else {
            $string = self::getString('View on Facebook');
        }

        return $string;
    }

    static public function getShareString($source)
    {
        $string = "";

        if($source['vendor'] === 'facebook') {
            $string = self::getString('Share');
        }

        return $string;
    }

    static public function getPostMessage($item, $source)
    {
        $itemMessage = '';

        if (isset($item['message'])) {
            $itemMessage = $item['message'];
        }

        if($source['vendor'] === 'facebook') {
            $postStatus = $item['status'];

            if (isset($item['story'])) {
                if ($postStatus != 'share') {
                    $itemMessage = $item['story'];
                }
            } else if (isset($item['atch_title'])) {
                if (!$itemMessage && $postStatus != 'share') {
                    $itemMessage = $item['atch_title'];
                }
            }
        }

        return $itemMessage;
    }
}