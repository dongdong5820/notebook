<?php
/**
 * Created by PhpStorm.
 * User: ADMIN
 * Date: 2021-02-28
 * Time: 13:12
 * desc: PHP最常见最经典算法题
 */

/**
 * title: 一群猴子排成一圈，按1，2，…，n依次编号。然后从第1只开始数，数到第m只,把它踢出圈，从它后面再开始数，再数到第m只，在把它踢出去…，
 * 如此不停的进行下去，直到最后只剩下一只猴子为止，那只猴子就叫做大王。要求编程模拟此过程，输入m、n, 输出最后那个大王的编号。
 *
 * @param $n
 * @param $m
 * @return mixed
 */
function kings($n, $m){
    $monkeys = range(1, $n);
    $i=0;
    while (count($monkeys) > 1) {
        if (($i+1)%$m == 0) { // $i为数组下标，$i+1为猴子标号
            unset($monkeys[$i]);
        } else {
            // 如果余数不等于0，则把数组下标2为$i的放在最后，形成圆形结构
            array_push($monkeys, $monkeys[$i]);
            unset($monkeys[$i]);
        }
        $i++;
    }

    return current($monkeys);  // 猴子数量为1，登出猴王
}
echo sprintf("猴王编号: %d", kings(41,3));
echo "\r\n";

/**
 * title: 有一母牛，到4岁可生育，每年一头，所生均是一样的母牛，到15岁绝育，不再能生，20岁死亡，问n年后有多少头牛。
 * @param $year
 * @return mixed
 */
function cow($year) {
    $pigs = [0]; // 猪圈年龄
    for ($a=1; $a<=$year;$a++) {
        foreach ($pigs as $k=>&$age) {
            // 已死亡
            if ($age==-2) {
                continue;
            }
            $age++;
            // 能生育
            if ($age>=4 && $age<15) {
                $pigs[count($pigs)] = -1;
            }
            //标记死亡
            if ($age >= 20) {
                $age = -2;
            }
        }
    }
    //过滤掉死亡的猪
    $alivePig = array_filter($pigs, function ($age) {
        return $age != -2;
    });
    return count($alivePig);
}
$n=9;
echo sprintf("%d年后有%d头牛", $n, cow($n));
echo "\r\n";

/**
 * title: 快速排序
 * @param $arr
 * @return array
 */
function quickSort($arr) {
    $length = count($arr);
    if ($length <= 1) {
        return $arr;
    }
    // 选择一个元素作为基准
    $base = $arr[0];
    //初始化两个数组
    $leftArray = array();  //小于基准的
    $rightArray = array();  //大于基准的
    for ($i=1; $i<$length;$i++) {
        if ($arr[$i] < $base) {
            $leftArray[] = $arr[$i];
        } else {
            $rightArray[] = $arr[$i];
        }
    }
    // 再分别对左右两边数组进行同样的排序方式，递归调用
    $leftArray = quickSort($leftArray);
    $rightArray = quickSort($rightArray);

    // 合并
    return array_merge($leftArray, [$base], $rightArray);
}
$arr = [1,5,6,98,3];
echo sprintf("数列[%s]排序后的值:[%s]", implode(',', $arr), implode(',', quickSort($arr)));
echo "\r\n";

/**
 * title: 引用算法，生成树状结构(分类树)
 * @param $data
 * @return array
 */
function getChildTree($data) {
    $tree = [];
    $items = array_column($data, null, 'id');
    foreach ($items as $key => $item) {
        if (isset($items[$item['parentId']])) {
            $items[$item['parentId']]['children'][] = &$items[$key];
        } else {
            $tree[] = &$items[$key];
        }
    }

    return $tree;
}
$data = [
    ['id'=>1,'cate'=>'服装','parentId'=>0],
    ['id'=>2,'cate'=>'电子','parentId'=>0],
    ['id'=>3,'cate'=>'女装','parentId'=>1],
    ['id'=>4,'cate'=>'连衣裙','parentId'=>3],
    ['id'=>5,'cate'=>'裙子','parentId'=>3],
    ['id'=>6,'cate'=>'男装','parentId'=>1],
    ['id'=>7,'cate'=>'手机','parentId'=>2],
];
$result = getChildTree($data);
echo sprintf("数组结构为: %s", json_encode($result));
echo "\r\n";