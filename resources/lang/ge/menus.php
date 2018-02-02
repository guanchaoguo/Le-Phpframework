<?php
return array (
  'success' => 'Der erfolgreiche Betrieb',
  'fails' => 'Operation fehlgeschlagen',
  'menu_not_exist' => 'Menü nicht vorhanden ist',
  'empty_list' => 'Datenliste ist leer',
  'data_error' => 'Datenfehler',
  'parent_id' => 
  array (
    'required' => 'Ihre Eltern Menü kann nicht leer sein',
  ),
  'class' => 
  array (
    'required' => 'Menütyp kann nicht null sein',
    'integer' => 'Menüsart Feldtyp kann nur ein Integer-Typ sein',
    'max' => 'Menütypen können nur eingeben 0-9',
  ),
  'title_cn' => 
  array (
    'required' => 'Chinesisch-Menü-Name darf nicht leer sein',
    'max' => 'Chinesisch-Menü kann nur den Namen von maximal 45 Zeichen eingeben',
  ),
  'title_en' => 
  array (
    'required' => 'Menü Englisch Name darf nicht leer sein',
    'max' => 'Englisch-Menü-Name kann nur maximal 45 Zeichen eingeben',
  ),
  'icon' => 
  array (
    'required' => 'Menüsymbol kann nicht leer sein',
  ),
  'link_url' => 
  array (
    'required' => 'Menü Link-Adresse darf nicht leer sein',
    'max' => 'Menü-Link-Adresse kann nur maximal 255 Zeichen eingeben',
  ),
  'sort' => 
  array (
    'required' => 'Sort-Menü kann nicht leer sein',
    'integer' => 'Sort-Menü kann nur Integer-Typ eingeben',
  ),
  'state' => 
  array (
    'required' => 'Bitte wählen Sie, ob das Menü anzuzeigen',
    'integer' => 'Menüzustandswert Typ Fehler',
    'max' => 'Menüstatuswertfehler',
  ),
  'menu_code' => 
  array (
    'required' => 'Menü-IDs kann nicht leer sein',
  ),
  'agent_id' => 
  array (
    'required' => 'ID darf nicht leer sein Haupthalle',
    'integer' => 'Nur die Hauptnummer ID Saaltypen',
  ),
  'grade_id' => 
  array (
    'required' => 'Haupthalle Typ-ID darf nicht leer sein',
    'integer' => 'Nur die Haupttypen ID-Nummer Saal',
  ),
  'menus' => 
  array (
    'required' => 'Assigned Menü darf nicht leer sein',
  ),
);