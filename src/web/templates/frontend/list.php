<?php
use CompygoSocialFeed\Helper\HtmlHelper;
use CompygoSocialFeed\Helper\DataHelper;
use CompygoSocialFeed\Model\Feed;

$listClass = HtmlHelper::getListClass($feed);
$wrapperClass = HtmlHelper::getWrapperClass($feed);
$designConfig = $feed['config']['design'];
$seeMore = (isset($designConfig['see_more']) && $designConfig['see_more']['enable']) ?
    json_encode([DataHelper::getString('See More'), DataHelper::getString('See Less')]) : '';
$padding = isset($designConfig['post']['padding']) ? $designConfig['post']['padding'] : Feed::DEFAULT_PADDING;
?>

<div class="cgusf__wrapper <?php echo esc_attr($wrapperClass) ?>"
     data-id="<?php echo esc_attr($feed['id']) ?>"
     data-load-type="<?php echo esc_attr(HtmlHelper::getFeedLoadingType($feed)) ?>"
     data-padding="<?php echo esc_attr($padding) ?>"
     data-see-more="<?php echo esc_attr($seeMore) ?>">
    <div class="cgusf__list <?php echo esc_attr($listClass) ?>">
        <?php if(HtmlHelper::isMasonry($feed)): ?>
            <div class="masonry-sizer"></div>
        <?php endif ?>
    </div>

    <?php $this->insert('blocks/list_controls', ['item' => $item ?? null, 'feed' => $feed]) ?>
    <?php $this->insert('blocks/lightbox', ['item' => $item ?? null, 'feed' => $feed]) ?>
</div>
