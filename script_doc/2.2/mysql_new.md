## 修改 api调用日志字段
    ALTER TABLE `bak__api_log` MODIFY COLUMN `end_time`  timestamp NOT NULL DEFAULT '2002-11-13 07:37:23' COMMENT '调用结束时间' AFTER `start_time`;
## 新增 api统计统计登录表
    CREATE TABLE `bak__api_login_log` (
     `id` int(11) DEFAULT NULL,
     `agent` varchar(255) DEFAULT NULL COMMENT '代理名称',
     `postData` varchar(255) DEFAULT NULL COMMENT '请求的参数',
     `apiName` varchar(255) DEFAULT NULL COMMENT '请求接口名称',
     `ip_info` varchar(255) DEFAULT NULL COMMENT '代理请求的IP ',
     `log_type` varchar(255) DEFAULT NULL COMMENT '登录类型 login 登录 api 接口请求',
     `code` varchar(255) DEFAULT NULL COMMENT '返回状态码  0  正常',
     `text` varchar(255) DEFAULT NULL COMMENT '返回结果字段   Success',
     `result` varchar(255) DEFAULT NULL COMMENT 'result 请求结果数据',
     `start_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '开始时间',
     `end_time` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '结束时间'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='api统计统计登录';
    
    
## 新增 联调代理接口统计表
    CREATE TABLE `bak__api_statistics_log` (
      `id` int(11) DEFAULT NULL,
      `apiName` varchar(255) DEFAULT NULL COMMENT '联调接口名称',
      `agent` varchar(255) DEFAULT NULL COMMENT '联调厅主',
      `status` tinyint(255) DEFAULT NULL COMMENT '是否联调 默认 0  1 联调',
      `succeds` int(255) DEFAULT NULL COMMENT '成功次数 默认 0',
      `sum` varchar(255) DEFAULT NULL COMMENT '联调总次数 默认 0'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='联调代理接口统计';

## 修改 现金记录表字段
    ALTER TABLE `bak__cash_record` MODIFY COLUMN `type` INT (11) NOT NULL COMMENT '操作类型,1转帐,2打赏,3优惠退水,4线上变更,5公司入款,6优惠冲销,7视讯派彩,8系统取消出款,9系统拒绝出款,10取消派彩变更,21旗舰厅下注，22为至尊厅下注，23为金臂厅下注，24为贵宾厅下注,31视讯取消退回,32旗舰厅取消退回,33金臂厅取消退回,34至尊厅取消退回,35贵宾厅取消退回' AFTER `hall_id`;
    ALTER TABLE `bak__cash_record` MODIFY COLUMN `user_money`  decimal(16,2) NULL DEFAULT NULL COMMENT '操作完后用户余额' AFTER `status`;

## 修改 取消派彩日志表字段
    ALTER TABLE `bak__exception_cash_log` ADD COLUMN `order_sn`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '订单号，等于user_chart_Info记录ID' AFTER `user_order_id`;
    ALTER TABLE `bak__exception_cash_log` ADD COLUMN `agent_name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '代理商登录名' AFTER `agent_id`;
    ALTER TABLE `bak__exception_cash_log` ADD COLUMN `before_user_money`  decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '用户取消注单前的余额' AFTER `payout_win`;
    ALTER TABLE `bak__exception_cash_log`
    MODIFY COLUMN `bet_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '下注时间' AFTER `user_money`,
    MODIFY COLUMN `add_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' AFTER `action_passivity`;
    ALTER TABLE `bak__exception_cash_log` DROP COLUMN `agnet_name`;

## 新增 总平台操作系统日志表
    CREATE TABLE `bak__system_log` (
      `id` int(11) DEFAULT NULL,
      `action_name` varchar(255) DEFAULT NULL COMMENT '操作的项目',
      `user_id` int(11) DEFAULT NULL COMMENT '操作者ID',
      `action_user` varchar(255) DEFAULT NULL COMMENT '操作者',
      `action_desc` varchar(255) DEFAULT NULL COMMENT '操作具体内容',
      `action_passivity` varchar(255) DEFAULT NULL COMMENT '操作对象',
      `action_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '操作日期',
      `ip_info` varchar(255) DEFAULT NULL COMMENT 'IP 操作者IP'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='总平台操作系统日志';
## 修改 游戏报表,包含用户输赢 字段信息
    ALTER TABLE `bak__user_chart_info` ADD COLUMN `dwRound`  int(10) UNSIGNED NULL DEFAULT 0 COMMENT '局信息' AFTER `game_name`;
    ALTER TABLE `bak__user_chart_info` ADD COLUMN `game_period`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '靴局信息' AFTER `dwRound`;
    ALTER TABLE `bak__user_chart_info` ADD COLUMN `remark`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '牌信息' AFTER `game_period`;
    ALTER TABLE `bak__user_chart_info` ADD COLUMN `user_name`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '玩家名' AFTER `remark`;
## 修改 注单明细表 字段信息
    ALTER TABLE `bak__user_order` ADD COLUMN `round_no`  varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '荷官端生成的唯一局ID' AFTER `game_round_id`;
    ALTER TABLE `bak__user_order` ADD COLUMN `game_period`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '靴+局' AFTER `round_no`;
    ALTER TABLE `bak__user_order` ADD COLUMN `server_name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '桌号' AFTER `server_id`;
## 修改 厅主游戏活动管理表字段
    ALTER TABLE `game_platform_activity` MODIFY COLUMN `update_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间' AFTER `add_date`;
## 修改 游戏文案banner表字段
    ALTER TABLE `game_platform_banner` ADD COLUMN `p_name`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '厅主登录名' AFTER `p_id`;
    ALTER TABLE `game_platform_banner` ADD COLUMN `status`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '审核状态，0：未审核，1：已审核，2：审核不通过' AFTER `update_date`;
    ALTER TABLE `game_platform_banner` ADD COLUMN `is_use`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '启用状态：0 未使用，1已使用' AFTER `status`;
    ALTER TABLE `game_platform_banner` ADD COLUMN `sort`  int(10) NOT NULL DEFAULT 1 COMMENT '排序：数字越小越靠前' AFTER `is_use`;
    ALTER TABLE `game_platform_banner` ADD COLUMN `url`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'url地址' AFTER `sort`;
    ALTER TABLE `game_platform_banner` MODIFY COLUMN `label`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属平台,0为视讯PC，1为视讯H5，2为视讯APP' AFTER `play_type`;
    ALTER TABLE `game_platform_banner` MODIFY COLUMN `update_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间' AFTER `add_date`;
    CREATE INDEX `index_p_id_p_name` USING BTREE ON `game_platform_banner`(`p_id`, `p_name`);
        
## 修改 游戏包网费用设置表字段
    ALTER TABLE `game_platform_cost` MODIFY COLUMN `update_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间' AFTER `add_user`;
##  修改 游戏期数交收规则表字段
    ALTER TABLE `game_platform_delivery` MODIFY COLUMN `add_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' AFTER `add_user`;
    ALTER TABLE `game_platform_delivery` MODIFY COLUMN `update_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间' AFTER `add_time`;
## 修改 游戏厅主文案LOGO信息表字段
    ALTER TABLE `game_platform_logo` ADD COLUMN `p_name`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '厅主登录名' AFTER `p_id`;
    ALTER TABLE `game_platform_logo` ADD COLUMN `status`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '审核状态，0：未审核，1：已审核，2：审核不通过' AFTER `update_date`;
    ALTER TABLE `game_platform_logo` ADD COLUMN `is_use`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '启用状态：0 未使用，1已使用' AFTER `status`;
    ALTER TABLE `game_platform_logo` ADD COLUMN `sort`  int(10) NOT NULL DEFAULT 1 COMMENT '排序：数字越小越靠前' AFTER `is_use`;
    ALTER TABLE `game_platform_logo` MODIFY COLUMN `label`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '所属平台,0为视讯PC，1为视讯H5，2为视讯APP' AFTER `title`;
    ALTER TABLE `game_platform_logo` MODIFY COLUMN `update_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间' AFTER `add_date`;
    CREATE INDEX `index_p_id_p_name` USING BTREE ON `game_platform_logo`(`p_id`, `p_name`);
## 修改 游戏费用管理表字段
    ALTER TABLE `game_platform_scale` MODIFY COLUMN `add_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' AFTER `add_user`;
## 修改  游戏模板对应图片表
    ALTER TABLE `game_template_images` MODIFY COLUMN `add_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' AFTER `img`;
    
## 新增 在线更新，版本更新表
    CREATE TABLE `game_version` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `label` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '游戏平台,0为视讯PC，1为视讯H5，2为视讯APP',
      `version_n` varchar(50) NOT NULL DEFAULT '' COMMENT '版本号',
      `url` varchar(50) NOT NULL DEFAULT '' COMMENT 'url地址',
      `forced_up` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否强制更新，0否，1是，默认0',
      `content` text NOT NULL COMMENT '更新说明',
      `add_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
      `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
      `user_update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '用户添加的更新时间记录',
      PRIMARY KEY (`id`),
      KEY `index_label_version_n_add_time_forced_up` (`label`,`version_n`,`add_time`,`forced_up`) USING BTREE,
      KEY `index_version` (`version_n`) USING BTREE,
      KEY `index_add_time` (`add_time`) USING BTREE,
      KEY `index_forced_up` (`forced_up`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8 COMMENT='在线更新，版本更新';
    
## 修改 玩家用户表字段
    ALTER TABLE `lb_user` MODIFY COLUMN `user_name`  varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '玩家在第三方平台账号，带前缀' AFTER `uid`;
    ALTER TABLE `lb_user` MODIFY COLUMN `profit_share_platform`  varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '平台分成' AFTER `mapping`;
## 新增 代理商活跃会员统计表
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
## 新增 按小时和下注金额对下注次数进行统计表
    CREATE TABLE `statis_bet_distribution` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `rank1` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '[1-1000)',
      `total1` int(10) unsigned NOT NULL DEFAULT '0',
      `rank2` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '[1000-5000)',
      `total2` int(10) unsigned NOT NULL DEFAULT '0',
      `rank3` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '[5000-10000)',
      `total3` int(10) unsigned NOT NULL DEFAULT '0',
      `rank4` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '[10000-50000)',
      `total4` int(10) unsigned NOT NULL DEFAULT '0',
      `rank5` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '[50000-200000)',
      `total5` int(10) unsigned NOT NULL DEFAULT '0',
      `rank6` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '[200000-',
      `total6` int(10) unsigned NOT NULL DEFAULT '0',
      `bettime` datetime NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `bettime` (`bettime`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=1262182 DEFAULT CHARSET=utf8 COMMENT='按小时和下注金额对下注次数进行统计,rank*代表下注金额在对应区域中的总次数，total*代表rank*的总金额';
## 新增 代理商用户在线统计表
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
    ) ENGINE=InnoDB AUTO_INCREMENT=13283 DEFAULT CHARSET=utf8 COMMENT='代理商用户在线统计表';
    
## 修改 平台方用户操作日志表字段
    ALTER TABLE `system_log` MODIFY COLUMN `action_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间' AFTER `action_user`;
## 修改 系统维护表字段
    ALTER TABLE `system_maintain` MODIFY COLUMN `start_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '系统维护开始时间' AFTER `hall_id`;
## 修改 系统公告表字段
    ALTER TABLE `system_message` ADD COLUMN `title`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题' AFTER `id`;
    ALTER TABLE `system_message` MODIFY COLUMN `coment_cn`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '公告内容' AFTER `title`;
    ALTER TABLE `system_message` MODIFY COLUMN `coment_en`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '英文公告（暂时没用到）' AFTER `coment_cn`;
    ALTER TABLE `system_message` MODIFY COLUMN `type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '公告发布的平台，0为所有平台，1为视讯PC，2为视讯H5，3为视讯APP。默认为0' AFTER `update_date`;
    

    
