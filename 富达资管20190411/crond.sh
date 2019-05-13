#!/bin/sh
step=60
for (( i = 0; i < 60; i=(i+step) ));
do
cd /home/wwwroot/syzg
php think test
sleep $step
done
exit


