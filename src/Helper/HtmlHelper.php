<?php
namespace CompygoSocialFeed\Helper;

use CompygoSocialFeed\Api\Social\Facebook;
use CompygoSocialFeed\Helper\Api\FacebookHelper;

class HtmlHelper
{
    const ALLOWED_HTML = [
        "a" => [
            "href" => [],
            "target" => [],
        ]
    ];

    /**
     * @param $feed
     * @return string
     */
    static function getFeedLoadingType($feed)
    {
        $data = '';

        if ($feed['config']['loading']['enable']) {
            $data = $feed['config']['loading']['type'];
        }

        return $data;
    }

    static function getWrapperClass($feed)
    {
        $class = [];

        if ($feed['config']['loading']['enable']) {
            if (!self::isMasonry($feed)) {
                $class[] = $feed['config']['loading']['animation'];
            }
        }

        return implode(' ', $class);
    }

    /**
     * @param $feed
     * @return string
     */
    static function getItemClass($item, $source)
    {
        $class = [];

        if ($source['vendor'] == 'facebook') {
            $class[] = $item['status'];
            $class[] = 'cgusf__item--fb';
        } else if($source['vendor'] == 'instagram') {
            $class[] = 'cgusf__item--inst';
        }

        return empty($class) ? '' : ' ' .  implode(' ', $class);
    }

    /**
     * @param $feed
     * @return bool
     */
    static function isMasonry($feed)
    {
        return $feed['config']['feed_layout'] === 'masonry';
    }

    /**
     * @param $feed
     * @return string
     */
    static function getListClass($feed)
    {
        $class = [];
        $class[] = $feed['config']['type'];
        $confLayout = $feed['config']['design']['layout']['col'];
        if ($feed['config']['feed_layout'] != 'list') {
            $class[] = 'm-col-' . $confLayout['m'];
            $class[] = 't-col-' . $confLayout['t'];
            $class[] = 'd-col-' . $confLayout['d'];
        }

        $class[] = $feed['config']['feed_layout'];

        if ($feed['config']['type'] === 'post') {
            $class[] = $feed['config']['post_layout'];
        }

        if ($feed['config']['slider'] === true) {
            $class[] = 'slider';
        }

        $post = $feed['config']['design']['post'];

        $class[] = $post['border'] ? 'border' : ' no-border';
        $class[] = $post['shadow'] ? 'shadow' : ' no-shadow';
        $class[] = !isset($post['image_gap']) || $post['image_gap'] ? 'image_gap' : '';

       // $class[] = $feed['config']['design']['post']['line'] ? 'line' : '';

        return implode(' ', $class);
    }

    static public function parseText($value)
    {
        return $value;
    }

    static public function convertTime($datetime, $format = 'ago')
    {
        if ($format == 'ago') {
            $now = new \DateTime('now');
            $ago = new \DateTime($datetime);
            $diff = $now->diff($ago);

            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;

            $string = array(
                'y' => __('year'),
                'm' => __('month'),
                'w' => __('week'),
                'd' => __('day'),
                'h' => __('hour'),
                'i' => __('minute'),
                's' => __('second'),
            );
            foreach ($string as $index => &$word) {
                if ($diff->$index) {
                    $word = $diff->$index . ' ' . $word . ($diff->$index > 1 ? 's' : '');
                } else {
                    unset($string[$index]);
                }
            }

            if (isset($string['y'])) {
                $string = array_slice($string, 0, 2);
            } else {
                $string = array_slice($string, 0, 1);
            }

            return $string ? implode(', ', $string) . __(' ago') : __('just now');
        }

        if ($format == 'dmt') {
             $date = new \DateTime($datetime);
             return $date->format('d M Y, H:i');
        }
    }
}