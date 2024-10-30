<?php
namespace CompygoSocialFeed\Helper;

use CompygoSocialFeed\Model\Logger;
use CompygoSocialFeed\Model\Feed;
use \Exception;

class Validator
{
    /**
     * @param $data
     * @return bool
     */
    static public function validateOptions($data)
    {
        try {
            if(!self::isArray($data['general'])) { throw new Exception(); }
            if(!self::isString($data['general']['license_key'])) {  throw new Exception(); }
            if(!self::isArray($data['feeds'])) { throw new Exception(); }
            if(!self::isString($data['feeds']['custom_css'])) {  throw new Exception(); }
            if(!self::isArray($data['strings'])) { throw new Exception(); }
        } catch (Exception $e) {
            Logger::addLog(Logger::ERROR_VALIDATION, 'Options validation is failed');
            return false;
        }
        return true;
    }

    /**
     * @param $data
     * @return bool
     */
    static public function validateFeed($data)
    {
        try {
            $map = Feed::FEED_MAP_VALIDATOR;
            $arrayIterator = new \RecursiveArrayIterator($map);

            if(!self::recursiveValidate($data, $map)) { throw new Exception(); }

        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    static protected function recursiveValidate($array, $map)
    {
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $status = self::recursiveValidate($item, $map[$key]);

                if ($status === false) {
                    return false;
                }
            } else {
                if(isset($map[$key]) && !self::validateValue($item, $map[$key])) {
                    return false;
                }
            }
        }

        return true;
    }

    static protected function validateValue($value, $type)
    {
        $result = false;

        if (!empty($value)) {
            switch ($type) {
                case 'int':
                    $result = self::isNumber($value);
                    break;
                case 'string':
                    $result = self::isString($value);
                    break;
                case 'bool':
                    $result = self::isBool($value);
                    break;
                case 'url':
                    $result = self::isUrl($value);
                    break;
                case 'color':
                    $result = self::isColor($value);
                    break;
                case null:
                    $result = true;
            }
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * @param $data
     * @return bool
     */
    static public function validateSource($data)
    {
        try {
            if(!self::isString($data['id'])) { throw new Exception(); }
            if(!self::isString($data['id'])) { throw new Exception(); }
        } catch (Exception $e) {
            Logger::addLog(Logger::ERROR_VALIDATION, 'Source validation is failed');
            return false;
        }
        return true;
    }

    /**
     * @param $data
     * @return bool
     */
    static public function required($data)
    {        
        return isset($data) && !empty($data);
    }

    /**
     * @param $data
     * @param $length
     * @return bool
     */
    static public function isLength($data, $length)
    {        
        return strlen($data) <= $length;
    }

    /**
     * @param $data
     * @return bool
     */
    static public function isArray($data)
    {
        return is_array($data);
    }

    /**
     * @param $data
     * @return bool
     */
    static public function isLetter($data)
    {
        return is_string($data) && strlen($data) == 1;
    }

    /**
     * @param $data
     * @return bool
     */
    static public function isBool($data)
    {
        return is_bool($data) || $data === 'true' || $data === 'false' || $data === 1 || $data === 0;
    }

    /**
     * @param $data
     * @return bool
     */
    static public function isString($data)
    {
        return is_string($data);
    }

    /**
     * @param $data
     * @return bool
     */
    static public function isNumber($data)
    {
        return is_numeric($data);
    }

    /**
     * @param $data
     * @return bool
     */
    static public function isColor($data)
    {
        $color = substr($data, 1, 8);
        if ($data[0] === '#' && ctype_xdigit(strtoupper($color)) && (strlen($color)==3 || strlen($color)==6 || strlen($color)==8)) {
            return true;
        }
        
        return false;
    }

    static public function isUrl($data)
    {
        return (bool)esc_url_raw($data);
    }

    /**
     * @param $status
     * @param $text
     * @param $param
     * @return array[]
     */
    static public function getMessage($status, $text = null, $param = [])
    {
        return [
            'message' => [
                'status' => $status,
                'text' => $text ? $text : __('Something went wrong'),
                'param' => $param
            ]
        ];
    }
}