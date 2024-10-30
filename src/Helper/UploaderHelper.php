<?php
namespace CompygoSocialFeed\Helper;

class UploaderHelper
{
    const IMAGE_EXT = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];

    public static function uploadFile($fileName, $file)
    {
        if (in_array($file['type'], self::IMAGE_EXT)) {
            $upload = wp_upload_bits($fileName, null, file_get_contents($file["tmp_name"]));

            return $upload['url'] ?: false;
        }
        
        return false;
    }

    public static function removeFile($fileName)
    {
        $pos = strpos($fileName, 'wp-content');
        $fileName = ABSPATH . ($pos >= 0 ? substr($fileName, $pos) : $fileName);

        wp_delete_file($fileName);
    }

    public static function downloadImage($name, $url)
    {
        $tmpFile = download_url($url);
        $newTmpFile = str_replace('.tmp', '.jpg', $tmpFile);
        rename($tmpFile, $newTmpFile);

        $file['tmp_name'] = $newTmpFile;
        $file['type'] = 'image/jpg';

        return self::uploadFile($name, $file) ?: $url;
    }
}