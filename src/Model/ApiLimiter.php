<?php
namespace CompygoSocialFeed\Model;

use CompygoSocialFeed\Helper\Validator;
use CompygoSocialFeed\Model\Logger;
use Exception;

class ApiLimiter
{
    static public function upTick()
    {
        if (get_transient('cgusf_api_call_tick')) {
            $apiCallCount = (int)get_option(CGUSF_PREFIX.'api_call_count') + 1;
            update_option(CGUSF_PREFIX.'api_call_count',  $apiCallCount);
        } else {
            set_transient('cgusf_api_call_tick', true, 3600);
            update_option(CGUSF_PREFIX.'api_call_count',  1);
        }
    }
}