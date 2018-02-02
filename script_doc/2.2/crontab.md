## 玩家在线统计定时任务，每10分钟一次

    */10 * * * * /usr/local/php7/bin/php /usr/local/nginx/html/platform/artisan CountUserOnline &>/dev/null 2>&1
                                     
## 活跃玩家定时任务， 每10分钟一次
    
    */10 * * * * /usr/local/php7/bin/php /usr/local/nginx/html/platform/artisan ActiveUser &>/dev/null 2>&1

                                     
## 交收统计定时任务， 每10分钟一次
    
    */10 * * * * /usr/local/php7/bin/php /usr/local/nginx/html/platform/artisan CountDelivery &>/dev/null 2>&1