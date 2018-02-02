<?php
return array (
  'success' => '成功した操作',
  'fails' => '操作が失敗しました',
  'menu_not_exist' => 'メニューは存在しません。',
  'empty_list' => 'データリストは空です',
  'data_error' => 'データエラー',
  'parent_id' => 
  array (
    'required' => 'あなたの親メニューは空にすることはできません',
  ),
  'class' => 
  array (
    'required' => 'メニューの種類はnullにすることはできません',
    'integer' => 'メニューの種類のフィールドタイプは整数型を指定できます',
    'max' => 'メニューの種類は0-9のみを入力することができます',
  ),
  'title_cn' => 
  array (
    'required' => '中国のメニュー名は空にすることはできません',
    'max' => '中国のメニューは、最大45文字の名前を入力することができます',
  ),
  'title_en' => 
  array (
    'required' => '英語メニュー名は空にすることはできません',
    'max' => '英語のメニュー名は45文字まで入力することができます',
  ),
  'icon' => 
  array (
    'required' => 'メニューアイコンを空にすることはできません',
  ),
  'link_url' => 
  array (
    'required' => 'メニューリンクアドレスを空にすることはできません',
    'max' => 'メニューリンクアドレスは最大255個の文字を入力することができます',
  ),
  'sort' => 
  array (
    'required' => 'ソートメニューは空にすることはできません',
    'integer' => 'ソートメニューは、整数型のみを入力することができます',
  ),
  'state' => 
  array (
    'required' => 'メニューを表示するかどうかを選択してください',
    'integer' => 'メニュー状態値の型エラー',
    'max' => 'メニューステータス値の誤差',
  ),
  'menu_code' => 
  array (
    'required' => 'メニュー識別子は空にすることはできません',
  ),
  'agent_id' => 
  array (
    'required' => 'IDは、空のメインホールにはできません',
    'integer' => '唯一のメインホールID番号の種類',
  ),
  'grade_id' => 
  array (
    'required' => 'メインホールタイプのIDは空にすることはできません',
    'integer' => 'メインホールタイプID番号の種類のみ',
  ),
  'menus' => 
  array (
    'required' => '割り当てられたメニューは空にすることはできません',
  ),
);