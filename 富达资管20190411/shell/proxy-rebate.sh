#!/bin/bash
#盈利代理商返点-(每天停盘后的时间段 16-23点)
#*/2 16-22 * * * www /home_path/shell/proxy-rebate.sh >> /dev/null 2>&1

$(curl 'http://www.syzg888.com/cron/proxy-rebate')