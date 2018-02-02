<?php
return array (
  'title' => 
  array (
    'required' => 'The template title can not be empty',
    'unique' => 'The template title already exists',
  ),
  'code' => 
  array (
    'required' => 'Style code can not be empty',
    'unique' => 'Style code already exists',
  ),
  'not_exist' => 'The template does not exist',
);