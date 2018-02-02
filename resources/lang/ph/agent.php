<?php
return array (
    'area' =>
        array (
            'required' => 'Mangyaring piliin ang operating lugar',
        ),
    'time_zone' =>
        array (
            'required' => 'Mangyaring piliin ang time zone',
        ),
    'agent_name' =>
        array (
            'required' => 'login name hindi maaaring walang laman',
            'unique' => 'login name ay umiiral na',
            'regex' => 'Login ng pangalan ay dapat magsimula sa isang sulat, 6-20 titik, underscore, at numero',
        ),
    'real_name' =>
        array (
            'required' => 'Username Hindi maaaring walang laman',
            'regex' => 'Username ay kailangang huwag 3-20 titik, underscore, mga numero at Chinese komposisyon',
        ),
    'password' =>
        array (
            'required' => 'Password ay hindi maaaring walang laman',
            'min' => 'Ang password ay hindi maaaring mas mababa sa 6',
            'confirmed' => 'Password at Kumpirmahin ang Password naaayon',
        ),
    'tel' =>
        array (
            'required' => 'Ang numero ng telepono ay hindi maaaring walang laman',
        ),
    'email' =>
        array (
            'required' => 'E-mail ay hindi maaaring walang laman',
            'email' => 'E-mail format error',
            'unique' => 'E-mail ay mayroon na',
        ),
    'hall_id' =>
        array (
            'required' => 'Mangyaring pumili nang direkta sa ilalim ng main hall',
        ),
    'agent_code' =>
        array (
            'required' => 'Ahente code ay hindi maaaring walang laman',
            'unique' => 'Ahente code ay umiiral na',
            'error' => 'Ahente mula sa code ay dapat magsimula sa isang sulat, 3-6 mga titik, underscore, at numero',
        ),
    'success' => 'Ang matagumpay na operasyon',
    'save_fails' => 'I-save ang nabigo',
    'save_success' => 'matagumpay na nai-save',
    'add_fails' => 'Idagdag Nabigong',
    'grade_id_error' => 'grade_id parameter na halaga error',
    'fails' => 'nabigo ang pagpapatakbo',
    'user_not_exist' => 'Players hindi umiiral',
    'user_has_exist' => 'Manlalaro ay mayroon na',
    'agent_not_exist' => 'Ahente hindi umiiral',
    'hall_not_exist' => 'Main hall ay hindi na umiiral',
    'game_not_exist' => 'Ang laro ay hindi umiiral',
    'limit_group_exist' => 'Hangganan ng grupo ay mayroon na',
    'limit_group_not_exist' => 'Walang limitasyon pagpapangkat',
    'param_error' => 'Maling halaga ng parameter',
    'insufficient_balance' => 'sapat na mga pondo',
    'file_not_eixt' => 'File ay hindi umiiral',
    'min_max_error' => 'Hindi mas mababa sa isang maximum na halaga katumbas ng minimum',
    'last_max_error' => 'Sa wakas, isang maximum Dapat bakante',
    'last_max_next_min' => 'Ang isang maximum na halaga sa isang minimum ay dapat na katumbas ng',
    'ip_error' => 'IP address ay hindi tama',
    'domain_error' => 'Hindi tamang pangalan ng domain',
    'whitelist_not_exist' => 'White list ay hindi umiiral',
    'balance_str_error' => 'Limit ay kailangang isang numero',
    'export_requisite_uid' => 'Mangyaring pumili ng isang player at pagkatapos ay i-export ang data',
    'no_data_export' => 'Data ay walang laman, hindi maaaring i-export',
    'hall_requiset' => 'Mangyaring pumili ng isang pangunahing bulwagan',
    'agent_requiset' => 'Mangyaring pumili ng isang proxy',
    'player_requiset' => 'Pakipili ang isang player',
    'scale_error' => 'Dapat ito ay mas malaki kaysa sa 0 sa proporsyon accounted',
    'user_name' => 'Login ng pangalan ay dapat magsimula sa isang sulat, 6-20 titik, underscore, at numero',
    'hall_has_data' => 'Ang pangunahing bulwagan ay naidagdag na sa ibabaw ng data',
    'alias' => 'Username Hindi maaaring walang laman',
    'user_sign_out' => 'Manlalaro ay nai-log out',
);