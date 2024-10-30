<?php
namespace CompygoSocialFeed\View;

use CompygoSocialFeed\Helper\RequestHelper;
use League\Plates\Engine as PlatesEngine;
use CompygoSocialFeed\Model\Source as SourceModel;
use CompygoSocialFeed\Model\Api as ApiModel;

class AdminHtml {

    /**
     * @return void
     */
    public function getFeedsHtml()
    {
        $templates = new PlatesEngine(CGUSF_FF_DIR . '/src/web/templates/admin');
        $html = $templates->render('feeds');

        echo wp_kses_post($html);
    }

    /**
     * @return void
     */
    public function getSettingsHtml() {
        $paramTokens= RequestHelper::getRequestParam('tokens', 'array', 'get');

        if ($paramTokens && is_array($paramTokens)) {
           $this->createSource($paramTokens);
        }

        $templates = new PlatesEngine(CGUSF_FF_DIR . '/src/web/templates/admin');
        $html = $templates->render('settings');

        echo wp_kses_post($html);
    }

    /**
     * @return void
     */
    protected function createSource($paramTokens)
    {
        foreach ($paramTokens as $data) {
            if (!SourceModel::existSource($data['id'], $data['vendor'], $data['type']) && isset($data['vendor'])) {
                SourceModel::createSource($data, ApiModel::getApiByVendor(null, [
                    'vendor' => $data['vendor'],
                    'account_type' => $data['type'],
                    'id' => $data['id']
                ]));
            }
        }
    }
}