<?php
// 命令行传参 php fb.php -a 5
$param = getopt('a:');

// 斐波拉契数列 前n位的值 1,1,2,3,5,8,13,21,...
// 斐波拉契数列 f(n) = f(n-1) + f(n-2) n>=3

// 2n次递归，效率较低
function simpleSum($n)
{
	if (1 == $n || 2 == $n) {
		return 1;
	}
	return simpleSum($n-1) + simpleSum($n-2);
}

// n次递归，效率较好
function sum($n, $pre=1, $cur=1)
{
	if ($n <= 2) {
		return $cur;
	}
	list($pre, $cur) = [$cur,$pre+$cur];
	return sum($n-1, $pre, $cur);
}

// 循环方式-for, 时间复杂度O(n),时间复杂度随着n的增长而线性增长
function forSum($n)
{
    $f0 = $f1 = 1;
    if ($n <= 2) {
        return $f1;
    }
    for ($i=3;$i<=$n;$i++) {
        list($f0,$f1) = [$f1, $f0+$f1];
    }
    return $f1;
}

// 循环方式-while, 时间复杂度O(n),时间复杂度随着n的增长而线性增长
function whileSum($n)
{
    list($a, $b, $c) = [0,1,1];
    while ($n) {
        list($a, $b, $c) = [$b,$c,$b+$c];
        $n--;
    }

    return $a;
}

$list = [];
for ($i=1;$i<=$param['a'];$i++) {
    //$list[] = simpleSum($i);
    //$list[] = sum($i);
    //$list[] = forSum($i);
    $list[] = whileSum($i);
}
echo sprintf('斐波拉契数列前%d项为%s', $param['a'], implode(',', $list));
