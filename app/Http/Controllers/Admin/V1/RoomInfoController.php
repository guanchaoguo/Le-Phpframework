<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/8
 * Time: 17:38
 */
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\RoomGameType;
use App\Models\RoomInfo;
use App\Models\RoomRules;
use App\Models\RoomDefaultOdds;
use Illuminate\Support\Facades\DB;
class RoomInfoController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {get} /room 十三水房间管理列表
     * @apiDescription 十三水房间管理列表
     * @apiGroup room
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} type_id 游戏分类
     * @apiParam {Number} status 房间是否可用,1为可用,0为不可用
     * @apiParam {Number} page 当前页 默认1
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *{
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
    "id": 157,  // 游戏种类
    "cat_id": 4, // 游戏种类
    "room_name": "十三水--新手场",// 房间名称
    "bottom_score": 500,//房间底分 最低输掉的金额
    "sort_id": 0,//排序字段
    "status": 1,//房间是否可用,1为可用,0为不可用，2已删除
    "is_recommand": 0,//是否推荐,0为不推荐,1为推荐
    "type_id": 1,// 十三水游戏种类
    "thirteen_poker_room": {
    "type_name": "基础十三水"
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
    "result": {
    "data": [
    {
    "id": 157, // 游戏种类
    "cat_id": 4,// 游戏种类
    "room_name": "十三水--新手场",// 房间名称
    "bottom_score": 500,//房间底分 最低输掉的金额
    "sort_id": 0,//排序字段
    "status": 1,//房间是否可用,1为可用,0为不可用，2已删除
    "is_recommand": 0,//是否推荐,0为不推荐,1为推荐
    "type_id": 1,// 十三水游戏种类
    "thirteen_poker_room": {
    "type_name": "基础十三水"
    }
    }
    ]
    }
    }
     */
    public function index(Request $request)
    {
        $type_id = $request->input('type_id');
        $status = $request->input('status');
        $page_num = $request->input('page_num',10);
        $is_page = $request->input('is_page', 1);

         // 关联数据游戏种类 统计信息
         $db =  RoomInfo::leftJoin('room_game_type', function ($join){
            $join->on("room_game_type.id", "=", "room_info.type_id")
                ->select('room_game_type.type_name');
        })->leftJoin('room_statistics', function ($join){
            $join->on("room_statistics.room_id", "=", "room_info.id")->where('date_time',date('Y-m-d'))
                ->select('room_statistics.total_lose_money, room_statistics.total_winning_money');
        });

//         echo $db->tosql();die;
        $field = [
            'room_info.id',
            'room_info.room_name',
            'room_info.bottom_score',
            'room_info.status',
            'room_info.max_limit',
            'room_game_type.type_name',
            'room_statistics.total_lose_money',
            'room_statistics.total_winning_money',
        ];

        if(isset($type_id) && $type_id !=='') {
            $db->where('type_id',$type_id);
        }

        if(isset($status) && $status !== '') {

            $db->where('status',$status);
        }
        $db->where('status', '<>', 2)->orderby('id','desc');

        if(!$is_page) {
            $rooms = $db->get($field);
        } else {
            $rooms = $db->select($field)->paginate($page_num);
        }

        return $this->response->array([
            'code' => 0,
            'text' =>trans('room.success'),
            'result' => !$is_page? ['data' => $rooms]:$rooms,
        ]);
    }

    /**
     * @api {get} /room/cat 十三水游戏分类
     * @apiDescription 十三水游戏分类
     * @apiGroup room
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
    "id": 1, //十三水游戏分类Id
    "cat_id": 4,
    "type_name": "基础十三水",//十三水游戏分类名称
    "sort_id": 100//排序字段
    }
    ]
    }
    }
     */
    public function cat()
    {
        $cates = RoomGameType::orderby('sort_id','asc')->get();

        return $this->response->array([
            'code' => 0,
            'text' => trans('room.success'),
            'result' => [
                'data' => $cates,
            ],
        ]);
    }

    /**
     * @api {post} /room 十三水房间添加
     * @apiDescription 添加房间
     * @apiGroup room
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} room_name 游戏名称
     * @apiParam {Number} type_id 游戏种类
     * @apiParam {Number} bottom_score 房间底分
     * @apiParam {Number} max_limit 房间限制
     * @apiParam {Number} status 房间是否可用,1为可用,0为不可用
     * @apiParam {String} icon 游戏图标
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
            'type_id' => 'required',
            'room_name' => 'required|unique:room_info',
            'bottom_score' => 'required',
            'max_limit' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        $attributes = $request->except('token','locale');

        DB::beginTransaction();
        $insertData = [
            'type_id' => $attributes['type_id'],
            'room_name' => $attributes['room_name'],
            'bottom_score' => $attributes['bottom_score'],
            'max_limit' => $attributes['max_limit'],
            'status' => $attributes['max_limit'],
        ];
        if($id = RoomInfo::create($insertData)->id){
            // 查询默认赔率
            $defaultOdd = $this->getDefaultOdd();
            $oddsData = [];
            foreach ($defaultOdd as $Odd){
                $oddsData[] =  [
                    'room_id' => $id,
                    'room_name' => $attributes['room_name'],
                    'card_type' => $Odd['card_type'],
                    'play_name_type' => $Odd['play_name_type'],
                    'play_rules_odds' => $Odd['play_rules_odds'],
                ];
            }

            // 初始化该房间默认赔率
            if(!RoomRules::insert($oddsData )){
                DB::rollBack();
                return $this->response->array([
                    'code' => 400,
                    'text' =>trans('room.fails'),
                    'result' => '',
                ]);
            }

            DB::commit();
            @addLog(['action_name'=>'添加十三水房间','action_desc'=>' 添加了一个新房间，新游戏为： '.$attributes['room_name'],'action_passivity'=>'十三水房间列表']);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('room.success'),
                'result' => '',
            ]);
        } else {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.fails'),
                'result' => '',
            ]);
        }

    }


    private  function getDefaultOdd():array
    {
        $feild = [
            'id as default_id',
            'card_type',
            'play_name_type',
            'play_rules_odds',
            'play_odds_name',
        ];
        return RoomDefaultOdds::get($feild)->toArray();
    }

    /**
     * @api {put} /room/status 编辑十三水房间状态
     * @apiDescription 编辑十三水房间状态
     * @apiGroup room
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} room_id 房间ID
     * @apiParam {Number} status 游戏是否可用,1为可用,0为不可用
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
    public function updateStatus(Request $request)
    {

        if(!$rommId = $request->input('room_id')){
            return $this->response->array([
                'code'=>400,
                'text'=> trans('room.param_error'),
                'result'=>'',
            ]);
        }
        $room = RoomInfo::find($rommId);
        if(!$room) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.room_not_exist'),
                'result' => '',
            ]);
        }

        $validator = \Validator::make($request->input(), [
            'status' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        $attributes = $request->except('token','locale','room_id');

        $re = $room->where('id', $rommId)->update($attributes);


        $statusName = $attributes['status'] ==1 ? '启动':'禁用';
        if($re !== false) {
            @addLog(['action_name'=>'编辑十三水房间状态','action_desc'=>'房间名： '.$room['room_name'].'状态改为'.$statusName, 'action_passivity'=>'十三水房间列表']);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('room.save_success'),
                'result' => '',
            ]);

        } else {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.save_fails'),
                'result' => '',
            ]);

        }

    }


    /**
     * @api {put} /room/rules 十三水房间赔率方案修改
     * @apiDescription 十三水房间赔率方案修改
     * @apiGroup room
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} rule_id  赔率Id
     * @apiParam {Number} card_type 是否为特殊牌型 1 特殊 2 默认为普通
     * @apiParam {Number} play_name_type 牌型类型 牌型类型 1-13 为特殊 14-22 为普通  1 至尊青龙:,2 一条龙:,3 十二皇族:,4 三同花顺:, 5 三分天下:,6 全大:, 7 全小:, 8 凑一色:, 9 四套三条:, 10 五对三条:,11 六对半:, 12 三顺子:,13 三同花:,14 赢一水:,15 输一水:, 16 和:, 17 冲三:,18 尾墩同花顺:, 19 中墩铁支:, 20 中墩葫芦:, 21 尾墩同花顺:, 22 尾墩铁支:
        data:
        {
            {
                rule_id:1,//十三水房间赔率规则方案Id
                play_name_type:1, //牌型类型 牌型类型 1-13 为特殊 14-22 为普通
                play_rules_odds:1,//牌型赔率
            }
            {
                rule_id:1,
                play_name_type:1,
                play_rules_odds:1,
            }
            }
        }
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
    public function updatepRules(Request $request)
    {
        $attributes = $request->except('token','locale');
        foreach ($attributes['data'] as $attr){
            $validator = \Validator::make($attr, [
                'rule_id' => 'required|integer|max:255',
                'play_name_type' => 'required|integer|max:255',
                'play_rules_odds' => 'required|integer|max:255',
            ]);

            $updateData[] = [
                'rule_id'=>$attr['rule_id'],
                'play_rules_odds'=>$attr['play_rules_odds'],
            ];
        }

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        $sratrIds= array_column($updateData,'rule_id');
        $room = RoomRules::whereIn('id', $sratrIds )->get()->toArray();
        if(count($room) !== count($sratrIds) ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.room_not_exist'),
                'result' => '',
            ]);
        }
        
        $ids = implode(',', $sratrIds);
        $sql = "UPDATE `room_play_rules` SET play_rules_odds = CASE id ";
        foreach ($updateData as  $attr) {
            $sql .= sprintf("WHEN %d THEN %d ", $attr['rule_id'], $attr['play_rules_odds']);
        }
        $sql .= "END WHERE id IN ($ids)";

        if(DB::update(DB::raw($sql))) {
            @addLog([
                'action_name'=>'十三水房间赔率方案修改',
                'action_desc'=>'房间Id ： '.$room['room_name'],
                'action_passivity'=>'十三水房间列表'
            ]);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('room.save_success'),
                'result' => '',
            ]);

        } else {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.update_fails'),
                'result' => '',
            ]);

        }

    }


    /**
     * @api {put} /room/odds 调整十三水房间盈利率
     * @apiDescription 调整十三水房间盈利率
     * @apiGroup room
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} room_id 房间ID
     * @apiParam {Number} min_odds 调整盈利率下限
     * @apiParam {Number} max_odds 调整盈利率上限
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
    public function updateOdds(Request $request)
    {

        if(!$rommId = $request->input('room_id')){
            return $this->response->array([
                'code'=>400,
                'text'=> trans('room.param_error'),
                'result'=>'',
            ]);
        }
        $room = RoomInfo::find($rommId);
        if(!$room) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.room_not_exist'),
                'result' => '',
            ]);
        }

        $validator = \Validator::make($request->input(), [
            'min_odds' => 'required|integer|max:255',
            'max_odds' => 'required|integer|max:255',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        $attributes = $request->except('token','locale','room_id');

        if($attributes['max_odds'] <=  $attributes['min_odds']){
            return $this->response->array([
                'code'=>400,
                'text'=>trans('room.min_max_error'),
                'result'=>'',
            ]);
        }

        $re = $room->where('id', $rommId)->update($attributes);


        if($re !== false) {
            @addLog([
                'action_name'=>'调整十三水房间盈利率',
                'action_desc'=>'房间名： '.$room['room_name'].'上限：'.$attributes['max_odds'].'下限：'.$attributes['min_odds'],
                'action_passivity'=>'十三水房间列表'
            ]);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('room.save_success'),
                'result' => '',
            ]);

        } else {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.save_fails'),
                'result' => '',
            ]);

        }

    }

    /**
     * @api {get} /room/status/show/{room_id} 电子游戏十三水状态显示
     * @apiDescription 电子游戏十三水状态显示
     * @apiGroup room
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
    "id": 1,// 房间Id
    "room_name": "十三水--新手场",// 房间名称
    "status": 1 //房间状态
    }
    }
    }
     *@apiErrorExample {json} Error-Response:
    {
    "code": 400,
    "text": "房间不存在",
    "result": ""
    }
     */
    public function showSatus(Request $request,$id)
    {
        $room = RoomInfo::find($id,['id', 'room_name' ,'status']);

        if(!$room) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('room.room_not_exist'),
                'result' => '',
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'data' => $room,
            ],
        ]);
    }

    /**
     * @api {get} /room/rules/show/{room_id} 十三水电子游戏房间赔率方案显示
     * @apiDescription 十三水电子游戏房间赔率方案显示
     * @apiGroup room
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
        "data": [
            {
                "rule_id": 1, //赔率Id
                "cat_id": 4,  // 游戏种类
                "room_id": 1, // 房间Id
                "room_name": "十三水—新手场", //房间名称
                "card_type": 1, // 赔率是否为特殊 1 特殊 2 普通
                "play_name_type": "1", //赔率类型名称
                "play_rules_odds": 68  //赔率
                },
                {
                "rule_id": 2,
                "cat_id": 4,
                "room_id": 1,
                "room_name": "十三水—新手场",
                "card_type": 1,
                "play_name_type": "1",
                "play_rules_odds": 99
            }
        ]
        }
    }
     *@apiErrorExample {json} Error-Response:
    {
    "code": 400,
    "text": "房间不存在",
    "result": ""
    }
     */
    public function showRules(Request $request,$id)
    {
        $roomRules = RoomRules::where('room_id',$id)
            ->get(['id as rule_id','room_id','room_name','card_type','play_name_type','play_rules_odds'])
            ->toArray();

        if(!$roomRules) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('room.room_not_exist'),
                'result' => '',
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('room.success'),
            'result' => [
                'data' => $roomRules,
            ],
        ]);
    }


    /**
     * @api {get} /room/odds/show/{room_id} 电子游戏十三水房间盈利率显示
     * @apiDescription 电子游戏房间盈利率修改显示
     * @apiGroup room
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
                "id": 1, //房间Id
                "min_odds": 35, //盈利率下限
                "max_odds": 45,//盈利率上限
                "room_name": "十三水--新手场"//房间名称
            }
        }
    }
     *@apiErrorExample {json} Error-Response:
    {
    "code": 400,
    "text": "房间不存在",
    "result": ""
    }
     */
    public function showOdds(Request $request,$id)
    {
        $room = RoomInfo::find($id,['id', 'min_odds','max_odds','room_name']);

        if(!$room) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('room.room_not_exist'),
                'result' => '',
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('room.success'),
            'result' => [
                'data' => $room,
            ],
        ]);
    }

    /**
     * @api {get} /room/defalutOdds 十三水游戏默认赔率方案显示
     * @apiDescription 十三水游戏分类
     * @apiGroup room
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
    "card_type": 1,//牌型分为  普通 1 特殊 2 默认为普通
    "play_name_type": "1",//牌型类型 1-13 为特殊 14-22 为普通
    "play_rules_odds": 108,//牌型赔率
    "play_odds_name": "至尊青龙"//牌型赔率名称
    },
    {
    "card_type": 1,
    "play_name_type": "2",
    "play_rules_odds": 36,
    "play_odds_name": "一条龙"
    },
    {
    "card_type": 1,
    "play_name_type": "3",
    "play_rules_odds": 24,
    "play_odds_name": "十二皇族"
    },
    {
    "card_type": 1,
    "play_name_type": "4",
    "play_rules_odds": 20,
    "play_odds_name": "三同花顺"
    },
    {
    "card_type": 1,
    "play_name_type": "5",
    "play_rules_odds": 20,
    "play_odds_name": "三分天下"
    },
    {
    "card_type": 1,
    "play_name_type": "6",
    "play_rules_odds": 10,
    "play_odds_name": "全大"
    },
    {
    "card_type": 1,
    "play_name_type": "7",
    "play_rules_odds": 10,
    "play_odds_name": "全小"
    },
    {
    "card_type": 1,
    "play_name_type": "8",
    "play_rules_odds": 10,
    "play_odds_name": "凑一色"
    },
    {
    "card_type": 2,
    "play_name_type": "9",
    "play_rules_odds": 6,
    "play_odds_name": "四套三条"
    },
    {
    "card_type": 2,
    "play_name_type": "10",
    "play_rules_odds": 5,
    "play_odds_name": "五对三条"
    },
    {
    "card_type": 2,
    "play_name_type": "11",
    "play_rules_odds": 4,
    "play_odds_name": "六对半"
    },
    {
    "card_type": 2,
    "play_name_type": "12",
    "play_rules_odds": 4,
    "play_odds_name": "三顺子"
    },
    {
    "card_type": 2,
    "play_name_type": "13",
    "play_rules_odds": 3,
    "play_odds_name": "三同花"
    },
    {
    "card_type": 2,
    "play_name_type": "14",
    "play_rules_odds": 1,
    "play_odds_name": "赢一水"
    },
    {
    "card_type": 2,
    "play_name_type": "15",
    "play_rules_odds": 3,
    "play_odds_name": "冲三"
    },
    {
    "card_type": 2,
    "play_name_type": "16",
    "play_rules_odds": 9,
    "play_odds_name": "中墩同花顺"
    },
    {
    "card_type": 2,
    "play_name_type": "17",
    "play_rules_odds": 5,
    "play_odds_name": "尾墩同花顺"
    },
    {
    "card_type": 2,
    "play_name_type": "18",
    "play_rules_odds": -1,
    "play_odds_name": "输一水"
    },
    {
    "card_type": 3,
    "play_name_type": "19",
    "play_rules_odds": 7,
    "play_odds_name": "中墩铁支"
    },
    {
    "card_type": 4,
    "play_name_type": "20",
    "play_rules_odds": 4,
    "play_odds_name": "尾墩铁支"
    },
    {
    "card_type": 5,
    "play_name_type": "21",
    "play_rules_odds": 0,
    "play_odds_name": "和"
    },
    {
    "card_type": 6,
    "play_name_type": "22",
    "play_rules_odds": 2,
    "play_odds_name": "中墩葫芦"
    }
    ]
    }
    }
     */
    public function getDefaultOdds()
    {
        return $this->response->array([
            'code' => 0,
            'text' => trans('room.success'),
            'result' => [
                'data' => $this->getDefaultOdd(),
            ],
        ]);
    }

    /**
     * @api {put} /room/defalutOdds 十三水房间默认赔率方案修改
     * @apiDescription 十三水房间房间默认赔率方案修改
     * @apiGroup room
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} default_id 默认赔率方案Id
     * @apiParam {Number} card_type 是否为特殊牌型 1 特殊 2 默认为普通
     * @apiParam {Number} play_name_type 牌型类型 牌型类型 1-13 为特殊 14-22 为普通  1 至尊青龙:,2 一条龙:,3 十二皇族:,4 三同花顺:, 5 三分天下:,6 全大:, 7 全小:, 8 凑一色:, 9 四套三条:, 10 五对三条:,11 六对半:, 12 三顺子:,13 三同花:,14 赢一水:,15 输一水:, 16 和:, 17 冲三:,18 尾墩同花顺:, 19 中墩铁支:, 20 中墩葫芦:, 21 尾墩同花顺:, 22 尾墩铁支:
     * @apiParam {Number} play_rules_odds 牌型赔率
    data:
    {
    {
    default_id:1,//十三水房间默认赔率方案Id
    play_name_type:1,//牌型类型 1-13 为特殊 14-22 为普通
    play_rules_odds:1,//牌型赔率
    }
    {
    default_id:1,
    play_name_type:1,
    play_rules_odds:1,
    }
    }
    }
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
    public function updatepDefaultOdds(Request $request)
    {
        $attributes = $request->except('token','locale');
        foreach ($attributes['data'] as $attr){
            $validator = \Validator::make($attr, [
                'default_id' => 'required|integer',
                'card_type' => 'required|integer|max:255',
                'play_name_type' => 'required|integer|max:255',
                'play_rules_odds' => 'required|integer|max:999',
            ]);

            $updateData[] = [
                'default_id'=>$attr['default_id'],
                'play_rules_odds'=>$attr['play_rules_odds'],
            ];
        }

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        $sratrIds= array_column($attributes['data'],'default_id');
        $room = RoomDefaultOdds::whereIn('id', $sratrIds )->get()->toArray();

        if(count($room) !== count($sratrIds) ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.room_not_exist'),
                'result' => '',
            ]);
        }

        $attributes = $request->except('token','locale');

        $ids = implode(',', $sratrIds);
        $sql = "UPDATE room_default_odds SET play_rules_odds = CASE id ";
        foreach ($updateData as  $attr) {
            $sql .= sprintf("WHEN %d THEN %d ", $attr['default_id'], $attr['play_rules_odds']);
        }
        $sql .= "END WHERE id IN ($ids)";
        
        if(DB::update(DB::raw($sql))) {
            @addLog([
                'action_name'=>'十三水房间默认赔率方案修改',
                'action_desc'=>'房间Id ： '.$room['room_name'],
                'action_passivity'=>'十三水房间列表'
            ]);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('room.save_success'),
                'result' => '',
            ]);

        } else {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('room.update_fails'),
                'result' => '',
            ]);

        }

    }


}