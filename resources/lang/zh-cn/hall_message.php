<?php
/**
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/10/13
 * Time: 14:50
 */
return [
    'message' => [
        'required' => '内容不能为空'
    ],
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
    'data_not_exist' => '数据不存在',
];