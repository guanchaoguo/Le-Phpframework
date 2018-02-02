<?php
return array (
  'dealer_id' => 
  array (
    'required' => 'รหัสดีลเลอร์ต้องไม่ว่างเปล่า',
    'unique' => 'มีตัวแทนจำหน่ายอยู่แล้ว',
  ),
  'dealer_name' => 
  array (
    'required' => 'ชื่อดีลเลอร์ต้องไม่ว่างเปล่า',
  ),
  'dealer_img' => 
  array (
    'required' => 'โปรดอัปโหลดรูปภาพ',
  ),
);