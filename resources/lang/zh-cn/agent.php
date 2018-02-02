<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/5
 * Time: 13:38
 */
return [
    'area' => [
        'required' => '请选择运营地区',
    ],
    'time_zone' => [
        'required' => '请选择时区',
    ],
    'agent_name' =>[
        'required' => '登录名不能为空',
        'unique' => '登录名已经存在',
        'regex' => '登录名必须由字母开头， 6-20位字母、下划线、和数字组成',
    ],
    'real_name' =>[
        'required' => '用户名不能为空',
        'regex' => '用户名必须为 3-20位字母、下划线、数字和中文组成',
    ],
    'password' => [
        'required' => '密码不能为空',
        'min' => '密码不能小于6位',
        'confirmed' => '密码和确认密码不一致',
    ],
    'tel' => [
        'required' => '手机号码不能为空',
    ],
    'email' => [
        'required' => '邮箱不能为空',
        'email' => '邮箱格式错误',
        'unique' => '邮箱已存在',
    ],
    'hall_id' => [
        'required' => '请选择直属厅主',
    ],
    'agent_code' => [
        'required' => '代理商code不能为空',
        'unique' => '代理商code已存在',
        'error' => '代理商code必须由字母开头， 3-6位字母、下划线、和数字组成',
    ],
    'success' => '操作成功',
    'save_fails' => '保存失败',
    'save_success' => '保存成功',
    'add_fails' => '添加失败',
    'grade_id_error' => 'grade_id 参数值错误',
    'fails' => '操作失败',
    'user_not_exist' => '玩家不存在',
    'user_has_exist' => '玩家已存在',
    'agent_not_exist' => '代理商不存在',
    'hall_not_exist' => '厅主不存在',
    'game_not_exist' => '游戏不存在',
    'limit_group_exist' => '限额分组已存在',
    'limit_group_not_exist' => '限额分组不存在',
    'param_error' => '参数错误',
    'insufficient_balance' => '余额不足',
    'file_not_eixt' => '文件不存在',
    'min_max_error' => '最大值不能小于等于最小值',
    'last_max_error' => '最后一条的最大值必须为空',
    'last_max_next_min' => '上一条的最大值必须等于下一条的最小值',
    'ip_error' => 'IP地址不正确',
    'domain_error' => '域名不正确',
    'whitelist_not_exist' => '白名单不存在',
    'balance_str_error' => '限额必须为数字',
    'param_error' => '参数值错误',
    'export_requisite_uid' => '请先选择玩家再导出数据',
    'no_data_export' => '数据为空，无法导出',
    'hall_requiset' => '请选择一个厅主',
    'agent_requiset' => '请选择一个代理',
    'player_requiset' => '请选择一个玩家',
    'scale_error' => '占成比例必须大于0',
    'user_name' => '登录名必须由字母开头， 6-20位字母、下划线、和数字组成',
    'hall_has_data' => '该厅主已经添加过数据了',

    'alias' => '用户名不能为空',
    'debugAccount' => '登录名必须为联调代理',
    'user_sign_out' => '玩家已登出',
    'notify_url' => [
        'required'  => '玩家离线通知地址不能为空'
    ]
];