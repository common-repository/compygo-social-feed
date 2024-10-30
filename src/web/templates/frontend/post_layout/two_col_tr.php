<div class="wrapper">
    <?php $this->insert('blocks/post_media', ['item' => $item, 'feed' => $feed, 'source' => $source]) ?>
    <div class="cgusf__post-col">
        <?php $this->insert('blocks/account_meta', ['item' => $item, 'feed' => $feed, 'data' => []]) ?>
        <?php $this->insert('blocks/post_text', ['item' => $item, 'feed' => $feed, 'source' => $source, 'data' => []]) ?>
    </div>
</div>
<?php $this->insert('blocks/post_meta', ['item' => $item, 'feed' => $feed, 'source' => $source]) ?>
