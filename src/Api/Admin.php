<?php
namespace CompygoSocialFeed\Api;

use CompygoSocialFeed\Model\Api as ApiModel;
use CompygoSocialFeed\Helper\RequestHelper;
use CompygoSocialFeed\Model\Post;
use CompygoSocialFeed\Model\Options as OptionsModel;
use CompygoSocialFeed\Model\Feed as FeedModel;
use CompygoSocialFeed\Model\Source as SourceModel;
use CompygoSocialFeed\Model\Logger as LoggerModel;
use CompygoSocialFeed\Model\Stats as StatsModel;
use CompygoSocialFeed\Helper\Validator;
use CompygoSocialFeed\Api\Frontend;

class Admin extends Frontend 
{
    /**
     * Fetch options
     */
    public function fetchOptionsAjax() 
    {
        if ($this->checkNounce()) {
            $data = OptionsModel::getPluginSettings();

            wp_send_json($data);
        }
    }

    /**
     * Update options
     */
    public function updateOptionsAjax() 
    {
        if ($this->checkNounce()) {
            $paramData = RequestHelper::getRequestParam('data', 'array');

            if (OptionsModel::savePluginSettings($paramData)) {
                $data = Validator::getMessage('success', 'Setting are saved');
            } else {
                $data = Validator::getMessage('danger');
            }

            wp_send_json($data);
        }
    }

    /**
     * Fetch feed by ajax
     */
    public function fetchFeedAjax() 
    {
        if ($this->checkNounce()) {
            if ($paramFeedId = RequestHelper::getRequestParam('feed_id', 'key')) {
                $data = ['feed' => FeedModel::getFeed($paramFeedId)];
            } else {
                $data = FeedModel::getFeedCollection();
            }

            wp_send_json($data);
        }
    }

    /**
     * Fetch feed by ajax
     */
    public function restFetchFeedAjax() 
    {
        if ($paramFeedId = RequestHelper::getRequestParam('feed_id', 'key')) {
            $data = ['feed' => FeedModel::getFeed($paramFeedId)];
        } else {
            $data = FeedModel::getFeedCollection();
        }

        wp_send_json($data);
    }

    /**
     * Remove feed by ajax
     */
    public function removeFeedAjax() 
    {
        if ($this->checkNounce()) {
            $paramFeedId = RequestHelper::getRequestParam('feed_id', 'key');

            if (!empty($paramFeedId)) {
                $data = FeedModel::removeFeed($paramFeedId);
            } else {
                $paramFeedId = RequestHelper::getRequestParam('feed_id', 'array');
                foreach ($paramFeedId as $id) {
                    FeedModel::removeFeed($id);
                }
                $data = Validator::getMessage('success', 'Feed have been removed');
            }

            $data['feeds'] = FeedModel::getFeedCollection();

            wp_send_json($data);
        }
    }
    
    /**
     * Duplicate feed by ajax
     */
    public function duplicateFeedAjax() 
    {
        if ($this->checkNounce()) {
            $paramFeedId = RequestHelper::getRequestParam('feed_id', 'key');
            $data = FeedModel::duplicateFeed($paramFeedId);
            $data['feeds'] = FeedModel::getFeedCollection();
            wp_send_json($data);
        }
    }

    /**
     * Create or update feed by ajax
     */
    public function createOrUpdateFeedAjax() 
    {
        if ($this->checkNounce()) {
            $paramData = RequestHelper::getRequestParam('data', 'array');
            $feed = FeedModel::createOrUpdateFeed($paramData);

            if ($feed) {
                $data = Validator::getMessage('success', 'The feed was saved successfully', ['id' => $feed]);
            } else {
                $data = Validator::getMessage('danger');
            }

            wp_send_json($data);
        }
    }

    /**
     * Save cutom logo by ajax
     */
    public function saveFeedLogo() 
    {
        if ($this->checkNounce()) {
            $paramFeedId = RequestHelper::getRequestParam('id', 'key');
            $logoPath = FeedModel::saveFeedLogo($paramFeedId, $_FILES['file_logo']);

            if ($logoPath) {
                $data = Validator::getMessage('success', 'The logo was saved', ['file' => $logoPath]);
            } else {
                $data = Validator::getMessage('danger');
            }

            wp_send_json($data);
        }
    }

    /**
     * Save cutom logo by ajax
     */
    public function removeFeedLogo() 
    {

        if ($this->checkNounce()) {
            $paramFeedId = RequestHelper::getRequestParam('feed_id', 'key');
            $feed = FeedModel::removeFeedLogo($paramFeedId);

            if ($feed) {
                $data = Validator::getMessage('success', 'The logo was removed', ['id' => $feed]);
            } else {
                $data = Validator::getMessage('danger');
            }

            wp_send_json($data);
        }
    }

    /**
     * Fetch source by ajax
     */
    public function fetchSourceAjax() 
    {
        if ($this->checkNounce()) {
            if ($paramSourceId = RequestHelper::getRequestParam('source_id', 'key')) {
                $data = SourceModel::getSource($paramSourceId);
            } else {
                $data = SourceModel::getSourceCollection();
            }

            wp_send_json($data);
        }
    }

    public function createOrUpdateSourceAjax()
    {
        if ($this->checkNounce()) {
            $sources = RequestHelper::getRequestParam('sources', 'array');
            $vendor = RequestHelper::getRequestParam('vendor');
            $vendorType = RequestHelper::getRequestParam('vendor_type');
            $accessToken = RequestHelper::getRequestParam('access_token');
            $facebookAppId = RequestHelper::getRequestParam('facebook_app_id');
            $facebookAppSecret = RequestHelper::getRequestParam('facebook_app_secret');

            if ($facebookAppId && $facebookAppSecret) {
                update_option(CGUSF_PREFIX .'facebook_app_id', $facebookAppId);
                update_option(CGUSF_PREFIX .'facebook_app_secret', $facebookAppSecret);
            }

            if ($vendor === "youtube") {
                update_option(CGUSF_PREFIX .'youtube_api_key', $accessToken);

            }

            foreach ($sources as $source) {
                if (!SourceModel::existSource($source['id'], $vendor, $vendorType)) {
                    $source['account_id'] = $source['id'];
                    $source['vendor'] = $vendor;
                    $source['account_type'] = $vendorType;
                    $source['access_token'] = $source['access_token'] ? $source['access_token'] : $accessToken;

                    SourceModel::createSource($source, ApiModel::getApiByVendor(null, [
                        'vendor' => $vendor,
                        'account_type' => $vendorType,
                        'account_id' => $source['id']
                    ]));
                }
            }

            wp_send_json(['status' => 'success']);
        }
    }

    /**
     * @return void
     */
    public function removeSourceAjax() 
    {
        if ($this->checkNounce()) {
            $paramSourceId = RequestHelper::getRequestParam('source_id', 'key');
            $data = SourceModel::removeSource($paramSourceId);

            wp_send_json($data);
        }
    }

    /**
     * @return void
     */
    public function fetchLogsAjax()
    {
        if ($this->checkNounce()) {
            $data = LoggerModel::getLogs();

            wp_send_json($data);
        }
    }

    /**
     * @return void
     */
    public function flushCache()
    {
        if ($this->checkNounce()) {
            $paramFeedId = RequestHelper::getRequestParam('feed_id', 'key');
            $feed = FeedModel::getFeed($paramFeedId);

            if ($feed['config']['source']) {
                foreach ($feed['config']['source'] as $source) {
                    $data = Post::deletePostsSourceId($source);
                }
            }

            wp_send_json($data);
        }
    }

    /**
     * @return void
     */
    public function sendStats()
    {
        if ($this->checkNounce()) {
            $stats = get_option(CGUSF_PREFIX.'stats');

            if (!empty($stats)) {
                $result = StatsModel::sendStats($stats);

                if ($result) {
                    wp_send_json($result);
                }
            }
        }
        
        wp_send_json(false);
    }
}