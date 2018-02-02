<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/8
 * Time: 17:38
 */
namespace App\Http\Controllers\Admin\V1;

use App\Models\Agent;
use Illuminate\Http\Request;
use App\Models\UserChartInfo;
use App\Models\GameHall;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class GameStatisticsController extends BaseController
{
    public function __construct()
    {
        if( ! File::exists('excel/')) {
            File::makeDirectory('excel/');
        }
    }


    /**
     * @api {get} /totalBet 报表统计-查询总投注额
     * @apiDescription 报表统计-查询总投注额
     * @apiGroup report
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} game_hall_id 游戏厅类型,0:旗舰厅，1贵宾厅，2：金臂厅， 3：至尊厅
     * @apiParam {Number} login_type 登录类型,0 网页登陆；1 手机字符登录 2 手机手势登录
     * @apiParam {String} start_time 开始时间 2017-01-20 15:07:07
     * @apiParam {String} end_time 结束时间  2017-01-20 15:07:07
     * @apiParam {Number} page 当前页,默认1
     * @apiParam {Number} page_num 每页显示条数，默认10
     * @apiParam {Number} is_export 是否导出 1是，0否 默认为 0
     * @apiSuccessExample {json} 查询总投注额数据格式
        {
        "code": 0,
        "text": "操作成功",
        "result": {
        "total": 2,//总页数
        "per_page": 1,//每页显示条数
        "current_page": 1,//当前页
        "data": [
        {
        "hall_id": 1,//厅主id
        "hall_name": "csj",//厅主名称
        "game_hall_id": 0,//厅id
        "game_hall_code": "GH0001",//厅标识码
        "game_round_num": 23,//总笔数
        "valid_bet_score_total": 6450,//总有效投注额
        "total_bet_score": 7500,//总投注额
        "operator_win_score": 350//商家盈利
        }
        ],
        "total_page_score": {//当前页的小计
        "game_round_num": 23,//总笔数
        "valid_bet_score_total": 6450,//总有效投注额
        "total_bet_score": 7500,//总投注额
        "operator_win_score": 350//商家盈利
        },
        "total_score": {//总计
        "game_round_num": 24,//总笔数
        "valid_bet_score_total": 6750,6450,//总有效投注额
        "total_bet_score": 7800,//总投注额
        "operator_win_score": 50//商家盈利
        }
        }
        }
     * @apiSuccessExample {json} 导出总投注额数据格式
        {
        "code": 0,
        "text": "操作成功",
        "result": {
        "url": "http://platform.va/excel/查询总投注额_20170330.xlsx"//excel地址
        }
        }
     */
    public function totalBet(Request $request)
    {
        $game_hall_id = $request->input('game_hall_id');
        $login_type = $request->input('login_type');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $is_export = $request->input('is_export', 0);
        $page = (int)$request->input('page', 1);
        $page_num = (int)$request->input('page_num', 10);

        $skip = (int) ($page-1) * $page_num;
        $limit = (int) $page_num;

        //按游戏厅和厅主分组
        $group = [
            'hall_id' => '$hall_id',
        ];
        //显示的字段
        $field = [
//            'is_cancel' =>1,
            'hall_id' =>1,
            'hall_name' =>1,
            'game_hall_id' =>1,
            'game_hall_code' =>1,
            'game_round_num' =>1,
            'valid_bet_score_total' =>1,
            'total_bet_score' =>1,
            'operator_win_score' =>1,
        ];
        //过滤
        $match = [
            'is_cancel' => 0,
        ];

        //获取测试厅主id
        $ids = Agent::where(['grade_id' => 1, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id')->toArray();
        $match['hall_id']['$nin'] = $ids;
        //查询厅
        if(isset($game_hall_id) && $game_hall_id !== '') {

            $match['game_hall_id'] = (int)$game_hall_id;
        }

        if( $login_type === null ) $login_type = '';
        //过滤登录类型
        if(isset($login_type) && $login_type !== '') {

            $match['login_type'] = (int)$login_type;
        }

        if(isset($start_time) && !empty($start_time)) {
            $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($start_time)* 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_time) && !empty($end_time)) {
            $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($end_time)* 1000);
            $match['start_time']['$lt'] = $e_time;
        }


        //数据导出
        if($is_export) {
            set_time_limit(0);
            ini_set('memory_limit','500M');
            //导出数据最多1000条
            $data = $count_data = self::getUserChartInfo($group, $match, $field,['hall_id' =>1], 0, 1000);
//            $data = $count_data = self::groupbyData($data, 'hall_id');
            //金额格式化
            foreach ($data as &$v) {
                $obj = Agent::select('real_name')->find($v['hall_id']);
                $v['hall_name'] = $v['hall_name']."（{$obj->real_name}）";
//                $v['game_hall_code'] = trans('gamehall.'.$v['game_hall_code']);
                $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'],2);
                $v['total_bet_score'] = number_format($v['total_bet_score'],2);
                $v['operator_win_score'] = number_format($v['operator_win_score'],2);
                unset($v['hall_id']);
                unset($v['game_hall_id']);
                unset($v['game_hall_code']);
            }
            unset($v);

            $title = [
                trans('export.hall_login_name'),
                trans('export.cout_num'),
                trans('export.valid_bet_score'),
                trans('export.bet_score'),
                trans('export.win_score'),
            ];
            $total_data = self::getCountScore($count_data);
            $num = count($title)-count($total_data);
            $total_arr = array_merge(array_fill(0, $num, ""), $total_data);
            $total_arr[0] = 'Total';
            array_push($data, $total_arr);


            array_unshift($data, $title);
            $sub_title = trans('export.sub_title');//Query main report
            $widths = [15,10,12,12,12];
            //游戏厅标题
            $game_hall_title = trans('gamehall.title').':';
            $game_hall_title .= isset($game_hall_id) && $game_hall_id !== '' ? trans('gamehall.'.$game_hall_id) : trans('gamehall.all');
            //设备标题
            $device_title = trans('device.title').':';
            $device_title .= $login_type !== '' ? trans('device.'.$login_type) : trans('device.all');

            $date_area = trans('export.date_range').':'.$start_time.' - '.$end_time;

//            $header = '开始日期：'.($start_time ? $start_time : '无限制，').' 结束日期：'.($end_time ? $end_time : '无限制');
            $header = $game_hall_title.','.$device_title.','.$date_area;

            $filename = $sub_title.'_'.date('Ymd',time()).time();

            $re = self::export($filename,$sub_title,$header,$data,$widths);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'url' => 'http://'.$request->server("HTTP_HOST").'/'.$re['full']
                ],
            ]);

        } else {

            $total_data = self::getUserChartInfo($group, $match, $field,['hall_id' =>1]);
//            $total_data = self::groupbyData($total_data, 'hall_id');

            $data = $count_data = array_slice($total_data,$skip,$limit);

            foreach ($data as &$v) {
                $obj = Agent::select('real_name')->find($v['hall_id']);
                $v['hall_name'] = $v['hall_name']."（{$obj->real_name}）";
                $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'],2);
                $v['total_bet_score'] = number_format($v['total_bet_score'],2);
                $v['operator_win_score'] = number_format($v['operator_win_score'],2);
            }
            unset($v);
            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'total' => count($total_data),
                    'per_page' => $page_num,
                    'current_page' => $page,
                    'data' => $data,
                    'total_page_score' => self::getCountScore($count_data),
                    'total_score' => self::getCountScore($total_data),
                ],
            ]);
        }


    }

    private static function getCountScore($data)
    {
        $total = [
            'game_round_num' => 0,
            'valid_bet_score_total' => 0,
            'total_bet_score' => 0,
            'operator_win_score' => 0,
        ];
        if( $data ) {

            foreach ($data as $k => $v) {
                $total['game_round_num'] += $v['game_round_num'];
                $total['valid_bet_score_total'] += $v['valid_bet_score_total'];
                $total['total_bet_score'] += $v['total_bet_score'];
                $total['operator_win_score'] += $v['operator_win_score'];
            }

            $total['valid_bet_score_total'] = number_format($total['valid_bet_score_total'], 2);
            $total['total_bet_score'] = number_format($total['total_bet_score'], 2);
            $total['operator_win_score'] = number_format($total['operator_win_score'], 2);
        }

        return $total;
    }
    /**
     * @api {get} /totalBet/hall 报表统计-查询指定厅主
     * @apiDescription 报表统计-查询指定厅主
     * @apiGroup report
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} hall_id 厅主id
     * @apiParam {String} hall_name 厅主名称
     * @apiParam {Number} game_hall_id 游戏厅类型,0:旗舰厅，1贵宾厅，2：金臂厅， 3：至尊厅
     * @apiParam {Number} game_id 游戏id
     * @apiParam {String} start_time 开始时间 2017-01-20 15:07:07
     * @apiParam {String} end_time 结束时间  2017-01-20 15:07:07
     * @apiParam {Number} is_export 是否导出 1是，0否 默认为 0
     * @apiSuccessExample {json} 列表返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "data": [
    {
    "hall_id": 1,//厅主id
    "hall_name": "",//厅主名称
    "game_hall_id": 0,//游戏厅类型,0:旗舰厅，1贵宾厅，2：金臂厅， 3：至尊厅
    "game_hall_code": "GH0001",//游戏厅标识码
    "game_name": "极速百家乐",//游戏名称
    "game_round_num": 8,//局数
    "valid_bet_score_total": 3510,//有效投注额
    "total_bet_score": 3510,//投注额
    "operator_win_score": -16000//盈利
    }
    ]
    }
    }
     * @apiSuccessExample {json} 导出数据返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "url": "http://platform.va/excel/查询指定厅主_20170330.xlsx"//excel地址
    }
    }
     */
    public function hallTotalBet(Request $request)
    {
        $hall_id = $request->input('hall_id');
        $hall_name = $request->input('hall_name');
        $game_hall_id = $request->input('game_hall_id');
        $game_id = $request->input('game_id');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $is_export = $request->input('is_export',0);

        if(!$hall_id && !$hall_name) {
            if($is_export) {
                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.no_data_export'),
                    'result'=>'',
                ]);
            }
            //要指定厅主
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.hall_requiset'),
                'result'=>'',
            ]);
        }

        //按厅主分组
        $group = [
            'hall_id' => '$hall_id',
        ];
        //显示的字段
        $field = [
//            'is_cancel'=>1,
            'hall_id' =>1,
            'hall_name' =>1,
            'game_hall_id' =>1,
            'game_hall_code' =>1,
            'game_name' =>1,
            'game_round_num' =>1,
            'valid_bet_score_total' =>1,
            'total_bet_score' =>1,
            'operator_win_score' => 1,

        ];
        //过滤
        $match = [
            'is_cancel'=>0
        ];
        //查询厅
        if(isset($game_hall_id) && $game_hall_id !== '') {
            $match['game_hall_id'] = (int)$game_hall_id;
        }
        //厅主id
        if(isset($hall_id) && !empty($hall_id)) {
            $match['hall_id'] = (int)$hall_id;
        }

        if(isset($hall_name) && !empty($hall_name)) {
//            $match['hall_name']['$regex'] = $hall_name;
            $match['hall_name'] = $hall_name;
        }

        if(isset($game_id) && !empty($game_id)) {
            $match['game_id'] = (int)$game_id;
        }

        if(isset($start_time) && !empty($start_time)) {
            $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($start_time)* 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_time) && !empty($end_time)) {
            $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($end_time)* 1000);
            $match['start_time']['$lt'] = $e_time;
        }
        $data = self::getUserChartInfo($group,$match,$field);

//        $data = self::groupbyData($data, 'hall_id');

        foreach ($data as &$v) {
            if($game_hall_id === '') {
                $v['game_hall_id'] = '';
                $v['game_hall_code'] = '';
            }
            if(!$game_id) {
                $v['game_name'] = '';
            }

            $obj = Agent::select('real_name')->find($v['hall_id']);
            $v['hall_name'] = $v['hall_name']."（{$obj->real_name}）";
            $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'], 2);
            $v['total_bet_score'] = number_format($v['total_bet_score'], 2);
            $v['operator_win_score'] = number_format($v['operator_win_score'], 2);
            if($is_export) {
                $v['game_hall_id'] = $v['game_hall_id'] ? trans('gamehall.'.$v['game_hall_id']) : trans('gamehall.all');
                $v['game_name'] = $v['game_name'] ? $v['game_name'] : trans('gamehall.all');
                unset($v['hall_id']);
                unset($v['game_hall_code']);
            }
        }
        unset($v);

        //数据导出
        if($is_export) {
            $title = [
//                '厅主ID',
                trans('export.login_name'),
                trans('export.game_hall_title'),
                trans('export.game_title'),
                trans('export.cout_num'),
                trans('export.valid_bet_score'),
                trans('export.bet_score'),
                trans('export.win_score'),
            ];
            array_unshift($data, $title);
            $sub_title = trans('export.sub_hall_title');
            $widths = [15,12,13,12,10,10,10];

            $header = trans('export.date_range').':'.$start_time.' - '.$end_time;

//            $header = '开始日期：'.($start_time ? $start_time : '无限制，').' 结束日期：'.($end_time ? $end_time : '无限制');
            $filename = $sub_title.'_'.date('Ymd',time()).time();

            $re = self::export($filename,$sub_title,$header,$data,$widths);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'url' => 'http://'.$request->server("HTTP_HOST").'/'.$re['full']
                ],
            ]);

        } else {

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'data' => $data,
                ],
            ]);
        }

    }

    /**
     * @api {get} /totalBet/agent 报表统计-查询指定代理
     * @apiDescription 报表统计-查询指定代理
     * @apiGroup report
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} agent_id 代理id
     * @apiParam {String} agent_name 代理名称
     * @apiParam {Number} game_hall_id 游戏厅类型,0:旗舰厅，1贵宾厅，2：金臂厅， 3：至尊厅
     * @apiParam {Number} game_id 游戏id
     * @apiParam {String} start_time 开始时间 2017-01-20 15:07:07
     * @apiParam {String} end_time 结束时间  2017-01-20 15:07:07
     * @apiParam {Number} is_export 是否导出 1是，0否 默认为 0
     * @apiSuccessExample {json} 列表返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "data": [
    {
    "agent_id": 1,//代理id
    "agent_name": "",//代理名称
    "game_hall_id": 0,//游戏厅类型,0:旗舰厅，1贵宾厅，2：金臂厅， 3：至尊厅
    "game_hall_code": "GH0001",//游戏厅标识码
    "game_name": "极速百家乐",//游戏名称
    "game_round_num": 8,//局数
    "valid_bet_score_total": 3510,//有效投注额
    "total_bet_score": 3510,//投注额
    "operator_win_score": -16000//盈利
    }
    ]
    }
    }
     * @apiSuccessExample {json} 导出数据返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "url": "http://platform.va/excel/查询指定代理_20170330.xlsx"//excel地址
    }
    }
     */
    public function agentTotalBet(Request $request)
    {
        $agent_id = $request->input('agent_id');
        $agent_name = $request->input('agent_name');
        $game_hall_id = $request->input('game_hall_id');
        $game_id = $request->input('game_id');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $is_export = $request->input('is_export',0);

        if( !$agent_id && !$agent_name ) {
            if($is_export) {
                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.no_data_export'),
                    'result'=>'',
                ]);
            }
            //要指定代理
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_requiset'),
                'result'=>'',
            ]);
        }

        //按代理分组
        $group = [
            'agent_id' => '$agent_id',
        ];
        //显示的字段
        $field = [
//            'is_cancel' =>1,
            'agent_id' =>1,
            'agent_name' =>1,
            'game_hall_id' =>1,
            'game_hall_code' =>1,
            'game_name' =>1,
            'game_round_num' =>1,
            'valid_bet_score_total' =>1,
            'total_bet_score' =>1,
            'operator_win_score' =>1,
        ];
        //过滤
        $match = [
            'is_cancel'=>0
        ];
        //查询厅
        if(isset($game_hall_id) && $game_hall_id !== '') {
            $match['game_hall_id'] = (int)$game_hall_id;
        }
        //厅主id
        if(isset($agent_id) && !empty($agent_id)) {
            $match['agent_id'] = (int)$agent_id;
        }

        if(isset($agent_name) && !empty($agent_name)) {
//            $match['agent_name']['$regex'] = $agent_name;
            $match['agent_name'] = $agent_name;
        }

        if(isset($game_id) && !empty($game_id)) {
            $match['game_id'] = (int)$game_id;
        }

        if(isset($start_time) && !empty($start_time)) {
            $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($start_time)* 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_time) && !empty($end_time)) {
            $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($end_time)* 1000);
            $match['start_time']['$lt'] = $e_time;
        }

        $data = self::getUserChartInfo($group,$match,$field);
//        $data = self::groupbyData($data, 'agent_id');
        foreach ($data as &$v) {
            if($game_hall_id === '') {
                $v['game_hall_id'] = '';
                $v['game_hall_code'] = '';
            }
            if(!$game_id) {
                $v['game_name'] = '';
            }

            $obj = Agent::select('real_name')->find($v['agent_id']);
            $v['agent_name'] = $v['agent_name']."（{$obj->real_name}）";
            $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'], 2);
            $v['total_bet_score'] = number_format($v['total_bet_score'], 2);
            $v['operator_win_score'] = number_format($v['operator_win_score'], 2);
            if($is_export) {
                $v['game_hall_id'] = $v['game_hall_id'] ? trans('gamehall.'.$v['game_hall_id']) : trans('gamehall.all');
                $v['game_name'] = $v['game_name'] ? $v['game_name'] : trans('gamehall.all');
                unset($v['agent_id']);
                unset($v['game_hall_code']);
            }
        }

        //数据导出
        if($is_export) {
            $title = [
//                '代理ID',
                trans('export.login_name'),
                trans('export.game_hall_title'),
                trans('export.game_title'),
                trans('export.cout_num'),
                trans('export.valid_bet_score'),
                trans('export.bet_score'),
                trans('export.win_score'),
            ];
            array_unshift($data, $title);
            $sub_title = trans('export.sub_agent_title');
            $widths = [10,15,15,12,10,10,10];
            $header = trans('export.date_range').':'.$start_time.' - '.$end_time;
//            $header = '开始日期：'.($start_time ? $start_time : '无限制，').' 结束日期：'.($end_time ? $end_time : '无限制');
            $filename = $sub_title.'_'.date('Ymd',time()).time();

            $re = self::export($filename,$sub_title,$header,$data,$widths);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'url' => 'http://'.$request->server("HTTP_HOST").'/'.$re['full']
                ],
            ]);

        } else {
            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'data' => $data,
                ],
            ]);
        }

    }

    /**
     * @api {get} /totalBet/player 报表统计-查询指定玩家
     * @apiDescription 报表统计-查询指定玩家
     * @apiGroup report
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} user_id 玩家id
     * @apiParam {String} account 玩家账号
     * @apiParam {Number} game_hall_id 游戏厅类型,0:旗舰厅，1贵宾厅，2：金臂厅， 3：至尊厅
     * @apiParam {Number} game_id 游戏id
     * @apiParam {String} start_time 开始时间 2017-01-20 15:07:07
     * @apiParam {String} end_time 结束时间  2017-01-20 15:07:07
     * @apiParam {Number} is_export 是否导出 1是，0否 默认为 0
     * @apiSuccessExample {json} 列表返回格式
        {
        "code": 0,
        "text": "操作成功",
        "result": {
        "data": [
        {
        "user_id": 965,//玩家id
        "account": "a9TEST607821",//玩家账号（登录名）
        "game_hall_id": 0,//游戏厅id
        "game_hall_code": "GH0001",//游戏厅标识码
        "game_name": "",//游戏名称
        "game_round_num": 1,//总笔数
        "valid_bet_score_total": 10,//总有效数投注
        "total_bet_score": 10,//总投注
        "total_win_score": 10//玩家盈利
        }
        ]
        }
        }
     * @apiSuccessExample {json} 导出数据返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "url": "http://platform.va/excel/查询指定代理_20170330.xlsx"//excel地址
    }
    }
     */
    public function playerTotalBet(Request $request)
    {
        $user_id = $request->input('user_id');
        $account = $request->input('account');
        $game_hall_id = $request->input('game_hall_id');
        $game_id = $request->input('game_id');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $is_export = $request->input('is_export',0);

        if(!$user_id && !$account) {
            if($is_export) {
                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.no_data_export'),
                    'result'=>'',
                ]);
            }
            //要指定玩家
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.player_requiset'),
                'result'=>'',
            ]);
        }

        //按玩家分组
        $group = [
            'user_id' => '$user_id',
        ];
        //显示的字段
        $field = [
//            'is_cancel' =>1,
            'account' =>1,
            'game_hall_id' =>1,
            'game_hall_code' =>1,
            'game_name' =>1,
            'game_round_num' =>1,
            'valid_bet_score_total' =>1,
            'total_bet_score' =>1,
            'total_win_score' =>1,
        ];
        //过滤
        $match = [
            'is_cancel'=>0,
        ];


        //查询厅
        if(isset($game_hall_id) && $game_hall_id !== '') {
            $match['game_hall_id'] = (int)$game_hall_id;
        }
        //玩家id
        if(isset($user_id) && !empty($user_id)) {
            $match['user_id'] = (int)$user_id;
        }

        //玩家名称
        if(isset($account) && !empty($account)) {
//            $match['account']['$regex'] = $account;
            $match['account'] = $account;
        }

        if(isset($game_id) && !empty($game_id)) {
            $match['game_id'] = (int)$game_id;
        }

        if(isset($start_time) && !empty($start_time)) {
            $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($start_time)* 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_time) && !empty($end_time)) {
            $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($end_time)* 1000);
            $match['start_time']['$lt'] = $e_time;
        }
        $data = self::getUserChartInfo($group,$match,$field,['user_id' =>1]);
//        $data = self::groupbyData($data, 'user_id');
        foreach ($data as &$v) {
            if($game_hall_id === '') {
                $v['game_hall_id'] = '';
                $v['game_hall_code'] = '';
            }
            if(!$game_id) {
                $v['game_name'] = '';
            }

            $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'], 2);
            $v['total_bet_score'] = number_format($v['total_bet_score'], 2);
            $v['total_win_score'] = number_format($v['total_win_score'], 2);

            if($is_export) {
                $v['game_hall_id'] = $v['game_hall_id'] ? trans('gamehall.'.$v['game_hall_id']) : trans('gamehall.all');
                $v['game_name'] = $v['game_name'] ? $v['game_name'] : trans('gamehall.all');
                unset($v['game_hall_code']);
            }
        }
        unset($v);

        //数据导出
        if($is_export) {
            $title = [
//                '玩家ID',
                trans('export.login_name'),
                trans('export.game_hall_title'),
                trans('export.game_title'),
                trans('export.cout_num'),
                trans('export.valid_bet_score'),
                trans('export.bet_score'),
                trans('export.win_score'),
            ];
            array_unshift($data, $title);
            $sub_title = trans('export.sub_player_title');
            $widths = [10,15,15,17,10,10,10];
            $header = trans('export.date_range').':'.$start_time.' - '.$end_time;
//            $header = '开始日期：'.($start_time ? $start_time : '无限制，').' 结束日期：'.($end_time ? $end_time : '无限制');
            $filename = $sub_title.'_'.date('Ymd',time()).time();

            $re = self::export($filename,$sub_title,$header,$data,$widths);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'url' => 'http://'.$request->server("HTTP_HOST").'/'.$re['full']
                ],
            ]);

        } else {
            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'data' => $data,
                ],
            ]);
        }

    }

    /**
     * @api {get} /totalBet/game 报表统计-查询游戏
     * @apiDescription 报表统计-查询游戏
     * @apiGroup report
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} game_id 游戏id
     * @apiParam {String} start_time 开始时间 2017-01-20 15:07:07
     * @apiParam {String} end_time 结束时间  2017-01-20 15:07:07
     * @apiParam {Number} page 当前页 默认1
     * @apiParam {Number} page_num 每页显示条数 默认10
     * @apiParam {Number} is_export 是否导出 1是，0否 默认为 0
     * @apiSuccessExample {json} 列表返回格式
        {
        "code": 0,
        "text": "操作成功",
        "result": {
        "total": 5,
        "per_page": "1",
        "current_page": "1",
        "data": [
        {
        "game_id": 93,//游戏id
        "game_name": "龙虎 ",//游戏名称
        "game_round_num": 5,//局数
        "valid_bet_score_total": 2500,//有效投注额
        "total_bet_score": 2500,//投注额
        "operator_win_score": 100//商家盈利
        }
        ],
        "total_page_score": {//当前页的小计
        "game_round_num": 5,//总笔数
        "valid_bet_score_total": 2500,//总有效投注额
        "total_bet_score": 2500,//总投注额
        "operator_win_score": 100//商家盈利
        },
        "total_score": {//总计
        "game_round_num": 24,//总笔数
        "valid_bet_score_total": 6750,//总有效投注额
        "total_bet_score": 7800,//总投注额
        "operator_win_score": 50//商家盈利
        }
        }
        }
     * @apiSuccessExample {json} 导出数据返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "url": "http://platform.va/excel/查询指定代理_20170330.xlsx"//excel地址
    }
    }
     */
    public function gameTotalBet(Request $request)
    {
        $game_id = $request->input('game_id');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $page = $request->input('page',1);
        $page_num = $request->input('page_num',10);
        $is_export = $request->input('is_export',0);

        $skip = (int) ($page-1) * $page_num;
        $limit = (int) $page_num;
        //按游戏分组
        $group = [
            'game_id' => '$game_id',
        ];
        //显示的字段
        $field = [
//            'is_cancel' =>1,
            'game_id' =>1,
            'game_name' =>1,
            'game_round_num' =>1,
            'valid_bet_score_total' =>1,
            'total_bet_score' =>1,
            'operator_win_score' =>1,
        ];
        //过滤
        $match = [
            'is_cancel'=>0,
        ];

        //获取测试厅主id
        $ids = Agent::where(['grade_id' => 1, 'is_hall_sub' => '0'])->whereIn('account_type',[2,3])->pluck('id')->toArray();
        $match['hall_id']['$nin'] = $ids;

        if(isset($game_id) && !empty($game_id)) {
            $match['game_id'] = (int)$game_id;
        }

        if(isset($start_time) && !empty($start_time)) {
            $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($start_time)* 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_time) && !empty($end_time)) {
            $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($end_time)* 1000);
            $match['start_time']['$lt'] = $e_time;
        }

        //数据导出
        if($is_export) {
            set_time_limit(0);
            ini_set('memory_limit','500M');
            $data = $count_data = self::getUserChartInfo($group, $match, $field,['game_id' =>1], 0, 30000);
            //手动--->按游戏分组
//            $data = $count_data = self::groupbyData($data, 'game_id');
            foreach ($data as &$v) {
                $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'],2);
                $v['total_bet_score'] = number_format($v['total_bet_score'],2);
                $v['operator_win_score'] = number_format($v['operator_win_score'],2);
            }
            unset($v);
            $total_data = self::getCountScore($count_data);
            $num = count($field)-count($total_data);
            $total_arr = array_merge(array_fill(0, $num, ""), $total_data);
            $total_arr[0] = '总计';
            array_push($data, $total_arr);
            $title = [
//                '游戏ID',
                trans('export.game_title'),
                trans('export.cout_num'),
                trans('export.valid_bet_score'),
                trans('export.bet_score'),
                trans('export.win_score'),
            ];
            array_unshift($data, $title);
            $sub_title = trans('export.sub_game_title');
            $widths = [10,15,15,17,10];
            $header = trans('export.date_range').':'.$start_time.' - '.$end_time;
//            $header = '开始日期：'.($start_time ? $start_time : '无限制，').' 结束日期：'.($end_time ? $end_time : '无限制');
            $filename = $sub_title.'_'.date('Ymd',time()).time();

            $re = self::export($filename,$sub_title,$header,$data,$widths);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'url' => 'http://'.$request->server("HTTP_HOST").'/'.$re['full']
                ],
            ]);

        } else {
            $total_data = self::getUserChartInfo($group, $match, $field,['game_id' =>1]);
            //手动--->按游戏分组
//            $total_data = self::groupbyData($total_data, 'game_id');
            //分页
            $data  = $count_data = array_slice($total_data,$skip,$limit);
            foreach ($data as &$v) {
                $v['valid_bet_score_total'] = number_format($v['valid_bet_score_total'],2);
                $v['total_bet_score'] = number_format($v['total_bet_score'],2);
                $v['operator_win_score'] = number_format($v['operator_win_score'],2);
            }
            unset($v);
            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => [
                    'total' => count($total_data),
                    'per_page' => $page_num,
                    'current_page' => $page,
                    'data' => $data,
                    'total_page_score' => self::getCountScore($count_data),//每页小计
                    'total_score' => self::getCountScore($total_data),//总计
                ],
            ]);
        }

    }

    /**报表统计-二维数组分组并统计金额
     * @param array $data 二维数组
     * @param string $key 要分组的字段名称
     * @return array
     */
    protected function groupbyData(array $data, string $key)
    {

        $grouped = [];
        foreach ($data as $v) {
            $grouped[$v[$key]][] = $v;
        }
        $datas = [];

        foreach ($grouped as $k => $v) {

            isset($v[0]['hall_id']) && $datas[$k]['hall_id'] = $v[0]['hall_id'];
            isset($v[0]['agent_id']) && $datas[$k]['agent_id'] = $v[0]['agent_id'];
            isset($v[0]['account']) && $datas[$k]['account'] = $v[0]['account'];
            isset($v[0]['hall_name']) && $datas[$k]['hall_name'] = $v[0]['hall_name'];
            isset($v[0]['agent_name']) && $datas[$k]['agent_name'] = $v[0]['agent_name'];
            isset($v[0]['game_hall_id']) && $datas[$k]['game_hall_id'] = $v[0]['game_hall_id'];
            isset($v[0]['game_hall_code']) && $datas[$k]['game_hall_code'] = $v[0]['game_hall_code'];
            isset($v[0]['game_name']) && $datas[$k]['game_name'] = $v[0]['game_name'];

            $datas[$k]['game_round_num'] = count($v);
            $datas[$k]['total_bet_score'] = 0;
            $datas[$k]['valid_bet_score_total'] = 0;
            $datas[$k]['operator_win_score'] = 0;
            foreach ($v as $vv) {
                $datas[$k]['total_bet_score'] += $vv['total_bet_score'];
                $datas[$k]['valid_bet_score_total'] += $vv['valid_bet_score_total'];
                !$vv['is_cancel'] && $datas[$k]['operator_win_score'] += $vv['operator_win_score'];
            }

        }
        return $datas;
    }
    /**
     * 游戏数据报表分组查询
     * @param array $group mongo的分组 格式 ['game_hall_id' => '$game_hall_id']
     * @param array $match mongo的过滤 格式 ['is_cancel'=>0]
     * @param array $field 要显示的字段 ['hall_id' =>1]
     * @param array $sort 排序 ['hall_id' =>1] 正序：1，倒叙：-1
     * @param int $skip 从第几页开始
     * @param int $limit 取出条数

     * @return array
     */
    public static function getUserChartInfo(array $group, array $match, array $field, array $sort = [] ,  $skip = '', $limit = '') : array
    {
        $data = [];
        $aggregate = [];
        $match_ =  $match ? ['$match' => $match] : '';
        $skip_ = $skip ? ['$skip' => $skip] : '';
        $limit_ = $limit ? ['$limit' => $limit] : '';
        $sort_ = $sort ? ['$sort' => $sort] : '';
        $project_ = $field ? ['$project' => $field] : '';
        $group_ = '';
        if( $group ) {

            $groups = [
                '_id' => $group,
                'is_cancel'=> ['$first' =>'$is_cancel'],
                'table_no' => ['$first' =>'$table_no'],
                'round_no' => ['$first' =>'$round_no'],
                'agent_id' => ['$first' =>'$agent_id'],
                'agent_name' => ['$first' =>'$agent_name'],
                'hall_id' => ['$first' =>'$hall_id'],
                'hall_name' => ['$first' =>'$hall_name'],
                'user_id' => ['$first' =>'$user_id'],
                'account' => ['$first' =>'$account'],
                'game_hall_id' => ['$first' =>'$game_hall_id'],
                'game_id' => ['$first' =>'$game_id'],
                'game_hall_code' => ['$first' =>'$game_hall_code'],
                'game_name' => ['$first' =>'$game_name'],
                'game_round_num' => ['$sum'  => 1],
                'valid_bet_score_total' => ['$sum'  => '$valid_bet_score_total'],
                'total_bet_score' => ['$sum'  => '$total_bet_score'],
                'total_win_score' => ['$sum'  => '$total_win_score'],
                'operator_win_score'=>['$sum'  => '$operator_win_score'],
            ];
            $group_ = ['$group' =>$groups];

        }

        $match_ && $aggregate[] = $match_;
        $group_ && $aggregate[] = $group_;
        $sort_ && $aggregate[] = $sort_;
        $limit_ && $aggregate[] = $limit_;
        $skip_ && $aggregate[] = $skip_;
        $project_ && $aggregate[] = $project_;
//        var_export($aggregate);die;
        $res = UserChartInfo::raw(function($collection) use($aggregate) {
            return $collection->aggregate($aggregate);
        });

        if($res) {
            $data = $res->toArray();

        }
        return $data;
    }


    /**
     * 导出excel
     * @param string $filename 保存的文件名
     * @param string $sub_title sheet标题
     * @param string $header 头部标题
     * @param array $data 数据
     * @param array $widhs 单元格宽度 [10,20]
     * @param string $FirstRowBackground 第一行背景颜色
     * @return mixed
     */
    private static function export(string $filename, string $sub_title, string $header, array $data, array $widhs=[],string $FirstRowBackground='#FFB6C1') : array
    {

        $re = Excel::create($filename, function($excel) use($data,$header,$sub_title,$widhs,$FirstRowBackground) {

            $excel->sheet($sub_title, function($sheet) use($data,$header,$sub_title,$widhs,$FirstRowBackground) {
                $column = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X'];
                //设置第一行头标题
                $sheet->row(1, array(
                    $header
                ));
                //设置第一行背景颜色
                /*$sheet->row(1, function($row) use($FirstRowBackground) {
                    $row->setBackground($FirstRowBackground);
                });*/

                //从第二行开始渲染数据
                $sheet->fromArray($data, null, 'A2', true, false);
                //第一行合并单元格
                $sheet->mergeCells('A1:'.$column[(Count($data[0])-1)].'1');
                //设置单元格宽度
                foreach ($widhs as $k => $v){
                    $sheet->setWidth($column[$k], $v);
                }
                //冻结第一行
                $sheet->freezeFirstRow();
            });

        })->store('xlsx', 'excel' , true);

        return $re;
    }

}