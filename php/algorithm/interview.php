<?php
// 算术运算题(明源云)
$a=1;$b=2;$c=3;$d=4;$e=5;
$b=&$a;
echo sprintf("a:%s, b:%s, c:%s, d:%s, e:%s\r\n", $a,$b,$c,$d,$e);
$b = "30$a";
echo sprintf("a:%s, b:%s, c:%s, d:%s, e:%s\r\n", $a,$b,$c,$d,$e);
/* xx=$b++：先将$b带入表达式，$b再自增1;  xx=++$b：$b先自增1，再将$b带入表达式
 * $c=$b++; ==> $c=$b;$b++;
 * $c=++$b; ==> $b++;$c=$b;
 * $c=$b+=2; ==> $b=$b+2; $c=$b;
*/
$e = $c = ($b++);
echo sprintf("a:%s, b:%s, c:%s, d:%s, e:%s\r\n", $a,$b,$c,$d,$e);
$d = ($c > $b) ? ($a+=2) : (--$b);
echo sprintf("a:%s, b:%s, c:%s, d:%s, e:%s\r\n", $a,$b,$c,$d,$e);
$e = implode(',', [$c,$e]);
echo sprintf("a:%s, b:%s, c:%s, d:%s, e:%s\r\n", $a,$b,$c,$d,$e);

/**
 * 递归题(富途)
 * 写一段代码实现以下逻辑
$array = [
    'Name' => 'testname',
    'Data' => [
        'title' => 'testtitle',
        'date' => '20210225',
        'list' => [
            'id1' => '123',
            'Di2' => '321',
        ],
    ],
    'Page' => '3',
];
 * 已知给定一个多维数据(如上)，需要将数组中所有的key和value拼接成一段字符串，输出拼接后字符串的md5值，逻辑如下：
 * 1、数组中所有的字段和值，按照k1=v1;k2=v2...的格式，拼接字符串
 * 2、要求所有的key为小写且按照key的字母升序排序
 * 3、多维数组则以.拼接key的值。如$a['abc']['def'] = 'test',则生成abc.def=test
 * 4、如上述数组，最终生成的字符串为:
 * data.date=20210225;data.list.di2=321;data.list.id1=123;data.title=testtitle;name=testname;page=3
 * @param $arr
 * @param string $index
 * @return bool|string
 */
function getArrayElem($arr, $index='') {
    static $elemStr;
    if (!is_array($arr)) {
        return false;
    }
    $sortKey = array_map('strtolower', array_keys($arr));
    array_multisort($sortKey, SORT_ASC, $arr);
    foreach ($arr as $key => $val) {
        if (!empty($index)) {
            $key = strtolower(sprintf('%s.%s', $index, $key));
        } else {
            $key = strtolower($key);
        }

        if (is_array($val)) {
            getArrayElem($val, $key);
        } else {
            $elemStr .= sprintf('%s=%s;', $key, $val);
        }
    }

    return trim($elemStr, ';');
}

$array = [
    'Name' => 'testname',
    'Data' => [
        'title' => 'testtitle',
        'date' => '20210225',
        'list' => [
            'id1' => '123',
            'Di2' => '321',
        ],
    ],
    'Page' => '3',
];
$str = getArrayElem($array);
echo sprintf('数组处理后值: %s', $str);
echo "\r\n";

/**
 * 阶梯计费(富途)
 * 用户在平台进行交易，需要交平台使用费，平台使用费的梯度收费方案如下，请用代码实现一个函数，计算用户一个月共计交费多少元。
 * 每月累计订单数  每笔订单(元)
 * 1-5          30.00
 * 6-20         15.00
 * 21-50        10.00
 * 51-100       9.00
 * 101-500      8.00
 * 501-1000     7.00
 * 1001及以上    1.00
 *如 23笔的费用: 5*30 + (20-5)*15 + (23-20)*10
 *
 * @param $used  int 订单数
 * @param $rules array 规则
 * @return int
 */
function getFeel($used, $rules){
    $sum = 0;
    foreach ($rules as $key => $value) {
        // 小于计费段起始值，结束循环
        if ($used < $value[0]) {
            break;
        }
        // 累加前期计费
        if ($used > $value[1]) {
            $sum += $key * ($value[1] - $value[0]);
        }
        // 最后计费段内
        if ($value[0] < $used && $used <= $value[1]) {
            $sum += $key * ($used - $value[0]);
        }
    }

    return $sum;
}
$rules = [
    '30.00' => [0,5],
    '15.00' => [5,20],
    '10.00' => [20,50],
    '9.00' => [50,100],
    '8.00' => [100,500],
    '7.00' => [500,1000],
    '1.00' => [1000, PHP_INT_MAX],
];
/*
PHP_INT_MAX: php整数integer最大值
9223372036854775807: 九千二百二十二亿亿三千三百七十二万亿零三百六十八亿五千四百七十七万五千八百零七)
*/
$used = 53;
echo sprintf("每月累计%d笔订单，使用费为: %d元", $used, getFeel($used, $rules));