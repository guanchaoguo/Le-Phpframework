<?php
return array (
  'title' => 
  array (
    'required' => '模板標題不能為空',
    'unique' => '模板標題已存在',
  ),
  'code' => 
  array (
    'required' => '風格代碼不能為空',
    'unique' => '風格代碼已存在',
  ),
  'not_exist' => '模板不存在',
);