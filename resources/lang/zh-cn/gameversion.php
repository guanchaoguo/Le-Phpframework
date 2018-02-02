<?php
/**
 * 游戏版本更新
 * User: chensongjian
 * Date: 2017/6/16
 * Time: 15:32
 */
return [
    'label' => [
        'required' => '请选择游戏平台',
        'in' => '游戏平台类型错误',
    ],
    'update_time' => [
        'required' => '请选择更新时间',
    ],
    'user_update_time' => [
        'required' => '本地更新时间不能为空',
    ],
    'content' => '请填写更新内容',
    'version_n' => '请填写版本号',
    'url' => '请填写url地址',
    'forced_up' => [
        'required' => '请选择是否强制更新',
        'in' => '强制更新状态值错误',
    ],
];