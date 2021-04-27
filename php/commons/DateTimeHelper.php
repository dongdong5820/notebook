<?php
class DateTimeHelper
{
    /**
     * 获取服务器默认时区
     * @return string
     */
    public static function getDefaultTimezone(): string
    {
        return date_default_timezone_get();
    }

    /**
     * 设置服务器时区
     * @param $timezoneId, 如 UTC, PRC
     */
    public static function setDefaultTimezone($timezoneId)
    {
        date_default_timezone_set($timezoneId);
    }
}