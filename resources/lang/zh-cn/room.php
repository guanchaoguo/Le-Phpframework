<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/5
 * Time: 13:38
 */
return [
    'room_name' =>[
        'required' => '房间名不能为空',
        'unique' => '房间名已经存在',
        'regex' => '房间名必须由字母开头， 6-20位字母、下划线、和数字组成',
    ],

    'room_id' => [
        'required' => '请选择房间',
    ],

    'success' => '操作成功',
    'save_fails' => '保存失败',
    'update_fails' => '请勿重复修改',
    'save_success' => '保存成功',
    'add_fails' => '添加失败',
    'room_id_error' => 'grade_id 参数值错误',
    'fails' => '操作失败',
    'room_not_exist' => '房间不存在',
    'param_error' => '参数错误',
    'insufficient_balance' => '余额不足',
    'min_max_error' => '最大值不能小于等于最小值',
    'param_error' => '参数值错误',
    'no_data_export' => '数据为空，无法导出',
    'scale_error' => '占成比例必须大于0',
    'user_name' => '登录名必须由字母开头， 6-20位字母、下划线、和数字组成',
];