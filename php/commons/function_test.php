<?php
require_once ROOT_PATH . '/commons/DateTimeHelper.php';
require_once ROOT_PATH . '/commons/UtilsHelper.php';
require_once ROOT_PATH . '/geoip/geoip2.phar';
use GeoIp2\Database\Reader;

/**
 * 序列化和反序列化
 */
function testSerialize()
{
    $a = <<<str
a:1:{s:4:"hash";s:60:"$2a$10$iG8rzM4KAGVzY0Zz2abAK.gnzIfNf0DA3LkcfM0gh5OuM59A2W8F.";}
str;
    var_dump(unserialize($a));exit;
}

/**
 * 日期时间函数测试
 */
function testDateTime()
{
    // 服务区时区
    echo DateTimeHelper::getDefaultTimezone();
    echo "\r\n";
    // 设置服务区时区
    DateTimeHelper::setDefaultTimezone('PRC');
    $dateStr = date('Y-m-d 00:00:00', strtotime('-6 month'));
    $timeStr = strtotime($dateStr);
    var_dump($timeStr, strtotime('-6 month')); exit;
}

/**
 * app请求签名测试
 */
function testGetSign()
{
    $params = [
        'version'  => 6,
        'module'   => 'captchaloging',
        'account'  => '15602961486',
        'password' => 'admin111',
        'ip'       => '172.21.21.21',
        'token'    => 'sdfasdfasf165a4sfd65a4sf6',
    ];
    $text = UtilsHelper::getSign($params);
    var_dump($params, $text);exit;
}

/**
 * 解析dubbo服务配置测试
 */
function testParseServiceConfig()
{
    $config = <<<str
{"providers":["hessian%3A%2F%2F10.130.84.151%3A11027%2Fcom.oneplus.membership.api.MemberQueryFacade%3Fanyhost%3Dtrue%26application%3Dmembership%26default.retries%3D0%26default.service.filter%3Ddefault%2ClogProviderFilter%26default.timeout%3D60000%26dispatcher%3Dall%26dubbo%3D2.5.3.4%26interface%3Dcom.oneplus.membership.api.MemberQueryFacade%26loadbalance%3Drandom%26methods%3DqueryMembershipInfoV2%2CqueryTvLandingPage%2CqueryMemberByUserId%2CqueryUserAndMemberInfo%2CqueryUserAndMemberInfoForTv%2CqueryMembershipInfoForWeb%2CqueryMembershipInfoUnlogin%2CqueryMembershipInfo%2CqueryMemberGrowthInfo%2CqueryAllTierLevel%26owner%3Doneplus%26pid%3D11%26revision%3D1.0.27%26server%3Djetty%26side%3Dprovider%26threadpool%3Dfixed%26threads%3D300%26timestamp%3D1618887535088%26version%3D1.0.0"],"consumers":["consumer%3A%2F%2F10.130.84.16%2Fcom.oneplus.membership.api.MemberQueryFacade%3Fapplication%3Dmall%26category%3Dconsumers%26check%3Dfalse%26dubbo%3D2.0.2%26interface%3Dcom.oneplus.membership.api.MemberQueryFacade%26lazy%3Dfalse%26methods%3DqueryMembershipInfoV2%2CqueryMembershipService%2CqueryUserAndMemberInfo%2CqueryMemberByUserId%2CqueryMembershipInfoForWeb%2CqueryMembershipInfoUnlogin%2CqueryMembershipInfo%2CqueryMemberGrowthInfo%2CqueryAllTierLevel%26pid%3D42%26qos.enable%3Dfalse%26release%3D2.7.4.1%26revision%3D1.0.0%26side%3Dconsumer%26sticky%3Dfalse%26timeout%3D10000%26timestamp%3D1618888380771%26validation%3Dfalse%26version%3D1.0.0","consumer%3A%2F%2F10.130.84.157%2Fcom.oneplus.membership.api.MemberQueryFacade%3Fapplication%3Dzeus%26category%3Dconsumers%26check%3Dfalse%26dubbo%3D2.0.2%26interface%3Dcom.oneplus.membership.api.MemberQueryFacade%26methods%3DqueryMemberByUserId%2CqueryUserAndMemberInfo%2CqueryMembershipInfoUnlogin%2CqueryMembershipInfoForWeb%2CqueryAllTierLevel%2CqueryMemberGrowthInfo%2CqueryMembershipInfo%26pid%3D41%26qos.enable%3Dfalse%26revision%3D1.0.0%26side%3Dconsumer%26timeout%3D10000%26timestamp%3D1618887569007%26validation%3Dfalse%26version%3D1.0.0","consumer%3A%2F%2F10.130.84.79%2Fcom.oneplus.membership.api.MemberQueryFacade%3Fapplication%3Dpromotioncenter-service%26category%3Dconsumers%26check%3Dfalse%26dubbo%3D2.0.2%26interface%3Dcom.oneplus.membership.api.MemberQueryFacade%26lazy%3Dfalse%26loadbalance%3Drandom%26methods%3DqueryMemberByUserId%2CqueryUserAndMemberInfo%2CqueryMembershipInfoForWeb%2CqueryMembershipInfoUnlogin%2CqueryAllTierLevel%2CqueryMemberGrowthInfo%2CqueryMembershipInfo%26owner%3Doneplus%26pid%3D126812%26protocol%3Ddubbo%26reference.filter%3DlogConsumerFilter%2Cdefault%26release%3D2.7.4.1%26retries%3D0%26revision%3D1.0.13%26side%3Dconsumer%26sticky%3Dfalse%26timeout%3D60000%26timestamp%3D1619628450405%26version%3D1.0.0","consumer%3A%2F%2F10.130.84.15%2Fcom.oneplus.membership.api.MemberQueryFacade%3Fapplication%3Dlevin%26category%3Dconsumers%26check%3Dfalse%26dubbo%3D2.0.2%26interface%3Dcom.oneplus.membership.api.MemberQueryFacade%26lazy%3Dfalse%26loadbalance%3Drandom%26methods%3DqueryMemberByUserId%2CqueryUserAndMemberInfo%2CqueryMembershipInfoForWeb%2CqueryMembershipInfoUnlogin%2CqueryAllTierLevel%2CqueryMembershipInfo%2CqueryMemberGrowthInfo%26owner%3Doneplus%26pid%3D11%26protocol%3Ddubbo%26release%3D2.7.4.1%26revision%3D1.0.13%26side%3Dconsumer%26sticky%3Dfalse%26timestamp%3D1618887389800%26version%3D1.0.0","consumer%3A%2F%2F10.130.84.10%2Fcom.oneplus.membership.api.MemberQueryFacade%3Fapplication%3Daccountcenter%26category%3Dconsumers%26check%3Dfalse%26default.reference.filter%3DlogConsumerFilter%2Cdefault%2C%26default.timeout%3D60000%26dubbo%3D2.5.3.4%26interface%3Dcom.oneplus.membership.api.MemberQueryFacade%26methods%3DqueryMembershipInfoV2%2CqueryTvLandingPage%2CqueryMemberByUserId%2CqueryUserAndMemberInfo%2CqueryUserAndMemberInfoForTv%2CqueryMembershipInfoUnlogin%2CqueryMembershipInfoForWeb%2CqueryMembershipInfo%2CqueryAllTierLevel%2CqueryMemberGrowthInfo%26owner%3Doneplus%26pid%3D11%26protocol%3Ddubbo%26retries%3D0%26revision%3D1.0.27%26side%3Dconsumer%26timeout%3D60000%26timestamp%3D1618887951117%26version%3D1.0.0"]}
str;
    $res = UtilsHelper::parseServiceConfig($config);
    echo json_encode($res);exit;
}

/**
 * 解析provider测试
 */
function testFormatProvider()
{
    $provider = <<<str
hessian://10.130.71.2:10882/com.oneplus.user.api.UserFacade?anyhost=true&application=user-service&bean.name=com.oneplus.user.api.UserFacade&deprecated=false&dubbo=2.0.2&dynamic=true&generic=false&group=in&interface=com.oneplus.user.api.UserFacade&loadbalance=random&methods=resetPassword,unbindThirdUser,getUserByThirdParty,modifyPassword,getOrAddGetUserByUnionType,modifyMobileEmailPassowrd,modifyEmail,modifyMobileInAllStatus,addUser,getUserByAccount,activeModifiedEmail,isUserExisted,modifyUser,getThirdUser,addUserByMobileAndThirdParty,unbindMobile,bindThirdUser,recoverUser,modifyMobileForced,listUserIdByUserQuery,isUserExistedInAllStatus,recoverAndLogin,bindThirdPartyToUser,modifyMobile,modifyEmailByPassword,resetNewPassword,authenticatePasswordInAllStatus,bindThirdPartyToUserByPassprot,getOrBindThirdUser,batchAddUser,unbindUserFromThirdParty,resetPasswordInAllStatus,modifyEmailInAllStatus,deactivatedUser,getUserByMobile,listUserByUserIds,getUserAndThirdAccounts,queryUserStatusByAccount,confirmUserOperation,addUserByCheckAllStatus,getUserByAccountInAllStatus,listDeactivatedUser,getUserByEmail,authenticatePassword,confirmDeleteUserData,modifyUserStatusAndEmailStatus,modifyIdentityInfo,modifyUserInAllStatus&owner=oneplus&pid=536&release=2.7.4.1-SNAPSHOT&retries=0&revision=4.1.0-SNAPSHOT&service.filter=default,logProviderFilter,securityProviderFilter&side=provider&timeout=60000&timestamp=1618906425798&version=4.0.0
str;
    setOptions();
    $provider = UtilsHelper::formatProvider($provider);
    var_dump($provider);exit;
}

/**
 * 设置选项
 */
function setOptions()
{
    //设置选项
    UtilsHelper::setOption('version', '4.0.0');
    UtilsHelper::setOption('group', '');
    //超时时间
    UtilsHelper::setOption('connectTimeout', 2);
    UtilsHelper::setOption('executeTimeout', 5);
    UtilsHelper::setOption('dubbo', '2.5.3.1-SNAPSHOT');
    UtilsHelper::setOption('loadbalance', 'random');
    UtilsHelper::setOption('owner', 'php');
    //pid
    UtilsHelper::setOption('pid', 4536);
    UtilsHelper::setOption('protocol', 'http');
    UtilsHelper::setOption('side', 'consumer');
}

/**
 * GeoIP测试
 */
function testGeoIp()
{
//    $ip = "27.159.237.36"; // Putian
//    $ip = "14.215.177.38"; // Guangzhou
//    $ip = "58.60.185.11"; // Shenzhen
//    $ip = "39.99.228.188"; // Hangzhou
    $ip = "128.101.101.101"; // 美国 明尼苏达州 明尼阿波利斯
    // geoip文件夹请至百度网盘下载
    $mmdbFile = ROOT_PATH. '/geoip/GeoIP2-City.mmdb';
    $reader = new Reader($mmdbFile);
    $record = $reader->city($ip);
    print("国家简码：" . $record->country->isoCode . "\n");
    print("国家编码：" . $record->country->name . "\n");
    print("国家编码(zh-cn)：" . $record->country->names['zh-CN'] . "\n");
    print("provinceCode：" . $record->mostSpecificSubdivision->isoCode . "\n");
    print("province：" . $record->mostSpecificSubdivision->name . "\n");
    print("province(zh-cn)：" . $record->mostSpecificSubdivision->names['zh-CN'] . "\n");
    print("city：" . $record->city->name . "\n");
    print("city(zh-cn)：" . $record->city->names['zh-CN'] . "\n");
    print("经度：" . $record->location->longitude . "\n");
    print("纬度：" . $record->location->latitude . "\n");

//    var_dump($record->city);
    exit;
}