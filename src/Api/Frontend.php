<?php
namespace CompygoSocialFeed\Api;

use CompygoSocialFeed\Core\Singleton;
use CompygoSocialFeed\Model\Api as ApiModel;
use CompygoSocialFeed\Model\Feed as FeedModel;
use CompygoSocialFeed\View\FrontendHtml;
use CompygoSocialFeed\Api\Social\Facebook as FacebookApi;
use CompygoSocialFeed\Api\Social\Instagram as InstagramApi;
use CompygoSocialFeed\Model\Source as SourceModel;
use CompygoSocialFeed\Helper\DataHelper;
use CompygoSocialFeed\Helper\RequestHelper;

class Frontend extends Singleton 
{
    /**
     * Fetch feed posts
     */
    public function fetchFeedPostsAjax() 
    {
        if ($this->checkNounce()) {
            $feed = null;

            if ($paramFeed = RequestHelper::getRequestParam('feed', 'array')) {
                //Get feed for admin preview
                $feed = $paramFeed;
            } else if ($feedId = RequestHelper::getRequestParam('feed_id', 'key')) {
                // Get feed for frontend
                $feed = FeedModel::getFeed($feedId);
            }

            $result = '';
            if ($feed) {
                $result = FrontendHtml::getHtml($feed);
            }

            $allowedAttributes = [
                'class' => [],
                'id' => [],
                'style' => [],
                'src' => [],
                'href' => [],
                'alt' => [],
                'target' => [],
                'allow' => [],
                'frameborder' => [],
                'data-id' => [],
                'data-type' => [],
                'data-source' => [],
                'data-json' => [],
                'data-padding' => [],
                'data-see-more' => [],
                'data-token' => [],
            ];

            echo wp_kses($result, [
                'a' => $allowedAttributes,
                'p' => $allowedAttributes,
                'b' => $allowedAttributes,
                'strong' => $allowedAttributes,
                'span' => $allowedAttributes,
                'div' => $allowedAttributes,
                'img' => $allowedAttributes,
                'iframe' => $allowedAttributes,
            ]);
        }

        wp_die();
    }

    /**
     * Fetch feed sources
     */
    public function fetchFeedSourcesAjax() 
    {
        if ($this->checkNounce()) {
            $feedId = RequestHelper::getRequestParam('feed_id', 'key');
            $accountsData = [];
            $feed = FeedModel::getFeed($feedId);
            $sourceIds = RequestHelper::getRequestParam('sources', 'array') ?: $feed['config']['source'];

            $sources = SourceModel::getSourceCollection(is_array($sourceIds) ? array_values($sourceIds) : $sourceIds);

            foreach ($sources as $source) {
                $api = ApiModel::getApiByVendor($feed, $source);
                $account = $api->getAccount($source);
                $accountsData[$source['id']] = DataHelper::sanitizeAccountData($account, $feed);
            }

            wp_send_json($accountsData);
        }
    }

    protected function checkNounce()
    {
        $paramAdmin = RequestHelper::getRequestParam('admin', 'bool');
        $nonce = empty($paramAdmin) ? CGUSF_AJAX_NONCE : CGUSF_AJAX_ADMIN_NONCE;
        check_ajax_referer($nonce);
        if (!wp_verify_nonce($_REQUEST['_ajax_nonce'], $nonce)) {
            return false;
        }

        return true;
    }
}
