<?php
namespace CompygoSocialFeed\Api\Social;

use CompygoSocialFeed\Helper\RequestHelper;
use CompygoSocialFeed\Helper\Api\YoutubeHelper;

class Youtube extends Social
{
    const API_URL = 'https://www.googleapis.com/youtube/v3';

    protected function fetchAccount($source)
    {
        $accessToken = $this->getAccessToken($source);

        $requestUrl = self::API_URL . '/channels';
        $response = RequestHelper::requestData(
            $requestUrl,
            [
                'id' =>  $source['account_id'],
                'key' => $accessToken,
                'part' => 'id,snippet'
            ]
        );

        return $response ?
            ['account' => YoutubeHelper::sanitizeAccountData($response['items'][0])]
            : false;
    }

    protected function fetchPosts($source)
    {
        $accessToken = $this->getAccessToken($source);

        $requestUrl = self::API_URL . '/search';
        $response = RequestHelper::requestData(
            $requestUrl,
            [
                'channelId' =>  $source['account_id'],
                'key' => $accessToken,
                'part' => 'id',
                'order' => 'date',
                'type' => 'video',
                'maxResults' => CGUSF_SF_LIMIT,
                'pageToken' => $this->paginationToken
            ]
        );

        // Add additional info
        if (isset($response['items'])) {
            foreach ($response['items'] as $key => $video) {
                $response['items'][$key] = $this->fetchVideoById($source, $video['id']['videoId']);
            }
            $sanitizedItems = YoutubeHelper::sanitizePosts($response, $source);
        }

        return !isset($response['items']) ? false : $sanitizedItems;
    }

    protected function fetchVideoById($source, $videoId)
    {
        $accessToken = $this->getAccessToken($source);

        $requestUrl = self::API_URL . '/videos';
        $response = RequestHelper::requestData(
            $requestUrl,
            [
                'id' =>  $videoId,
                'key' => $accessToken,
                'part' => 'snippet,statistics',
            ]
        );

        return !isset($response['items']) ? false : $response['items'][0];
    }
}