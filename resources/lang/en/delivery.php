<?php
return array (
  'success' => 'Successful operation',
  'fails' => 'operation failed',
  'issue_not_exist' => 'The data does not exist',
  'empty_list' => 'The data list is empty',
  'data_error' => 'data error',
  'issue' => 
  array (
    'required' => 'The number of periods can not be empty',
    'numeric' => 'The number of periods can only be numeric',
  ),
  'start_date' => 
  array (
    'required' => 'The start time can not be empty',
  ),
  'end_date' => 
  array (
    'required' => 'The end time can not be empty',
    'le_start' => 'The end time can not be less than or equal to the start time',
    'has_been' => 'Time in the segment has been occupied',
  ),
  'issue_exist' => 'The period name already exists',
);