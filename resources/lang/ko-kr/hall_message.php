<?php
return array (
  'message' => 
  array (
    'required' => '콘텐츠는 비워 둘 수 없습니다.',
  ),
  'start_date' => 
  array (
    'required' => '시작 시간은 비워 둘 수 없습니다.',
    'date_format' => '시작 시간이 잘못되었습니다.',
    'after' => '시작 시간이 현재 시간보다 큽니다.',
  ),
  'end_date' => 
  array (
    'required' => '종료 시간은 비워 둘 수 없습니다.',
    'date_format' => '종료 시간의 형식이 잘못되었습니다.',
    'after' => '종료 시간이 시작 시간보다 큽니다.',
  ),
  'data_not_exist' => '데이터가 존재하지 않습니다.',
);