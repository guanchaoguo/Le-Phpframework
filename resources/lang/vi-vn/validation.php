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

    'accepted'             => ':attribute Nó phải được chấp nhận.',
    'active_url'           => ':attribute Nó không phải là một URL hợp lệ.',
    'after'                => ':attribute Nó phải ở trong một :date Sau ngày.',
    'alpha'                => ':attribute Chỉ bằng chữ cái.',
    'alpha_dash'           => ':attribute Chỉ có thể bao gồm các chữ cái, chữ số và dấu gạch chéo.',
    'alpha_num'            => ':attribute Nó chỉ có thể bao gồm chữ và số.',
    'array'                => ':attribute Nó phải là một mảng.',
    'before'               => ':attribute Nó phải ở trong một :date Trước ngày đó.',
    'between'              => [
        'numeric' => ':attribute bạn phải giữa :min - :max giữa',
        'file'    => ':attribute bạn phải giữa :min - :max kb giữa',
        'string'  => ':attribute bạn phải giữa :min - :max Giữa các nhân vật.',
        'array'   => ':attribute phải chỉ :min - :max Đơn vị.',
    ],
    'boolean'              => ':attribute Bạn phải là một giá trị boolean.',
    'confirmed'            => ':attribute Hai mục không khớp.',
    'date'                 => ':attribute Nó không phải là một ngày hợp lệ.',
    'date_format'          => ':attribute Định dạng phải :format',
    'different'            => ':attribute và :other Nó phải khác.',
    'digits'               => ':attribute cần phải :digits Chữ số.',
    'digits_between'       => ':attribute Phải từ :min và :max Chữ số.',
    'distinct'             => ':attribute Đã tồn tại.',
    'email'                => ':attribute Nó không phải là một hộp thư hợp lệ.',
    'exists'               => ':attribute Không tồn tại。',
    'filled'               => ':attribute Nó không thể để trống.',
    'image'                => ':attribute Nó phải là một bức tranh.',
    'in'                   => 'Tính chọn :attribute Bất hợp pháp.',
    'in_array'             => ':attribute không :other Trong.',
    'integer'              => ':attribute Nó phải là một số nguyên.',
    'ip'                   => ':attribute Nó phải là một địa chỉ IP hợp lệ.',
    'json'                 => ':attribute Phải là định dạng JSON đúng.',
    'max'                  => [
        'numeric' => ':attribute không vượt quá :max',
        'file'    => ':attribute không vượt quá :max kb',
        'string'  => ':attribute không vượt quá :max Ký tự.',
        'array'   => ':attribute tại hầu hết các :max Đơn vị.',
    ],
    'mimes'                => ':attribute Nó phải là một :values Các loại tập tin.',
    'min'                  => [
        'numeric' => ':attribute Nó phải lớn hơn hoặc bằng :min',
        'file'    => ':attribute Kích thước không thể nhỏ hơn :min kb',
        'string'  => ':attribute ít nhất :min Ký tự.',
        'array'   => ':attribute có ít nhất :min Đơn vị.',
    ],
    'not_in'               => 'Tính chọn :attribute Bất hợp pháp.',
    'numeric'              => ':attribute Nó phải là một số.',
    'present'              => ':attribute Nó phải tồn tại.',
    'regex'                => ':attribute Định dạng là không đúng.',
    'required'             => ':attribute Nó không thể để trống.',
    'required_if'          => 'khi nào :other là :value khi nào :attribute Nó không thể để trống.',
    'required_unless'      => 'khi nào :other nó không phải là :value khi nào :attribute Nó không thể để trống.',
    'required_with'        => 'khi nào :values có :attribute Nó không thể để trống.',
    'required_with_all'    => 'khi nào :values có :attribute Nó không thể để trống.',
    'required_without'     => ':attribute Nó không thể để trống.',
    'required_without_all' => 'khi nào :values Không có :attribute Nó không thể để trống.',
    'same'                 => ':attribute và :other Họ phải giống nhau.',
    'size'                 => [
        'numeric' => ':attribute Kích phải :size',
        'file'    => ':attribute Kích phải :size kb',
        'string'  => ':attribute phải là :size nhân vật',
        'array'   => ':attribute phải là :size Đơn vị.',
    ],
    'string'               => ':attribute phải là một chuỗi.',
    'timezone'             => ':attribute phải là một giá trị múi giờ hợp lệ.',
    'unique'               => ':attribute đã tồn tại.',
    'url'                  => ':attribute Định dạng là không đúng.',

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
        'name' => 'tên',
        'username' => 'Tên đăng nhập',
        'user_name' => 'đăng nhập',
        'email' => 'hộp thư',
        'first_name' => 'tên',
        'last_name' => 'họ',
        'password' => 'mật khẩu',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'city' => 'thành phố',
        'country' => 'nước',
        'address' => 'địa chỉ',
        'phone' => 'điện thoại',
        'mobile' => 'điện thoại di động',
        'age' => 'trẻ',
        'sex' => 'tính',
        'gender' => 'tính',
        'day' => 'ngày',
        'month' => 'tháng',
        'year' => 'năm',
        'hour' => 'khi nào',
        'minute' => 'chia',
        'second' => 'thứ hai',
        'title' => 'Headline',
        'content' => 'Nội dung',
        'description' => 'miêu tả',
        'excerpt' => 'tóm lại',
        'date' => 'ngày',
        'time' => 'thời gian',
        'available' => 'có sẵn',
        'size' => 'kích thước',
        'cat_id' => 'Trò chơi Thể loại',
        'game_id' => 'trò chơi',
        'hall_type' => 'đại sảnh',
        'max_money' => 'trần nhà',
        'min_money' => 'tối thiểu',
        'username_md' => 'người chơi',
        'password_md' => 'mật khẩu',
        'password_md_confirmation' => 'Xác nhận mật khẩu',
        'agent_id' => 'Đại lý',
        'language' => 'ngôn ngữ',
        'account_state' => 'trạng thái',
        'area' => 'vùng',
        'time_zone' => 'múi giờ',
        'lang_code' => 'ngôn ngữ',
        'grade_id' => 'Đại lý Gõ',
        'money' => 'tiền',
        'status' => 'kiểu',
        'game_name' => 'trò chơi Tiêu đề',
        'items' => 'đặt cược giá trị khu vực',
        'agent_domain' => 'Đại lý miền',
        'ip_info' => 'IP',
        'captcha' => 'mã xác nhận',

    ],

];
