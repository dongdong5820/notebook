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

echo 9>>2;

