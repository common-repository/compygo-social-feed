<?php
namespace CompygoSocialFeed\Helper;

use Exception;

class FormatHelper
{
    /**
     * @param $datetime
     * @return string|void
     * @throws Exception
     */
    static public function convertTimeToAgo($datetime, $format = 'ago')
    {        
       // if ($format == 'ago') {
            $now = new \DateTime;
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
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }
    
            // TODO
        
            $string = array_slice($string, 0, 1);

            return $string ? implode(', ', $string) . __(' ago') : __('just now');
        // }
       
        // if ($format == 'dmt') {
        //     $date = new \DateTime($datetime);
        //     return $date->format('d M Y, H:i');
        // }
    } 
}