#!/bin/bash

start=$(date +%s)
startStr=$(date)
a=0
while [ "$a" -lt 500000 ]
do
 # echo ${a} >> expire.log
  let "a++"
done
end=$(date +%s)
endStr=$(date)
time=$(( $end - $start ))
echo "starting at ${startStr}, ending at ${endStr}." >> expire.log
echo "total consuming $time s." >> expire.log
