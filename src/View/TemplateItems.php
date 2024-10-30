<?php
namespace CompygoSocialFeed\View;

use CompygoSocialFeed\Api\Frontend as Api;
use League\Plates\Engine as PlatesEngine;
use CompygoSocialFeed\Helper\DataHelper;

class TemplateItems {
    static public function getHtml($feed, $items, $sources = null) {
        $templates = new PlatesEngine(CGUSF_FF_DIR . '/src/web/templates/frontend');
        $result = '';

        if (!empty($items)) {
            foreach ($items as $item) {
                $result .= $templates->render(
                    'post_layout/wrapper',
                    ['item' => $item, 'feed' => $feed, 'source' => $sources[$item['source_id']]]
                );
            }

            $result .= $templates->render('blocks/items_data', ['items' => $items, 'sources' => $sources]);
            $result .= $templates->render('blocks/pagination_date_token', ['items' => $items]);
        }
        return $result;
    }
}