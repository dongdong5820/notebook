<?php
/**
 * Created by PhpStorm.
 * 约瑟夫环问题:
 * 罗马人攻占桥塔帕特后，41个人藏在一个山洞里躲过了这场浩劫。这41个人中，包括历史学家Josephus(约瑟夫)和他的一个朋友。
 * 剩余的39个人为了表示不向罗马人屈服，决定集体自杀。大家制定了一个自杀方案，所有这41个人围成一个圆圈，由第一个人开始顺时针报数，
 * 每报数为3的人就立刻自杀，然后再由下一个人重新开始报数，仍然是每报数为3的人就立刻自杀...，直到所有人都自杀身亡为止。
 * 约瑟夫和他的朋友并不想自杀，于是约瑟夫想到了一个计策，他们两个同样参与到自杀方案中，但是最后却躲过了自杀。请问，他们是怎么做到的？
 */

/**
 * 循环输出自杀身亡的编号
 * @param array $peoples 所有人数组
 * @param int $mod 每次循环mod次
 * @return array
 */
function cycleForJosephus(array $peoples = [], $mod = 3)
{
    $data = [];

    while (!empty($peoples)) {
        for ($i=1;$i<=$mod;$i++) {
            // 弹出数组的第一个元素
            $tmp = array_shift($peoples);
            if ($mod == $i) {
                $data[] = $tmp;
            } else {
                // 向数组尾部插入元素
                array_push($peoples, $tmp);
            }
        }
    }

    return $data;
}

/**
 * 递归输出自杀身亡的编号
 * @param array $peoples 所有人数组
 * @param int $mod 每次循环mod次
 * @return array
 */
function recursiveForJosephus(array $peoples = [], $mod = 3, $current = 0)
{
    static  $data;
    $number = count($peoples);
    $num = 1;
    if (1 == $number) {
        $data[] = $peoples[0];
        return $data;
    }
    while ($num++ < $mod) {
        $current++;
        $current = $current%$number;
    }
    $data[] = $peoples[$current];
    // 移除选定的元素，并用新元素替代
    array_splice($peoples, $current, 1);
    return recursiveForJosephus($peoples, $mod, $current);
}

$num = 41;
$mod = 3;
$peoples = range(1,$num);
//echo sprintf('自杀身亡的编号依次为：%s', implode(',', cycleForJosephus($peoples, $mod)));
echo sprintf('自杀身亡的编号依次为：%s', implode(',', recursiveForJosephus($peoples, $mod)));