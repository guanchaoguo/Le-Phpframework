<?php
return array (
    'area' =>
        array (
            'required' => 'Please select the operating area',
        ),
    'time_zone' =>
        array (
            'required' => 'Please select the time zone',
        ),
    'agent_name' =>
        array (
            'required' => 'The login name can not be empty',
            'unique' => 'The login name already exists',
            'regex' => 'The login name must start with a letter, 6-20 letters, underscore, and numbers',
        ),
    'real_name' =>
        array (
            'required' => 'Username can not be empty',
            'regex' => 'The user name must consist of 3-20 letters, underscore, number, and Chinese',
        ),
    'password' =>
        array (
            'required' => 'password can not be blank',
            'min' => 'The password can not be less than 6 digits',
            'confirmed' => 'Password and confirmation password are inconsistent',
        ),
    'tel' =>
        array (
            'required' => 'The phone number can not be empty',
        ),
    'email' =>
        array (
            'required' => 'E-mail can not be empty',
            'email' => 'The mailbox is malformed',
            'unique' => 'The mailbox already exists',
        ),
    'hall_id' =>
        array (
            'required' => 'Please choose the main hall directly',
        ),
    'agent_code' =>
        array (
            'required' => 'The agent code can not be empty',
            'unique' => 'The agent code already exists',
            'error' => 'The agent code must consist of letters beginning with, 3-6 characters, underscore, and numbers',
        ),
    'success' => 'Successful operation',
    'save_fails' => 'Save failed',
    'save_success' => 'Saved successfully',
    'add_fails' => 'add failed',
    'grade_id_error' => 'Grade_id parameter value is incorrect',
    'fails' => 'operation failed',
    'user_not_exist' => 'The player does not exist',
    'user_has_exist' => 'The player already exists',
    'agent_not_exist' => 'The agent does not exist',
    'hall_not_exist' => 'Hall does not exist',
    'game_not_exist' => 'The game does not exist',
    'limit_group_exist' => 'The quota group already exists',
    'limit_group_not_exist' => 'Quota group does not exist',
    'param_error' => 'Parameter value is incorrect',
    'insufficient_balance' => 'Insufficient balance',
    'file_not_eixt' => 'file does not exist',
    'min_max_error' => 'The maximum value can not be less than or equal to the minimum value',
    'last_max_error' => 'The maximum value of the last one must be empty',
    'last_max_next_min' => 'The maximum value of the previous item must be equal to the minimum value of the next item',
    'ip_error' => 'IP address is incorrect',
    'domain_error' => 'The domain name is incorrect',
    'whitelist_not_exist' => 'White list does not exist',
    'balance_str_error' => 'The quota must be a number',
    'export_requisite_uid' => 'Please select the player and then export the data',
    'no_data_export' => 'The data is empty and can not be exported',
    'hall_requiset' => 'Please choose a main hall',
    'agent_requiset' => 'Please select a proxy',
    'player_requiset' => 'Please select a player',
    'scale_error' => 'The proportion must be greater than zero',
    'user_name' => 'The login name must start with a letter, 6-20 letters, underscore, and numbers',
    'hall_has_data' => 'The owner has added the data',
    'alias' => 'Username can not be empty',
    'user_sign_out' => 'The player has logged out',
);