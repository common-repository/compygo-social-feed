<?php $this->insert('blocks/account_meta', ['item' => $item, 'feed' => $feed, 'data' => []]) ?>
<div class="wrapper">
    <?php $this->insert('blocks/post_media', ['item' => $item, 'feed' => $feed, 'source' => $source]) ?>
    <?php $this->insert('blocks/post_text', ['item' => $item, 'feed' => $feed, 'source' => $source, 'data' => []]) ?>
</div>
<?php $this->insert('blocks/post_meta', ['item' => $item, 'feed' => $feed, 'source' => $source]) ?>
