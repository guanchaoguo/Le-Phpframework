<?php
return array (
  'success' => '操作成功',
  'fails' => '操作失敗',
  'menu_not_exist' => '菜單不存在',
  'empty_list' => '數據列表為空',
  'data_error' => '數據錯誤',
  'sub_account' => '該分組下還有子賬戶，不能進行刪除操作，需要解除子賬戶關聯關係後才能進行',
  'user_exists' => '用戶名已經存在',
  'group_name' => 
  array (
    'required' => '請輸入角色名稱',
    'max' => '角色名稱最大隻能輸入45個字符',
  ),
  'roles' => 
  array (
    'required' => '角色權限不能為空',
  ),
  'user_name' => 
  array (
    'required' => '賬號名稱不能為空',
    'max' => '賬號長度為3-45個字符',
    'min' => '賬號長度為3-45個字符',
  ),
  'password' => 
  array (
    'required' => '密碼不能為空',
    'max' => '密碼長度為6-20個字符',
    'min' => '密碼長度為6-20個字符',
    'confirmation' => '兩次密碼輸入不一致',
  ),
  'desc' => 
  array (
    'max' => '備註信息不能超過45個字符',
  ),
  'group' => 
  array (
    'required' => '請選擇角色分組',
    'integer' => '角色分組數據類型只能為整型',
  ),
);