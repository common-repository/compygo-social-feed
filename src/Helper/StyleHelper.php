<?php
namespace CompygoSocialFeed\Helper;

use CompygoSocialFeed\Model\Feed;

class StyleHelper
{
    /**
     * @param $feed
     * @return string
     */
    static public function getCssVariable($feed)
    {
        $post = $feed['config']['design']['post'];

        $rule = '.cgusf__wrapper {';
        $rule .= '--cgusf-main-post-padding: ' . (isset($post['padding']) ? $post['padding'] : Feed::DEFAULT_PADDING) . 'px;';
        $rule .= '--cgusf-font-size-title: ' . $feed['config']['size']['title'] . 'px;';
        $rule .= '--cgusf-font-size-date: ' . $feed['config']['size']['date'] . 'px;';
        $rule .= '--cgusf-font-size-text: ' . $feed['config']['size']['text'] . 'px;';
        $rule .= '--cgusf-font-size-action: ' . $feed['config']['size']['action'] . 'px;';
        $rule .= '--cgusf-color-bg: ' . self::hex2rgba($feed['config']['color']['bg']) . ';';
        $rule .= '--cgusf-color-bg2: ' . self::hex2rgba($feed['config']['color']['bg_2']) . ';';
        $rule .= '--cgusf-color-bg3: ' . self::hex2rgba($feed['config']['color']['bg_3']) . ';';
        $rule .= '--cgusf-color-title: ' . self::hex2rgba($feed['config']['color']['title']) . ';';
        $rule .= '--cgusf-color-text: ' . self::hex2rgba($feed['config']['color']['text']) . ';';
        $rule .= '--cgusf-color-text2: ' . self::hex2rgba($feed['config']['color']['text_2']) . ';';
        $rule .= '--cgusf-color-link: ' . self::hex2rgba($feed['config']['color']['link']) . ';';
        $rule .= '--cgusf-color-border: ' . self::hex2rgba($feed['config']['color']['border']) . ';';
        $rule .= '--cgusf-border-radius: ' . $post['border_radius'] . 'px;';
        $rule .= '}';

        return $rule;
    }

    static public function hex2rgba($color) 
    {
        $default = 'rgb(0,0,0)';
    
        // Ignore "#" if provided
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }
    
        // Check if color has 6 or 3 characters, get values
        if (strlen($color) == 8) {
            $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5], $color[6] . $color[7] );
        } elseif ( strlen( $color ) == 6 ) {
            $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
            $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
            return $default;
        }
    
        $rgb =  array_map( 'hexdec', $hex );

        if (strlen($color) == 8) {
            $output = 'rgba(' . implode( ",", array_slice($rgb, 0, 3) ) . ', ' . (round($rgb[3]/255, 2)) . ')';
        } else {
            $output = 'rgb(' . implode( ",", $rgb ) . ')';
        }

        return $output;
    }
}