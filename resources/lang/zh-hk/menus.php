<?php
return array (
  'success' => '操作成功',
  'fails' => '操作失敗',
  'menu_not_exist' => '菜單不存在',
  'empty_list' => '數據列表為空',
  'data_error' => '數據錯誤',
  'parent_id' => 
  array (
    'required' => '所屬父類菜單不能為空',
  ),
  'class' => 
  array (
    'required' => '菜單類型不能為空',
    'integer' => '菜單類型字段類型只能為整數類型',
    'max' => '菜單類型只能輸入0-9',
  ),
  'title_cn' => 
  array (
    'required' => '菜單中文名稱不能為空',
    'max' => '菜單中文名稱最大隻能輸入45個字符',
  ),
  'title_en' => 
  array (
    'required' => '菜單英文名稱不能為空',
    'max' => '菜單英文名稱最大隻能輸入45個字符',
  ),
  'icon' => 
  array (
    'required' => '菜單圖標不能為空',
  ),
  'link_url' => 
  array (
    'required' => '菜單鏈接地址不能為空',
    'max' => '菜單鏈接地址最大隻能輸入255個字符',
  ),
  'sort' => 
  array (
    'required' => '菜單排序不能為空',
    'integer' => '菜單排序只能輸入整數類型',
  ),
  'state' => 
  array (
    'required' => '請選擇菜單是否顯示',
    'integer' => '菜單狀態值類型錯誤',
    'max' => '菜單狀態值錯誤',
  ),
  'menu_code' => 
  array (
    'required' => '菜單標識符不能為空',
  ),
  'agent_id' => 
  array (
    'required' => '廳主ID不能為空',
    'integer' => '廳主ID只能為數字類型',
  ),
  'grade_id' => 
  array (
    'required' => '廳主類型ID不能為空',
    'integer' => '廳主類型ID只能為數字類型',
  ),
  'menus' => 
  array (
    'required' => '分配的菜單不能為空',
  ),
);