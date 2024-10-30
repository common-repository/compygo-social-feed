<?php
    use CompygoSocialFeed\Helper\Api\InstagramHelper;
?>

<div class="cgusf__post-meta-inst">
    <?php if($source['account_type'] == InstagramHelper::ACCOUNT_TYPE_BUSINESS): ?>
        <div class="cgusf__icon cgusf__icon--likes">
            <span class="num">
                <?php echo esc_html($item['like_count']) ?>
            </span>
        </div>
        <div class="cgusf__icon cgusf__icon--comments">
            <span class="num">
                <?php echo esc_html($item['comment_count']) ?>
            </span>
        </div>
    <?php endif ?>
</div>
