## 数据库修改脚本

    ALTER TABLE `lb_agent_user`
    MODIFY COLUMN `account_type`  tinyint(3) NULL DEFAULT 1 COMMENT '账号种类,1为正常账号,2为测试账号，3为调试账号' AFTER `lock_reason`;
    ALTER TABLE `system_message`
    ADD COLUMN `type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '公告发布的平台，1为PC端，2为移动端。默认为1' AFTER `update_date`;
    
     ALTER TABLE lb_agent_user ADD notify_url INT NULL DEFAULT NULL AFTER group_id;
     ALTER TABLE lb_agent_user CHANGE notify_url notify_url VARCHAR(100) NULL DEFAULT NULL COMMENT '第三方通知URL';
      
## MYSQL索引优化
    
    ALTER TABLE `lb_user`
    ADD INDEX `index_agent_name` (`agent_name`) USING BTREE ;
    
    ALTER TABLE `lb_agent_user`
    ADD INDEX `index_agent_name` (`agent_name`) USING BTREE ;

    
    ALTER TABLE `lb_user`
        ADD INDEX `index_add_date` (`add_date`) USING BTREE ;
    
    ALTER TABLE `lb_user`
            ADD INDEX `index_account_state` (`account_state`) USING BTREE ;
            
    ALTER TABLE `lb_user`
            ADD INDEX `index_hall_id` (`hall_id`) USING BTREE ;
            
    ALTER TABLE `lb_user`
            ADD INDEX `index_agent_id` (`agent_id`) USING BTREE ;
            
## 修改mysql数据库脚本

    ALTER TABLE `game_platform_banner`
        ADD COLUMN `p_name`  varchar(20) NOT NULL DEFAULT '' COMMENT '厅主登录名' AFTER `p_id`,
        ADD COLUMN `status`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '审核状态，0：未审核，1：已审核，2：审核不通过' AFTER `update_date`,
        ADD COLUMN `is_use`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '启用状态：0 未使用，1已使用' AFTER `status`,
        ADD COLUMN `sort`  int(10) NOT NULL DEFAULT 1 COMMENT '排序：数字越小越靠前' AFTER `is_use`,
        ADD INDEX `index_p_id_p_name` (`p_id`, `p_name`) USING BTREE ;

    ALTER TABLE `game_platform_logo`
        ADD COLUMN `p_name`  varchar(20) NOT NULL DEFAULT '' COMMENT '厅主登录名' AFTER `p_id`,
        ADD COLUMN `status`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '审核状态，0：未审核，1：已审核，2：审核不通过' AFTER `update_date`,
        ADD COLUMN `is_use`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '启用状态：0 未使用，1已使用' AFTER `status`,
        ADD COLUMN `sort`  int(10) NOT NULL DEFAULT 1 COMMENT '排序：数字越小越靠前' AFTER `is_use`,
        ADD INDEX `index_p_id_p_name` (`p_id`, `p_name`) USING BTREE ;
        
        ALTER TABLE `game_platform_banner`
        ADD COLUMN `url`  varchar(50) NOT NULL DEFAULT '' COMMENT 'url地址' AFTER `sort`;
        
## bak__cash_record添加字段

    ALTER TABLE `bak__cash_record`
         ADD COLUMN `agent_id`  int(11) NOT NULL DEFAULT '0' COMMENT '代理id' AFTER `user_name`,
         ADD COLUMN `hall_id`  int(11) NOT NULL DEFAULT '0' COMMENT '厅主id' AFTER `agent_id`;

## 游戏版本表
    CREATE TABLE `game_version` (
    `id`  int(11) NOT NULL AUTO_INCREMENT ,
    `label`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '游戏平台,0为视讯PC，1为视讯H5，2为视讯APP' ,
    `version_n`  varchar(50) NOT NULL DEFAULT '' COMMENT '版本号' ,
    `url`  varchar(50) NOT NULL DEFAULT '' COMMENT 'url地址' ,
    `forced_up`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否强制更新，0否，1是，默认0' ,
    `content`  text NOT NULL COMMENT '更新说明' ,
    `add_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' ,
    `update_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间' ,
    PRIMARY KEY (`id`),
    INDEX `index_label_version_n_add_time_forced_up` (`label`, `version_n`, `add_time`, `forced_up`) USING BTREE ,
    INDEX `index_version` (`version_n`) USING BTREE ,
    INDEX `index_add_time` (`add_time`) USING BTREE ,
    INDEX `index_forced_up` (`forced_up`) USING BTREE 
    )
    ENGINE=InnoDB
    DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
    COMMENT='在线更新，版本更新'
    ;
    

## 更改lb_suer表的add_date字段
    ALTER TABLE `lb_user`
    MODIFY COLUMN `add_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '注册时间' AFTER `alias`,
    MODIFY COLUMN `create_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间' AFTER `add_date`;

## 系统公告表添加了本地时间字段 
    ALTER TABLE `system_message`
    ADD COLUMN `user_start_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '用户添加的开始时间记录，和系统美东时间区别' AFTER `end_date`,
    ADD COLUMN `user_end_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '用户添加的结束时间记录，和系统美东时间区别' AFTER `user_start_date`;
    
## 游戏版本更新表 添加了本地更新时间字段
    ALTER TABLE `game_version`
    ADD COLUMN `user_update_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '用户添加的更新时间记录' AFTER `update_time`;
    
## 创建文档管理表
    CREATE TABLE `game_platform_document` (
    `id`  int NOT NULL AUTO_INCREMENT ,
    `title`  varchar(50) NOT NULL DEFAULT '' COMMENT '文档名称' ,
    `size`  varchar(8) NOT NULL DEFAULT '' COMMENT '文档大小' ,
    `path`  varchar(255) NOT NULL DEFAULT '' COMMENT '文档路径 ，相对路径' ,
    `add_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' ,
    PRIMARY KEY (`id`),
    INDEX `index_title` (`title`) USING BTREE 
    )
    ENGINE=InnoDB
    DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
    COMMENT='文档管理表'
    ;
    
## 文档表新增desc字段 2017/06/20
    ALTER TABLE `game_platform_document`
    ADD COLUMN `desc`  varchar(255) NOT NULL DEFAULT '' COMMENT '文档备注、描述' AFTER `path`;
    
 ## 活跃玩家统计表
    
    CREATE TABLE `statis_active_user` (
      `id` int(10) unsigned NOT NULL,
      `hall_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '厅主ID',
      `hall_name` varchar(25) NOT NULL DEFAULT '' COMMENT '厅主登录名称',
      `agent_id` int(11) NOT NULL DEFAULT '0' COMMENT '代理商id',
      `agent_name` varchar(25) NOT NULL DEFAULT '' COMMENT '代理商登录名称',
      `add_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '记录添加时间',
      `date_year` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '所属日期年份',
      `date_month` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '所属日期月份',
      `date_day` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '所属日期天',
      `active_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活跃会员数量',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代理商活跃会员统计表';
    
 ## 用户在线统计表
 
    CREATE TABLE `statis_online_user` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `hall_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '厅主ID',
      `hall_name` varchar(25) NOT NULL DEFAULT '' COMMENT '厅主登录名称',
      `agent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代理商ID',
      `agent_name` varchar(25) NOT NULL DEFAULT '' COMMENT '代理商登录名称',
      `online_user` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '在线用户数量',
      `date_scale` smallint(4) NOT NULL DEFAULT '0' COMMENT '所属时间刻度数（一天1440分钟，每10分钟一个刻度，所以又144个刻度）',
      `add_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '记录添加时间',
      `date_year` smallint(4) unsigned NOT NULL DEFAULT '0' COMMENT '所属时间年份',
      `date_month` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '所属时间月份',
      `date_day` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '所属时间天',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1736 DEFAULT CHARSET=utf8 COMMENT='代理商用户在线统计表';
    
 ## 系统公告表
    CREATE TABLE `system_message` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL COMMENT '标题',
      `coment_cn` text NOT NULL COMMENT '公告内容',
      `coment_en` varchar(255) NOT NULL DEFAULT '' COMMENT '英文公告（暂时没用到）',
      `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '公告开始时间',
      `end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '公告结束时间',
      `user_start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '用户添加的开始时间记录，和系统美东时间区别',
      `user_end_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '用户添加的结束时间记录，和系统美东时间区别',
      `state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '公告的启用状态，0为未启用，1为启用，2为删除，默认为0',
      `add_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
      `update_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
      `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '公告发布的平台，1为PC端，2为移动端。默认为1',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8 COMMENT='系统公告表';
   
  ##系统公告修改type字段，新增一个所有平台2017-09-18
    ALTER TABLE `system_message`
    MODIFY COLUMN `type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '公告发布的平台，0为所有平台，1为视讯PC，2为视讯H5，3为视讯APP。默认为0' AFTER `update_date`;
    
 ##logo表字段修改 2017-09-19
    ALTER TABLE `game_platform_logo`
    MODIFY COLUMN `label`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '所属平台,0为视讯PC，1为视讯H5，2为视讯APP' AFTER `title`;
 ## banner表字段修改2017-09-19
    ALTER TABLE `game_platform_banner`
    MODIFY COLUMN `label`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属平台,0为视讯PC，1为视讯H5，2为视讯APP' AFTER `play_type`;
    
##修改banner表字段 2017-0926
    ALTER TABLE `game_platform_banner`
    MODIFY COLUMN `url`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'url地址' AFTER `sort`;
    


