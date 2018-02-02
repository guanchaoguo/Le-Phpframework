<?php
/**
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/4/11
 * Time: 14:32
 */
return [
    'title' => [
        'required' => '模板标题不能为空',
        'unique' => '模板标题已存在',
    ],
    'code' => [
        'required' => '风格代码不能为空',
        'unique' => '风格代码已存在',
    ],
    'not_exist' => '模板不存在',
];