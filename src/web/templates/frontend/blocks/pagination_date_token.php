<?php if(isset($items)): ?>
    <?php $token = end($items)['date'] ?>
    <div class="cgusf__pagination_token" data-token="<?php echo esc_attr($token) ?>"></div>
<?php endif ?>
