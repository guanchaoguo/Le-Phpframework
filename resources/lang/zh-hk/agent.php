<?php
return array (
    'area' =>
        array (
            'required' => '請選擇運營地區',
        ),
    'time_zone' =>
        array (
            'required' => '請選擇時區',
        ),
    'agent_name' =>
        array (
            'required' => '登錄名不能為空',
            'unique' => '登錄名已經存在',
            'regex' => '登錄名必須由字母開頭， 6-20位字母、下劃線、和數字組成',
        ),
    'real_name' =>
        array (
            'required' => '用戶名不能為空',
            'regex' => '用戶名必須為 3-20位字母、下劃線、數字和中文組成',
        ),
    'password' =>
        array (
            'required' => '密碼不能為空',
            'min' => '密碼不能小於6位',
            'confirmed' => '密碼和確認密碼不一致',
        ),
    'tel' =>
        array (
            'required' => '手機號碼不能為空',
        ),
    'email' =>
        array (
            'required' => '郵箱不能為空',
            'email' => '郵箱格式錯誤',
            'unique' => '郵箱已存在',
        ),
    'hall_id' =>
        array (
            'required' => '請選擇直屬廳主',
        ),
    'agent_code' =>
        array (
            'required' => '代理商code不能為空',
            'unique' => '代理商code已存在',
            'error' => '代理商code必須由字母開頭， 3-6位字母、下劃線、和數字組成',
        ),
    'success' => '操作成功',
    'save_fails' => '保存失敗',
    'save_success' => '保存成功',
    'add_fails' => '添加失敗',
    'grade_id_error' => 'grade_id 參數值錯誤',
    'fails' => '操作失敗',
    'user_not_exist' => '玩家不存在',
    'user_has_exist' => '玩家已存在',
    'agent_not_exist' => '代理商不存在',
    'hall_not_exist' => '廳主不存在',
    'game_not_exist' => '遊戲不存在',
    'limit_group_exist' => '限額分組已存在',
    'limit_group_not_exist' => '限額分組不存在',
    'param_error' => '參數值錯誤',
    'insufficient_balance' => '餘額不足',
    'file_not_eixt' => '文件不存在',
    'min_max_error' => '最大值不能小於等於最小值',
    'last_max_error' => '最後一條的最大值必須為空',
    'last_max_next_min' => '上一條的最大值必須等於下一條的最小值',
    'ip_error' => 'IP地址不正確',
    'domain_error' => '域名不正確',
    'whitelist_not_exist' => '白名單不存在',
    'balance_str_error' => '限額必須為數字',
    'export_requisite_uid' => '請先選擇玩家再導出數據',
    'no_data_export' => '數據為空，無法導出',
    'hall_requiset' => '請選擇一個廳主',
    'agent_requiset' => '請選擇一個代理',
    'player_requiset' => '請選擇一個玩家',
    'scale_error' => '佔成比例必須大於0',
    'user_name' => '登錄名必須由字母開頭， 6-20位字母、下劃線、和數字組成',
    'hall_has_data' => '該廳主已經添加過數據了',
    'alias' => '用戶名不能為空',
    'user_sign_out' => '玩家已登出',
    'notify_url' => [
        'required'  => '玩家離線通知地址不能為空'
    ]
);