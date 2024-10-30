<?php
use CompygoSocialFeed\Helper\HtmlHelper;
use CompygoSocialFeed\Helper\DataHelper;

foreach ($items as &$item) {
    $item['date'] =  HtmlHelper::convertTime($item['date']);

    $itemMessage = DataHelper::getPostMessage($item, $sources[$item['source_id']]);
    $item['message'] = HtmlHelper::parseText($itemMessage);
    $item['atch_description'] = HtmlHelper::parseText($item['atch_description']);
}
?>

<div style="display: none !important" data-type="cgusf-data">
    <?php echo json_encode($items, JSON_UNESCAPED_UNICODE) ?>
</div>