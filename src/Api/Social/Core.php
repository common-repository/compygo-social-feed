<?php
namespace CompygoSocialFeed\Api\Social;

use CompygoSocialFeed\Helper\HttpHelper;
use CompygoSocialFeed\Model\Cache;
use CompygoSocialFeed\Model\Logger;

abstract class Core
{
    public $feed;
    public $source;

    function __construct($feed, $source)
    {
        $this->source = $source;
        $this->feed = $feed;
    }

    public function setFeed($feed)
    {
        $this->feed = $feed;
    }

    public function fetch($function, ...$params)
    {
        if ($this->feed) {
            $feedId = $this->feed['id'];
            $paramSourceId = HttpHelper::getRequestParam('source_id', 'key');
            $isSourceIdPost = !empty($paramSourceId) && $paramSourceId !== '';
            $sourceId = $isSourceIdPost ? $paramSourceId : $this->feed['config']['source'];
            $paginationToken = $this->getPaginationToken();

            //Handle cache
            $cacheKey = Cache::getCacheKey($feedId, $sourceId, $paginationToken, $function);
            $cachedResponse = Cache::getByKey($cacheKey);
            $isExpired = false;

            if ($cachedResponse) {
                $isExpired = Cache::isExpired($cachedResponse);

                if (!$isExpired) {
                    return $cachedResponse['cache_value'];
                }
            }

            //Limit API calls
            if (get_option(COMPYGO_PREFIX.'api_call_count') >= COMPYGO_LIMIT) {
                return $cachedResponse['cache_value'] ?: '';
            }

            // Fetch data
            $result = $this->$function(...$params);

            // If cache is expired and API wrong
            if (empty($result)) {
                Cache::setByKey($cacheKey, $cachedResponse['cache_value'], $feedId, true);

                return $cachedResponse['cache_value'];
            } else {
                Cache::setByKey($cacheKey, $result, $feedId, $isExpired);
            }

            return $result;
        } else {
            return $this->$function(...$params);
        }
    }

    abstract protected function getPaginationToken();
    abstract protected function fetchAccountData($accountId, $accessToken);
    abstract protected function fetchAccountPosts($accountId, $accessToken);
}