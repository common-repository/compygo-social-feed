<?php
    use CompygoSocialFeed\Model\Translations;
?>

<div id="cgusf_transalations" 
    style="display: none" 
    data-json="<?php echo esc_html(json_encode(Translations::get())) ?>">
</div>