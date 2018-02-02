ALTER TABLE `white_list`
MODIFY COLUMN `seckey_exp_date`  datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'seckey最后有效时间' AFTER `agent_seckey`;