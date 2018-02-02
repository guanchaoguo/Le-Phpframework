<?php
return array (
  'dealer_id' => 
  array (
    'required' => 'Händlernummer darf nicht leer sein',
    'unique' => 'Händlernummer existiert bereits',
  ),
  'dealer_name' => 
  array (
    'required' => 'Händlername darf nicht leer sein',
  ),
  'dealer_img' => 
  array (
    'required' => 'Bitte Bilder hochladen',
  ),
);