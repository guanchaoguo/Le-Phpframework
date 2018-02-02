<?php
return array (
    'area' =>
        array (
            'required' => 'Vui lòng chọn khu vực điều hành',
        ),
    'time_zone' =>
        array (
            'required' => 'Vui lòng chọn múi giờ',
        ),
    'agent_name' =>
        array (
            'required' => 'Tên đăng nhập không thể để trống',
            'unique' => 'Tên đăng nhập đã tồn tại',
            'regex' => 'Tên đăng nhập phải bắt đầu bằng một bức thư, 6-20 chữ, gạch dưới, và số',
        ),
    'real_name' =>
        array (
            'required' => 'Tên người dùng không thể để trống',
            'regex' => 'Tên tài khoản phải 3-20 chữ cái, dấu gạch dưới, chữ số và thành phần của Trung Quốc',
        ),
    'password' =>
        array (
            'required' => 'Mật khẩu không thể để trống',
            'min' => 'Mật khẩu không thể ít hơn 6',
            'confirmed' => 'Mật khẩu và Xác nhận mật khẩu không phù hợp',
        ),
    'tel' =>
        array (
            'required' => 'Số điện thoại không thể để trống',
        ),
    'email' =>
        array (
            'required' => 'E-mail không thể để trống',
            'email' => 'E-mail lỗi định dạng',
            'unique' => 'E-mail đã tồn tại',
        ),
    'hall_id' =>
        array (
            'required' => 'Vui lòng chọn trực thuộc hành lang chính',
        ),
    'agent_code' =>
        array (
            'required' => 'Đại lý mã không thể để trống',
            'unique' => 'Đại lý đang đã tồn tại',
            'error' => 'Đại lý từ mã phải bắt đầu bằng một bức thư, 3-6 chữ, gạch dưới, và số',
        ),
    'success' => 'hoạt động thành công',
    'save_fails' => 'Lưu thất bại',
    'save_success' => 'Đã lưu thành công',
    'add_fails' => 'Thêm Không',
    'grade_id_error' => 'lỗi giá trị tham số grade_id',
    'fails' => 'Thao tác thất bại',
    'user_not_exist' => 'Người chơi không tồn tại',
    'user_has_exist' => 'Người chơi đã tồn tại',
    'agent_not_exist' => 'Đại lý không tồn tại',
    'hall_not_exist' => 'sảnh chính không tồn tại',
    'game_not_exist' => 'Các trò chơi không tồn tại',
    'limit_group_exist' => 'nhóm giới hạn đã tồn tại',
    'limit_group_not_exist' => 'Không có giới hạn nhóm',
    'param_error' => 'giá trị tham số không chính xác',
    'insufficient_balance' => 'không đủ tiền',
    'file_not_eixt' => 'Tập tin không tồn tại',
    'min_max_error' => 'Không thấp hơn giá trị tối đa bằng mức tối thiểu',
    'last_max_error' => 'Cuối cùng, một tối đa phải được trống',
    'last_max_next_min' => 'Một giá trị tối đa ở mức tối thiểu phải bằng các',
    'ip_error' => 'Địa chỉ IP là không chính xác',
    'domain_error' => 'tên miền không chính xác',
    'whitelist_not_exist' => 'danh sách trắng không tồn tại',
    'balance_str_error' => 'Giới hạn phải là một số',
    'export_requisite_uid' => 'Vui lòng chọn một cầu thủ và sau đó dữ liệu xuất khẩu',
    'no_data_export' => 'Dữ liệu là trống rỗng, không thể xuất khẩu',
    'hall_requiset' => 'Vui lòng chọn một sảnh chính',
    'agent_requiset' => 'Vui lòng chọn một proxy',
    'player_requiset' => 'Vui lòng chọn một cầu thủ',
    'scale_error' => 'Nó phải lớn hơn 0 trong tỷ lệ chiếm',
    'user_name' => 'Tên đăng nhập phải bắt đầu bằng một bức thư, 6-20 chữ, gạch dưới, và số',
    'hall_has_data' => 'Sảnh chính đã được thêm vào so với dữ liệu',
    'alias' => 'Tên người dùng không thể để trống',
    'user_sign_out' => 'Người chơi đã bị đăng xuất',
);