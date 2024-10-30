<?php 
    use CompygoSocialFeed\Helper\HtmlHelper;

    $itemTime = $item ? HtmlHelper::convertTime($item['date']) : '';
    $designConfig = $feed['config']['design'];

    $isLightbox = isset($data['lightbox']);
    $isAuthor = $designConfig['author']['enable'] || $isLightbox;
    $isDate= $designConfig['date']['enable'] || $isLightbox;
    $isLogo= $designConfig['logo']['enable'] || $isLightbox;
?>

<?php if($isAuthor || $isDate || $isLogo): ?>
    <div class="cgusf__account-meta">
        <?php if($isLogo): ?>
            <div class="cgusf__account-img">
                <a class="cgusf__account-img-link" href="#"></a>
                <img src="#" alt="Profile picture">
            </div>
        <?php endif ?>

        <?php if($isAuthor || $isDate): ?>
            <div class="cgusf__account-title">
                <?php if($isAuthor): ?>
                    <a class="cgusf__account-author" href="#"></a>
                <?php endif ?>
                <?php if($isDate): ?>
                    <div class="cgusf__post-time">
                        <?php echo esc_html($itemTime) ?>
                    </div>
                <?php endif ?>
            </div>
        <?php endif ?>
    </div>
<?php endif ?>