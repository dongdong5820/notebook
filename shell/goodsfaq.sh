#! /bin/bash
# 每秒执行商品faq翻译脚本
# nohup ./goodsfaq.sh url count 2>&1 &
# $1:执行的url  $2:执行多少次(不传默认10)

if [ "" = "$1" ]
then
  callUrl="http://www.baidu.com"
else
  callUrl=$1
fi
if [ "" = "$2" ]
then
  max=10
else
  max=$2
fi
i=0
cmd=`sudo curl ${callUrl}`
logFile="goodsfaq.log"
while (($i<$max))
do
  flag=${cmd}
  let "i++"

  echo "$flag" >> ${logFile}
  # 休眠1秒
  sleep 1
done
