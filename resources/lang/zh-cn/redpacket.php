<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/6/28
 * Time: 11:19
 */
return [
    'time_not_coss' => '时间不能交叉！',
    'data_not_operate' => '该数据不能被操作！',
    'start_date' => [
        'required' => '开始时间不能为空',
        'date_format' => '开始时间格式错误',
        'after' => '开始时间要大于当前时间'
    ],
    'end_date' => [
        'required' => '结束时间不能为空',
        'date_format' => '结束时间格式错误',
        'after' => '结束时间要大于开始时间'
    ],
    'title'=> [
        'required' => '标题不能为空'
    ],
    'type'=> [
        'required' =>  '红包类型不能为空'
    ],
    'trigger'=> [
        'required' =>  '出发类型不能为空'
    ],
    'user_max'=> [
        'required' => '会员红包个数上限不能为空'
    ],
    'total_amount'=> [
        'required' => '红包金额不能为空'
    ],
    'total_number'=> [
        'required' => '红包个数不能为空'
    ],
    'requirements_type'=> [
        'required' =>  '条件设定不能为空'
    ],
    'user_largest'=> [
        'required' => '大额会员不能为空'
    ],
    'requirements_amount'=> [
        'required' =>  '抢红包条件时间不能为空'
    ],
];