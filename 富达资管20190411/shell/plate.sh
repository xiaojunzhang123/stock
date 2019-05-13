#!/bin/bash
#板块行情
#*/1 * * * * www /home_path/shell/plate.sh >> /dev/null 2>&1
 $(curl 'http://www.syzg888.com/cron/plate')
#
#step=3 #间隔的秒数，不能大于60    
    
#for (( i = 0; i < 60; i=(i+step) )); do
#    $(curl 'http://www.syzg888.com/cron/plate')
#    sleep $step
#done

exit 0
