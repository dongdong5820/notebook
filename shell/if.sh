#! /bin/bash
# if [ $a==$b ] $a前和$b后必须要有空格

a=10
b=12
if [ $a==$b ]
then
  echo "a 等于 b"
elif [ $a -gt $b ]
then
  echo "a 大于 b"
elif [ $a -lt $b ]
then
  echo " a 小于 b"
else
  echo "没有符合的条件"
fi
