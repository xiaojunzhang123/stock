#!/bin/bash
#股票列表(半小时)
#*/30 * * * * www /home_path/shell/stock.sh >> /dev/null 2>&1

$(curl 'http://www.syzg888.com/cron/stock')