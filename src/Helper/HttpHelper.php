<?php

namespace CompygoSocialFeed\Helper;

use CompygoSocialFeed\Model\Logger;
use CompygoSocialFeed\Model\ApiLimiter;

class HttpHelper
{
    /**
     * @param $requestUrl
     * @param $params
     * @param $method
     * @return false|mixed
     */
    static function requestData($requestUrl, $params)
    {
        // Increase API counter
        ApiLimiter::upTick();

        $params = self::getParamString($params);
        $requestUrl = $requestUrl . $params;
        $response = wp_remote_get($requestUrl);

        Logger::addLog(Logger::ERROR_SYSTEM, $requestUrl);
        if (is_wp_error($response)) {
            Logger::addLog(Logger::ERROR_SYSTEM, $response->get_error_message());
        } else {
            $json = json_decode(wp_remote_retrieve_body($response), true);

            return self::validateOnError($json) ? $json : false;
        }

        return false;
    }

    /**
     * @param $params
     * @return string
     */
    static public function getParamString($params)
    {        
        if(empty($params)) {
            return '';
        }

        $string = '?';
        $index = 1;

        foreach($params as $key => $value) {
            if (isset($value)) {
                $string .= $key . '=' . $value;

                if($index < count($params)) {
                    $string .= '&';
                }

                $index++;
            }
        }

        return $string;
    }

    /**
     * @param $json
     * @return bool
     */
    static private function validateOnError($json)
    {
        if (isset($json['error'])) {
            Logger::addLog(Logger::ERROR_API, $json['error']);

            return false;
        }

        return true;
    }

    /**
     * @param $key
     * @return string
     */
    static public function getRequestParam($key, $sanitizeType = 'string', $method = 'post')
    {
        $value = $method === 'post' ? $_POST : $_GET;

        if (isset($value[$key])) {
            switch ($sanitizeType) {
                case 'array':
                    $param = self::sanitizeArray($value[$key]);
                    break;
                case 'key':
                    $param = sanitize_key($value[$key]);
                    break;
                default:
                    $param = sanitize_text_field($value[$key]);
                    break;
            }

            return $param;
        }

        return '';
    }

    static private function sanitizeArray($array)
    {
        if (is_array($array)) {
            array_walk_recursive($array, function (&$item, $key) {
                $item = sanitize_text_field($item);
            });
        }

        return $array;
    }

    static public function isAdminPreview($method = 'post')
    {
        $paramFeed = HttpHelper::getRequestParam('feed', 'array', $method);
        $paramCgPreview = HttpHelper::getRequestParam('cg_preview', 'bool', $method);

        return !empty($paramFeed) || !empty($paramCgPreview);
    }
}