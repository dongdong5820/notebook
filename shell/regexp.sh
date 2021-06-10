#!/bin/sh

# 判断正则是否匹配
# str="M1622198861456"
str="你好的"
pat="^[A-Z]{1}[0-9]{13}$"
if [[ "$str" =~ $pat ]];then
   echo "未修改昵称"
else
   echo "修改过昵称:"$str 
fi

# 判断两个数是否相等
num1=$[2*3]
num2=$[1+5]
if test $[num1] -eq $[num2]
then
    echo '两个数字相等!'
else
    echo '两个数字不相等!'
fi

# 匹配IP
newip='192.168.1.1009'
reg='^([0-9]{1,3}.){3}[0-9]{1,3}$'
if [[ "$newip" =~ $reg ]];then
    echo '找到了ip地址'
else
    echo '未找到ip: '$newip 
fi
