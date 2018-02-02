<?php
return array (
  'dealer_id' => 
  array (
    'required' => 'ID peniaga tidak boleh kosong',
    'unique' => 'ID peniaga sudah wujud',
  ),
  'dealer_name' => 
  array (
    'required' => 'Nama peniaga tidak boleh kosong',
  ),
  'dealer_img' => 
  array (
    'required' => 'Sila muat naik gambar',
  ),
);