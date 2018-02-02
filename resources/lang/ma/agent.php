<?php
return array (
    'area' =>
        array (
            'required' => 'Sila pilih kawasan operasi',
        ),
    'time_zone' =>
        array (
            'required' => 'Sila pilih zon masa',
        ),
    'agent_name' =>
        array (
            'required' => 'nama log masuk tidak boleh kosong',
            'unique' => 'nama log masuk sudah wujud',
            'regex' => 'nama log masuk mesti bermula dengan surat, 6-20 huruf, garis bawah, dan nombor',
        ),
    'real_name' =>
        array (
            'required' => 'Nama pengguna tidak boleh kosong',
            'regex' => 'Nama pengguna mesti mengandungi 3-20 huruf, garis bawah, nombor dan komposisi Cina',
        ),
    'password' =>
        array (
            'required' => 'Kata laluan tidak boleh kosong',
            'min' => 'Kata laluan tidak boleh kurang daripada 6',
            'confirmed' => 'Kata laluan dan Sahkan kata laluan tidak konsisten',
        ),
    'tel' =>
        array (
            'required' => 'Nombor telefon tidak boleh kosong',
        ),
    'email' =>
        array (
            'required' => 'E-mel tidak boleh kosong',
            'email' => 'E-mel ralat format',
            'unique' => 'E-mel sudah wujud',
        ),
    'hall_id' =>
        array (
            'required' => 'Sila pilih secara langsung di bawah dewan utama',
        ),
    'agent_code' =>
        array (
            'required' => 'kod ejen tidak boleh kosong',
            'unique' => 'kod ejen sudah wujud',
            'error' => 'Ejen dari kod mesti bermula dengan huruf, 3-6 huruf, garis bawah, dan nombor',
        ),
    'success' => 'operasi berjaya',
    'save_fails' => 'Simpan gagal',
    'save_success' => 'berjaya disimpan',
    'add_fails' => 'menambah Gagal',
    'grade_id_error' => 'ralat grade_id nilai parameter',
    'fails' => 'operasi gagal',
    'user_not_exist' => 'Pemain tidak wujud',
    'user_has_exist' => 'Pemain sudah wujud',
    'agent_not_exist' => 'Ejen tidak wujud',
    'hall_not_exist' => 'dewan utama tidak wujud',
    'game_not_exist' => 'Permainan ini tidak wujud',
    'limit_group_exist' => 'kumpulan had sudah wujud',
    'limit_group_not_exist' => 'Tidak ada perkumpulan had',
    'param_error' => 'nilai parameter yang tidak betul',
    'insufficient_balance' => 'dana yang tidak mencukupi',
    'file_not_eixt' => 'Fail tidak wujud',
    'min_max_error' => 'Tidak kurang daripada nilai maksimum bersamaan dengan minimum',
    'last_max_error' => 'Akhir sekali, maksimum boleh kosong',
    'last_max_next_min' => 'Nilai maksimum sekurang-kurangnya mestilah sama dengan yang',
    'ip_error' => 'Alamat IP adalah tidak betul',
    'domain_error' => 'nama domain yang tidak betul',
    'whitelist_not_exist' => 'senarai putih tidak wujud',
    'balance_str_error' => 'Had mestilah nombor',
    'export_requisite_uid' => 'Sila pilih pemain dan kemudian data eksport',
    'no_data_export' => 'Data kosong, tidak boleh dieksport',
    'hall_requiset' => 'Sila pilih dewan utama',
    'agent_requiset' => 'Sila pilih proksi',
    'player_requiset' => 'Sila pilih pemain',
    'scale_error' => 'Ia mesti lebih besar daripada 0 berkadaran menyumbang',
    'user_name' => 'nama log masuk mesti bermula dengan surat, 6-20 huruf, garis bawah, dan nombor',
    'hall_has_data' => 'Dewan utama telah ditambah ke atas data yang',
    'alias' => 'Nama pengguna tidak boleh kosong',
    'user_sign_out' => 'Pemain telah log keluar',
    'max_balance_is_null' => 'Sila masukkan siling suntikan sahaja',
    'min_balance_is_null' => 'Sila masukkan ambang suntikan sahaja',
    'max_balance_str_error' => 'A suntikan siling tunggal mestilah nombor',
    'min_balance_str_error' => 'A suntikan sahaja amaun minimum mestilah nombor',
    'min_balance_is_not_0' => 'A suntikan sahaja amaun minimum mestilah lebih besar daripada 0',
);