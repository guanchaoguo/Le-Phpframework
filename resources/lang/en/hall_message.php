<?php
return array (
  'message' => 
  array (
    'required' => 'the content can not be blank',
  ),
  'start_date' => 
  array (
    'required' => 'The start time can not be empty',
    'date_format' => 'The start time is malformed',
    'after' => 'The start time is greater than the current time',
  ),
  'end_date' => 
  array (
    'required' => 'The end time can not be empty',
    'date_format' => 'The end time is in the wrong format',
    'after' => 'The end time is greater than the start time',
  ),
  'data_not_exist' => 'Data does not exist',
);