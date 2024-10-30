<?php 
    use CompygoSocialFeed\Helper\DataHelper;
?>
<div class="cgusf__controls">
    <?php if($feed['config']['loading']['enable'] && $feed['config']['loading']['type'] === 'load-btn'): ?>
        <div class="cgusf__load-button">
            <span>
                <?php echo esc_html(DataHelper::getString('Load More')); ?>
            </span>
            <div class="cgusf__load-button-loader"></div>
        </div>
    <?php endif ?>
    <div class="cgusf__loader-wrapper">
        <div class="cgusf__loader"></div>
    </div>
</div>