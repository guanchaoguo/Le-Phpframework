# 定时任务脚本集合

##  交收统计定时任务(每小时执行一次)
    
    0 * * * * root /usr/local/php/bin/php /data/web/ht/LiveGame-Platform/artisan CountDelivery &>/dev/null 2>&1

