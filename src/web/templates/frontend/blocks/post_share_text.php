<?php
use CompygoSocialFeed\Helper\Api\FacebookHelper;
use CompygoSocialFeed\Helper\HtmlHelper;

$shareMessage = strlen($item['atch_description']) > 250
    ? substr($item['atch_description'], 0, 250).'...'
    : $item['atch_description'];

?>

<div class="cgusf__post-share-text">
    <p class="cgusf__post-message">
        <?php if($item['status'] === 'share_update'): ?>
            <span class="share_title">
                <?php echo esc_html($item['atch_title']) ?>
            </span>
            <span class="share_date">
                <?php echo esc_html(HtmlHelper::convertTime($item['atch_date'])) ?>
            </span>
        <?php else: ?>
            <span class="share_link">
                <?php echo esc_html($item['atch_domain']) ?>
            </span>
            <span class="share_title">
                <?php echo esc_html($item['atch_title']) ?>
            </span>
        <?php endif ?>
        <span class="share_message">
            <?php echo esc_html($shareMessage) ?>
        </span>
    </p>
</div>
