
# 厅主添加链接方式

    ALTER TABLE `lb_agent_user`
    ADD COLUMN `connect_mode`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '厅主对接方式，0为额度转换，1为共享钱包，默认为0' AFTER `notify_url`;
    
