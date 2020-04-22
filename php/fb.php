<?php
// 命令行传参 php fb.php -a 5
$param = getopt('a:');

// 斐波拉契数列 第n位的值 1,1,2,3,5,8,13,21,...

// 2n次递归，效率较低
function simpleSum($n)
{
	if (1 == $n || 2 == $n) {
		return 1;
	}
	return simpleSum($n-1) + simpleSum($n-2);
}
echo simpleSum($param['a']);

echo "\r\n";

// (n次递归，效率较好)
function sum($n, $pre=1, $cur=1)
{
	if ($n <= 2) {
		return $cur;
	}
	list($pre, $cur) = [$cur,$pre+$cur];
	return sum($n-1, $pre, $cur);
}
echo sum($param['a']);