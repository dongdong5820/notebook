#! /bin/bash
# 每秒执行商品faq翻译脚本
# nohup ./goodsfaq.sh url count 2>&1 &
# $1:执行的url $2:循环的次数

if [ "" = "$1" ]
then
  callUrl="http://www.baidu.com"
else
  callUrl=$1
fi
if [ "" = "$2" ]
then
  max=5
else
  max=$2
fi
cmd=`sudo curl "${callUrl}"`
logFile="goodsfaq.log"
i=0
while (($i<$max))
do
  flag=${cmd}
  let "i++"

  echo "$flag" >> ${logFile}
  # 休眠1秒
  sleep 1
done
