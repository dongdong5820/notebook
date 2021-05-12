<?php
define(ROOT_PATH, dirname(__FILE__));

require_once ROOT_PATH . '/commons/function_test.php';

// 序列化和反序列化
testSerialize();

// 日期时间测试
//testDateTime();

// app请求签名测试
//testGetSign();

// 解析dubbo服务配置测试
//testParseServiceConfig();

// 解析provider测试
//testFormatProvider();

// GeoIP测试
//testGeoIp();
