<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/7/12
 * Time: 16:33
 */
return [
    'number'    => [
        'required'  => '生成期数不能为空',
        'integer'   => '期数只能为整数类型',
        'max'       => '期数最多只能生成12期'
    ],
    'auto_start_error'  => '时间段已经被占用',
    'year'  => [
      'required'=> '请选择年份'
    ]
];