<?php
return array (
  'area' => 
  array (
    'required' => 'လည်ပတ်မှုဧရိယာကို select ပေးပါ',
  ),
  'time_zone' => 
  array (
    'required' => 'အချိန်ကိုဇုန်ကို select ပေးပါ',
  ),
  'agent_name' => 
  array (
    'required' => 'login name ကိုဗလာမဖွစျနိုငျ',
    'unique' => 'login name ကိုရှိထားပြီးသား',
    'regex' => 'login အမည်, စာတစ်စောင်နဲ့စတင် 6-20 အက္ခရာများ, အလေးပေးနှင့်နံပါတ်များကိုရမယ်',
  ),
  'real_name' => 
  array (
    'required' => 'username ဗလာမဖွစျနိုငျ',
    'regex' => 'username 6-20 အက္ခရာများ, underscores, နံပါတ်နှင့်တရုတ်ဖွဲ့စည်းမှုရှိရမည်',
  ),
  'password' => 
  array (
    'required' => 'Password ကိုအချည်းနှီးမဖွစျနိုငျ',
    'min' => 'စကားဝှက်ကို 6 ထက်မနည်းနိုင်',
    'confirmed' => 'ကိုက်ညီမှု Password ကို password နဲ့အတည်ပြုပါ',
  ),
  'tel' => 
  array (
    'required' => 'ဖုန်းနံပါတ်အချည်းနှီးမဖွစျနိုငျ',
  ),
  'email' => 
  array (
    'required' => 'E-mail ကိုအချည်းနှီးမဖွစျနိုငျ',
    'email' => 'E-mail ကို format နဲ့အမှား',
    'unique' => 'E-mail ကိုရှိထားပြီးသား',
  ),
  'hall_id' => 
  array (
    'required' => 'အဓိကခန်းမအောက်တွင်တိုက်ရိုက်ကို select ပေးပါ',
  ),
  'agent_code' => 
  array (
    'required' => 'အေးဂျင့် code ကိုအချည်းနှီးမဖွစျနိုငျ',
    'unique' => 'ကုဒ်တစ်ခုရှိထားပြီးသားအေးဂျင့်များ',
    'error' => 'code ကိုကနေအေးဂျင့်တစ်ဦးစာတစ်စောင်နှင့်အတူ 3-6 အက္ခရာများ, underscores နှင့်နံပါတ်များကိုစတင်ရပါမည်',
  ),
  'success' => 'အောင်မြင်သောစစ်ဆင်ရေး',
  'save_fails' => 'မအောင်မြင် Save',
  'save_success' => 'အောင်မြင်စွာသည်ကယ်တင်ခြင်းသို့ရောက်',
  'add_fails' => 'မှု Add',
  'grade_id_error' => 'grade_id parameter သည်တန်ဖိုးတစ်ခုအမှား',
  'fails' => 'စစ်ဆင်ရေးမအောင်မြင်ခဲ့',
  'user_not_exist' => 'ကစားသမားများမတည်ရှိပါဘူး',
  'user_has_exist' => 'ကစားသမားများရှိထားပြီးသား',
  'agent_not_exist' => 'အေးဂျင့်မတည်ရှိပါဘူး',
  'hall_not_exist' => 'ပင်မခန်းမမတည်ရှိပါဘူး',
  'game_not_exist' => 'ဂိမ်းမတည်ရှိပါဘူး',
  'limit_group_exist' => 'ကန့်သတ်အုပ်စုတစ်စုရှိထားပြီးသား',
  'limit_group_not_exist' => 'အဘယ်သူမျှမကန့်သတ်အာဆီယံရှိပါတယ်',
  'param_error' => 'မှားယွင်းနေ parameter သည်တန်ဖိုးတစ်ခု',
  'insufficient_balance' => 'မလုံလောက်သောရံပုံငွေ',
  'file_not_eixt' => 'file ကိုမတည်ရှိပါဘူး',
  'min_max_error' => 'နိမ့်ဆုံးနဲ့ညီမျှအများဆုံးတန်ဖိုးကိုထက်မနည်း',
  'last_max_error' => 'နောက်ဆုံးအနေနဲ့အများဆုံးဗလာဖြစ်ရမည်',
  'last_max_next_min' => 'နိမ့်ဆုံးမှာတစ်ဦးကအမြင့်ဆုံးတန်ဖိုးညီမျှရှိရမည်',
  'ip_error' => 'IP လိပ်စာမမှန်ကန်',
  'domain_error' => 'မှားယွင်းနေ domain name ကို',
  'whitelist_not_exist' => 'White ကစာရင်းမတည်ရှိပါဘူး',
  'balance_str_error' => 'ကန့်သတ်မယ့်အရေအတွက်ကိုဖြစ်ရမည်',
  'export_requisite_uid' => 'ကစားသူတစ်ဦး, ပြီးတော့ပို့ကုန်ဒေတာကို select ပေးပါ',
  'no_data_export' => 'ဒေတာ, ဗလာဖြစ်နေသည်များကိုမတင်ပို့နိုင်',
  'hall_requiset' => 'အဓိကခန်းမကို select ပေးပါ',
  'agent_requiset' => 'တစ်ဦးကို proxy ကိုရွေးပါ ကျေးဇူးပြု.',
  'player_requiset' => 'ကစားသူတစ်ဦးကို select ပေးပါ',
  'scale_error' => 'ဒါဟာမှတ်အချိုးအစားအတွက်ပါ 0 င်ထက် သာ. ကြီးမြတ်ဖြစ်ရမည်',
  'user_name' => 'login အမည်, စာတစ်စောင်နဲ့စတင် 6-20 အက္ခရာများ, အလေးပေးနှင့်နံပါတ်များကိုရမယ်',
  'hall_has_data' => 'အဓိကခန်းမဒေတာကျော်ကဆက်ပြောသည်ထားပြီး',
  'alias' => 'username ဗလာမဖွစျနိုငျ',
  'user_sign_out' => 'ကစားသမားများထွက်ထားပြီ',
  'notify_url' => 
  array (
    'required' => 'Player ကိုအော့ဖ်လိုင်းအကြောင်းကြားစာလိပ်စာဗလာမဖွစျနိုငျဖြစ်ပါတယ်',
  ),
);