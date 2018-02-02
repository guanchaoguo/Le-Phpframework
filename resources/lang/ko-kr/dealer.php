<?php
return array (
  'dealer_id' => 
  array (
    'required' => '딜러 ID는 비워 둘 수 없습니다.',
    'unique' => '딜러 ID가 이미 있습니다.',
  ),
  'dealer_name' => 
  array (
    'required' => '딜러 이름은 비워 둘 수 없습니다.',
  ),
  'dealer_img' => 
  array (
    'required' => '사진을 업로드하십시오.',
  ),
);