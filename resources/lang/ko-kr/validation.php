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

    'accepted'             => ':attribute 그것은 인정해야합니다.',
    'active_url'           => ':attribute 그것은 유효한 URL이 아닙니다.',
    'after'                => ':attribute 그것은에 있어야합니다 :date 날짜 후.',
    'alpha'                => ':attribute 만 문자로.',
    'alpha_dash'           => ':attribute 문자, 숫자 및 슬래시로 구성 될 수 있습니다.',
    'alpha_num'            => ':attribute 그것은 문자와 숫자로 구성 될 수 있습니다.',
    'array'                => ':attribute 그것은 배열해야합니다.',
    'before'               => ':attribute 그것은에 있어야합니다 :date 날짜 전에.',
    'between'              => [
        'numeric' => ':attribute 당신은 사이 여야합니다 :min - :max 사이.',
        'file'    => ':attribute 당신은 사이 여야합니다 :min - :max kb 사이.',
        'string'  => ':attribute 당신은 사이 여야합니다 :min - :max 문자 사이.',
        'array'   => ':attribute 해야 만 :min - :max 단위.',
    ],
    'boolean'              => ':attribute 당신은 부울 값이어야합니다.',
    'confirmed'            => ':attribute 그것은 유효한 날짜가 아닙니다.',
    'date'                 => ':attribute 그것은 유효한 날짜가 아닙니다.',
    'date_format'          => ':attribute 형식이어야합니다 :format.',
    'different'            => ':attribute 和 :other 이 달라야합니다.',
    'digits'               => ':attribute 여야 :digits 숫자.',
    'digits_between'       => ':attribute 사이이어야합니다 :min 과 :max 숫자.',
    'distinct'             => ':attribute 이미 존재한다.',
    'email'                => ':attribute 그것은 유효한 사서함이 아니다.',
    'exists'               => ':attribute 그것은 존재하지 않습니다.',
    'filled'               => ':attribute 그것은 비어있을 수 없습니다.',
    'image'                => ':attribute 이 사진이어야합니다.',
    'in'                   => '선택한 속성 :attribute 불법.',
    'in_array'             => ':attribute 아니 :other 아니',
    'integer'              => ':attribute 그것은 정수 여야합니다.',
    'ip'                   => ':attribute 그것은 유효한 IP 주소 여야합니다.',
    'json'                 => ':attribute 올바른 JSON 형식이어야합니다.',
    'max'                  => [
        'numeric' => ':attribute 초과하지 않음 :max.',
        'file'    => ':attribute 초과하지 않음 :max kb.',
        'string'  => ':attribute 초과하지 않음 :max 문자.',
        'array'   => ':attribute 대부분의시 :max 단위.',
    ],
    'mimes'                => ':attribute 그것은이어야합니다 :values 파일의 종류.',
    'min'                  => [
        'numeric' => ':attribute 그것은보다 크거나 같아야합니다 :min.',
        'file'    => ':attribute 크기보다 작을 수 없습니다 :min kb.',
        'string'  => ':attribute 적어도 :min 문자.',
        'array'   => ':attribute 적어도이 있습니다 :min 적어도이 있습니다',
    ],
    'not_in'               => '선택한 속성 :attribute 불법.',
    'numeric'              => ':attribute 그것은 존재해야합니다.',
    'present'              => ':attribute 그것은 존재해야합니다.',
    'regex'                => ':attribute 형식이 올바르지 않습니다.',
    'required'             => ':attribute 그것은 비어있을 수 없습니다.',
    'required_if'          => '언제 :other 인가 :value 언제 :attribute 그것은 비어있을 수 없습니다.',
    'required_unless'      => '언제 :other 그것은 아니다 :value 언제 :attribute 그것은 비어있을 수 없습니다.',
    'required_with'        => '언제 :values 이 :attribute 그것은 비어있을 수 없습니다.',
    'required_with_all'    => '언제 :values 이 :attribute 그것은 비어있을 수 없습니다.',
    'required_without'     => ':attribute 그것은 비어있을 수 없습니다.',
    'required_without_all' => '언제 :values 더 없다 :attribute 그것은 비어있을 수 없습니다.',
    'same'                 => ':attribute 과 :other 그들은 동일해야합니다.',
    'size'                 => [
        'numeric' => ':attribute 크기는 다음과 같아야합니다 :size.',
        'file'    => ':attribute 크기는 다음과 같아야합니다 :size kb.',
        'string'  => ':attribute 여야 :size 문자.',
        'array'   => ':attribute 그것은해야합니다 :size 단위.',
    ],
    'string'               => ':attribute 그것은 문자열이어야합니다.',
    'timezone'             => ':attribute 그것은 올바른 시간대 값이어야합니다.',
    'unique'               => ':attribute 이미 존재한다.',
    'url'                  => ':attribute 형식이 올바르지 않습니다.',

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

    /*'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],*/

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
        'name' => '이름',
        'username' => '사용자 이름',
        'user_name' => '로그인',
        'email' => '사서함',
        'first_name' => '이름',
        'last_name' => '성',
        'password' => '암호',
        'password_confirmation' => '비밀번호 확인',
        'city' => '도시',
        'country' => '국가',
        'address' => '주소',
        'phone' => '전화',
        'mobile' => '휴대폰',
        'age' => '젊은',
        'sex' => '성',
        'gender' => '성',
        'day' => '일',
        'month' => '월',
        'year' => '년',
        'hour' => '언제',
        'minute' => '분할',
        'second' => '초',
        'title' => '표제',
        'content' => '함유량',
        'description' => '기술',
        'excerpt' => '개요',
        'date' => '날짜',
        'time' => '시간',
        'available' => '유효한',
        'size' => '크기',
        'cat_id' => '게임 카테고리',
        'game_id' => '경기',
        'hall_type' => '홀',
        'max_money' => '천장',
        'min_money' => '최소한의',
        'username_md' => '플레이어',
        'password_md' => '암호',
        'password_md_confirmation' => '비밀번호 확인',
        'agent_id' => '에이전트',
        'language' => '언어',
        'account_state' => '상태',
        'area' => '지방',
        'time_zone' => '시간대',
        'lang_code' => '언어',
        'grade_id' => '에이전트는 입력',
        'money' => '돈',
        'status' => '유형',
        'game_name' => '게임 제목',
        'items' => '지역 값 내기',
        'agent_domain' => '에이전트 도메인',
        'ip_info' => 'IP',
        'captcha' => '확인 코드',

    ],

];
