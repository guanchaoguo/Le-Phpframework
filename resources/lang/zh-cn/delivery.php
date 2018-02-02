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
    'issue_not_exist'    => '数据不存在',
    'empty_list'    => '数据列表为空',
    'data_error'    => '数据错误',

    'issue' => [
        'required'  => '期数不能为空',
        'numeric'   => '期数只能为数字类型'
    ],
    'start_date'    => [
        'required'  => '开始时间不能为空',
    ],
    'end_date'    => [
        'required'  => '结束时间不能为空',
        'le_start'  => '结束时间不能小于等于开始时间',
        'has_been'  => '时间段已被占用',
    ],

    'issue_exist'   => '期数名称已经存在',


];