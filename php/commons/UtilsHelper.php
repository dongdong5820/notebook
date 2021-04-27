<?php
class UtilsHelper
{
    /**
     * 参数,其中：
     * version, group用于和服务端匹配,group细化在方法一级
     * timeout
     * dubbo
     * loadbalance
     *
     * @var array
     */
    public static $options = [];

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

    /**
     * 设置选项
     * @param $name
     * @param $value
     */
    public static function setOption($name, $value)
    {
        self::$options[$name] = $value;
    }

    /**
     * 解析dubbo服务配置
     * @param $config
     * @return array
     */
    public static function parseServiceConfig($config)
    {
        $data = [];
        if (empty($config)) {
            return $data;
        }
        $config = json_decode($config, true);
        if (!empty($config['providers'])) {
            foreach ($config['providers'] as $provider) {
                $data['providers'][] = urldecode($provider);
            }
        }
        if (!empty($config['consumers'])) {
            foreach ($config['consumers'] as $consumer) {
                $data['consumers'][] = urldecode($consumer);
            }
        }

        return $data;
    }

    /**
     * 目前只处理hessian前缀的情况
     * @param $provider
     * @return string
     */
    public static function formatProvider($provider)
    {
        $provider = str_replace('hessian:', 'http:', $provider);
        //处理选项
        $urlInfo = parse_url($provider);
        $arr     = explode('&', $urlInfo['query']);
        $params  = array();
        foreach ($arr as $item) {
            $arrItem             = explode('=', $item);
            $params[$arrItem[0]] = $arrItem[1];
        }

        //添加自定义的选项
        foreach (self::$options as $key => $value) {
            //超时时间dubbo的单位为毫秒
            if ('connectTimeout' == $key) {
                $value = $value * 1000;
            }
            $params[$key] = $value;
        }

        $provider = $urlInfo['scheme'] . '://' . $urlInfo['host'];
        if (isset($urlInfo['port']) && ($urlInfo['port'] != 80)) {
            $provider .= ':' . $urlInfo['port'];
        }
        $provider .= $urlInfo['path'] . '?';
        $i        = 0;
        foreach ($params as $key => $value) {
            if ($i > 0) {
                $provider .= '&';
            }
            $provider .= $key . '=' . urlencode($value);
            ++$i;
        }

        return $provider;
    }
}