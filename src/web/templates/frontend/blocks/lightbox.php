<?php if($feed['config']['lightbox']['enable']): ?>
    <div class="cgusf__lb <?php echo esc_attr($feed['config']['type']) ?>">
        <div class="cgusf__bg"></div>
        <div class="cgusf__post">
            <div class="cgusf__post-media">
                <div class="cgusf__cross"></div>
                <div class="cgusf__arrow-l"></div>
                <div class="cgusf__arrow-r"></div>
                
                <div class="cgusf__img-wrapper">
                    <div class="cgusf__video">
                        <iframe src="" frameborder="0"
                                style="border:none;overflow:hidden"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share">
                        </iframe>
                    </div>
                    <div class="cgusf__img">
                        <img src="" alt="">
                    </div>
                </div>
                <div class="cgusf__lb-slider"></div>
            </div>
            <?php if($feed['config']['type'] === 'post'): ?>
                <div class="cgusf__sidebar">
                    <?php $this->insert('blocks/account_meta', ['item' => $item, 'feed' => $feed, 'data' => ['lightbox' => 1]]) ?>
                    <div class="cgusf__info">
                        <?php $this->insert('blocks/post_text', ['item' => $item, 'feed' => $feed, 'source' => ['vendor' => ''], 'data' => ['lightbox' => 1]]) ?>
                    </div>
                    <?php $this->insert('blocks/post_meta', ['item' => $item, 'feed' => $feed, 'data' => ['lightbox' => 1]]) ?>
                </div>
            <?php endif ?>
        </div>
    </div>
<?php endif ?>