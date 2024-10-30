<?php
namespace CompygoSocialFeed\View;

use CompygoSocialFeed\Helper\DataHelper;
use CompygoSocialFeed\Model\Source as SourceModel;
use CompygoSocialFeed\View\ViewCore;

class FrontendHtml {
    static public function getHtml($feed) 
    {
        //TODO add multiple sources
        $source = SourceModel::getSource($feed['config']['source']);

        if (!$source) {
            return false;
        }

        DataHelper::prepareJson($feed['config']);

        $view = new ViewCore();

        return $view->getHtml($feed);
    }

    static protected function minifierHtml($html) {
        $search = [
            // Remove whitespaces after tags
            '/\>[^\S ]+/s',
            // Remove whitespaces before tags
            '/[^\S ]+\</s',
            // Remove multiple whitespace sequences
            '/(\s)+/s',
            // Removes comments
            '/<!--(.|\s)*?-->/'
        ];
        $replace = array('>', '<', '\\1');

        return preg_replace($search, $replace, $html);
    }
}