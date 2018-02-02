<?php
return array (
  'title' => 
  array (
    'required' => '템플릿 제목은 비워 둘 수 없습니다',
    'unique' => '템플릿 제목이 이미 존재합니다',
  ),
  'code' => 
  array (
    'required' => '스타일 코드는 비워 둘 수 없습니다',
    'unique' => '이미 존재하는 스타일 코드',
  ),
  'not_exist' => '템플릿이 존재하지 않습니다',
);