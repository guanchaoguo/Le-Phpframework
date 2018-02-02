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

    /*****************数据验提示********************/
    'parent_id' => [
        'required'  => '所属父类菜单不能为空',
    ],
    'class'     => [
        'required'  => '菜单类型不能为空',
        'integer'   => '菜单类型字段类型只能为整数类型',
        'max'       => '菜单类型只能输入0-9',
    ],
    'title_cn'  => [
        'required'   => '菜单中文名称不能为空',
        'max'        => '菜单中文名称最大只能输入45个字符',
    ],
    'title_en'  => [
        'required'   => '菜单英文名称不能为空',
        'max'        => '菜单英文名称最大只能输入45个字符',
    ],
    'icon'      => [
        'required'      => '菜单图标不能为空',
    ],
    'link_url'   => [
        'required'     => '菜单链接地址不能为空',
        'max'           => '菜单链接地址最大只能输入255个字符',
    ],
    'sort'      => [
        'required'     => '菜单排序不能为空',
        'integer'       => '菜单排序只能输入整数类型',
    ],
    'state'     => [
        'required'      => '请选择菜单是否显示',
        'integer'       => '菜单状态值类型错误',
        'max'           => '菜单状态值错误',
    ],
    'menu_code'     => [
        'required'      => '菜单标识符不能为空',
    ],
    'agent_id'  => [
        'required'      => '厅主ID不能为空',
        'integer'       => '厅主ID只能为数字类型',
    ],
    'grade_id'  => [
        'required'      => '厅主类型ID不能为空',
        'integer'       => '厅主类型ID只能为数字类型',
    ],
    'menus' => [
        'required'  => '分配的菜单不能为空'
    ],
];