## 添加新字段脚本

    ALTER TABLE `game_platform_delivery_info`
    ADD COLUMN `local_start_date`  datetime NOT NULL DEFAULT "0000-00-00 00:00:00" COMMENT '用户添加期数的开始时间记录，和系统美东时间区别' AFTER `is_over`,
    ADD COLUMN `local_end_date`  datetime NOT NULL DEFAULT "0000-00-00 00:00:00" COMMENT '用户添加期数的结束时间记录，和系统美东时间区别' AFTER `local_start_date`;
    
    ALTER TABLE `game_platform_delivery_info`
    MODIFY COLUMN `issue`  char(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '交收期数' AFTER `id`;
    
