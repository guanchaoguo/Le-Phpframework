<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | such as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted'             => ':attribute 我々は受け入れなければなりません。',
    'active_url'           => ':attribute それは有効なURLではありません。',
    'after'                => ':attribute それはでなければなりません :date 日付の後。',
    'alpha'                => ':attribute 文字だけによって。',
    'alpha_dash'           => ':attribute テキストダKEよNI〜テ。',
    'alpha_num'            => ':attribute これは、文字と数字のみで構成することができます。',
    'array'                => ':attribute それは配列でなければなりません。',
    'before'               => ':attribute それはでなければなりません :date 日付の前に。',
    'between'              => [
        'numeric' => ':attribute あなたはの間でなければなりません :min - :max 間に。',
        'file'    => ':attribute あなたはの間でなければなりません :min - :max kb 間に。',
        'string'  => ':attribute あなたはの間でなければなりません :min - :max 文字間。',
        'array'   => ':attribute なければならない唯一の :min - :max 単位。',
    ],
    'boolean'              => ':attribute あなたはブール値でなければなりません。',
    'confirmed'            => ':attribute 2つのエントリが矛盾しています。',
    'date'                 => ':attribute これは、有効な日付ではありません。',
    'date_format'          => ':attribute フォーマットでなければなりません :format。',
    'different'            => ':attribute と :other 異なっている必要があります。',
    'digits'               => ':attribute でなければなりません :digits 数字。',
    'digits_between'       => ':attribute 間でなければなりません :min と :max 数字。',
    'distinct'             => ':attribute すでに存在しています。',
    'email'                => ':attribute これは、有効な電子メールではありません。',
    'exists'               => ':attribute 存在しません。。',
    'filled'               => ':attribute 空にすることはできません。',
    'image'                => ':attribute 写真でなければなりません。',
    'in'                   => '選択したプロパティ :attribute 違法。',
    'in_array'             => ':attribute ではありません :other で。',
    'integer'              => ':attribute これは、整数でなければなりません。',
    'ip'                   => ':attribute これは、有効なIPアドレスでなければなりません。',
    'json'                 => ':attribute 正しいJSON形式でなければなりません。',
    'max'                  => [
        'numeric' => ':attribute 超えてはなりません :max。',
        'file'    => ':attribute 超えてはなりません :max kb。',
        'string'  => ':attribute 超えてはなりません :max 文字。',
        'array'   => ':attribute せいぜい :max 単位。',
    ],
    'mimes'                => ':attribute それでなければなりません :values ファイルの種類。',
    'min'                  => [
        'numeric' => ':attribute それは以上である必要があります。 :min。',
        'file'    => ':attribute サイズはより小さくすることはできません :min kb。',
        'string'  => ':attribute 少なくとも :min 文字。',
        'array'   => ':attribute 少なくともあります :min 単位。',
    ],
    'not_in'               => '選択したプロパティ :attribute 違法。',
    'numeric'              => ':attribute それは数字でなければなりません。',
    'present'              => ':attribute 存在している必要があります。',
    'regex'                => ':attribute 不正な形式。',
    'required'             => ':attribute 空にすることはできません。',
    'required_if'          => 'とき :other あります :value とき :attribute 空にすることはできません。',
    'required_unless'      => 'とき :other そうではありません :value とき :attribute 空にすることはできません。',
    'required_with'        => 'とき :values あり :attribute 空にすることはできません。',
    'required_with_all'    => 'とき :values あり :attribute 空にすることはできません。',
    'required_without'     => ':attribute 空にすることはできません。',
    'required_without_all' => 'とき :values 何もありません :attribute 空にすることはできません。',
    'same'                 => ':attribute と :other それは同じでなければなりません。',
    'size'                 => [
        'numeric' => ':attribute サイズでなければなりません :size。',
        'file'    => ':attribute サイズでなければなりません :size kb。',
        'string'  => ':attribute でなければなりません :size 文字。',
        'array'   => ':attribute それでなければなりません :size 単位。',
    ],
    'string'               => ':attribute それは文字列でなければなりません。',
    'timezone'             => ':attribute これは、有効なタイムゾーン値でなければなりません。',
    'unique'               => ':attribute すでに存在しています。',
    'url'                  => ':attribute 不正な形式。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention 'attribute.rule' to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of 'email'. This simply helps us make messages a little cleaner.
    |
    */

    'attributes'           => [
        'name'                  => '名前',
        'username'              => 'ユーザー名',
        'user_name'              => 'ログイン',
        'email'                 => 'ポスト',
        'first_name'            => '名前',
        'last_name'             => '姓',
        'password'              => 'パスワード',
        'password_confirmation' => 'パスワードを確認',
        'city'                  => '街',
        'country'               => '国',
        'address'               => 'アドレス',
        'phone'                 => '電話',
        'mobile'                => '携帯電話',
        'age'                   => '若いです',
        'sex'                   => '性別',
        'gender'                => '性别',
        'day'                   => '日',
        'month'                 => '月',
        'year'                  => '年',
        'hour'                  => 'と',
        'minute'                => '分',
        'second'                => '秒',
        'title'                 => '見出し',
        'content'               => 'コンテンツ',
        'description'           => '説明',
        'excerpt'               => '概要',
        'date'                  => '日付',
        'time'                  => '時間',
        'available'             => '利用できます',
        'size'                  => 'サイズ',
        'cat_id'                => 'ゲームカテゴリ',
        'game_id'               => 'ゲーム',
        'hall_type'             => 'ホール',
        'max_money'             => '上限',
        'min_money'             => '最小',
        'username_md'           => 'プレーヤー',
        'password_md'           => 'パスワード',
        'password_md_confirmation'           => 'パスワードを確認',
        'agent_id'              => 'エージェント',
        'language'              => '言語',
        'account_state'         => '状態',
        'area'                  => '地域',
        'time_zone'             => '時間帯',
        'lang_code'             => '言語',
        'grade_id'              => 'エージェントは、タイプ',
        'money'                 => 'お金',
        'status'                => 'タイプ',
        'game_name'             => 'ゲームタイトル',
        'items'                 => '地域の価値ベット',
        'agent_domain'          => 'エージェントのドメイン',
        'ip_info'                 => 'IP',
        'captcha' => '検証コード',

    ],

];
