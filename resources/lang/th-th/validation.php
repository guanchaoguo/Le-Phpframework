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

    'accepted'             => ':attribute มันจะต้องได้รับการยอมรับ',
    'active_url'           => ':attribute มันไม่ได้เป็น URL ที่ถูกต้อง',
    'after'                => ':attribute มันจะต้องอยู่ใน :date หลังจากวันที่',
    'alpha'                => ':attribute โดยเฉพาะตัวอักษร',
    'alpha_dash'           => ':attribute เท่านั้นที่สามารถประกอบด้วยตัวอักษรตัวเลขและเครื่องหมายทับ',
    'alpha_num'            => ':attribute มันสามารถประกอบด้วยตัวอักษรและตัวเลข',
    'array'                => ':attribute มันจะต้องเป็นอาร์เรย์',
    'before'               => ':attribute มันจะต้องอยู่ใน :date ก่อนวันที่',
    'between'              => [
        'numeric' => ':attribute คุณจะต้องอยู่ระหว่าง :min - :max ระหว่าง',
        'file'    => ':attribute คุณจะต้องอยู่ระหว่าง :min - :max kb ระหว่าง',
        'string'  => ':attribute คุณจะต้องอยู่ระหว่าง :min - :max ระหว่างตัวอักษร',
        'array'   => ':attribute ต้องเท่านั้น :min - :max หน่วย',
    ],
    'boolean'              => ':attribute คุณจะต้องเป็นค่าบูลีน',
    'confirmed'            => ':attribute ทั้งสองรายการไม่ตรงกับ',
    'date'                 => ':attribute มันไม่ได้เป็นวันที่ถูกต้อง',
    'date_format'          => ':attribute รูปแบบจะต้อง :format。',
    'different'            => ':attribute และ :other มันจะต้องแตกต่างกัน',
    'digits'               => ':attribute จะต้องเป็น :digits ตัวเลข',
    'digits_between'       => ':attribute ต้องอยู่ระหว่าง :min และ :max ตัวเลข',
    'distinct'             => ':attribute มีอยู่แล้ว',
    'email'                => ':attribute มันไม่ได้เป็นกล่องจดหมายที่ถูกต้อง',
    'exists'               => ':attribute มันไม่ได้อยู่',
    'filled'               => ':attribute มันต้องไม่ว่างเปล่า',
    'image'                => ':attribute มันจะต้องเป็นภาพ',
    'in'                   => 'คุณสมบัติที่เลือก :attribute ที่ผิดกฎหมาย',
    'in_array'             => ':attribute ไม่ :other ใน',
    'integer'              => ':attribute มันต้องเป็นจำนวนเต็ม',
    'ip'                   => ':attribute มันจะต้องเป็นที่อยู่ IP ที่ถูกต้อง',
    'json'                 => ':attribute จะต้องเป็นรูปแบบ JSON ที่ถูกต้อง',
    'max'                  => [
        'numeric' => ':attribute ไม่เกิน :max',
        'file'    => ':attribute ไม่เกิน :max kb',
        'string'  => ':attribute ไม่เกิน :max ตัวละคร',
        'array'   => ':attribute ที่มากที่สุด :max หน่วย',
    ],
    'mimes'                => ':attribute มันจะต้องเป็น :values ประเภทของไฟล์',
    'min'                  => [
        'numeric' => ':attribute มันจะต้องมากกว่าหรือเท่ากับ :min',
        'file'    => ':attribute ขนาดไม่สามารถจะมีขนาดเล็กกว่า :min kb',
        'string'  => ':attribute อย่างน้อยที่สุด :min ตัวละคร',
        'array'   => ':attribute มีอย่างน้อย :min หน่วย',
    ],
    'not_in'               => 'คุณสมบัติที่เลือก :attribute ที่ผิดกฎหมาย',
    'numeric'              => ':attribute มันต้องเป็นตัวเลข',
    'present'              => ':attribute มันต้องมีอยู่',
    'regex'                => ':attribute รูปแบบไม่ถูกต้อง',
    'required'             => ':attribute มันต้องไม่ว่างเปล่า',
    'required_if'          => 'เมื่อ :other คือ :value เมื่อ :attribute มันต้องไม่ว่างเปล่า',
    'required_unless'      => 'เมื่อ :other มันไม่ได้เป็น :value เมื่อ :attribute มันต้องไม่ว่างเปล่า',
    'required_with'        => 'เมื่อ :values นอกจากนี้ :attribute มันต้องไม่ว่างเปล่า',
    'required_with_all'    => 'เมื่อ :values นอกจากนี้ :attribute มันต้องไม่ว่างเปล่า',
    'required_without'     => ':attribute มันต้องไม่ว่างเปล่า',
    'required_without_all' => 'เมื่อ :values ไม่มี :attribute มันต้องไม่ว่างเปล่า',
    'same'                 => ':attribute และ :other พวกเขาจะต้องเหมือนกัน',
    'size'                 => [
        'numeric' => ':attribute ต้องมีขนาด :size',
        'file'    => ':attribute ต้องมีขนาด :size kb',
        'string'  => ':attribute จะต้องเป็น :size ตัวละคร',
        'array'   => ':attribute มันจะต้องเป็น :size หน่วย',
    ],
    'string'               => ':attribute มันต้องเป็นสตริง',
    'timezone'             => ':attribute มันจะต้องเป็นค่าโซนเวลาที่ถูกต้อง',
    'unique'               => ':attribute มีอยู่แล้ว',
    'url'                  => ':attribute รูปแบบไม่ถูกต้อง',

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
        'name' => 'ชื่อ',
        'username' => 'ชื่อผู้ใช้',
        'user_name' => 'เข้าสู่ระบบ',
        'email' => 'ตู้จดหมาย',
        'first_name' => 'ชื่อ',
        'last_name' => 'นามสกุล',
        'password' => 'รหัสผ่าน',
        'password_confirmation' => 'ยืนยันรหัสผ่าน',
        'city' => 'เมือง',
        'country' => 'ประเทศ',
        'address' => 'ที่อยู่',
        'phone' => 'โทรศัพท์',
        'mobile' => 'โทรศัพท์เคลื่อนที่',
        'age' => 'หนุ่มสาว',
        'sex' => 'เพศ',
        'gender' => 'เพศ',
        'day' => 'วัน',
        'month' => 'เดือน',
        'year' => 'ปี',
        'hour' => 'เมื่อ',
        'minute' => 'หาร',
        'second' => 'ที่สอง',
        'title' => 'พาดหัว',
        'content' => 'เนื้อหา',
        'description' => 'ลักษณะ',
        'excerpt' => 'ย่อ',
        'date' => 'วันที่',
        'time' => 'เวลา',
        'available' => 'ที่มีจำหน่าย',
        'size' => 'ขนาด',
        'cat_id' => 'แจกเกมส์',
        'game_id' => 'เกม',
        'hall_type' => 'ห้องโถง',
        'max_money' => 'เพดาน',
        'min_money' => 'ขั้นต่ำ',
        'username_md' => 'ผู้เล่น',
        'password_md' => 'รหัสผ่าน',
        'password_md_confirmation' => 'ยืนยันรหัสผ่าน',
        'agent_id' => 'ตัวแทน',
        'language' => 'ภาษา',
        'account_state' => 'รัฐ',
        'area' => 'ภูมิภาค',
        'time_zone' => 'โซนเวลา',
        'lang_code' => 'ภาษา',
        'grade_id' => 'ตัวแทนพิมพ์',
        'money' => 'เงิน',
        'status' => 'ชนิด',
        'game_name' => 'ชื่อเกม',
        'items' => 'ทางออกที่คุ้มค่าในภูมิภาค',
        'agent_domain' => 'โดเมนตัวแทน',
        'ip_info' => 'IP',
        'captcha' => 'รหัสยืนยัน',

    ],

];
