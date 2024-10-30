<?php
namespace CompygoSocialFeed\Api\Social;

use CompygoSocialFeed\Helper\HtmlHelper;
use CompygoSocialFeed\Helper\RequestHelper;
use CompygoSocialFeed\Helper\Api\FacebookHelper;

class Facebook extends Social
{
    const API_URL = 'https://graph.facebook.com/';
    const STATUS_EVENT = 'created_event';
    const STATUS_PHOTO = 'added_photos';
    const STATUS_SHARE = 'shared_story';
    const STATUS_TYPES_STANDARD = [
        'added_photos',
        'added_video',
        'app_created_story',
        'approved_friend',
        'created_event',
        'created_group',
        'created_note',
        'published_story',
        'shared_story',
        'tagged_in_photo',
        'wall_post'
    ];

    const STATUS_TYPES_UPDATE = [
        'mobile_status_update',
    ];

    protected function fetchAccount($source)
    {
        $accountId = $source['account_id'];
        $accessToken = $this->getAccessToken($source);

        $requestUrl = self::API_URL . $accountId;
        $response = RequestHelper::requestData(
            $requestUrl,
            [
            'access_token' => $accessToken,
            'fields' => 'name,link,picture'
        ]);

        return $response ?
            ['account' => FacebookHelper::sanitizeAccountData($response)]
            : false;
    }

    protected function fetchPosts($source)
    {
        $accountId = $source['info']['account']['id'];
        $accessToken = $this->getAccessToken($source);

        $requestUrl = self::API_URL . $accountId . '/feed';
        $response = RequestHelper::requestData(
            $requestUrl,
            [
                'access_token' => $accessToken,
                'fields' => 'id,attachments{media_type,type,media,title,description,target,unshimmed_url,subattachments{media{image{src}}}},message,created_time,permalink_url,likes.summary(true).limit(0),shares.summary(true).limit(0),comments{id,message,created_time,like_count,from{name,link,picture{url}}},status_type,story',
                'locale' => get_option(CGUSF_PREFIX .'locale'),
                'limit' => CGUSF_SF_LIMIT,
                'after' => $this->paginationToken
            ]);

        // Add additional info
        if (isset($response['data'])) {
            $sanitizedItems = FacebookHelper::sanitizePosts($response, $source);
            foreach ($sanitizedItems as &$sanitizedItem) {
                if ('event' === $sanitizedItem['status']) {
                    $this->fetchEvent($accountId, $accessToken, $sanitizedItem);
                }

                if ('share_update' === $sanitizedItem['status']) {
                    $this->fetchShare($accountId, $accessToken, $sanitizedItem);
                }
            }
        }

        return !isset($response['data']) ? false : $sanitizedItems;
    }

    protected function fetchEvent($accountId, $accessToken, &$sanitizedItem)
    {
        $event = $this->fetchEventById($accountId, $accessToken, $sanitizedItem['atch_target_id']);
        $sanitizedItem = array_merge($sanitizedItem, [
            'atch_image' => isset($event['item']['cover']['source']) ? $event['item']['cover']['source'] : '',
            'event_description' => isset($event['item']['description']) ? $event['item']['description'] : '',
            'event_place' => isset($event['item']['place']) ? $event['item']['place']['name'] : '',
            'event_date' => HtmlHelper::convertTime($event['item']['start_time'], 'dmt'),
        ]);
    }

    protected function fetchShare($accountId, $accessToken, &$sanitizedItem)
    {
        $share = $this->fetchShareById($accountId, $accessToken, $sanitizedItem['atch_target_id']);
        $sanitizedItem = [
            'atch_title' => $share['item']['from']['name'],
            'atch_description' => $share['item']['name'],
            'atch_date' => $share['item']['created_time'],
        ];
    }

    protected function fetchPhotos($source)
    {
        $accountId = $source['info']['account']['id'];
        $accessToken = $this->getAccessToken($source);

        $requestUrl = self::API_URL . $accountId . '/photos';
        $response = RequestHelper::requestData(
            $requestUrl,
            [
                'access_token' => $accessToken,
                'fields' => 'images,created_time,comments,likes,shares',
                'type' => 'uploaded',
                'locale' => get_option(CGUSF_PREFIX .'locale'),
                'limit' => CGUSF_SF_LIMIT,
                'after' =>  $this->paginationToken
            ]);

        return !isset($response['data']) ? false : [
            'items' => FacebookHelper::sanitizePhotos($response['data']),
            'paging' => $response['paging']
        ];
    }

    protected function fetchEventById($accountId, $accessToken, $id)
    {
        $requestUrl = self::API_URL . $id;
        $response = RequestHelper::requestData(
            $requestUrl,
            [
                'access_token' => $accessToken,
                'fields' => 'place,cover,end_time,start_time,description',
                'locale' => get_option(CGUSF_PREFIX .'locale'),
                'limit' => 1
            ]);

        return !isset($response['id']) ? false : [
            'item' => $response,
        ];
    }

    protected function fetchShareById($accountId, $accessToken, $id)
    {
        $requestUrl = self::API_URL . $id;
        $response = RequestHelper::requestData(
            $requestUrl,
            [
                'access_token' => $accessToken,
                'fields' => 'name,created_time,from',
                'locale' => get_option(CGUSF_PREFIX .'locale'),
                'limit' => 1
            ]);

        return !isset($response['id']) ? false : [
            'item' => $response,
        ];
    }
}