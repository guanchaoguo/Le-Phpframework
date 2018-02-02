<?php
return array (
    'area' =>
        array (
            'required' => '動作領域を選択してください',
        ),
    'time_zone' =>
        array (
            'required' => 'タイムゾーンを選択してください',
        ),
    'agent_name' =>
        array (
            'required' => 'ログイン名は空にすることはできません',
            'unique' => 'ログイン名はすでに存在します',
            'regex' => 'ログイン名は、文字、6-20文字、アンダースコア、数字で始まる必要があります。',
        ),
    'real_name' =>
        array (
            'required' => 'ユーザー名は空にすることはできません',
            'regex' => 'ユーザー名は3-20文字、アンダースコア、数字と中国の構図でなければなりません',
        ),
    'password' =>
        array (
            'required' => 'パスワードが空にすることはできません',
            'min' => 'パスワードは6未満することはできません',
            'confirmed' => '矛盾したパスワードをパスワードおよびパスワードの確認',
        ),
    'tel' =>
        array (
            'required' => '電話番号は空にすることはできません',
        ),
    'email' =>
        array (
            'required' => 'Eメールは、空にすることはできません',
            'email' => 'Eメールの形式エラー',
            'unique' => 'Eメールが既に存在しています',
        ),
    'hall_id' =>
        array (
            'required' => 'メインホールの直下に選択してください',
        ),
    'agent_code' =>
        array (
            'required' => 'エージェント・コードは空にすることはできません',
            'unique' => 'コードがすでに存在しているエージェント',
            'error' => 'コードからのエージェントは手紙、3-6文字、アンダースコア、数字で始まる必要があります。',
        ),
    'success' => '成功した操作',
    'save_fails' => '保存に失敗しました',
    'save_success' => '正常に保存',
    'add_fails' => '追加に失敗しました',
    'grade_id_error' => 'grade_idパラメータ値の誤差',
    'fails' => '操作が失敗しました',
    'user_not_exist' => 'プレイヤーは存在しません。',
    'user_has_exist' => 'プレイヤーはすでに存在しています',
    'agent_not_exist' => 'エージェントは存在しません。',
    'hall_not_exist' => 'メインホールは存在しません。',
    'game_not_exist' => 'ゲームは存在しません。',
    'limit_group_exist' => 'リミット・グループはすでに存在しています',
    'limit_group_not_exist' => '制限なしのグループ化はありません',
    'param_error' => '誤ったパラメータ値',
    'insufficient_balance' => '資金不足',
    'file_not_eixt' => 'ファイルが存在しません。',
    'min_max_error' => '最小値に等しい最大値よりも小さくありません',
    'last_max_error' => '最後に、最大値は空である必要があります',
    'last_max_next_min' => '最小で最大値が等しくなければなりません',
    'ip_error' => 'IPアドレスが正しくありません。',
    'domain_error' => '不正なドメイン名',
    'whitelist_not_exist' => 'ホワイトリストは存在しません。',
    'balance_str_error' => 'リミットは数でなければなりません',
    'export_requisite_uid' => 'プレーヤーその後、エクスポートデータを選択してください',
    'no_data_export' => 'データは、空でエクスポートすることはできません',
    'hall_requiset' => 'メイン会場を選択してください',
    'agent_requiset' => 'プロキシを選択してください',
    'player_requiset' => 'プレイヤーを選択してください',
    'scale_error' => 'それは占め比例して0より大きくなければなりません',
    'user_name' => 'ログイン名は、文字、6-20文字、アンダースコア、数字で始まる必要があります。',
    'hall_has_data' => 'メインホールは、データの上に追加されました',
    'alias' => 'ユーザー名は空にすることはできません',
    'user_sign_out' => 'プレイヤーは、ログアウトされました',
);