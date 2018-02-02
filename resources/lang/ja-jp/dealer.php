<?php
return array (
  'dealer_id' => 
  array (
    'required' => 'ディーラーIDは空にできません',
    'unique' => 'ディーラーIDは既に存在します',
  ),
  'dealer_name' => 
  array (
    'required' => 'ディーラー名は空にできません',
  ),
  'dealer_img' => 
  array (
    'required' => '写真をアップロードしてください',
  ),
);