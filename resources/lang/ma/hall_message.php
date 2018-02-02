<?php
return array (
  'message' => 
  array (
    'required' => 'Kandungan tidak boleh kosong',
  ),
  'start_date' => 
  array (
    'required' => 'Masa mula tidak boleh kosong',
    'date_format' => 'Masa permulaan adalah salah',
    'after' => 'Masa permulaan adalah lebih besar daripada masa semasa',
  ),
  'end_date' => 
  array (
    'required' => 'Masa tamat tidak boleh kosong',
    'date_format' => 'Masa tamat dalam format yang salah',
    'after' => 'Masa tamat lebih besar dari masa mula',
  ),
  'data_not_exist' => 'Data tidak wujud',
);