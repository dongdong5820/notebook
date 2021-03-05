#!/bin/bash
# 定时执行更新公司库的脚本
# 后台执行 ./runCompany.sh > /dev/null 2>&1 &

start=$(date +%s)
startStr=$(date)

while true
do
  nowStr=$(date)
  flag=`sudo php /home/vagrant/webroot/ycg-fuczlm/protected/yii data-cleaning/run-company-v2`
  echo "$nowStr $flag" >> runCompany.log
  # 休眠2秒
  usleep 200
done

end=$(date +%s)
endStr=$(date)
time=$(( $end - $start ))

echo "starting at ${startStr}, ending at ${endStr}." >> runComany.log
