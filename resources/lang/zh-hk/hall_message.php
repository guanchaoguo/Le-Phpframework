<?php
return array (
  'message' => 
  array (
    'required' => '內容不能為空',
  ),
  'start_date' => 
  array (
    'required' => '開始時間不能為空',
    'date_format' => '開始時間格式錯誤',
    'after' => '開始時間要大於當前時間',
  ),
  'end_date' => 
  array (
    'required' => '結束時間不能為空',
    'date_format' => '結束時間格式錯誤',
    'after' => '結束時間要大於開始時間',
  ),
  'data_not_exist' => '數據不存在',
);