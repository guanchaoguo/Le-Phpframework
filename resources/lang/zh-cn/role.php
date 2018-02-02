<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkl.com
 * Date: 2017/2/13
 * Time: 10:58
 * 后台菜单接口提示语
 */
return [
    'success'   => '操作成功',
    'fails'     => '操作失败',
    'menu_not_exist'    => '菜单不存在',
    'empty_list'    => '数据列表为空',
    'data_error'    => '数据错误',
    'sub_account'   => '该分组下还有子账户，不能进行删除操作，需要解除子账户关联关系后才能进行',
    'user_exists'  => '用户名已经存在',

    /*****************数据验提示********************/
    'group_name' => [
        'required'  => '请输入角色名称',
        'max'       => '角色名称最大只能输入45个字符'
    ],
    'roles' => [
        'required'  => '角色权限不能为空'
    ],

    'user_name' =>[
        'required'  => '账号名称不能为空',
        'max'       => '账号长度为3-45个字符',
        'min'       => '账号长度为3-45个字符',
    ],

    'password'  => [
        'required'  => '密码不能为空',
        'max'       => '密码长度为6-20个字符',
        'min'       => '密码长度为6-20个字符',
        'confirmation'  => '两次密码输入不一致',
    ],
    'desc'  => [
        'max'   => '备注信息不能超过45个字符',
    ],
    'group' => [
        'required'  => '请选择角色分组',
        'integer'   => '角色分组数据类型只能为整型'
    ]

];