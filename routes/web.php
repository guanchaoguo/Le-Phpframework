<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */
$app->get('socket',[
    'uses'  => 'Admin\V1\BaseController@putSocketServer'//测试socket
]);
$app->get('sysMaintain',[
    'uses'  => 'Admin\V1\GameServerController@sysMaintain'//测试socket
]);
$app->get('hallMaintain',[
    'uses'  => 'Admin\V1\GameServerController@hallMaintain'//测试socket
]);
$app->get('userOut',[
    'uses'  => 'Admin\V1\GameServerController@userLoginOut'//测试socket
]);
$app->get('/', function () use ($app) {
    //    http://app2.dev/?locale=zh-CN
//    new \MongoDB\Driver\Manager();
//    echo trans('auth.incorrect');
   return 'platformSystem Api';
    return $app->version();
});
$app->get('test',[
    'uses' =>'Admin\V1\TestController@index'
]);
//交收定时任务
$app->get('delivery/crontab',[
    'uses'      => 'Admin\V1\DeliveryController@crontab'
]);
//活跃会员定时任务
$app->get('active/crontab',[
    'uses'      => 'Admin\V1\ActiveUserCrontabController@run'
]);


$api = app('Dingo\Api\Routing\Router');

//与游戏对接的API
$api->version(['lebogame.v2.2'],['namespace' => 'App\Http\Controllers\Game','middleware' => 'locale'], function ($api){

    //玩家认证
    $api->post('lebogame/user/token', [
        'uses' => 'AuthController@login',
    ]);

    $api->group(['middleware' => 'auth:player'], function ($api) {

        //获取banner
        $api->post('lebogame/banner', [
            'uses' => 'GamePlatformBannerController@index',
        ]);

        //获取logo
        $api->post('lebogame/logo', [
            'uses' => 'GamePlatformLogoController@index',
        ]);



    });

});
// admin_v1 version API
// choose version add this in header    Accept:application/vnd.lumen.admin_v1+json
$api->version(['v0.1.0'], ['namespace' => 'App\Http\Controllers\Admin\V1','middleware' => 'locale'], function ($api) {

    //图片上传
    $api->post('upload',[
        'as' => 'file.upload',
        'uses' =>'UploadController@index',
    ]);

    //删除文件
    $api->delete('file', [
        'as' => 'file.delete',
        'uses' => 'UploadController@delete',
    ]);

    // Captcha 验证码
    $api->post('captcha', [
        'as' => 'captcha.index',
        'uses' => 'CaptchaController@index',
    ]);

    // 语言列表
    $api->post('language', [
        'as' => 'auth.language',
        'uses' => 'AuthController@language',
    ]);

    // Auth login 登录认证
    $api->post('authorization', [
        'as' => 'auth.login',
        'uses' => 'AuthController@login',
    ]);
    // register
    $api->post('users', [
        'as' => 'users.store',
        'uses' => 'AuthController@register',
    ]);

    // AUTH
    // refresh jwt token
    $api->post('auth/token/refresh', [
        'as' => 'auth.token.refresh',
        'uses' => 'AuthController@refreshToken',
    ]);

    // need authentication
    $api->group(['middleware' => 'auth:admin'], function ($api) {

        //需要经过权限验证的操作接口
        $api->group(['middleware' => 'menu'],function ($api){

            //添加 （编辑）厅主（代理商）
            $api->post('agents', [
                'as' => 'agents.store',
                'uses' => 'AgentController@store',
            ]);

            //添加厅主代理（新）
            $api->post('agent/grade/{grade_id}', [
                'as' => 'agent.grade.store',
                'uses' => 'AgentController@store2',
            ]);
            //编辑厅主代理（新）
            $api->put('agent_/{agent_id}/grade/{grade_id}', [
                'as' => 'agent.grade.update',
                'uses' => 'AgentController@update',
            ]);
            //当厅主扣费模式改变时，检测厅主下面玩家是否有余额
            $api->post('agent_/connectMode/', [
                'uses' => 'AgentController@checkHallAgentConnectMode',
            ]);

            //编辑 厅主（代理商）获取数据
            $api->post('agents/{agent_id}', [
                'as' => 'agents.Show',
                'uses' => 'AgentController@agentShow',
            ]);

            //修改代理商邮箱和手机
            $api->patch('agent/{agent_id}/grade/{grade_id}/emailTel', [
                'uses' => 'AgentController@setEmailTel',
            ]);

            //修改代理商状态
            $api->patch('agent/{agent_id}/grade/{grade_id}/locking', [
                'uses' => 'AgentController@setLock',
            ]);

            //修改玩家密码
            $api->post('player/{user_id}/password', [
                'as' => 'player.password',
                'uses' => 'PlayerController@password',
            ]);

            //玩家余额扣取（充值）
            $api->post('player/{user_id}/balance',[
                'as' => 'player.balance.handle',
                'uses' => 'PlayerController@balanceHandle'
            ]);

            //添加、保存厅主游戏费用数据&包网费用数据
            $api->post('agent/{id}/gameCost', [
                'as' => 'gameCost.store',
                'uses' => 'gameCostController@store',
            ]);

            //修改期数
            $api->patch('issue/{id}',[
                'as'    => 'delivery.update',
                'uses'  => 'DeliveryController@update'
            ]);

            //厅主、代理分配菜单
            $api->post('agent/addRole',[
                'uses'  => 'AgentMenusController@agentAddMenus'
            ]);

            //一键登出所有玩家操作
            $api->post('outAllUser',[
                'uses'  => 'MaintainController@signOutAllUser'
            ]);

        });

        //账号管理
        //厅主（代理商）
        $api->get('agents/{grade_id}', [
            'as' => 'agents.index',
            'uses' => 'AgentController@index',
        ]);

        //游戏种类
        $api->get('hall/games', [
            'as' => 'hall.games',
            'uses' => 'GameHallController@games',
        ]);

        //厅主（代理商）修改密码
        $api->post('agents/{agent_id}/password', [
            'as' => 'agents.password',
            'uses' => 'AgentController@password',
        ]);

        //玩家列表
        $api->get('player',[
            'as' => 'player.index',
            'uses' => 'PlayerController@index',
        ]);

        //玩家管理员列表
        $api->get('admin/player',[
            'uses' => 'PlayerController@adminTest',
        ]);

        //添加玩家
        $api->post('player', [
            'as' => 'player.store',
            'uses' => 'PlayerController@store',
        ]);
        //编辑玩家
        $api->put('player/{id}', [
            'as' => 'player.update',
            'uses' => 'PlayerController@update',
        ]);
        //编辑玩家时获取数据
        $api->post('player/{user_id}', [
            'as' => 'player.show',
            'uses' => 'PlayerController@show',
        ]);



        //修改玩家状态（启用，冻结，停用）
        $api->patch('player/{id}/status', [
            'as' => 'player.status.update',
            'uses' => 'PlayerController@statusUpdate',
        ]);

        //玩家登出
        $api->patch('/player/{id}/onLine', [
            'as' => 'player.signOut',
            'uses' => 'PlayerController@signOut',
        ]);

        //查询玩家余额
        $api->get('player/{user_id}/balance', [
            'as' => 'player.balance',
            'uses' => 'PlayerController@balance',
        ]);

        //查询玩家余额（共享钱包）
        $api->get('player/{user_id}/getUserBalance', [
            'as' => 'player.getUserBalance',
            'uses' => 'PlayerController@getUserBalance',
        ]);


        //查询玩家下注 （注单查询）
        $api->get('player/order',[
            'as' => 'player.order',
            'uses' => 'PlayerController@userChartInfo'
        ]);

        //注单查询 游戏结果，根据_id
        $api->get('player/order/{_id}',[

            'as' => 'player.showOrder',
            'uses' => 'PlayerController@showOrder'
        ]);

        //注单查询 游戏结果
        $api->get('/player/order/{account}/{round_no}',[
            'uses' => 'PlayerController@showOrderDetail'
        ]);

        //注单回滚
        $api->post('player/order/rollbackOrder',[
            'as' => 'player.rollbackOrder',
            'uses' => 'PlayerController@rollbackOrder'
        ]);

        //注单取消（单条）
        $api->post('player/order/cancelOrder',[
            'uses' => 'PlayerController@cancelPay'
        ]);
        //注单取消（一键取消）
        $api->post('player/order/bulkCancelOrder',[
            'uses' => 'PlayerController@bulkCancelPay'
        ]);

        //注单查询 游戏结果，根据round_no局号
        $api->get('player/order/round_no/{round_no}',[
            'uses' => 'PlayerController@showOrderByroundNo'
        ]);


        //游戏列表
        $api->get('game', [
            'as' => 'game.index',
            'uses' => 'GameController@index',
        ]);

        //添加游戏
        $api->post('game', [
            'as' => 'game.store',
            'uses' => 'GameController@store',
        ]);

        //编辑游戏
        $api->put('game/{id}', [
            'as' => 'game.update',
            'uses' => 'GameController@update',
        ]);

        //编辑游戏时获取数据
        $api->post('game/{id}', [
            'as' => 'game.show',
            'uses' => 'GameController@show',
        ]);

        //删除游戏
        $api->delete('game/{id}', [
            'as' => 'game.delete',
            'uses' => 'GameController@delete',
        ]);

        //游戏分类
        $api->get('game/cat', [
            'as' => 'game.cat',
            'uses' => 'GameController@cat',
        ]);

        //游戏厅限额查询
        $api->get('hall/quota', [
            'as' => 'hall.quota',
            'uses' => 'HallQuotaController@index',
        ]);

        //游戏厅限额添加
        $api->post('hall/quota', [
            'as' => 'hall.store',
            'uses' => 'HallQuotaController@store',
        ]);

        //为厅主添加默认游戏限额
        $api->get('addHallGameLimite', [
            'as' => 'addHallGameLimite',
            'uses' => 'HallQuotaController@addHallGameLimite',
        ]);

        /*++++++++++++++++++++++后台系统菜单相关Begin++++++++++++++++++++++++++++++*/
        //获取后台系统菜单列表
        $api->get('menus',[
            'as'    => 'menus.index',
            'uses'  => 'MenusController@index',
        ]);

        //编辑后台系统菜单获取数据
        $api->post('menus/{id}',[
            'as'    => 'menus.show',
            'uses'  => 'MenusController@show',
        ]);

        //添加后台菜单操作
        $api->post('menus',[
            'as'    => 'menus.store',
            'uses'  => 'MenusController@store',
        ]);

        //修改后台系统菜单提交
        $api->patch('menus/{id}',[
            'as'    => 'menus.update',
            'uses'  => 'MenusController@update'
        ]);

        //删除后台单个菜单
        $api->delete('menus/{id}',[
            'as'    => 'menus.destroy',
            'uses'  => 'MenusController@destroy'
        ]);

        /*++++++++++++++++++++++后台系统菜单相关End++++++++++++++++++++++++++++++*/

        /*++++++++++++++++++++++后台系统角色管理相关Begin+++++++++++++++++++++++++++++*/

        //后台角色列表
        $api->get('role',[
            'as'    => 'role.index',
            'uses' => 'RoleController@index'
        ]);

        //后台新增角色操作
        $api->post('role',[
            'as'        => 'role.store',
            'uses'      => 'RoleController@store'
        ]);

        //编辑角色权限时获取权限列表
        $api->post('role/showMenus/{id}',[
            'as'        => 'role.showMenus',
            'uses'      => 'RoleController@showMenus'
        ]);

        //编辑修改角色权限菜单
        $api->patch('role/updateRole/{id}',[
            'as'        => 'role.updateRole',
            'uses'      => 'RoleController@updateRole'
        ]);

        //删除角色分组信息
        $api->delete('role/deleteGroup/{id}',[
            'as'        => 'role.deleteGroup',
            'uses'      => 'RoleController@deleteGroup'
        ]);

        //子账户列表
        $api->get('role/accountList',[
            'as'    => 'role.accountList',
            'uses'  => 'RoleController@accountList'
        ]);

        //新增子帐号操作
        $api->post('role/addAccount',[
            'as'        => 'role.addAccount',
            'uses'      => 'RoleController@addAccount'
        ]);

        //子帐号状态修改操作，删除、停用
        $api->patch('role/accountState/{id}',[
            'as'        => 'role.accountState',
            'uses'      => 'RoleController@accountState'
        ]);

        //编辑子账户权限时获取菜单信息
        $api->post('role/showSubMenus/{id}',[
            'as'    => 'role.getAccountInfo',
            'uses'  => 'RoleController@getAccountInfo'
        ]);

        //保存修改子账户权限菜单信息
        $api->patch('role/updateAccount/{id}',[
            'as'        => 'role.updateAccount',
            'uses'      => 'RoleController@updateAccount'
        ]);

        //子账户修改密码操作
        $api->patch('role/editPwd/{id}',[
            'as'        => 'role.accountEditPwd',
            'uses'     => 'RoleController@accountEditPwd'
        ]);
        /*++++++++++++++++++++++后台系统角色管理相关End+++++++++++++++++++++++++++++*/
        //游戏厅限额保存
        $api->put('hall/quota/{id}', [
            'as' => 'hall.update',
            'uses' => 'HallQuotaController@update',
        ]);

        //快捷设定限额（添加）
        $api->post('hall/quota/shortcut', [
            'as' => 'hall.shortcutStore',
            'uses' => 'HallQuotaController@shortcutStore',
        ]);

        //快捷设定限额（保存）
        $api->put('hall/quota/shortcut/{id}', [
            'as' => 'hall.shortcutUpdate',
            'uses' => 'HallQuotaController@shortcutUpdate',
        ]);

        /**
         * 报表统计start
         */

        //查询游戏&导出excel或cvs
        $api->get('game/chart', [
            'as' => 'game.chart',
            'uses' => 'GameController@chart',
        ]);
        //查询总投注额
        $api->get('totalBet', [
            'as' => 'gameStatistics.totalBet',
            'uses' => 'GameStatisticsController@totalBet',
        ]);

        //查询指定厅主
        $api->get('totalBet/hall', [
            'as' => 'gameStatistics.totalBet.hall',
            'uses' => 'GameStatisticsController@hallTotalBet',
        ]);

        //查询指定代理
        $api->get('totalBet/agent', [
            'as' => 'gameStatistics.totalBet.agent',
            'uses' => 'GameStatisticsController@agentTotalBet',
        ]);
        //查询指定玩家
        $api->get('totalBet/player', [
            'as' => 'gameStatistics.totalBet.player',
            'uses' => 'GameStatisticsController@playerTotalBet',
        ]);
        //查询游戏
        $api->get('totalBet/game', [
            'as' => 'gameStatistics.totalBet.game',
            'uses' => 'GameStatisticsController@gameTotalBet',
        ]);
        /**
         * 报表统计end
         */

        /**
         * 白名单管理start
         */
        //列表
        $api->get('whitelist', [
            'as' => 'whitelist.index',
            'uses' => 'WhitelistController@index',
        ]);

        //添加
        $api->post('whitelist', [
            'as' => 'whitelist.store',
            'uses' => 'WhitelistController@store',
        ]);

        //获取单条
        $api->get('whitelist/{id}', [
            'as' => 'whitelist.show',
            'uses' => 'WhitelistController@show',
        ]);

        //编辑
        $api->put('whitelist/{id}', [
            'as' => 'whitelist.update',
            'uses' => 'WhitelistController@update',
        ]);

        //删除
        $api->delete('whitelist/{id}', [
            'as' => 'whitelist.delete',
            'uses' => 'WhitelistController@delete',
        ]);

        //查看秘钥
        $api->get('whitelist/showKey/{id}',[
            'uses' => 'WhitelistController@showKey',
            //

        ]);
        /**
         * 白名单管理end
         */

        /**
         * 查询现金流start
         */

        $api->get('cashRecord', [
            'as' => 'cashRecord.index',
            'uses' => 'CashRecordController@index',
        ]);

        /**
         * 查询现金流end
         */

        /**
         * 首页统计start
         */

        //今日派彩、总投注、赢钱最多的代理，输钱最多的代理
        $api->get('statistics/today/money', [
            'as' => 'statistics.today.money',
            'uses' => 'HomeController@getTodayMoney',
        ]);

        //当前在线玩家、今日用户渠道
        $api->get('statistics/today/user', [
            'as' => 'statistics.today.user',
            'uses' => 'HomeController@getTodayUser',
        ]);
        //本周、上周的新增代理、总投注、派彩
        /*$api->get('statistics/week', [
            'as' => 'statistics.week',
            'uses' => 'HomeController@getWeekData',
        ]);

        //本月、上月新增厅主、新增代理、总投注额、总派彩
        $api->get('statistics/month', [
            'as' => 'statistics.month',
            'uses' => 'HomeController@getMonthData',
        ]);*/

        //周（月）统计（用户）
        $api->get('statistics/user', [
            'as' => 'statistics.user',
            'uses' => 'HomeController@getDataByUser',
        ]);
        //周（月）统计（金额）
        $api->get('statistics/score', [
            'as' => 'statistics.score',
            'uses' => 'HomeController@getDataByScore',
        ]);
        //近半年的总投注额、总派彩额
        $api->get('statistics/semi-annual', [
            'as' => 'statistics.semiAnnual',
            'uses' => 'HomeController@getSemiAnnualData',
        ]);
        /**
         * 首页统计end
         */

        /**
         * 游戏费用&包网费用设置start
         */

        //获取厅主游戏费用&包网费用数据
        $api->get('agent/{id}/gameCost', [
            'as' => 'gameCost.index',
            'uses' => 'gameCostController@index',
        ]);



        /**
         * 游戏费用&包网费用设置end
         */

        /**
         * 游戏风格模板start
         */

        //模板列表
        $api->get('gameTemplate',[
            'as' => 'game.template.index',
            'uses' => 'GameTemplateController@index',
        ]);

        //添加模板
        $api->post('gameTemplate',[
            'as' => 'game.template.store',
            'uses' => 'GameTemplateController@store',
        ]);

        //获取模板详情
        $api->get('gameTemplate/{id}',[
            'as' => 'game.template.show',
            'uses' => 'GameTemplateController@show',
        ]);

        //编辑模板
        $api->put('gameTemplate/{id}',[
            'as' => 'game.template.update',
            'uses' => 'GameTemplateController@update',
        ]);

        //删除模板
        $api->delete('gameTemplate/{id}',[
            'as' => 'game.template.delete',
            'uses' => 'GameTemplateController@delete',
        ]);

        //厅主选模板
        $api->patch('gameTemplate/{id}/agent/{a_id}',[
            'as' => 'game.template.agent',
            'uses' => 'GameTemplateController@saveAgentTemplate',
        ]);
        /**
         * 游戏风格模板end
         */

        /**
         * 文案LOGO start
         */
        //列表
        $api->get('copywriter/logo',[
            'as' => 'GamePlatformLogo.index',
            'uses' => 'GamePlatformLogoController@index',
        ]);
        //添加
        $api->post('copywriter/logo',[
            'as' => 'GamePlatformLogo.store',
            'uses' => 'GamePlatformLogoController@store',
        ]);
        //详情
        $api->get('copywriter/logo/{id}',[
            'as' => 'GamePlatformLogo.show',
            'uses' => 'GamePlatformLogoController@show',
        ]);
        //编辑保存
        $api->put('copywriter/logo/{id}',[
            'as' => 'GamePlatformLogo.update',
            'uses' => 'GamePlatformLogoController@update',
        ]);

        //审核
        $api->patch('copywriter/logo/{id}/review',[
            'as' => 'GamePlatformLogo.review',
            'uses' => 'GamePlatformLogoController@review',
        ]);

        //启用&禁用
        $api->patch('copywriter/logo/{id}/isUse',[
            'as' => 'GamePlatformLogo.isUse',
            'uses' => 'GamePlatformLogoController@isUse',
        ]);

        //删除
        $api->delete('copywriter/logo/{id}',[
            'as' => 'GamePlatformLogo.delete',
            'uses' => 'GamePlatformLogoController@delete',
        ]);
        /**
         * 文案LOGO end
         */

        /**
         * 文案Banner start
         */
        //列表
        $api->get('copywriter/banner',[
            'as' => 'GamePlatformBanner.index',
            'uses' => 'GamePlatformBannerController@index',
        ]);
        //添加
        $api->post('copywriter/banner',[
            'as' => 'GamePlatformBanner.store',
            'uses' => 'GamePlatformBannerController@store',
        ]);
        //详情
        $api->get('copywriter/banner/{id}',[
            'as' => 'GamePlatformBanner.show',
            'uses' => 'GamePlatformBannerController@show',
        ]);
        //编辑保存
        $api->put('copywriter/banner/{id}',[
            'as' => 'GamePlatformBanner.update',
            'uses' => 'GamePlatformBannerController@update',
        ]);

        //审核
        $api->patch('copywriter/banner/{id}/review',[
            'as' => 'GamePlatformBanner.review',
            'uses' => 'GamePlatformBannerController@review',
        ]);

        //删除
        $api->delete('copywriter/banner/{id}',[
            'as' => 'GamePlatformBanner.delete',
            'uses' => 'GamePlatformBannerController@delete',
        ]);

        //启用&禁用
        $api->patch('copywriter/banner/{id}/isUse',[
            'as' => 'GamePlatformBanner.isUse',
            'uses' => 'GamePlatformBannerController@isUse',
        ]);

        //排序
        $api->patch('copywriter/banner/{id}/sort',[
            'as' => 'GamePlatformBanner.sort',
            'uses' => 'GamePlatformBannerController@sort',
        ]);
        /**
         * 文案Banner end
         */

        /**
         * 文案活动 start
         */
        //列表
        $api->get('copywriter/activity',[
            'as' => 'GamePlatformActivity.index',
            'uses' => 'GamePlatformActivityController@index',
        ]);
        //添加
        $api->post('copywriter/activity',[
            'as' => 'GamePlatformActivity.store',
            'uses' => 'GamePlatformActivityController@store',
        ]);
        //详情
        $api->get('copywriter/activity/{id}',[
            'as' => 'GamePlatformActivity.show',
            'uses' => 'GamePlatformActivityController@show',
        ]);
        //编辑保存
        $api->put('copywriter/activity/{id}',[
            'as' => 'GamePlatformActivity.update',
            'uses' => 'GamePlatformActivityController@update',
        ]);

        //审核
        $api->patch('copywriter/activity/{id}',[
            'as' => 'GamePlatformActivity.review',
            'uses' => 'GamePlatformActivityController@review',
        ]);

        //删除
        $api->delete('copywriter/activity/{id}',[
            'as' => 'GamePlatformActivity.delete',
            'uses' => 'GamePlatformActivityController@delete',
        ]);
        /**
         * 文案活动 end
         */

        /**
         * 游戏交收设置start
         */
        //期数列表
        $api->get('issue',[
            'as'    => 'delivery.index',
            'uses'  => 'DeliveryController@index'
        ]);
        //添加期数
        $api->post('issue',[
            'as'    => 'delivery.store',
            'uses'  => 'DeliveryController@store'
        ]);
        //修改期数时获取数据
        $api->get('issue/{id}',[
            'as'    => 'delivery.getIssue',
            'uses'  => 'DeliveryController@getIssue'
        ]);

        //游戏交收数据列表
        $api->get('delivery',[
            'as'    => 'delivery.issueList',
            'uses'  => 'DeliveryController@issueList'
        ]);
        //标记交收是否已完成
        $api->patch('delivery/{id}',[
            'as'    => 'delivery.editState',
            'uses'  => 'DeliveryController@editIssueState'
        ]);
        //一键生成期数
        $api->post('autoIssue',[
            'uses'  => 'DeliveryController@autoCreateIssue'
        ]);
        /**
         * 游戏交收设置end
         */
        /**
         * 系统公告start
         */
        //公告列表
        $api->get('message',[
            'as'    => 'message.index',
            'uses'  => 'MessageController@index'
        ]);
        //添加公告
        $api->post('message',[
            'as'    => 'message.store',
            'uses'  => 'MessageController@store'
        ]);
        //修改公告获取数据
        $api->post('message/{id}',[
            'as'    => 'message.getMessage',
            'uses'  => 'MessageController@getMessage'
        ]);
        //修改公告保存数据
        $api->patch('message/{id}',[
            'as'    => 'message.update',
            'uses'  => 'MessageController@update'
        ]);
        //修改公告状态
        $api->patch('message/state/{id}',[
            'as'    => 'message.editState',
            'uses'  => 'MessageController@editState'
        ]);
        /**
         * 系统公告end
         */
        /**
         * 系统日志start
         */
        //系统日志
        $api->get('syslog',[
            'as'    => 'log.syslog',
            'uses'  => 'SyslogController@index'
        ]);
        //接口日志
        $api->get('apilog',[
            'as'=> 'log.apilog',
            'uses'  => 'SyslogController@apiLog'
        ]);
        //游戏玩家API登录日志
        $api->get('userLoginLog',[
            'as'=> 'log.userLoginLog',
            'uses'  => 'SyslogController@UserLoginLog'
        ]);

        //游戏玩家登录日志
        $api->get('playerLoginLog',[
            'as'=> 'log.playerLoginLog',
            'uses'  => 'SyslogController@PlayerLoginLog'
        ]);
        //查看厅主操作日志
        $api->get('agentOperationLog',[
            'as'=> 'log.agentOperationLog',
            'uses'  => 'SyslogController@AgentOperationLog'
        ]);
        //取消派彩操作日志
        $api->get('exception/cash/log',[
            'as'=> 'log.exceptionCashLog',
            'uses'  => 'SyslogController@ExceptionCashLog'
        ]);
        // 联调账号调用接口统计日志
        $api->get('debugAccount',[
            'as'=> 'log.apilog',
            'uses'  => 'SyslogController@DebugAccount'
        ]);

        /**
         * 系统日志end
         */
        /**
         * 系统维护start
         */
        //获取系统维护信息
        $api->get('getmaintain',[
            'uses'  => 'MaintainController@getmaintain'
        ]);
        //添加平台维护
        $api->post('sysmaintain',[
            'uses'  => 'MaintainController@sysmaintain'
        ]);
        //添加游戏厅维护
        $api->post('hallmaintain',[
            'uses'  => 'MaintainController@hallmaintain'
        ]);
        /**
         * 系统维护end
         */
        /**
         * 厅主菜单管理
         */
        //获取后台系统菜单列表
        $api->get('agent/menus',[
            'as'    => 'menus.index',
            'uses'  => 'AgentMenusController@index',
        ]);

        //编辑后台系统菜单获取数据
        $api->post('agent/menus/{id}',[
            'as'    => 'menus.show',
            'uses'  => 'AgentMenusController@show',
        ]);

        //添加后台菜单操作
        $api->post('agent/menus',[
            'as'    => 'menus.store',
            'uses'  => 'AgentMenusController@store',
        ]);

        //修改后台系统菜单提交
        $api->patch('agent/menus/{id}',[
            'as'    => 'menus.update',
            'uses'  => 'AgentMenusController@update'
        ]);

        //删除后台单个菜单
        $api->delete('agent/menus/{id}',[
            'as'    => 'menus.destroy',
            'uses'  => 'AgentMenusController@destroy'
        ]);

        //获取菜单数据
        $api->get('agent/getMenus',[
            'uses'  => 'AgentMenusController@getMenusList'
        ]);
        /**
         * 厅主菜单管理end
         */

        //首页统计厅主总数量
        $api->get('index/hallNumber',[
            'uses'  => 'AccountStatisticsController@IndexCountHallNumber'
        ]);
        //首页今日投注单数
        $api->get('index/chartNumber',[
            'uses'  => 'ChartStatisticsController@ToDayChartNumber'
        ]);
        //首页厅主排名
        $api->get('index/hallRanking',[
            'uses'  => 'GainStatisticsController@IndexHallRanking'
        ]);
        //首页厅主活跃会员排名
        $api->get('index/hallActiveUser',[
            'uses'  => 'AccountStatisticsController@IndexActiveMemberByHall'
        ]);


        //平台数据统计，天/月盈利数据
        $api->get('system/sysGainData',[
            'uses'  => 'GainStatisticsController@sysGainData'
        ]);
        //平台数据统计，天/月注单数据
        $api->get('system/sysChart',[
            'uses'  => 'ChartStatisticsController@sysChart'
        ]);
        //平台数据统计，厅主/代理总数和新增厅/代理主数量
        $api->get('system/countHallAndNewHall',[
            'uses'  => 'AccountStatisticsController@SysHallAndNewHall'
        ]);


//        //厅主/代理数据统计，所有厅主/代理盈利数据统计
//        $api->get('hall/gain',[
//            'uses'  => 'GainStatisticsController@AllHallGain'
//        ]);
//        //厅主/代理数据统计，所有厅主/代理的活跃会员数据统计
//        $api->get('hall/activeUser',[
//            'uses'  => 'AccountStatisticsController@AllActiveMemberByHall'
//        ]);
        //厅主/代理数据统计，单个厅主/代理下天/月盈利数据统计
        $api->get('hall/singleHallGain',[
            'uses'  => 'GainStatisticsController@SingleHallGain'
        ]);
        //厅主/代理统计，单个厅主/代理下天/月注单数据统计
        $api->get('hall/charNumber',[
            'uses'  => 'ChartStatisticsController@hallCharNumber'
        ]);
        //厅主统计，单个厅主下代理商和新增代理商数量统计
        $api->get('hall/agentCount',[
            'uses'  => 'AccountStatisticsController@CountAgentByHall'
        ]);
        //厅主统计，单个代理商下玩家总数和新增玩家数量统计
        $api->get('hall/AgentUser',[
            'uses'  => 'AccountStatisticsController@CountUserByAgent'
        ]);


        //玩家统计，最近15天/最近12个月新增玩家和活跃玩家的数据统计
        $api->get('user/ActiveAndNewAddUser',[
            'uses'  => 'AccountStatisticsController@ActiveAndNewAddUser'
        ]);

        //在线统计，昨日和今日在线统计对比
        $api->get('online/user',[
            'uses'  => 'AccountStatisticsController@CountUserOnline'
        ]);
        //在线统计，当天每小时不同投注额度的比例统计
        $api->get('online/charAmount',[
            'uses'  => 'ChartStatisticsController@ChartSectionByDate'
        ]);
        //在线统计，当天和昨天不同投注额度数量
        $api->get('online/chartNumber',[
            'uses'  => 'ChartStatisticsController@CountChartNumberByDays'
        ]);

        //厅主、代理商获取菜单权限列表
        $api->get('agent/menu/{grade_id}',[
            'uses'  => 'AgentController@getAgentMenu'
        ]);

        //保存厅主、代理商菜单权限
        $api->post('/agent/{agent_id}/menus',[
            'uses'  => 'AgentController@setMenuRole'
        ]);

        /**
         * 文档管理start
         */
            //文档管理列表
            $api->get('document', [
                'uses' => 'DocumentController@index'
            ]);
            //添加文档
            $api->post('document', [
                'uses' => 'DocumentController@store'
            ]);
            //获取文档管理详情
            $api->get('document/{id}', [
                'uses' => 'DocumentController@show'
            ]);
            //保存文档
            $api->put('document/{id}', [
                'uses' => 'DocumentController@update'
            ]);
            //删除文档
            $api->delete('document/{id}', [
                'uses' => 'DocumentController@delete'
            ]);
        /**
         * 文档管理end
         */

        //获取在线更新数据
        $api->get('iterate/index',[
            'uses'  => 'IterationController@index'
        ]);

        //在线更新，版本更新列表
        $api->get('gameVersion',[
            'uses'  => 'GameVersionController@index'
        ]);
        //添加版本更新
        $api->post('gameVersion',[
            'uses'  => 'GameVersionController@store'
        ]);
        //修改在线更新时获取数据
        $api->get('/gameVersion/{id}',[
            'uses'  => 'GameVersionController@show'
        ]);
        //保存在线更新
        $api->put('/gameVersion/{id}',[
            'uses'  => 'GameVersionController@update'
        ]);
        //删除在线更新
        $api->delete('/gameVersion/{id}',[
            'uses'  => 'GameVersionController@delete'
        ]);
        //游戏端调用在线更新
        $api->post('iterate/getInfo',[
            'uses'  => 'IterationController@getIterationInfo'
        ]);


        //电子游戏十三水房间列表显示
        $api->get('room', [
            'as' => 'room.index',
            'uses' => 'RoomInfoController@index',
        ]);

        //电子游戏十三水状态显示
        $api->get('room/status/show/{room_id}', [
            'as' => 'room.showSatus',
            'uses' => 'RoomInfoController@showSatus',
        ]);

        //电子游戏十三水游戏盈利率显示
        $api->get('room/odds/show/{room_id}', [
            'as' => 'room.showOdds',
            'uses' => 'RoomInfoController@showOdds',
        ]);

        //电子游戏十三水游戏赔率显示
        $api->get('room/rules/show/{room_id}', [
            'as' => 'room.showOdds',
            'uses' => 'RoomInfoController@showRules',
        ]);

        //电子游戏十三水房间添加
        $api->post('room', [
            'as' => 'room.store',
            'uses' => 'RoomInfoController@store',
        ]);

        //电子游戏十三水游戏分类显示
        $api->get('room/cat', [
            'as' => 'room.cat',
            'uses' => 'RoomInfoController@cat',
        ]);

        //电子游戏十三水房间状态修改
        $api->put('room/status', [
            'as' => 'room.updateStatus',
            'uses' => 'RoomInfoController@updateStatus',
        ]);

        //电子游戏十三水房间盈利率修改
        $api->put('room/odds', [
            'as' => 'room.updateStatus',
            'uses' => 'RoomInfoController@updateOdds',
        ]);

        //电子游戏十三水游戏赔率方案修改
        $api->put('room/rules', [
            'as' => 'room.updateStatus',
            'uses' => 'RoomInfoController@updatepRules',
        ]);

        //获取十三水游戏默认赔率方案
        $api->get('room/defalutOdds', [
            'as' => 'room.getDefaultOdds',
            'uses' => 'RoomInfoController@getDefaultOdds',
        ]);

        //获取十三水游戏默认赔率方案
        $api->put('room/defalutOdds', [
            'as' => 'room.updatepDefaultOdds',
            'uses' => 'RoomInfoController@updatepDefaultOdds',
        ]);


        //2.3版本路由区块 - 新周

        //风险控制列表
        $api->get('monitor',[
            'uses'  => 'MonitorController@list',
        ]);
        //设置单个监控项参数
        $api->put('monitor',[
            'uses'  => 'MonitorController@setMonitor'
        ]);
        //设置单个监控项的状态
        $api->put('monitor/status',[
            'uses'  => 'MonitorController@setStatus'
        ]);
        //获取报警账号列表操作
        $api->get('alarm/list',[
            'uses'  => 'MonitorController@alarmList'
        ]);
        //添加报警账号操作
        $api->post('alarm',[
            'uses'  => 'MonitorController@addAlarm'
        ]);
        //编辑报警账号时获取信息
        $api->get('alarm',[
            'uses'  => 'MonitorController@getAlarmInfo'
        ]);
        //编辑保存报警账号
        $api->put('alarm',[
            'uses'  => 'MonitorController@updateAlarm'
        ]);
        //删除报警账号操作
        $api->delete('alarm',[
            'uses'  => 'MonitorController@deleteAlarm'
        ]);
        //查看报警记录列表
        $api->get('push/list',[
            'uses'  => 'MonitorController@getPushLog'
        ]);
        //获取监控数据列表
        $api->get("trigger",[
            'uses'  => 'MonitorController@getLog'
        ]);

        //
        //2.3版本路由区块 - 朝国
        /*++++++++++++++++++++++红包活动管理 start +++++++++++++++++++++++++++++*/

        // 红包活动列表
        $api->get('redPackets', ['uses' => 'RedPacketsController@index']);

        // 单个红包活动 编辑数据获取
        $api->get('redPackets/show/{id}', ['uses' => 'RedPacketsController@show']);

        // 单个红包活动的详情
        $api->get('redPackets/showDetail/{id}', ['uses' => 'RedPacketsController@showDetail']);

        // 厅主红包活动列表
        $api->get('agentRedPackets', ['uses' => 'AgentRedPacketsController@index']);

        // 厅主红包活动列详情
        $api->get('agentRedPackets/show', ['uses' => 'AgentRedPacketsController@show']);

        // 添加红包活动
        $api->post('redPackets', ['uses' => 'RedPacketsController@store']);

        // 修改红包活动
        $api->put('redPackets/{packet_id}', ['uses' => 'RedPacketsController@updated']);

        // 删除红包活动
        $api->delete('redPackets/{packet_id}', ['uses' => 'RedPacketsController@delete']);

        // 联调账号注单数统计
        $api->get('sys/chatInfoCount', ['uses' => 'SyslogController@chatInfoCount']);



        /*++++++++++++++++++++++红包活动管理 end +++++++++++++++++++++++++++++*/


        //
        //2.3版本路由区块 - 松坚

            //厅主公告start
                //添加厅主公告
                $api->post('agent/message', [
                    'uses' => 'AgentMessageController@store',
                ]);
                //厅主公告列表
                $api->get('agent/message', [
                    'uses' => 'AgentMessageController@index',
                ]);
                //编辑厅主公告时获取数据
                $api->get('agent/message/{id}', [
                    'uses' => 'AgentMessageController@show',
                ]);
                //保存厅主公告
                $api->put('agent/message/{id}', [
                    'uses' => 'AgentMessageController@update',
                ]);
                //删除厅主公告
                $api->delete('agent/message/{id}', [
                    'uses' => 'AgentMessageController@delete',
                ]);
            //厅主公告end
            //荷官管理start
                //添加荷官
                $api->post('dealer', [
                    'uses' => 'DealerController@store',
                ]);
                //荷官列表
                $api->get('dealer', [
                    'uses' => 'DealerController@index',
                ]);
                //编辑荷官时获取数据
                $api->get('dealer/{id}', [
                    'uses' => 'DealerController@show',
                ]);
                //保存荷官
                $api->put('dealer/{id}', [
                    'uses' => 'DealerController@update',
                ]);
                //删除荷官
                $api->delete('dealer/{id}', [
                    'uses' => 'DealerController@delete',
                ]);
            //荷官管理end

        //*++++++++++++++++++++++域名管理start*++++++++++++++++++++++
        //获取域名列表
        $api->get('gamehost', [
            'uses' => 'GameHostController@index',
        ]);
        //修改域名
        $api->put('gamehost/{id}', [
            'uses' => 'GameHostController@update',
        ]);
        // 修改状态
        $api->put('gamehost/status/{id}', [
            'uses' => 'GameHostController@status',
        ]);
        // 添加域名
        $api->post('gamehost', [
            'uses' => 'GameHostController@store',
        ]);

        // 添加域名
        $api->delete('gamehost/{id}', [
            'uses' => 'GameHostController@destroy',
        ]);
        //*++++++++++++++++++++++域名管理end*++++++++++++++++++++++++

        //*+++++++++++++++++++++++（共享钱包）重新派彩管理+++++++++++++++++++++++++++
        //重新派彩数据列表
        $api->get("abnormalList",[
            "uses"  => "PayoutController@getList",
        ]);

        //进行重新派彩操作
        $api->post('refresh',[
            'uses' => 'PayoutController@refresh',
        ]);

        //获取回退金额列表
        $api->get('getRollbackList',[
            'uses'  => 'PayoutController@getRollbackList',
        ]);

        //进行用户金额回退操作
        $api->post('rollbackMoney',[
            'uses'  => 'PayoutController@rollbackMoney',
        ]);
    });

    });
