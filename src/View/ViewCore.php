<?php
namespace CompygoSocialFeed\View;

use CompygoSocialFeed\Model\Api as ApiModel;
use CompygoSocialFeed\Helper\RequestHelper;
use CompygoSocialFeed\Model\Source as SourceModel;
use WyriHaximus\HtmlCompress\Factory as HtmlCompress;
use WyriHaximus\HtmlCompress\HtmlCompressor;
use voku\helper\HtmlMin;

class ViewCore {
    public function getHtml($feed)
    {
        if (RequestHelper::isAdminPreview()) {
            $feed['config']['lightbox']['enable'] = false;
        }

        $paramAjaxNonce = RequestHelper::getRequestParam('_ajax_nonce');
        $paramAdmin = RequestHelper::getRequestParam('admin', 'bool');
        $paramListTemplate= RequestHelper::getRequestParam('listTemplate', 'bool');

        // Get template on initial page load
        if (!$paramAjaxNonce || ($paramAdmin && $paramListTemplate)) {
            $html = TemplateList::getHtml($feed);
        } else {
            // Get items by AJAX
            $sourceIds = is_array($feed['config']['source']) ? array_values($feed['config']['source']) : [$feed['config']['source']];
            $sources = SourceModel::getSourceCollection($sourceIds);
            $items = $this->getItems($feed, $sources);
            $html = TemplateItems::getHtml($feed, $items, $sources);
        }

        return $html;
    }

    protected function getItems($feed, $sources)
    {
        $allItems = [];
        $sourceItems = [];

        foreach ($sources as $source) {
            $api = ApiModel::getApiByVendor($feed, $source);
            $sourceItems[$source['id']] = $api->getPosts($source);
            $allItems = array_merge($allItems, $sourceItems[$source['id']]);
        }

        $allItems = $this->prepareItems($allItems);

        return array_slice($allItems, 0, CGUSF_SF_LIMIT);
    }

    protected function prepareItems($items)
    {
        $addedItemIds = [];
        $preparedItems = [];

        // Sort by Date
        $date = [];
        foreach ($items as $key => $item) {
            if (!isset($addedItemIds[$item['id']])) {
                array_push($preparedItems, $item);
                $date[$key] = $item['date'];
            }

            array_push($addedItemIds, $item['id']);
        }
        array_multisort($date, SORT_DESC, $preparedItems);

        return $preparedItems;
    }
}