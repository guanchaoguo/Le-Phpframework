## Mysql脚本 #

>脚本说明：系统维护添加用户原本时间记录
>以下为具体脚本：

    ALTER TABLE `system_maintain`
    MODIFY COLUMN `end_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '系统维护结束时间' AFTER `start_date`,
    MODIFY COLUMN `add_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' AFTER `add_user`,
    ADD COLUMN `user_start_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '用户添加的开始时间记录，和系统美东时间区别' AFTER `add_date`,
    ADD COLUMN `user_end_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '用户添加的结束时间记录，和系统美东时间区别' AFTER `user_start_date`;
    ALTER TABLE `system_maintain`
    MODIFY COLUMN `user_start_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '用户添加的开始时间记录，和系统美东时间区别' AFTER `add_date`;

>脚本说明：期数管理添加两个字段
>以下为具体脚本：

    ALTER TABLE `game_platform_delivery`
    ADD COLUMN `local_start_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '用户添加的开始时间记录，和系统美东时间区别' AFTER `update_time`,
    ADD COLUMN `local_end_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '用户添加的结束时间记录，和系统美东时间区别' AFTER `local_start_date`;
    ALTER TABLE `game_platform_delivery`
    MODIFY COLUMN `local_start_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '用户添加的开始时间记录，和系统美东时间区别' AFTER `update_time`,
    MODIFY COLUMN `local_end_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '用户添加的结束时间记录，和系统美东时间区别' AFTER `local_start_date`;


## Mysql数据库时区配置 （my.cnf文件配置） #

>配置说明：把mysql数据库的时区修改为美国东部时区--西五区
>以下为具体配置内容：

`    [mysqld]
`    default-time_zone = '-5:00'
