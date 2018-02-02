<?php
return array (
  'success' => 'Successful operation',
  'fails' => 'operation failed',
  'menu_not_exist' => 'The menu does not exist',
  'empty_list' => 'The data list is empty',
  'data_error' => 'data error',
  'parent_id' => 
  array (
    'required' => 'The parent class menu can not be empty',
  ),
  'class' => 
  array (
    'required' => 'The menu type can not be empty',
    'integer' => 'The menu type field type can only be an integer type',
    'max' => 'Menu type can only enter 0-9',
  ),
  'title_cn' => 
  array (
    'required' => 'The Chinese name of the menu can not be empty',
    'max' => 'Menu Chinese name can only enter the maximum 45 characters',
  ),
  'title_en' => 
  array (
    'required' => 'The menu English name can not be empty',
    'max' => 'Menu English name can only enter the maximum 45 characters',
  ),
  'icon' => 
  array (
    'required' => 'The menu icon can not be empty',
  ),
  'link_url' => 
  array (
    'required' => 'The menu link address can not be empty',
    'max' => 'Menu link address can only enter up to 255 characters',
  ),
  'sort' => 
  array (
    'required' => 'Menu sort can not be empty',
    'integer' => 'Menu sort can only enter integer type',
  ),
  'state' => 
  array (
    'required' => 'Please select whether the menu is displayed',
    'integer' => 'The menu status value is incorrect',
    'max' => 'The menu status value is incorrect',
  ),
  'menu_code' => 
  array (
    'required' => 'The menu identifier can not be empty',
  ),
  'agent_id' => 
  array (
    'required' => 'The hall ID can not be empty',
    'integer' => 'Office ID can only be a numeric type',
  ),
  'grade_id' => 
  array (
    'required' => 'The hall type ID can not be empty',
    'integer' => 'The hall type ID can only be a numeric type',
  ),
  'menus' => 
  array (
    'required' => 'The assigned menu can not be empty',
  ),
);