# 交收统计表添加厅主用户名字段
    
    ALTER TABLE `game_platform_delivery_info`
    ADD COLUMN `real_name`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '厅主用户名' AFTER `p_name`;
    
