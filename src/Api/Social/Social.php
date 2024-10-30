<?php
namespace CompygoSocialFeed\Api\Social;

use CompygoSocialFeed\Helper\RequestHelper;
use CompygoSocialFeed\Model\Logger;
use CompygoSocialFeed\Model\Post;
use CompygoSocialFeed\Model\Source;
use Exception;

abstract class Social
{
    public $feed;
    public $source;
    public $paginationToken = '';

    function __construct($feed, $source)
    {
        $this->source = $source;
        $this->feed = $feed;
    }

    public function getAccount($source)
    {
        $oldData = Source::getSource($source['id']);

        // Return data if not expired
        if ($oldData && !$this->isExpired($oldData)) {
            return $this->sanitizeAccount($oldData);
        }

        // Limit api calls to source endpoint
        if ($oldData && $this->isLimitApiCall()) {
            Logger::addLog(Logger::ERROR_API, 'API limit is reached');
            return $this->sanitizeAccount($oldData);
        }

        // Api call to source endpoint
        $newData = $this->fetchAccount($source);

        if (empty($newData)) {
            return $this->sanitizeAccount($oldData);
        } else {
            if ($oldData) {
                $oldData['info'] = $newData;
                Source::updateSource($oldData);
            }
        }

        return $newData;
    }

    public function getPosts($source)
    {
        $lastPostDate = $this->getPaginationTokenParam();
        $oldData = Post::getPostCollection($source['id'], CGUSF_SF_LIMIT, $lastPostDate);

        // Return data if not expired
        $count = count($oldData);
        $isExpired = $count === 0 ? true : $this->isExpired($oldData[0]);

        if ($count == CGUSF_SF_LIMIT && !$isExpired) {
            return $this->sanitizePosts($oldData);
        }

        // Limit api calls to source endpoint
        if ($this->isLimitApiCall()) {
            Logger::addLog(Logger::ERROR_API, 'API limit is reached');
            return $this->sanitizePosts($oldData);
        }

        // Remove all post if expired
        if ($count > 0 && $isExpired) {
            Post::deletePostsSourceId($source['id']);
        } else {
            // Set paging token from old data
            // If not paging token is ''
            $this->setPaginationToken($oldData, $source);
        }

        // Api call to source endpoint
        if ($this->paginationToken !== -1) {
            $newData = $this->fetchPosts($source);

            // Remove paging from the last post
            if ($newData && count($newData) < CGUSF_SF_LIMIT) {
                $newData[count($newData)-1]['paging'] = -1;
            }
        }

        if (empty($newData)) {
            if ($oldData) {
                $lastPost = end($oldData);
            } else {
                $lastPost = Post::getLastPost($source['id']);
            }

            if ($lastPost) {
                $lastPost['post_value']['paging'] = -1;
                $lastPost['post_value'] = wp_json_encode($lastPost['post_value'], JSON_UNESCAPED_UNICODE);
                Post::updatePost($lastPost);
            }

            return $oldData ? $this->sanitizePosts($oldData) : [];
        } else {
            Post::savePosts($source['id'], $newData);
        }

        if ($count < CGUSF_SF_LIMIT && !$isExpired) {
            $newData = array_merge($this->sanitizePosts($oldData), $newData);
        }

        return $newData;
    }

    /**
     * @param $oldData
     * @param $source
     * @return void
     */
    protected function setPaginationToken($oldData, $source)
    {
        if (count($oldData) == 0) {
            $lastPost = Post::getLastPost($source['id']);
            $this->paginationToken = empty($lastPost) ? '' : $lastPost[0]['post_value']['paging'];
        } else {
            $this->paginationToken = end($oldData)['post_value']['paging'];
        }
    }

    /**
     * @return array|string
     */
    protected function getPaginationTokenParam()
    {
        return RequestHelper::getRequestParam('pagination_token','string');
    }

    /**
     * @param $posts
     * @return array
     */
    protected function sanitizePosts($posts)
    {
        $sanitizedPosts = [];

        if (!empty($posts)) {
            foreach ($posts as $key => $post) {
                $sanitizedPosts[$key] = $post['post_value'];
            }
        }

        return $sanitizedPosts;
    }

    /**
     * @param $data
     * @return array|mixed
     */
    protected function sanitizeAccount($data)
    {
        if (empty($data)) {
            return [];
        }

        return $data['info'];
    }

    /**
     * @return bool
     */
    protected function isLimitApiCall()
    {
        return get_option(CGUSF_PREFIX.'api_call_count') >= CGUSF_SF_API_COUNT;
    }

    /**
     * @param $item
     * @return bool
     * @throws Exception
     */
    static public function isExpired($item)
    {
        $isExpired = true;

        if ($item['updated_at']) {
            $cacheTime = (int)get_option(CGUSF_PREFIX .'cache_time');
            $cacheUnit = get_option(CGUSF_PREFIX .'cache_unit');
            $now = new \DateTime();
            $cacheDate = new \DateTime(
                $item['updated_at']
            );
            $diff = $now->diff($cacheDate);


            $diffMin = ($diff->m*24*60*30)+($diff->d*24*60)+($diff->h*24)+$diff->i;

            if ($cacheUnit == 'm') {
                $isExpired = $diffMin > $cacheTime;
            }

            if ($cacheUnit == 'h') {
                $isExpired = $diffMin > ($cacheTime * 60);
            }

            if ($cacheUnit == 'd') {
                $isExpired = $diffMin > ($cacheTime * 24*60);
            }
        }

        return $isExpired;
    }

    public function getAccessToken($source)
    {
        return $source['access_token'];
    }

    abstract protected function fetchAccount($source);
    abstract protected function fetchPosts($source);
}