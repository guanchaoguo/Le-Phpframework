##厅主、代理商、玩家、现金流数据导入说明
### 1、第三方给我们的数据表有（可在192.168.231服务器mysql下的lb_livegame_import库查看）：
        lb_agent_user：厅主、代理商数据（一张表一个厅主和一个或多个代理商）
        lb_user：玩家表（hall_name、agent_name字段关联厅主、代理商）
        bak__cash_record：现金记录表（user_name字段关联玩家）
### 2、需要把第三方给的三张表重命名为：
        lb_agent_user -> lb_agent_user_import
        lb_user -> lb_user_import
        bak__cash_record -> bak__cash_record_import
### 3、保存sql语句
        将重命名好的三张数据表转存为sql文件，用于导入当前项目的数据库下，为导入数据做准备。
        在当前项目script_doc/2.03/agent_user_cash_record_import2017.8.9.sql，这是已经转存过的sql文件
### 4、在当前项目下app/Console/Commands已经写了导入脚本：
       app/Console/Commands/AgentUserToMysql.php ->导入代理商
       app/Console/Commands/UserToMysql.php ->导入玩家
       app/Console/Commands/CashRecordToMongodb.php ->导入现金流
### 5、在项目根目录下运行php 命令 （ps:要按顺序运行）：
        php artisan AgentUserImportToMysql ->导入代理商命令    
        php artisan UserImportToMysql  ->导入玩家命令
        php artisan cashRecordImportToMongodb  ->导入现金流命令
### 6、运行命令后，没有报错的话，就可以使用帐号在后台登录了  