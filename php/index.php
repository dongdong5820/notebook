<?php
require_once 'commons/UtilsHelper.php';

// app请求签名
$params = [
    'version' => 6,
    'module' => 'captchaloging',
    'account' => '15602961486',
    'password' => 'admin111',
    'ip' => '172.21.21.21',
    'token' => 'sdfasdfasf165a4sfd65a4sf6',
];
$text = UtilsHelper::getSign($params);
var_dump($text);