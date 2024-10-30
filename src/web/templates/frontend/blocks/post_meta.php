<?php 
    use CompygoSocialFeed\Helper\DataHelper;

    $isLightbox = isset($data['lightbox']);
    $source = isset($source) ? $source : ['vendor' => ''];
    $designConfig = $feed['config']['design'];
    $isLikes = $designConfig['likes']['enable'];
    $isEnabled = $isLikes || $designConfig['action_links']['enable'];
    $noStatsColor = $feed['config']['color']['bg'] == $feed['config']['color']['bg_2'];

    if (isset($item)) {
        if ($designConfig['views']['enable'] && isset($item['view_count'])) {
            $viewCount =  $item['view_count'];
        }
        if ($designConfig['likes']['enable'] && isset($item['like_count'])) {
            $likeCount =  $item['like_count'];
        }
        if ($designConfig['share']['enable'] && isset($item['share_count'])) {
            $shareCount =  $item['share_count'];
        }
        if ($designConfig['comments']['enable'] && isset($item['comment_count'])) {
            $commentCount =  $item['comment_count'];
        }
        $url = $item['url'];
    }
?>
<?php if($isEnabled): ?>
    <div class="cgusf__post-meta">
        <div class="cgusf__post-stats <?php echo ($noStatsColor ? 'no-bg' : '') ?>">
            <?php if(isset($viewCount)): ?>
                <div class="cgusf__icon cgusf__icon--view">
                    <span class="num">
                        <?php echo esc_html($viewCount) ?>
                    </span>
                </div>
            <?php endif ?>
            <?php if(isset($likeCount)): ?>
                <div class="cgusf__icon cgusf__icon--likes">
                    <span class="num">
                        <?php echo esc_html($likeCount) ?>
                    </span>
                </div>
            <?php endif ?>
            <?php if(isset($shareCount)): ?>
                <div class="cgusf__icon cgusf__icon--shares">
                    <span class="num">
                        <?php echo esc_html($shareCount) ?>
                    </span>
                </div>
            <?php endif ?>
            <?php if(isset($commentCount)): ?>
                <div class="cgusf__icon cgusf__icon--comments">
                    <span class="num">
                        <?php echo esc_html($commentCount) ?>
                    </span>
                </div>
            <?php endif ?>
        </div>
        <?php if($designConfig['action_links']['enable'] && !$isLightbox): ?>
            <div class="cgusf__post-action-links">
                <a class="cgusf_link-view" href="<?php echo esc_url($url) ?>" target="_blank">
                    <?php echo esc_html(DataHelper::getViewOnString($source)); ?>
                </a>

                <?php if(DataHelper::getShareString($source)): ?>
                    <span class="cgusf_link-separator"></span>
                <?php endif ?>

                <?php if($shareString = DataHelper::getShareString($source)): ?>
                    <a class="cgusf_link-view" href="<?php echo esc_url(DataHelper::getFacebookShareUrl($url)) ?>" target="_blank">
                        <?php echo esc_html($shareString) ?>
                    </a>
                <?php endif ?>
            </div>
        <?php endif ?>
    </div>
<?php endif ?>