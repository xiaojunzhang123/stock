#!/bin/bash
#自动递延处理(14:40-15:00 每分钟)
#40-59/1 14 * * * www /home_path/shell/defer.sh >> /dev/null 2>&1

step=2 #间隔的秒数，不能大于60    
    
for (( i = 0; i < 60; i=(i+step) )); do
    $(curl 'http://www.syzg888.com/cron/defer')
    sleep $step
done

exit 0