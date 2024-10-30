<?php
    $isLightbox = $feed['config']['lightbox']['enable'];
?>
<div class="cgusf__post-share">
    <?php if($item['atch_image']): ?>
        <div class="cgusf__post-media">
            <a class="cgusf__post-img-link" target="<?php echo $isLightbox ? '' : '_blank' ?>"
               href="<?php echo $isLightbox ? '#' : esc_url($item["url"]) ?>"></a>
            <img src="<?php echo esc_url($item['atch_image']) ?>" alt="">
        </div>
    <?php endif ?>
    <?php $this->insert('blocks/post_share_text', ['item' => $item, 'feed' => $feed]) ?>
</div>
