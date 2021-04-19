<?php

class UtilsHelper
{
    /**
     * 加密盐值
     */
    const APP_SIGN_SALT = 'BovmPRY8KSKfU8Ch';

    /**
     * 考虑到参数可能是数组的情况,递归处理
     * @param $allRequest
     * @param string $upValue
     * @return array
     */
    public static function makeSignStr($allRequest, $upValue='')
    {
        $text = [];
        if (!$allRequest || !is_array($allRequest)) {
            return $text;
        }
        ksort($allRequest, SORT_STRING);
        foreach($allRequest as $key => $value) {
            $keyStr = $upValue ? "{$upValue}[$key]" : $key;
            if (is_array($value)) {
                //参数值是数组的情况,数组内所有key也单独拿出来拼字符串
                $text = array_merge($text, self::makeSignStr($value, $keyStr));
            } else {
                $text[] = "{$keyStr}={$value}";
            }
        }

        return $text;
    }

    /**
     * 获取签名
     * @param $allRequest
     * @return string
     */
    public static function getSign($allRequest)
    {
        $returnStr = '';
        $text = self::makeSignStr($allRequest);
        if (empty($text)) {
            return $returnStr;
        }
        $text = implode('&', $text);
        $text .= self::APP_SIGN_SALT;
        $text = urlencode($text);
        $returnStr = sha1($text);

        return $returnStr;
    }
}