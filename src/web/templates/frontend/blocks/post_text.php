<?php
use CompygoSocialFeed\Helper\HtmlHelper;
use CompygoSocialFeed\Helper\DataHelper;
?>
<?php
    $designConfig = $feed['config']['design'];
    $itemMessage = DataHelper::getPostMessage($item, $source);
    $isEnabled = !(!$itemMessage || $designConfig['text']['enable'] == false) || isset($data['lightbox']);
?>
<div class="cgusf__post-text<?php echo !$isEnabled ? ' no-padding hidden' : '' ?>">
    <?php if(isset($item['status']) && $item['status'] === 'event'): ?>
        <p class="cgusf__post-message">
            <span class="event_title"><?php echo esc_html($item['atch_title']) ?></span>
            <span class="event_date">
                <?php echo esc_html($item['event_date']) ?>
            </span>
            <span class="event_place"><?php echo ' - ' . esc_html($item['event_place']) ?></span>
        </p>
    <?php else: ?>
        <p class="cgusf__post-message"></p>
    <?php endif ?>
</div>
