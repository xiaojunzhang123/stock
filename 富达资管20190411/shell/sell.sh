#!/bin/bash
#订单爆仓、止盈、止损(每2秒一次)
#*/1 * * * * www /home_path/shell/defer.sh >> /dev/null 2>&1

step=2 #间隔的秒数，不能大于60    
    
for (( i = 0; i < 60; i=(i+step) )); do
    $(curl 'http://www.syzg888.com/cron/sell')
    sleep $step
done

exit 0