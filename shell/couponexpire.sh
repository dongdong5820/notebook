#!/bin/bash
# 每秒执行优惠券过期数据脚本
# ./couponexpire.sh > /dev/null 2>&1

start=$(date +%s)
startStr=$(date)

while true
do
  flag=`sudo php /opt/htdocs/sunyanzi001/gearbest-task/artisan command:userCouponExpireEmail --dealSize=2000`
  if [ "$flag" == "today data is all done success!" ]
  then
    break
  fi
  echo "$flag" >> expire.log
  # 休眠1秒
  sleep 1
done

end=$(date +%s)
endStr=$(date)
time=$(( $end - $start ))

echo "starting at ${startStr}, ending at ${endStr}." >> expire.log
echo "total consuming $time s." >> expire.log
