<?php
namespace CompygoSocialFeed\Model;

use CompygoSocialFeed\Helper\RequestHelper;

class Stats
{
    const API_ENDPOINT_STATS = 'https://compygo.com/rest/V1/service/stats';

    static public function setStats($event)
    {
        $stats = get_option(CGUSF_PREFIX.'stats');
        $stats = array_merge($stats, [
            'scope' => CGUSF_NAME,
            'event' => $event,
            'source' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME']
        ]);

        update_option(CGUSF_PREFIX.'stats', $stats);

    }

    static public function sendStats($data)
    {
        $result = RequestHelper::requestData(
            self::API_ENDPOINT_STATS, 
            ['json' => json_encode($data)]
        );

        if (isset($result) && isset($result[0]) && $result[0] == 'success') {
            update_option(CGUSF_PREFIX.'stats', []);
        }

        return $result;
    }
}