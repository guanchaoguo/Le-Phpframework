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

    'accepted'             => ':attribute ဒါဟာလက်ခံရပေမည်။',
    'active_url'           => ':attribute ဒါဟာတရားဝင် URL မဟုတ်ပါ။',
    'after'                => ':attribute ဒါဟာအတွက်ဖြစ်ရပါမည် :date ရက်စွဲပြီးနောက်။',
    'alpha'                => ':attribute သာအက္ခရာများဖြင့်ပြုလုပ်နိုင်ပါတယ်။',
    'alpha_dash'           => ':attribute သာအက္ခရာများ, နံပါတ်များနှင့်မျဉ်းစောင်းများထားရှိရေးနိုင်ပါတယ်။',
    'alpha_num'            => ':attribute ကိုယ်ကသာအက္ခရာများနှင့်နံပါတ်များထားရှိရေးနိုင်ပါတယ်။',
    'array'                => ':attribute ဒါဟာတစ်ခင်းကျင်းဖြစ်ရမည်။',
    'before'               => ':attribute ဒါဟာအတွက်ဖြစ်ရပါမည် :date ရက်စွဲခြင်းမပြုမီ။',
    'between'              => [
        'numeric' => ':attribute သငျသညျအကြားဖြစ်ရပါမည် :min - :max အကြား။',
        'file'    => ':attribute သငျသညျအကြားဖြစ်ရပါမည် :min - :max kb အကြား။',
        'string'  => ':attribute သငျသညျအကြားဖြစ်ရပါမည် :min - :max ဇာတ်ကောင်များအကြား။',
        'array'   => ':attribute သာရပါမည် :min - :max ယူနစ်။',
    ],
    'boolean'              => ':attribute သင်တစ်ဦး boolean value ကိုဖြစ်ရပါမည်။',
    'confirmed'            => ':attribute နှစ်ခု entries တွေကိုမကိုက်ညီပါဘူး။',
    'date'                 => ':attribute ဒါဟာမှန်ကန်သောရက်စွဲတစ်ခုမဟုတ်ပါဘူး။',
    'date_format'          => ':attribute ပုံစံဖြစ်ရပါမည် :format',
    'different'            => ':attribute နှင့် :other ဒါဟာကွဲပြားခြားနားသောဖြစ်ရမည်။',
    'digits'               => ':attribute ဖြစ်ရမည် :digits ဂဏန်း။',
    'digits_between'       => ':attribute အကြားဖြစ်ရမည် :min နှင့် :max ဂဏန်း။',
    'distinct'             => ':attribute ယခုပင်လျှင်တည်ရှိ',
    'email'                => ':attribute ဒါဟာတရားဝင်စာတိုက်ပုံးမဟုတ်ပါဘူး။',
    'exists'               => ':attribute ဒါဟာမတည်ရှိပါဘူး။',
    'filled'               => ':attribute ဒါဟာအချည်းနှီးမဖြစ်နိုင်ပါ။',
    'image'                => ':attribute ဒါဟာပုံဖြစ်ရပါမည်။',
    'in'                   => 'Selected ဂုဏ်သတ္တိများ :attribute တရားမဝင်။',
    'in_array'             => ':attribute မဟုတ် :other ၌တည်၏။',
    'integer'              => ':attribute ၎င်းသည်ကိန်းဖြစ်ရပါမည်။',
    'ip'                   => ':attribute ဒါဟာမှန်ကန်သည့် IP လိပ်စာဖြစ်ရပါမည်။',
    'json'                 => ':attribute မှန်ကန်သော JSON format နဲ့ဖြစ်ရမည်။',
    'max'                  => [
        'numeric' => ':attribute ထက်မကျော်လွန် :max',
        'file'    => ':attribute ထက်မကျော်လွန် :max kb',
        'string'  => ':attribute ထက်မကျော်လွန် :max characters ။',
        'array'   => ':attribute အများဆုံး :max ယူနစ်။',
    ],
    'mimes'                => ':attribute ဒါဟာသူဖြစ်ရမည် :values ဖိုင်တွေအမျိုးအစားများ။',
    'min'                  => [
        'numeric' => ':attribute ဒါဟာထက် သာ. ကြီးမြတ်သို့မဟုတ်တန်းတူဖြစ်ရမည် :min',
        'file'    => ':attribute Size ကိုထက်သေးငယ်မဖွစျနိုငျ :min kb',
        'string'  => ':attribute အနည်းဆုံး :min characters ။',
        'array'   => ':attribute အနည်းဆုံးရှိပါတယ် :min ယူနစ်။',
    ],
    'not_in'               => 'Selected ဂုဏ်သတ္တိများ :attribute တရားမဝင်။',
    'numeric'              => ':attribute ဒါဟာအရေအတွက်ကိုဖြစ်ရမည်။',
    'present'              => ':attribute ဒါဟာတည်ရှိရပါမည်။',
    'regex'                => ':attribute format မှန်ကန်သောမဟုတ်ပါဘူး။',
    'required'             => ':attribute ဒါဟာအချည်းနှီးမဖြစ်နိုင်ပါ။',
    'required_if'          => 'ဘယ်အချိန်မှာ :other ဖြစ် :value ဘယ်အချိန်မှာ :attribute ဒါဟာအချည်းနှီးမဖြစ်နိုင်ပါ။',
    'required_unless'      => 'ဘယ်အချိန်မှာ :other ဒါဟာမဖြစ် :value ဘယ်အချိန်မှာ :attribute ဒါဟာအချည်းနှီးမဖြစ်နိုင်ပါ။',
    'required_with'        => 'ဘယ်အချိန်မှာ :values ရှိပါတယ် :attribute ဒါဟာအချည်းနှီးမဖြစ်နိုင်ပါ။',
    'required_with_all'    => 'ဘယ်အချိန်မှာ :values ရှိပါတယ် :attribute ဒါဟာအချည်းနှီးမဖြစ်နိုင်ပါ။',
    'required_without'     => ':attribute ဒါဟာအချည်းနှီးမဖြစ်နိုင်ပါ။',
    'required_without_all' => 'ဘယ်အချိန်မှာ :values မရှိပါ :attribute ဒါဟာအချည်းနှီးမဖြစ်နိုင်ပါ။',
    'same'                 => ':attribute နှင့် :other သူတို့ကအတူတူပင်ဖြစ်ရပါမည်။',
    'size'                 => [
        'numeric' => ':attribute Size ကိုသူဖြစ်ရမည် :size',
        'file'    => ':attribute Size ကိုသူဖြစ်ရမည် :size kb',
        'string'  => ':attribute ဖြစ်ရမည် :size characters ။',
        'array'   => ':attribute ဒါဟာသူဖြစ်ရမည် :size ယူနစ်။',
    ],
    'string'               => ':attribute ဒါဟာ string ကိုဖြစ်ရပါမည်။',
    'timezone'             => ':attribute ဒါဟာတရားဝင်အချိန်ဇုန်တန်ဖိုးများဖြစ်ရမည်။',
    'unique'               => ':attribute ရှိပြီးဖြစ်သည်။',
    'url'                  => ':attribute format မှန်ကန်သောမဟုတ်ပါဘူး။',

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
        'name'                  => 'အမည်',
        'username'              => ' အသုံးပြုသူအမည်',
        'user_name'              => 'ဝင်မည်',
        'email'                 => 'စာတိုက်ပုံး',
        'first_name'            => 'အမည်',
        'last_name'             => 'မြိုးရိုးအမညျ',
        'password'              => 'Password ကို',
        'password_confirmation' => 'စကားဝှက်ကိုအတည်ပြုပါ',
        'city'                  => 'မြို့',
        'country'               => 'ပြည်',
        'address'               => 'လိပ်စာ',
        'phone'                 => 'ဖုန်းနံပါတ်',
        'mobile'                => 'ဆယ်လူလာဖုန်းကို',
        'age'                   => 'အသက်အရွယ်',
        'sex'                   => 'ကျား, မ',
        'gender'                => 'ကျား, မ',
        'day'                   => 'နေ့',
        'month'                 => 'လ',
        'year'                  => 'ခုနှစ်',
        'hour'                  => 'ဘယ်အချိန်မှာ',
        'minute'                => 'ခှဲခွား',
        'second'                => 'ဒုတိယ',
        'title'                 => 'ခေါင်းစဉ်',
        'content'               => 'အကြောင်းအရာ',
        'description'           => 'ဖေါ်ပြချက်',
        'excerpt'               => 'အကျဉ်းချုပ်',
        'date'                  => 'နေ့စှဲ',
        'time'                  => 'အချိန်',
        'available'             => 'ရရှိနိုင်',
        'size'                  => 'အရွယ်',
        'cat_id'                => 'အားကစားပြိုင်ပွဲအမျိုးအစား',
        'game_id'               => 'ဂိမ်း',
        'hall_type'             => 'ခန်းမ',
        'max_money'             => 'မျက်နှာကြက်',
        'min_money'             => 'အနည်းဆုံး',
        'username_md'           => 'ကစားသမားများ',
        'password_md'           => 'Password ကို',
        'password_md_confirmation'           => 'စကားဝှက်ကိုအတည်ပြုပါ',
        'agent_id'              => 'အေးဂျင့်များ',
        'language'              => 'ဘာသာစကား',
        'account_state'         => 'ပြည်နယ်',
        'area'                  => 'ဒေသ',
        'time_zone'             => 'အချိန်ဇုန်',
        'lang_code'             => 'ဘာသာစကား',
        'grade_id'              => 'အေးဂျင့်ရိုက်ထည့်ပါ',
        'money'                 => 'ပိုက်ဆံ',
        'status'                => 'ပုံစံ',
        'game_name'             => 'ဂိမ်းခေါင်းစဉ်',
        'items'                 => 'ဒေသဆိုင်ရာတန်ဖိုးကိုအလောင်းအစား',
        'agent_domain'          => 'အေးဂျင့်ဒိုမိန်း',
        'ip_info'                 => 'အိုင်ပီ',
        'captcha' => 'verification code ကို',

    ],

];
