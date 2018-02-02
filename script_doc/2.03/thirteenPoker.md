###修改游戏种类表 2017.7.25
ALTER TABLE `game_cat`
MODIFY COLUMN `rank`  smallint(6) NOT NULL COMMENT '分类级别' AFTER `sub_count`,
ADD COLUMN `game_type`  smallint(6) NOT NULL DEFAULT 0 COMMENT ' 分为视讯和电子游戏  默认为视频游戏 0  电子游戏为1' AFTER `rank`;

##新增十三水游戏表2017.7.25
    CREATE TABLE `thirteen_poker_game_type` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `cat_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '游戏分类ID',
      `type_name` varchar(45) NOT NULL DEFAULT '' COMMENT '游戏名称',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8 COMMENT='十三水游戏种类列表';

    
    CREATE TABLE `room_info` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `cat_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '游戏分类ID',
    `room_name` varchar(45) NOT NULL DEFAULT '' COMMENT '房间名称',
    `bottom_score` int(11) NOT NULL DEFAULT '0' COMMENT '房间底分 最低输掉的金额 默认不限制',
    `sort_id` tinyint(3) NOT NULL DEFAULT '0' COMMENT  '排序字段',
    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '游戏是否可用,1为可用,0为不可用，2已删除',
    `table_count` int(11) NOT NULL DEFAULT '0' COMMENT '该游戏由几个桌子组成',
    `user_count` int(11) NOT NULL DEFAULT '0' COMMENT '用户数',
    `max_number` int(11) NOT NULL DEFAULT '0' COMMENT '房间限制',
    `current_table_count` int(11) NOT NULL DEFAULT '0' COMMENT '当前桌子',
    `current_user_count` int(11) NOT NULL DEFAULT '0' COMMENT '当前用户数',
    `is_recommand` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否推荐,0为不推荐,1为推荐',
    `profit_rate` tinyint(3) DEFAULT '0' COMMENT '房间盈利率',
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8 COMMENT='房间列表';


    CREATE TABLE `room_play_rules` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `cat_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '游戏分类ID',
      `room_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '房间ID',
      `room_name` varchar(45) NOT NULL DEFAULT '' COMMENT '房间名称',
      `profit_rate` tinyint(3) DEFAULT '0' COMMENT '房间盈利率',
      `card_type` tinyint(3) DEFAULT '1' COMMENT '牌型分为  普通 1 特殊 2 默认为普通 ',
      `play_name_type` varchar(45) NOT NULL DEFAULT '0' COMMENT '牌型类型 至尊青龙:1,一条龙:2,十二皇族:3,三同花顺:4,三分天下:5,全大:6,全小:7,凑一色:8,四套三条:9,五对三条:10,
     六对半:11,三顺子:12,三同花:13,赢一水:14,冲三:15,中墩同花顺:16,尾墩同花顺:17,输一水:18,中墩铁支:19,尾墩铁支:20,和:21,中墩葫芦:22,',
      `play_rules_odds` tinyint(3) DEFAULT '0' COMMENT '牌型赔率',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8 COMMENT='房间玩法赔率方案';

    

    

