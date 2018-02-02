<?php
return array (
    'area' =>
        array (
            'required' => '운영 지역을 선택하세요',
        ),
    'time_zone' =>
        array (
            'required' => '시간대를 선택하세요',
        ),
    'agent_name' =>
        array (
            'required' => '로그인 이름은 비워 둘 수 없습니다',
            'unique' => '로그인 이름이 이미 존재합니다',
            'regex' => '로그인 이름은 문자, 6-20 문자, 밑줄, 숫자로 시작해야합니다',
        ),
    'real_name' =>
        array (
            'required' => '사용자 이름은 비워 둘 수 없습니다',
            'regex' => '사용자 이름은 3-20 문자, 밑줄, 숫자 및 중국어 구성해야합니다',
        ),
    'password' =>
        array (
            'required' => '암호는 비워 둘 수 없습니다',
            'min' => '암호 수 없습니다 미만 6',
            'confirmed' => '암호 및 암호 확인 일관성을',
        ),
    'tel' =>
        array (
            'required' => '전화 번호는 비워 둘 수 없습니다',
        ),
    'email' =>
        array (
            'required' => '전자 메일은 비워 둘 수 없습니다',
            'email' => '전자 메일 형식 오류',
            'unique' => '이메일이 이미 존재합니다',
        ),
    'hall_id' =>
        array (
            'required' => '메인 홀에서 직접 선택하세요',
        ),
    'agent_code' =>
        array (
            'required' => '에이전트 코드는 비워 둘 수 없습니다',
            'unique' => '에이전트 코드가 이미 존재',
            'error' => '코드에서 에이전트는 문자, 3-6 문자, 밑줄, 숫자로 시작해야합니다',
        ),
    'success' => '성공적인 운영',
    'save_fails' => '저장 실패',
    'save_success' => '성공적으로 저장',
    'add_fails' => '추가 실패',
    'grade_id_error' => 'grade_id 파라미터 값 오차',
    'fails' => '작업이 실패',
    'user_not_exist' => '플레이어는 존재하지 않는',
    'user_has_exist' => '플레이어 이미 존재',
    'agent_not_exist' => '에이전트가 존재하지 않습니다',
    'hall_not_exist' => '메인 홀이 존재하지 않습니다',
    'game_not_exist' => '이 게임은 존재하지 않습니다',
    'limit_group_exist' => '제한 그룹이 이미 존재합니다',
    'limit_group_not_exist' => '제한 그룹화가 없습니다',
    'param_error' => '잘못된 매개 변수 값',
    'insufficient_balance' => '부족 자금',
    'file_not_eixt' => '파일이 존재하지 않습니다',
    'min_max_error' => '최소값 최대 값 이하 동일하지',
    'last_max_error' => '마지막으로, 최대 비어 있어야합니다',
    'last_max_next_min' => '최소에서 최대 값이 동일해야',
    'ip_error' => 'IP 주소가 올바르지 않습니다',
    'domain_error' => '잘못된 도메인 이름',
    'whitelist_not_exist' => '화이트리스트가 존재하지 않습니다',
    'balance_str_error' => '제한 숫자 여야합니다',
    'export_requisite_uid' => '선수 한 다음 내보내기 데이터를 선택하세요',
    'no_data_export' => '데이터를 내보낼 수 없습니다, 비어',
    'hall_requiset' => '메인 홀을 선택하세요',
    'agent_requiset' => '프록시를 선택하세요',
    'player_requiset' => '선수를 선택하세요',
    'scale_error' => '이 차지하는 비율이 0보다 커야합니다',
    'user_name' => '로그인 이름은 문자, 6-20 문자, 밑줄, 숫자로 시작해야합니다',
    'hall_has_data' => '본당은 데이터에 추가되었습니다',
    'alias' => '사용자 이름은 비워 둘 수 없습니다',
    'user_sign_out' => '플레이어가 로그 아웃되었습니다',
);