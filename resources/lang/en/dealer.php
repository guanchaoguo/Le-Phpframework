<?php
return array (
  'dealer_id' => 
  array (
    'required' => 'Dealer ID can not be empty',
    'unique' => 'Dealer ID already exists',
  ),
  'dealer_name' => 
  array (
    'required' => 'Dealer name can not be empty',
  ),
  'dealer_img' => 
  array (
    'required' => 'Please upload pictures',
  ),
);