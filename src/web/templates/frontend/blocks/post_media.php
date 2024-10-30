<?php
    use CompygoSocialFeed\Helper\Api\FacebookHelper;
    use CompygoSocialFeed\Helper\Api\InstagramHelper;
?>
<?php
    $designConfig = $feed['config']['design'];
    $itemMessage = $item ? (isset($item['message']) ? $item['message'] : '') : '';
    $class = !empty($item['atch_video']) ? ' video' : '';
    $isLightbox = $feed['config']['lightbox']['enable'];
?>


<?php if(FacebookHelper::isPostShare($item)): ?>
    <?php $this->insert('blocks/post_share_media', ['item' => $item, 'feed' => $feed]) ?>
<?php else: ?>
    <?php if($designConfig['media']['enable']): ?>
        <?php if(isset($item['atch_image'])): ?>
            <div class="cgusf__post-media<?php echo esc_attr($class) ?>">
                <a class="cgusf__post-img-link" target="<?php echo $isLightbox ? '' : '_blank' ?>"
                   <?php echo $isLightbox ? '' : ('href="' . esc_url($item['url']) . '"') ?>>
                   <?php if ($source['vendor'] == 'instagram'): ?>
                        <?php $this->insert('blocks/post_meta_instagram', ['item' => $item, 'feed' => $feed, 'source' => $source]) ?>
                    <?php endif ?>
                </a>
                <?php if($item['atch_image']): ?>
                    <img src="<?php echo esc_url($item['atch_image']) ?>" alt="">
                    <?php if(in_array($item['atch_type'], [InstagramHelper::MEDIA_TYPE_CAROUSEL_ALBUM, FacebookHelper::MEDIA_TYPE_ALBUM])): ?>
                        <div class="cgusf__icon cgusf__icon--album"></div>
                    <?php elseif ($item['atch_type'] == InstagramHelper::MEDIA_TYPE_VIDEO): ?>
                        <div class="cgusf__icon cgusf__icon--video"></div>
                    <?php endif ?>
                <?php endif ?>
            </div>
        <?php endif ?>
    <?php endif ?>
<?php endif ?>
