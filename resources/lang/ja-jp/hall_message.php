<?php
return array (
  'message' => 
  array (
    'required' => 'コンテンツを空にすることはできません',
  ),
  'start_date' => 
  array (
    'required' => '開始時間は空ではありません',
    'date_format' => '開始時刻が間違っています',
    'after' => '開始時刻が現在の時刻よりも大きい',
  ),
  'end_date' => 
  array (
    'required' => '終了時刻は空白にできません',
    'date_format' => '終了時刻の形式が間違っています',
    'after' => '終了時刻が開始時刻よりも大きい',
  ),
  'data_not_exist' => 'データが存在しません',
);