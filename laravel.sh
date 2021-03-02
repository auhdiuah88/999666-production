#!/bin/bash
step=1 #间隔的秒数

for (( i = 0; i < 60; i=(i+step) )); do
    php /home/wwwroot/sky-shop/artisan Result_Entry
    sleep $step
done

exit 0
