#!/bin/bash
# 执行abc关键词异步导入脚本
# 后台执行 ./abcindexkeywords.sh > /dev/null 2>&1 &

while true
do 
  flag=`php /opt/htdocs/sunyanzi002/gearbest-task/artisan command:syncAbcIndexKeywords --size=100`
  echo "$lag" >> /home/www/abcIndexKeywords.log
  # 休眠60秒
  sleep 60
done

