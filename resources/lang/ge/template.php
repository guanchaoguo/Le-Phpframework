<?php
return array (
  'title' => 
  array (
    'required' => 'Vorlagentitel darf nicht leer sein',
    'unique' => 'Vorlagentitel bereits vorhanden',
  ),
  'code' => 
  array (
    'required' => 'Style-Code darf nicht leer sein',
    'unique' => 'Style-Code, der bereits vorhanden',
  ),
  'not_exist' => 'Vorlage nicht vorhanden',
);