
## 厅主子账号角色组表修改描述
    
    ALTER TABLE `agent_role_group`
    COMMENT='厅主子账号角色组';
    
## 厅主子账号角色组权限表修改描述

    ALTER TABLE `agent_role_group_menus`
    COMMENT='厅主子账号角色组权限表';

    
## 修改厅主系统账户具体所属组权限

    ALTER TABLE `agent_menus`
    MODIFY COLUMN `role_id`  int(11) NOT NULL DEFAULT 0 COMMENT '所属角色组ID（厅主子账户组ID）' AFTER `parent_id`;
    

## 修改厅主菜单字段描述

    ALTER TABLE `agent_system_menus`
    MODIFY COLUMN `class`  tinyint(1) NULL DEFAULT 0 COMMENT '菜单类型分类，0为通用菜单，1为厅主类菜单，2为代理类菜单，默认为0通用菜单' AFTER `title_en`;
    
## 修改现金流注释 2017.7.19
 ALTER TABLE `bak__cash_record`
    ADD COLUMN `agent_id`  int(11) NOT NULL DEFAULT '0' COMMENT '代理id' AFTER `user_name`;
 ALTER TABLE `bak__cash_record`
        ADD COLUMN `hall_id`  int(11) NOT NULL DEFAULT '0' COMMENT '厅主id' AFTER `agent_id`;
    ALTER TABLE `bak__cash_record`
    MODIFY COLUMN `type`  int(11) NOT NULL COMMENT '操作类型,1转帐,2打赏,3优惠退水,4线上变更,5公司入款,6优惠冲销,7视讯派彩,8系统取消出款,9系统拒绝出款,10取消派彩变更,21旗舰厅下注，22为至尊厅下注，23为金臂厅下注，24为贵宾厅下注,31视讯取消退回,32旗舰厅取消退回,33金臂厅取消退回,34至尊厅取消退回' AFTER `hall_id`;

##新增取消派彩日志表2017.7.19
    CREATE TABLE `bak__exception_cash_log` (
    `id`  int NOT NULL AUTO_INCREMENT ,
    `user_order_id`  varchar(255) NOT NULL COMMENT '注单明细记录id' ,
    `uid`  int NOT NULL COMMENT '用户id' ,
    `user_name`  varchar(255) NOT NULL COMMENT '用户登录名' ,
    `agent_id`  int NOT NULL COMMENT '代理商id' ,
    `agnet_name`  varchar(255) NOT NULL COMMENT '代理商登录名' ,
    `hall_id`  int NOT NULL COMMENT '厅主id' ,
    `hall_name`  varchar(255) NOT NULL COMMENT '厅主名称' ,
    `round_no`  varchar(255) NOT NULL COMMENT '局id（注单明细表的round_no）' ,
    `payout_win`  decimal NOT NULL COMMENT '派彩金额' ,
    `user_money`  decimal NOT NULL COMMENT '用户余额' ,
    `bet_time`  datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '下注时间' ,
    `desc`  varchar(255) NOT NULL COMMENT '备注' ,
    `action_user`  varchar(255) NOT NULL COMMENT '操作人' ,
    `action_user_id`  int NOT NULL COMMENT '操作人id' ,
    `action_passivity`  varchar(255) NOT NULL COMMENT '操作对象' ,
    `add_time`  datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '添加时间' ,
    PRIMARY KEY (`id`)
    )
    ENGINE=InnoDB
    DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
    COMMENT='取消派彩日志'
    ;
    

 ## 交收毛利润字段允许带符号（负数）
 
    ALTER TABLE `game_platform_delivery_info`
    MODIFY COLUMN `platform_profit`  decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '期数对应厅主毛利润' AFTER `p_id`;
    

## 修改交收数据利润区间显示
    
    ALTER TABLE `game_platform_delivery_info`
    MODIFY COLUMN `scale`  varchar(1000) NOT NULL DEFAULT 0.00 COMMENT '平台占成比例' AFTER `platform_profit`;
    
## 修改现金流表
    ALTER TABLE `bak__cash_record`
    MODIFY COLUMN `order_sn`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '流水号，如果是游戏服务端操作，写每一局ID' AFTER `id`;
## 修改user_chart_info表字段
    ALTER TABLE `bak__user_chart_info`
    CHANGE COLUMN `uc_id` `id`  bigint(20) UNSIGNED NOT NULL FIRST ,
    DROP INDEX `uc_id_UNIQUE` ,
    ADD UNIQUE INDEX `id_UNIQUE` (`id`) USING BTREE ;
## 删除user_order表字段
    ALTER TABLE `bak__user_order`
    DROP COLUMN `order_sn`;
    
## 外网的代理商表要添加
    厅主名：tlebo01
    代理名：dlebo01 
    密码都为：123456 
 


    

    

