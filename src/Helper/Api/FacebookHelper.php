<?php
namespace CompygoSocialFeed\Helper\Api;

use CompygoSocialFeed\Api\Social\Facebook;
use CompygoSocialFeed\Helper\DataHelper;
use CompygoSocialFeed\Helper\Api\AbstractHelper;

class FacebookHelper extends AbstractHelper
{
    public const MEDIA_TYPE_ALBUM = "album";

    /**
     * @param $response
     * @param $source
     * @return array
     */
    static public function sanitizePosts($response, $source)
    {
        $result = [];
        $key = 0;

        foreach($response['data'] as $post) {
            $newPost = [
                'id' => $post['id'],
                'source_id' => $source['id'],
                'vendor' => self::VENDOR_FACEBOOK,
                'message' => isset($post['message']) ? $post['message'] : '',
                'date' => date(CGUSF_DEFAULT_DATE_FORMAT, strtotime($post['created_time'])),
                'url' => isset($post['permalink_url']) ? $post['permalink_url'] : '',
                'share_count' => isset($post['shares']) ? $post['shares']['count'] : 0,
                'like_count' => isset($post['likes']) ? $post['likes']['summary']['total_count'] : 0,
                'comment_count' => isset($post['comments']) ? $post['comments']['summary']['total_count'] : 0
            ];
            

            if($post['attachments']) {
                foreach($post['attachments']['data'] as $attachment) {
                    $newPost = array_merge($newPost, [
                        'atch_type' => isset($attachment['type']) ? $attachment['type'] : '',
                        'atch_image' => isset($attachment['media']['image']['src']) ? $attachment['media']['image']['src'] :  '',
                        'atch_video' => isset($attachment['media']['source']) ? $attachment['media']['source'] : '',
                        'atch_title' => isset($attachment['title']) ? $attachment['title'] : '',
                        'atch_description' => isset($attachment['description']) ? $attachment['description'] : '',
                        'atch_domain' => isset($attachment['unshimmed_url']) ? DataHelper::getDomainByUrl($attachment['unshimmed_url']) : '',
                        'atch_target_id' => isset($attachment['target']['id']) ? $attachment['target']['id'] : '',
                    ]);

                    if(isset($attachment['subattachments'])) {
                        $newPost['children'] = [];
                        foreach($attachment['subattachments']['data'] as $index => $subattachment) {
                            $newPost['children'][$index] = [];
                            $newPost['children'][$index]['media_url'] = $subattachment['media']['image']['src'];
                        }
                    }

                    break;
                }
            }

            $newPost['paging'] = $response['paging']['cursors']['after'];
            $newPost['status'] = self::getPostStatus($post, $newPost);


            $result[$key] = $newPost;
            $key++;
        }

        return $result;
    }

    /**
     * @param $photos
     * @return array
     */
    static public function sanitizePhotos($photos)
    {
        $new = [];

        return $new;
    }

    /**
     * @param $item
     * @return string
     */
    static public function getPostStatus($post, $newPost)
    {
        $status = '';

        if (isset($post['status_type'])) {
            if (!in_array($post['status_type'], Facebook::STATUS_TYPES_STANDARD)
                || (in_array($newPost['atch_type'], ['cover_photo', 'profile_media']) && $post['status_type'] === Facebook::STATUS_PHOTO)
            ) {
                $status = 'note';
            }

            if ($post['status_type'] === Facebook::STATUS_EVENT) {
                $status = 'event';
            }

            if ($post['status_type'] === Facebook::STATUS_SHARE) {
                $status = 'share';
            }

            if (in_array($post['status_type'], Facebook::STATUS_TYPES_UPDATE) && isset($post['atch_description'])) {
                $status = 'share_update';
            }
        }

        return $status;
    }

    /**
     * @param $item
     * @return bool
     */
    static public function isPostShare($item) 
    {
        return in_array($item['status'], ['share', 'share_update']);
    }

    /**
     * @param $value
     * @return mixed
     */
    static public function sanitizeAccountData($value) 
    {
        if ($value['picture']['data']['url']) {
            $value['picture'] = $value['picture']['data']['url'];
        }

        return $value;
    }
}