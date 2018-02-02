<?php
return array (
  'dealer_id' => 
  array (
    'required' => 'ID đại lý không được để trống',
    'unique' => 'ID người bán đã tồn tại',
  ),
  'dealer_name' => 
  array (
    'required' => 'Tên đại lý không được để trống',
  ),
  'dealer_img' => 
  array (
    'required' => 'Xin vui lòng tải lên hình ảnh',
  ),
);