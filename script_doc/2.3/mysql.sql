CREATE TABLE `activity_red_packets` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`title`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '红包活动标题' ,
`trigger`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '红包触发类型,0为大厅（红包雨），1为下注时（普通红包）' ,
`type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '红包类型，0为红包雨，1为普通红包，默认为红包雨' ,
`user_max`  tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '单个会员最大能在该活动抢到的红包数' ,
`total_amount`  decimal(10,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '红包的金额（只能为整数金额）' ,
`get_amount`  decimal(10,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '已经领取的金额' ,
`total_number`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总的红包个数' ,
`get_number`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '已经领取的红包个数' ,
`total_user`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '领取过该红包的会员数' ,
`start_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '红包活动开始时间' ,
`end_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '红包活动结束时间' ,
`status`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '红包状态，0为已结束，1为正常，默认为1' ,
`create_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间' ,
`last_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后修改时间' ,
`requirements_type`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '抢红包条件 1累计下注总额类型   2 当天下注总额' ,
`user_largest`  decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '大额用户的额度' ,
`requirements_amount`  decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '抢红包条件的额度 单位/元' ,
PRIMARY KEY (`id`),
INDEX `date_time` (`start_date`, `end_date`) USING BTREE
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;

CREATE TABLE `bak__alarm_push_log` (
`id`  int(10) NOT NULL ,
`rule_tag`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '具体触发的监控规则标识符（与监控规则表tag字段对应）' ,
`pass`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属平台，0为总平台，1为厅主系统' ,
`user_name`  varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户登录名称（监控对象）' ,
`hall_id`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '被监控用户所属厅主ID' ,
`hall_name`  varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '' COMMENT '被监控用户所属厅主登录名' ,
`agent_id`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '被监控用户所属代理商ID' ,
`agent_name`  varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '被监控用户所属代理商登录名' ,
`remark`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '报警备注' ,
`create_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '报警时间（创建时间）'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=latin1 COLLATE=latin1_swedish_ci
ROW_FORMAT=Dynamic
;

CREATE TABLE `bak__api_login_log` (
`id`  int(11) NULL DEFAULT NULL ,
`agent`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '代理名称' ,
`postData`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '请求的参数' ,
`apiName`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '请求接口名称' ,
`ip_info`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '代理请求的IP ' ,
`log_type`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '登录类型 login 登录 api 接口请求' ,
`code`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '返回状态码  0  正常' ,
`text`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '返回结果字段   Success' ,
`result`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'result 请求结果数据' ,
`start_time`  datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '开始时间' ,
`end_time`  datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '结束时间'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;

CREATE TABLE `bak__api_statistics_log` (
`id`  int(11) NULL DEFAULT NULL ,
`apiName`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联调接口名称' ,
`agent`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联调厅主' ,
`status`  tinyint(255) NULL DEFAULT NULL COMMENT '是否联调 默认 0  1 联调' ,
`succeds`  int(255) NULL DEFAULT NULL COMMENT '成功次数 默认 0' ,
`sum`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '联调总次数 默认 0'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;

ALTER TABLE `bak__exception_cash_log` MODIFY COLUMN `bet_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '下注时间' AFTER `user_money`;

ALTER TABLE `bak__exception_cash_log` MODIFY COLUMN `add_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' AFTER `action_passivity`;

CREATE TABLE `bak__packets_log` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`packets_id`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '红包活动ID' ,
`user_id`  int(10) NOT NULL DEFAULT 0 COMMENT '会员id' ,
`user_name`  varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '' COMMENT '会员登录名' ,
`hall_name`  varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '' COMMENT '会员所属厅主登录名' ,
`hall_id`  int(10) NOT NULL DEFAULT 0 COMMENT '会员所属厅主ID' ,
`agent_name`  varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '' COMMENT '会员所属代理商登录名' ,
`agent_id`  int(10) NOT NULL DEFAULT 0 COMMENT '会员所属代理商ID' ,
`packets_amount`  decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '抢到的红包金额' ,
`get_number`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '红包个数，恒定值为1，为了数据统计方便而用' ,
`create_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间（即抢到红包的时间）' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;

CREATE TABLE `bak__system_log` (
`id`  int(11) NULL DEFAULT NULL ,
`action_name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作的项目' ,
`user_id`  int(11) NULL DEFAULT NULL COMMENT '操作者ID' ,
`action_user`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作者' ,
`action_desc`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作具体内容' ,
`action_passivity`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作对象' ,
`action_date`  datetime NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '操作日期' ,
`ip_info`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'IP 操作者IP'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;

CREATE TABLE `bak__trigger_log` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`user_id`  int(10) NOT NULL DEFAULT 0 COMMENT '用户id' ,
`user_name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户登录名称' ,
`hall_name`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户所属厅主登录名' ,
`hall_id`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户所属厅主ID' ,
`agent_name`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '代理商名称' ,
`agent_id`  int(10) NOT NULL DEFAULT 0 COMMENT '代理商id' ,
`rule_tag`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '具体触发的监控规则标识符（与监控规则表tag字段对应）' ,
`user_real_value`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '用户真实的监控数据值' ,
`rule_value`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '具体监控项的阀值' ,
`number`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '触发规则的次数' ,
`pass`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '监控记录类型：0为总平台，1为厅主' ,
`last_trigger_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后触发事件' ,
`remark`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '报警备注' ,
`begin_balance`  decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '用户初始余额（高盈利监控）' ,
`monitor_balance`  decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '监控报警时用户余额（高盈利监控）' ,
`create_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '记录创建时间' ,
`ip_str`  char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.0.0.0' COMMENT '玩家IP地址' ,
`is_send_email`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已发送邮件，1为已发送' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;

ALTER TABLE `bak__user_chart_info` ADD COLUMN `agent_code`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '玩家账号代理商前缀' AFTER `user_name`;

CREATE TABLE `dealer_info` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`dealer`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '荷官ID(荷官编码)' ,
`dealer_name`  varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '荷官名称' ,
`dealer_img`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '荷官图片' ,
`last_update`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后更新时间' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;

ALTER TABLE `game_platform_activity` MODIFY COLUMN `update_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间' AFTER `add_date`;

ALTER TABLE `game_platform_banner` MODIFY COLUMN `label`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属平台,0为视讯PC，1为视讯H5，2为视讯APP' AFTER `play_type`;

ALTER TABLE `game_platform_banner` MODIFY COLUMN `update_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间' AFTER `add_date`;

ALTER TABLE `game_platform_cost` MODIFY COLUMN `update_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间' AFTER `add_user`;

ALTER TABLE `game_platform_delivery` MODIFY COLUMN `add_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' AFTER `add_user`;

ALTER TABLE `game_platform_delivery` MODIFY COLUMN `update_time`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间' AFTER `add_time`;

ALTER TABLE `game_platform_logo` MODIFY COLUMN `label`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '所属平台,0为视讯PC，1为视讯H5，2为视讯APP' AFTER `title`;

ALTER TABLE `game_platform_logo` MODIFY COLUMN `update_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间' AFTER `add_date`;

ALTER TABLE `game_platform_scale` MODIFY COLUMN `add_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' AFTER `add_user`;

ALTER TABLE `game_template_images` MODIFY COLUMN `add_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间' AFTER `img`;

CREATE TABLE `hall_message` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`message`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '厅主公告内容' ,
`start_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '公告开始时间' ,
`end_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '公告结束时间' ,
`create_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间' ,
PRIMARY KEY (`id`),
INDEX `start_date` (`start_date`, `end_date`) USING BTREE
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;

ALTER TABLE `lb_agent_user` ADD COLUMN `connect_mode`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '厅主对接方式，0为额度转换，1为共享钱包，默认为0' AFTER `notify_url`;

ALTER TABLE `lb_agent_user` ADD COLUMN `show_delivery`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '针对厅主，是否显示厅主交收统计  0：不显示，1：显示' AFTER `connect_mode`;

ALTER TABLE `lb_user` ADD COLUMN `grand_total_money`  decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT '充值扣款累计余额' AFTER `money`;

CREATE TABLE `statis_both_add_gold` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
`user_id`  int(11) UNSIGNED NOT NULL DEFAULT 0 ,
`user_name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名' ,
`both_add_times`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总对下次数' ,
`no_both_add_times`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '非对下次数' ,
`con_both_add_times`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '连续对下次数' ,
`con_both_add_times_cur`  int(11) NOT NULL DEFAULT 0 COMMENT '当前连续对下次数，临时值' ,
`add_date`  date NOT NULL DEFAULT '0000-00-00' COMMENT '当天日期' ,
`ip_str`  char(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '玩家IP地址（目前正式环境无法获取）' ,
`hall_id`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '厅主ID' ,
`hall_name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '\"\"' COMMENT '厅主名称' ,
`agent_id`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '代理商ID' ,
`agent_name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '\"\"' COMMENT '代理商名称' ,
`update_time`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间' ,
PRIMARY KEY (`id`),
UNIQUE INDEX `index_date` (`user_id`, `add_date`) USING BTREE
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;

ALTER TABLE `statis_cash_player` ADD COLUMN `totalwinnums`  int(11) NULL DEFAULT 0 COMMENT '当天总共胜多少局' AFTER `win_count_cur`;

ALTER TABLE `statis_cash_player` ADD COLUMN `totallosenums`  int(11) NULL DEFAULT 0 COMMENT '当天总共输多少局' AFTER `totalwinnums`;

CREATE TABLE `sys_alarm_account` (
`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
`hall_id`  int(11) NOT NULL DEFAULT 0 COMMENT '所属厅主id，默认为0，0时代表为总平台' ,
`mobile`  varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '手机号码' ,
`email`  varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '报警邮箱' ,
`last_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后修改时间' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=Dynamic
;

ALTER TABLE `system_log` MODIFY COLUMN `action_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间' AFTER `action_user`;

ALTER TABLE `system_maintain` MODIFY COLUMN `start_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '系统维护开始时间' AFTER `hall_id`;

ALTER TABLE `system_message` MODIFY COLUMN `type`  tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '公告发布的平台，0为所有平台，1为视讯PC，2为视讯H5，3为视讯APP。默认为0' AFTER `update_date`;

ALTER TABLE `agent_system_menus` MODIFY COLUMN `update_date`  datetime NULL DEFAULT NULL AFTER `menu_code`;


INSERT INTO `sys_monitor` VALUES ('1', '0', '刷水', 'M001', '0');
INSERT INTO `sys_monitor` VALUES ('2', '0', '大额投注', 'M002', '0');
INSERT INTO `sys_monitor` VALUES ('3', '0', '高盈利', 'M003', '1');
INSERT INTO `sys_monitor` VALUES ('4', '0', '连胜次数', 'M004', '0');
INSERT INTO `sys_monitor` VALUES ('5', '0', '胜率', 'M005', '0');

INSERT INTO `sys_monitor_rule` VALUES ('1', '0', 'M002', 'bet', '6', '2017-11-02 22:07:34');
INSERT INTO `sys_monitor_rule` VALUES ('2', '0', 'M002', 'gap', '1', '2017-11-02 22:07:28');
INSERT INTO `sys_monitor_rule` VALUES ('3', '0', 'M003', 'profit', '101', '2017-11-01 05:59:55');
INSERT INTO `sys_monitor_rule` VALUES ('4', '0', 'M003', 'gap', '50', '2017-10-18 04:11:10');
INSERT INTO `sys_monitor_rule` VALUES ('5', '0', 'M004', 'win_streak', '1000', '2017-10-26 21:56:04');
INSERT INTO `sys_monitor_rule` VALUES ('6', '0', 'M004', 'gap', '5', '2017-10-18 04:11:08');
INSERT INTO `sys_monitor_rule` VALUES ('7', '0', 'M005', 'victory_ratio', '200', '2017-10-26 21:56:07');
INSERT INTO `sys_monitor_rule` VALUES ('8', '0', 'M005', 'gap', '5', '2017-10-18 04:11:11');


ALTER TABLE `game_platform_delivery_info`
ADD COLUMN `red_packets`  decimal(10,2) UNSIGNED NULL DEFAULT 0.00 COMMENT '红包金额' AFTER `local_end_date`;


CREATE TABLE `game_host` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '游戏入口域名管理Id',
  `host_type` varchar(255) NOT NULL DEFAULT '0' COMMENT '游戏域名类型   1 pc游戏域名  2 h5游戏域名 ',
  `host_url` varchar(255) NOT NULL DEFAULT '' COMMENT '游戏域名',
  `status` tinyint(4) DEFAULT '0' COMMENT '是否启动 0 不启用  1 启用',
  `add_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `update_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='游戏入口域名管理表';

 ALTER TABLE `lb_agent_user`
    ADD COLUMN `connect_mode`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '厅主对接方式，0为额度转换，1为共享钱包，默认为0' AFTER `notify_url`;

CREATE TABLE `statis_shuashui` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_str` char(20) DEFAULT NULL COMMENT '玩家IP地址（目前正式环境无法获取）',
  `user_name` varchar(50) DEFAULT NULL COMMENT '用户名',
  `both_add_times` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总对下次数',
  `no_both_add_times` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '非对下次数',
  `con_both_add_times` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '连续对下次数',
  `con_both_add_times_cur` int(11) NOT NULL DEFAULT '0' COMMENT '当前连续对下次数，临时值',
  `add_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '当天日期',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_ip_date` (`ip_str`,`add_date`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='每天IP对下记录表（保存1周数据）';