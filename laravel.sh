#!/bin/bash
step=1 #间隔的秒数

for (( i = 0; i < 60; i=(i+step) )); do
    /usr/local/php/bin/php /home/wwwroot/sky-shop2/artisan Result_Entry
    sleep $step
done

exit 0
