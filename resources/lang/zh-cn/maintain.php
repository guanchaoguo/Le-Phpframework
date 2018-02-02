<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/5
 * Time: 11:17
 * 交收相关错误提示
 */
return [
    'success'   => '操作成功',
    'fails'     => '操作失败',

    'comtent' => [
        'required'  => '系统维护内容不能为空',
    ],
    'start_date'    => [
        'required'  => '开始时间不能为空',
    ],
    'end_date'    => [
        'required'  => '结束时间不能为空',
        'end_lt'    => '开始时间不能大于结束时间',
    ],
    'games' => [
        'required'  => '请选择需要维护的游戏'
    ],

];