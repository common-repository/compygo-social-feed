<?php
namespace CompygoSocialFeed\View;

use League\Plates\Engine as PlatesEngine;

class TemplateList {
    static public function getHtml($feed)
    {
        $templates = new PlatesEngine(CGUSF_FF_DIR . '/src/web/templates/frontend');
        $result = $templates->render('list', ['feed' => $feed]);

        return $result;
    }
}