<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/8
 * Time: 17:38
 */
namespace App\Http\Controllers\Admin\V1;

use App\Models\GameInfo;
use Illuminate\Http\Request;
use App\Models\GameCat;
use App\Models\UserChartInfo;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class GameController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {get} /game 游戏管理列表
     * @apiDescription 游戏管理列表
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} game_name 游戏名称
     * @apiParam {Number} id 游戏id
     * @apiParam {Number} cat_id 游戏分类
     * @apiParam {Number} status 游戏是否可用,1为可用,0为不可用
     * @apiParam {Number} page 当前页 默认1
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
         * {
            "code": 0,
            "text": "操作成功",
            "result": {
                "total": 1,
                "per_page": 10,
                "current_page": 1,
                "last_page": 1,
                "next_page_url": null,
                "prev_page_url": null,
                "from": 1,
                "to": 1,
                "data": [
                    {
                        "id": 88,
                        "cat_id": 1,
                        "game_name": "龙虎百家乐",
                        "sort_id": 1,
                        "status": 1,
                        "table_count": 2,
                        "user_count": 0,
                        "process_type": 109,
                        "icon": "",
                        "is_recommand": 0,
                            "game_cat": {
                            "id": 1,
                            "cat_name": "视频百家乐 "
                        }
                    }
                ]
            }
        }
     * @apiSuccessExample {json} 不分页时数据格式
     *     HTTP/1.1 200 OK
     * {
        "code": 0,
        "text": "操作成功",
        "result":  [
        {
        "id": 88,
        "cat_id": 1,
        "game_name": "龙虎百家乐",
        "sort_id": 1,
        "status": 1,
        "table_count": 2,
        "user_count": 0,
        "process_type": 109,
        "icon": "",
        "is_recommand": 0,
        "game_cat": {
            "id": 1,
            "cat_name": "视频百家乐 "
        }
        }
        ]
        }
     */
    public function index(Request $request)
    {

        $db = GameInfo::select('*');
        $game_name = $request->input('game_name');
        $id = $request->input('id');
        $cat_id = $request->input('cat_id');
        $status = $request->input('status');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page', 1);
        if(isset($game_name) && !empty($game_name)) {
            $db->where('game_name',$game_name);
        }

        if(isset($id)) {
            $db->where('id',$id);
        }

        if(isset($cat_id) && !empty($cat_id)) {
            $db->where('cat_id',$cat_id);
        }

        if(isset($status) && $status !== '') {

            $db->where('status',$status);
        }
        $db->where('status', '<>', 2);
        $db->orderby('sort_id');
        if(!$is_page) {
            $games = $db->get();
        } else {
            $games = $db->paginate($page_num);
        }


        foreach ($games as $game) {
            $game->cat_id = (int) $game->cat_id;
            $game->gameCat;
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => $games,
        ]);
    }



    /**
     * @api {post} /game 添加游戏
     * @apiDescription 添加游戏
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} game_name 游戏名称
     * @apiParam {Number} cat_id 游戏分类
     * @apiParam {Number} is_recommand 是否推荐
     * @apiParam {Number} sort_id 排序字段
     * @apiParam {Number} status 游戏是否可用,1为可用,0为不可用
     * @apiParam {String} icon 游戏图标
     * @apiParam {String} gameHall 游戏厅 数组格式：[0,1,2,3]
     * @apiSuccessExample {json} Success-Response:
        {
            "code": 0,
            "text": "操作成功",
            "result": ""
        }
     */
    public function store(Request $request)
    {


        $validator = \Validator::make($request->input(), [
            'cat_id' => 'required',
            'game_name' => 'required|unique:game_info',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        $attributes = $request->except('token','locale','gameHall');

        $re = GameInfo::create($attributes);
        if($re){

            $gameHall = $request->input('gameHall');
            //添加游戏厅
            if($gameHall) {
                $gh = [];
                foreach ($gameHall as $v) {
                    $gh[] = ['hall_id' => $v, 'game_id' => $re->id];
                }
                $hall_game_ra = \DB::table('hall_game_ra');
                $gh && $hall_game_ra->insert($gh);
            }
            @addLog(['action_name'=>'添加游戏','action_desc'=>' 添加了一个新游戏，新游戏为： '.$attributes['game_name'],'action_passivity'=>'游戏列表']);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
                'result' => '',
            ]);
        } else {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.fails'),
                'result' => '',
            ]);
        }

    }

    /**
     * @api {put} /game/{id} 编辑游戏
     * @apiDescription 编辑游戏
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} game_name 游戏名称
     * @apiParam {Number} cat_id 游戏分类
     * @apiParam {Number} is_recommand 是否推荐
     * @apiParam {Number} sort_id 排序字段
     * @apiParam {Number} status 游戏是否可用,1为可用,0为不可用
     * @apiParam {String} icon 游戏图标
     * @apiParam {String} gameHall[] 游戏厅 数组格式：[0,1,2,3]
     * @apiSuccessExample {json} Success-Response:
        {
            "code": 0,
            "text": "保存成功",
            "result": ""
        }
     *@apiErrorExample {json} Error-Response:
        {
            "code": 400,
            "text": "保存失败",
            "result": ""
        }
     */
    public function update(Request $request, $id)
    {
        $game = GameInfo::find($id);
        if(!$game) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.game_not_exist'),
                'result' => '',
            ]);
        }

        $validator = \Validator::make($request->input(), [
            'game_name' => 'required|unique:game_info,game_name,'.$id,
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        $attributes = $request->except('token','locale','gameHall');

        $re = $game->where('id', $id)->update($attributes);

        if($re !== false) {

            //编辑游戏厅
            $gameHall = $request->input('gameHall');
            if($gameHall) {
                $hall_game_ra = \DB::table('hall_game_ra');
                $hall_game_ra->where('game_id',$id)->delete();
                $gh = [];
                foreach ($gameHall as $v) {
                    $gh[] = ['hall_id' => $v, 'game_id' => $id];
                }

                $gh && $hall_game_ra->insert($gh);
            }
            @addLog(['action_name'=>'编辑游戏','action_desc'=>' 对游戏： '.$attributes['game_name'].' 进行了编辑','action_passivity'=>'游戏列表']);
            //设置游戏缓存
            self::setGameCahe();
            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.save_success'),
                'result' => '',
            ]);

        } else {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.save_fails'),
                'result' => '',
            ]);

        }

    }


    /**
     * @api {delete} /game/{id} 删除游戏
     * @apiDescription 删除游戏
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "保存成功",
    "result": ""
    }
     *@apiErrorExample {json} Error-Response:
    {
    "code": 400,
    "text": "保存失败",
    "result": ""
    }
     */
    public function delete(Request $request, $id)
    {
        $game = GameInfo::find($id);
        if(!$game) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.game_not_exist'),
                'result' => '',
            ]);
        }
        $re = GameInfo::where('id', $id)->update(['status' => 2]);
        if($re === false) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.fails'),
                'result' => '',
            ]);
        }
        @addLog(['action_name'=>'删除游戏','action_desc'=>' 对游戏： '.$game->game_name.' 进行了删除','action_passivity'=>'游戏列表']);
        //设置游戏缓存
        self::setGameCahe();
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => '',
        ]);
    }

    /**
     * @api {post} /game/{id} 编辑游戏时获取数据
     * @apiDescription 编辑游戏时获取数据
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
        {
            "code": 0,
            "text": "操作成功",
            "result": {
                "data": {
                    "id": 100,
                    "cat_id": 2,
                    "game_name": "龙虎百家乐",
                    "sort_id": 1,
                    "status": 1,
                    "table_count": 0,
                    "user_count": 0,
                    "process_type": 0,
                    "icon": "http://app-loc.dev/images/2017-02-09-17-30-58-589c36d250140.jpg",
                    "is_recommand": 1,
                    "game_cat": {
                        "id": 2,
                        "cat_name": "视频轮盘 "
                    }
                }
            }
        }
     *@apiErrorExample {json} Error-Response:
    {
    "code": 400,
    "text": "游戏不存在",
    "result": ""
    }
     */
    public function show(Request $request, $id)
    {

        $game = GameInfo::find($id);

        if(!$game) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.game_not_exist'),
                'result' => '',
            ]);
        }

        $game->gameCat;
        $game->icon = env('IMAGE_HOST').$game->icon;
        $game->gameHall;
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'data' => $game,
            ],
        ]);
    }

    /**
     * @api {get} /game/cat 游戏分类
     * @apiDescription 游戏分类
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     *
     * {
        "code": 0,
        "text": "操作成功",
        "result": {
            "data": [
                {
                    "id": 1,
                    "parent_id": 0,
                    "cat_name": "视频百家乐 ",
                    "sort_id": 1,
                    "state": 1,
                    "mapping": "",
                    "sub_count": 0,
                    "rank": 1
                },
                {
                    "id": 4,
                    "parent_id": 0,
                    "cat_name": "视频龙虎 ",
                    "sort_id": 2,
                    "state": 1,
                    "mapping": "",
                    "sub_count": 0,
                    "rank": 1
                },
                {
                    "id": 3,
                    "parent_id": 0,
                    "cat_name": "视频骰宝 ",
                    "sort_id": 3,
                    "state": 1,
                    "mapping": "",
                    "sub_count": 0,
                    "rank": 1
                },
                {
                    "id": 2,
                    "parent_id": 0,
                    "cat_name": "视频轮盘 ",
                    "sort_id": 4,
                    "state": 1,
                    "mapping": "",
                    "sub_count": 0,
                    "rank": 1
                }
                ]
            }
        }
     */
    public function cat()
    {
        $cates = GameCat::orderby('sort_id','asc')->get();

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'data' => $cates,
            ],
        ]);
    }


    /**
     * @api {get} /game/chart 报表统计-查询游戏 （导出）
     * @apiDescription 报表统计-查询游戏 导出）
     * @apiGroup report
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} game_hall_id 游戏厅类型,0:旗舰厅，1贵宾厅，2：金臂厅， 3：至尊厅
     * @apiParam {Number} game_id 游戏id
     * @apiParam {Number} agent_id 代理id
     * @apiParam {Number} hall_id 厅主id
     * @apiParam {Number} table_no 桌号
     * @apiParam {String} start_time 开始时间 2017-01-20 15:07:07
     * @apiParam {String} end_time 结束时间  2017-01-20 15:07:07
     * @apiParam {Number} order_by 排序类型(暂时没用到) start_time、user_id、game_hall_id、game_id
     * @apiParam {Number} is_export 是否导出 1是，0否 默认为0
     * @apiParam {Number} type 查询类型：1：查询总投注额，2：查询指定厅主，3：查询指定代理，4：查询厅，5：查询游戏，6：查询桌次， 默认为5：查询游戏
     * @apiParam {Number} table_no 查询桌次
     * @apiSuccessExample {json} Success-Response: 报表统计
     *
        {
        "code": 0,
        "text": "操作成功",
        "result": [
        {
        "game_id": 94,
        "game_round_num": 9,
        "valid_bet_score_total": 2950,
        "total_bet_score": 2950,
        "total_win_score": 15410,
        "win_rate": 0.19143413367943
        }
        ],
        "totals": {
        "game_round_num": 78,
        "valid_bet_score_total": 213776,
        "total_bet_score": 213776,
        "total_win_score": 12292,
        "win_rate": "571.58%"
        }
        }
     *
     *@apiSuccessExample {json} Success-Response: 报表统计-游戏导出
     * {
            "code": 0,
            "text": "操作成功",
            "result": {
                "url": "http://app-loc.dev/excel/user_chart_info_20170215.xls"
            }
        }
     */
    public function chart(Request $request)
    {


        $game_hall_id = $request->input('game_hall_id');
        $hall_id = (int)$request->input('hall_id');
        $game_id = $request->input('game_id');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $table_no = $request->input('table_no');
        $type = $request->input('type',5);
        /*$page = $request->input('page',1);
        $page_num = $request->input('page_num',0);*/
        $is_export = $request->input('is_export',0);
        /*if(!$is_export) {
            $skip = ($page-1) * $page_num;
        } else {
            $skip = 0;
            $page_num = 0;
        }*/

        switch ($type){
            //查询总投注额
            case 1:
                //按游戏厅和厅主分组
                $group = [
                    'game_hall_id' => '$game_hall_id',
                    'hall_id' => '$hall_id',
                ];

                $field = [
                    'hall_id' =>1,
                    'hall_name' =>1,
                    'game_hall_id' =>1,
                    'game_hall_code' =>1,
                    'game_round_num' =>1,
                    'valid_bet_score_total' =>1,
                    'total_bet_score' =>1,
                    'total_win_score' =>1,

                ];

                $title = [
                    '厅主ID',
                    '厅主登录名',
                    '游戏厅类型',
                    '游戏厅标识码',
                    '局数',
                    '有效投注',
                    '投注额',
                    '盈利',
                    '收益率',
                ];
                $sub_title = '查询总投注额';
                $widths = [10,15,15,17,10,10,10,10,10];
                break;
            //查询指定厅主
            case 2:
                if(!$hall_id) {
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

                $field = [
                'hall_id' =>1,
                'hall_name' =>1,
                'game_hall_id' =>1,
                'game_hall_code' =>1,
                'game_name' =>1,
                'game_round_num' =>1,
                'valid_bet_score_total' =>1,
                'total_bet_score' =>1,
                'total_win_score' =>1,

            ];

                $title = [
                    '厅主ID',
                    '厅主名称',
                    '游戏厅类型',
                    '游戏厅标识码',
                    '游戏名称',
                    '局数',
                    '有效投注',
                    '下注总金额',
                    '投注额',
                    '盈利',
                ];
                $sub_title = '查询指定厅主';
                break;
            //查询指定代理
            case 3:

                //按代理、厅和游戏分组
                $group = [
                    'agent_id' => '$agent_id',
                    'game_hall_id' => '$game_hall_id',
                    'game_id' => '$game_id',
                ];

                $field = [
                    'agent_id' =>1,
                    'agent_name' =>1,
                    'game_hall_id' =>1,
                    'game_hall_code' =>1,
                    'game_id' =>1,
                    'game_name' =>1,
                    'game_round_num' =>1,
                    'valid_bet_score_total' =>1,
                    'total_bet_score' =>1,
                    'total_win_score' =>1,

                ];
                $title = [
                    '代理id',
                    '代理名称',
                    '游戏厅类型',
                    '游戏厅标识码',
                    '游戏ID',
                    '游戏名称',
                    '局数',
                    '有效下注总金额',
                    '下注总金额',
                    '总派彩金额',
                    '收益率',
                ];
                $sub_title = '查询指定代理';
                break;
            //查询厅
            case 4:

                //按代理、厅和游戏分组
                $group = [
                    'game_hall_id' => '$game_hall_id',
                ];

                $field = [
                    'game_hall_id' =>1,
                    'game_hall_code' =>1,
                    'game_round_num' =>1,
                    'valid_bet_score_total' =>1,
                    'total_bet_score' =>1,
                    'total_win_score' =>1,

                ];
                $title = [
                    '游戏厅类型',
                    '游戏厅标识码',
                    '局数',
                    '有效下注总金额',
                    '下注总金额',
                    '总派彩金额',
                    '收益率',
                ];
                $sub_title = '查询厅';
                break;
            //查询游戏
            case 5:

                //按游戏分组
                $group = [
                    'game_id' => '$game_id',

                ];
                $field = [
                    'game_id' =>1,
                    'game_name' =>1,
                    'game_round_num' =>1,
                    'valid_bet_score_total' =>1,
                    'total_bet_score' =>1,
                    'total_win_score' =>1,

                ];
                $title = [
                    '游戏ID',
                    '游戏名称',
                    '局数',
                    '有效下注总金额',
                    '下注总金额',
                    '总派彩金额',
                    '收益率',
                ];
                $sub_title = '查询游戏';
                break;
            //查询桌次
            case 6:

                //按桌次分组
                $group = [
                    'table_no' => '$table_no',
                    'agent_id' => '$agent_id',
                ];

                $field = [
                    'table_no' =>1,
                    'agent_id' =>1,
                    'agent_name' =>1,
                    'game_hall_id' =>1,
                    'game_hall_code' =>1,
                    'game_id' =>1,
                    'game_name' =>1,
                    'game_round_num' =>1,
                    'valid_bet_score_total' =>1,
                    'total_bet_score' =>1,
                    'total_win_score' =>1,

                ];

                $title = [
                    '桌次',
                    '代理id',
                    '代理名称',
                    '游戏厅类型',
                    '游戏厅标识码',
                    '游戏ID',
                    '游戏名称',
                    '局数',
                    '有效下注总金额',
                    '下注总金额',
                    '总派彩金额',
                    '收益率',
                ];
                $sub_title = '查询桌次';
                break;

        }
        //收益率
//        $field['round_no'] = 1;

        //过滤
        $match = [
            'is_cancel'=>0
        ];
        //查询厅
        if(isset($game_hall_id) && $game_hall_id !== '') {
            $match['game_hall_id'] = $game_hall_id;
        }
        //查询游戏
        if(isset($game_id) && !empty($game_id)) {
            $match['game_id'] = $game_id;
        }


        if(isset($hall_id) && !empty($hall_id)) {
            $match['hall_id'] = $hall_id;
        }

        if(isset($agent_id) && !empty($agent_id)) {
            $match['agent_id'] = $agent_id;
        }

        if(isset($table_no) && !empty($table_no)) {
            $match['table_no'] = $table_no;
        }

        if(isset($start_time) && !empty($start_time)) {
            $s_time = new \MongoDB\BSON\UTCDateTime(strtotime($start_time)* 1000);
            $match['start_time']['$gte'] = $s_time;
        }

        if(isset($end_time) && !empty($end_time)) {
            $e_time = new \MongoDB\BSON\UTCDateTime(strtotime($end_time)* 1000);
            $match['start_time']['$lt'] = $e_time;
        }

        $db = new UserChartInfo;
        $data = $db->raw(function($collection) use($group, $match, $field) {
            return $collection->aggregate([
                [
                    '$match' => $match,
                ],


                [
                    '$group' =>
                        [
                            '_id' => $group,
                            'table_no' => ['$first' =>'$table_no'],
                            'round_no' => ['$first' =>'$round_no'],
                            'agent_id' => ['$first' =>'$agent_id'],
                            'agent_name' => ['$first' =>'$agent_name'],
                            'hall_id' => ['$first' =>'$hall_id'],
                            'hall_name' => ['$first' =>'$hall_name'],
                            'game_hall_id' => ['$first' =>'$game_hall_id'],
                            'game_id' => ['$first' =>'$game_id'],
                            'game_name' => ['$first' =>'$game_name'],
                            'game_hall_code' => ['$first' =>'$game_hall_code'],
                            'game_round_num' => ['$sum'  => 1],
                            'valid_bet_score_total' => ['$sum'  => '$valid_bet_score_total'],
                            'total_bet_score' => ['$sum'  => '$total_bet_score'],
                            'total_win_score' => ['$sum'  => '$total_win_score'],
                        ],
                ],

//                    ['$sort' => ['game_hall_id'=>1, 'game_id' => 1]],

                [
                    '$project' =>$field,
                ],

                /*[
                    '$skip' => 0
                ],
                [
                    '$limit' => 1
                ],*/

            ]);
        });

        $totals = [
            'game_round_num' => 0,
            'valid_bet_score_total' => 0,
            'total_bet_score' => 0,
            'total_win_score' => 0,
            'win_rate' => 0,
        ];

        if($data = $data->toArray()) {


            foreach ($data as &$v) {

                $v['total_win_score'] = ($v['valid_bet_score_total'] - $v['total_win_score']);//盈利
                $totals['game_round_num'] += $v['game_round_num'];
                $totals['valid_bet_score_total'] += $v['valid_bet_score_total'];
                $totals['total_bet_score'] += $v['total_bet_score'];
                $totals['total_win_score'] += $v['total_win_score'];
                $win_rate = $v['total_win_score'] == 0 ? 0 : ($v['total_win_score']/$v['valid_bet_score_total']);
                $v['win_rate'] = sprintf("%01.2f", $win_rate*100).'%';
            }
            unset($v);
            $win_rate = $totals['total_win_score'] == 0 ? 0 : ($totals['total_win_score']/$totals['valid_bet_score_total']);
            $totals['win_rate'] = sprintf("%01.2f", $win_rate*100).'%';
        }

        if( $is_export ) {

            array_unshift($data, $title);
            $num = count($title)-count($totals);
            $total_arr = array_merge(array_fill(0, $num, ""), $totals);
            $total_arr[0] = 'Total';
            array_push($data, $total_arr);

            $header = '开始日期：'.($start_time ? $start_time : '无限制，').' 结束日期：'.($end_time ? $end_time : '无限制');
            $filename = $sub_title.'_'.date('Ymd',time());

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
                    'totals' => $totals,
                ],
            ]);
        }
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

    /**
     * 设置游戏缓存
     */
    public function setGameCahe() {

        $agentGameInfo = DB::table('agent_game')->join('game_info','agent_game.game_id','=','game_info.id')->select(['agent_game.game_id','agent_game.hall_id','agent_game.agent_id'])->where('agent_game.status',1)->where('game_info.status',1)->orderby('agent_game.agent_id')->get();

        $redis = Redis::connection("default");

        if (count($agentGameInfo)) {
            $data = [];
            foreach (StringShiftToInt($agentGameInfo, ['game_id','hall_id','agent_id']) as $item) {
                $item = (array)$item;
                $agent_id = $item['agent_id'];
                unset( $item['agent_id']);
                $data[$agent_id][] = json_encode($item);
            }

            foreach ($data as $key => $item2) {
                $keyName = 'agent_game:'. (int)$key;
                $redis->del($keyName);
                $item2 && $redis->rpush($keyName, $item2);
            }

        } else {
            $redis->del($redis->keys('agent_game:*'));
        }

    }
}

