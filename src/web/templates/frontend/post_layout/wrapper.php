<?php 
use CompygoSocialFeed\Helper\HtmlHelper;
use CompygoSocialFeed\Helper\DataHelper;

$feedLayout = DataHelper::getPostLayoutPath($feed);
$class = "cgusf__item" . HtmlHelper::getItemClass($item, $source);
?>

<div class="<?php echo esc_attr($class) ?>"
    data-id="<?php echo esc_attr($item['id']) ?>"
    data-source="<?php echo esc_attr($source['id']) ?>">
    <div class="cgusf__wrap">
        <?php $this->insert($feedLayout, ['item' => $item, 'feed' => $feed, 'source' => $source]) ?>
    </div>
</div>
