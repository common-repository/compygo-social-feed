<?php
namespace CompygoSocialFeed\Api\Social;

use CompygoSocialFeed\Helper\RequestHelper;
use CompygoSocialFeed\Model\Source as SourceModel;
use CompygoSocialFeed\Helper\Api\InstagramHelper;

class Instagram extends Social
{
    const API_URL = 'https://graph.instagram.com/';
    const API_FB_URL = 'https://graph.facebook.com/';
    const PROFILE_URL = 'https://www.instagram.com/';

    public function refreshToken($source)
    {
        $accessToken = $this->getAccessToken($source);
        $response = RequestHelper::requestData(
            self::API_URL . 'refresh_access_token/',
            [
                'access_token' => $accessToken,
                'grant_type' => 'ig_refresh_token'
            ]);

        return isset($response['access_token']) ? $response['access_token']: false;
    }
    protected function fetchAccount($source)
    {
        $accessToken = $this->getAccessToken($source);

        if ($this->source['account_type'] == InstagramHelper::ACCOUNT_TYPE_BUSINESS) {
            // Instagram Business

                $response = RequestHelper::requestData(
                    self::API_FB_URL . $this->source['account_id'],
                    [
                        'access_token' => $accessToken,
                        'fields' => 'id,username,profile_picture_url'
                    ]);
        } else {
            // Instagram Personal
            $response = RequestHelper::requestData(
                self::API_URL . 'me/',
                [
                    'access_token' => $accessToken,
                    'fields' => 'id,username'
                ]);
        }

        return isset($response['id']) ?
            ['account' => InstagramHelper::sanitizeAccountData($response)]
            : false;

    }

    protected function fetchPosts($source)
    {
        $accountId = $source['info']['account']['id'];
        $accessToken = $this->getAccessToken($source);

        if ($this->source['account_type'] == InstagramHelper::ACCOUNT_TYPE_BUSINESS) {
            // Instagram Business
            $requestUrl = self::API_FB_URL . $accountId . '/media';
        } else {
            $requestUrl = self::API_URL . 'me/media';
        }
        $response = RequestHelper::requestData(
            $requestUrl,
            [
                'access_token' => $accessToken,
                'fields' => 'id,caption,media_type,media_url,permalink,like_count,comments_count,thumbnail_url,timestamp,children{fields=id,media_url,thumbnail_url,permalink}',
                'limit' => CGUSF_SF_LIMIT,
                'locale' => get_option(CGUSF_PREFIX .'locale'),
                'after' => $this->paginationToken
            ]);

        // Add additional info
        if (isset($response['data'])) {
            $sanitizedItems = InstagramHelper::sanitizePosts($response, $source);
        }

        return !isset($response['data']) ? false : $sanitizedItems;
    }
}