<?php
return array (
  'title' => 
  array (
    'required' => 'テンプレートのタイトルは空にすることはできません',
    'unique' => 'テンプレートのタイトルがすでに存在しています',
  ),
  'code' => 
  array (
    'required' => 'スタイルコードは空にすることはできません',
    'unique' => 'すでに存在するスタイルコード',
  ),
  'not_exist' => 'テンプレートが存在しません。',
);