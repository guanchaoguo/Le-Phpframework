<?php
return array (
  'message' => 
  array (
    'required' => 'Inhalt darf nicht leer sein',
  ),
  'start_date' => 
  array (
    'required' => 'Startzeit darf nicht leer sein',
    'date_format' => 'Die Startzeit ist fehlerhaft',
    'after' => 'Die Startzeit ist größer als die aktuelle Zeit',
  ),
  'end_date' => 
  array (
    'required' => 'Endzeit darf nicht leer sein',
    'date_format' => 'Die Endzeit ist im falschen Format',
    'after' => 'Die Endzeit ist größer als die Startzeit',
  ),
  'data_not_exist' => 'Daten existieren nicht',
);