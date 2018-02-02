<?php
return array (
  'dealer_id' => 
  array (
    'required' => '荷官ID不能為空',
    'unique' => '荷官ID已經存在',
  ),
  'dealer_name' => 
  array (
    'required' => '荷官名稱不能空',
  ),
  'dealer_img' => 
  array (
    'required' => '請上傳圖片',
  ),
);