<?php
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use  App\Models\RedPackets;
use  App\Models\RedPacketsLog;
use  Illuminate\Support\Facades\DB;

class RedPacketsController extends BaseController
{
    /**
     * @api {get} /redPackets   查看红包活动列表
     * @apiDescription 查看红包活动列表
     * @apiGroup redPackets
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {String} action_name 操作类型
     * @apiParam {String} action_passivity 被操作对象
     * @apiParam {Date} start_date 开始时间
     * @apiParam {Date} end_date 结束时间
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 8,
    "per_page": 50,
    "current_page": 1,
    "last_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 8,
    "data": [
    {
    "id": 1, //活动ID
    "title": "一言不合就发红包", // 标题
    "trigger": 1, // 红包触发类型,0为大厅（红包雨），1为下注时（普通红包）
    "type": 1, // 红包类型，0为红包雨，1为普通红包，默认为红包雨
    "user_max": 1, // 单个会员最大能在该活动抢到的红包数
    "total_amount": 100, // 红包的金额（只能为整数金额）
    "get_amount": 99,// 已经领取的金额
    "total_number": 100,// 总的红包个数
    "get_number": 99,// 已经领取的红包个数
    "total_user": 99,// 领取过该红包的会员数
    "start_date": "2017-10-16 10:43:31",// 红包活动开始时间
    "end_date": "2017-10-17 10:43:36",// 红包活动结束时间
    "status": 1,// 红包状态，0为已结束，1为正常，默认为1
    "available": 1// 1 可以修改删除  0 不可以修改删除
    }
    ],
    "total_page_score": { // 小计
    "total_amount": 310,// 领取金额
    "total_number": 17,// 领取个数
    "total_user": 1 //领取人数
    },
    "total_score": {// 总计
    "total_amount": "310.00",
    "total_number": "17",
    "total_user": "1"
    }
    }
    }
     */
    public function index(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $page_num = (int)$request->input('page_num',10);
        $is_page = $request->input('is_page', 1);

        $db = RedPackets::select();
        $sql = 'select sum(get_amount) as total_amount ,sum(get_number) as total_number ,sum(total_user) as total_user  from activity_red_packets ';
        if(!empty($start_date)) {
            $db->where('start_date','>=',$start_date);
            $sql .= " where `start_date` >=   '{$start_date}' ";
        }

        if(!empty($end_date) && strtotime($start_date) < strtotime($end_date))
        {
            $db->where('end_date','<=',$end_date);
            $sql .= " and  `end_date` <=  '{$end_date} '";
        }


        $db->orderBy('id','desc');
        if(!$is_page) {
            $data['data'] = $db->get()->toArray();
        } else {
            $data = $db->paginate($page_num)->toArray();
        }

        if( empty($data['data']) )
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        //判断的红包是否可以删除和编辑

        foreach ($data['data'] as  $k => &$v){
            $now_time = date('Y-m-d H:i:s');
            $v['available'] = 1;
            if($v['start_date'] <= $now_time ) $v['available'] = 0;
        }

        $data['total_page_score'] = self::perCountScore($data['data']);
        $data['total_score'] = DB::select($sql)[0];
        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $data
        ]);
    }

    /**
     * @api {post} /redPackets           添加红包活动
     * @apiDescription                  添加红包活动
     * @apiGroup  redPackets
     * @apiPermission                   JWT
     * @apiVersion                      0.1.0
     * @apiHeader {String} Accept       http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token         认证token
     * @apiParam {String} locale        语言
     * @apiParam {String}  title        标题 ,
     * @apiParam {Number}  type         红包触发类型,0为大厅（红包雨），1为下注时（普通红包）,
     * @apiParam {Number}  trigger      红包类型，0为红包雨，1为普通红包，默认为红包雨,
     * @apiParam {Number}  user_max     单个会员最大能在该活动抢到的红包数,
     * @apiParam {Number}  total_amount 红包的金额（只能为整数金额）,
     * @apiParam {Number}  total_number 总的红包个数,
     * @apiParam {Number}  requirements_type  抢红包的条件 1累计下注总额类型   2 当天下注总额,
     * @apiParam {Number}  requirements_amount 抢红包条件的额度 单位/元,
     * @apiParam {Number}  user_largest   大额用户的额度 单位/元,
     * @apiParam {Date}    start_date   红包活动开始时间,
     * @apiParam {Date}    end_date     红包活动结束时间,
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function store(Request $request)
    {
        $attributes = $request->input();
        $now_date = date('Y-m-d H:i:s');
        $msg = [
            'start_date.required' => trans('redpacket.start_date.required'),
            'start_date.date_format' => trans('redpacket.start_date.date_format'),
            'start_date.after' => trans('redpacket.start_date.after'),
            'end_date.required' => trans('redpacket.end_date.required'),
            'end_date.date_format' => trans('redpacket.end_date.date_format'),
            'end_date.after' => trans('redpacket.end_date.after'),
            'title.required' => trans('redpacket.title.required'),
            'type.required' => trans('redpacket.type.required'),
            'trigger.required' => trans('redpacket.trigger.required'),
            'user_max.required' => trans('redpacket.user_max.required'),
            'total_amount.required' => trans('redpacket.total_amount.required'),
            'total_number.required' => trans('redpacket.total_number.required'),
            'requirements_type.required' => trans('redpacket.requirements_type.required'),
            'user_largest.required' => trans('redpacket.user_largest.required'),
            'requirements_amount.required' => trans('redpacket.requirements_amount.required'),
        ];

        $validator = \Validator::make($attributes, [
            'title' =>  'required|string|min:1|max:255',
            'type' => 'required|numeric|min:0',
            'trigger' => 'required|numeric|min:0',
            'user_max' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'total_number' => 'required|numeric|min:0',
            'requirements_type' => 'required|numeric|min:0',
            'user_largest' => 'required|numeric|min:0',
            'requirements_amount' => 'required|numeric|min:0',
            'start_date' => ['required', 'date_format:Y-m-d H:i:s', 'after:'.$now_date],
            'end_date' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_date'],
        ],$msg);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        // 查询活动时间是否有交叉
        $sql = "select * from `activity_red_packets` where ('{$attributes['start_date']}' between `start_date` AND `end_date`) OR ('{$attributes['end_date']}' between `start_date` AND `end_date`)";
        if(  count(DB::select($sql)) ){
            return $this->response->array([
                'code' => 400,
                'text' =>trans('redpacket.time_not_coss'),
                'result' => '',
            ]);
        }



        $date = date('Y-m-d H:i:s');
        $insertData = [
            'title' => $attributes['title'],
            'type' => $attributes['type'],
            'trigger' => $attributes['trigger'],
            'user_max' => $attributes['user_max'],
            'total_amount' => $attributes['total_amount'],
            'total_number' => $attributes['total_number'],
            'requirements_amount' => $attributes['requirements_amount'],
            'requirements_type' => $attributes['requirements_type'],
            'user_largest' => $attributes['user_largest'],
            'end_date' => $attributes['end_date'],
            'start_date' => $attributes['start_date'],
            'create_date' => $date,
            'last_date' => $date,
        ];

        if(! RedPackets::create($insertData)->id){
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.fails'),
                'result' => '',
            ]);
        }

        // 发送消息到游戏端更新数据
        $msg = json_encode(['cmd'=>'RedPacketChange']);
        if(! RabbitmqController::publishMsgToExchange([env('MQ_SERVER_CHANNEL'), $msg])){
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.fails'),
                'result' => '',
            ]);
        }

        @addLog(['action_name'=>'添加红包活动','action_desc'=>\Auth::user()->user_name.'添加了一个新添加红包活动，新添加红包活动标题： '.$attributes['title'],'action_passivity'=>'红包活列表']);

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => '',
        ]);

    }

    /**
     * @api {get} /redPackets/show/{id} 查看编辑的单个红包数据
     * @apiDescription 查看编辑的单个红包数据
     * @apiGroup redPackets
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "id": 10,
    "title": "我要发红包",
    "trigger": 1,
    "type": 1,
    "user_max": 1,
    "total_amount": 100,
    "get_amount": 0,
    "total_number": 100,
    "get_number": 100,
    "total_user": 0,
    "start_date": "0000-00-00 00:00:00",
    "end_date": "0000-00-00 00:00:00",
    "status": 1
    }
    }
     */
    public function show(Request $request,int $packet_id)
    {
        if( !$res = RedPackets::find($packet_id)) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);

        }

        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $res
        ]);
    }


    /**
     * @api {put} /redPackets/{packet_id}  编辑红包活动
     * @apiDescription                  编辑红包活动
     * @apiGroup                         redPackets
     * @apiPermission                   JWT
     * @apiVersion                      0.1.0
     * @apiHeader {String} Accept       http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token         认证token
     * @apiParam {String} locale        语言
     * @apiParam {String}  title        标题 ,
     * @apiParam {Number}  type         红包触发类型,0为大厅（红包雨），1为下注时（普通红包）,
     * @apiParam {Number}  trigger      红包类型，0为红包雨，1为普通红包，默认为红包雨,
     * @apiParam {Number}  user_max     单个会员最大能在该活动抢到的红包数,
     * @apiParam {Number}  total_amount 红包的金额（只能为整数金额）,
     * @apiParam {Number}  total_number 总的红包个数,
     * @apiParam {Number}  requirements_type  抢红包的条件 1累计下注总额类型   2 当天下注总额,
     * @apiParam {Number}  requirements_amount 抢红包条件的额度 单位/元,
     * @apiParam {Number}  user_largest   大额用户的额度 单位/元,
     * @apiParam {Date}    start_date   红包活动开始时间,
     * @apiParam {Date}    end_date     红包活动结束时间,
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function updated(Request $request,int $packet_id)
    {

        if( !$res = RedPackets::find($packet_id)) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);

        }

        // 判断活动是否开始 不能编辑
        if($res['start_date']  <= date('Y-m-d H:i:s')){
            return $this->response->array([
                'code' => 400,
                'text' => trans('redpacket.data_not_operate'),
                'result' => '',
            ]);
        }

        $attributes = $request->input();
        $now_date = date('Y-m-d H:i:s');
        $msg = [
            'start_date.required' => trans('redpacket.start_date.required'),
            'start_date.date_format' => trans('redpacket.start_date.date_format'),
            'start_date.after' => trans('redpacket.start_date.after'),
            'end_date.required' => trans('redpacket.end_date.required'),
            'end_date.date_format' => trans('redpacket.end_date.date_format'),
            'end_date.after' => trans('redpacket.end_date.after'),
            'title.required' => trans('redpacket.title.required'),
            'type.required' => trans('redpacket.type.required'),
            'trigger.required' => trans('redpacket.trigger.required'),
            'user_max.required' => trans('redpacket.user_max.required'),
            'total_amount.required' => trans('redpacket.total_amount.required'),
            'total_number.required' => trans('redpacket.total_number.required'),
            'requirements_type.required' => trans('redpacket.requirements_type.required'),
            'user_largest.required' => trans('redpacket.user_largest.required'),
            'requirements_amount.required' => trans('redpacket.requirements_amount.required'),
        ];
        $validator = \Validator::make($attributes, [
            'title' =>  'required|string|min:1|max:255',
            'type' => 'required|numeric|min:0',
            'trigger' => 'required|numeric|min:0',
            'user_max' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'total_number' => 'required|numeric|min:0',
            'requirements_type' => 'required|numeric|min:0',
            'user_largest' => 'required|numeric|min:0',
            'requirements_amount' => 'required|numeric|min:0',
            'start_date' => ['required', 'date_format:Y-m-d H:i:s', 'after:'.$now_date],
            'end_date' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_date'],
        ],$msg);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        // 查询活动时间是否有交叉
        $sql = "select * from `activity_red_packets` where id <> {$packet_id}  AND (('{$attributes['start_date']}' between `start_date` AND `end_date`) OR ('{$attributes['end_date']}' between `start_date` AND `end_date`)) ";
        if(  count(DB::select($sql)) ){
            return $this->response->array([
                'code' => 400,
                'text' =>trans('redpacket.time_not_coss'),
                'result' => '',
            ]);
        }



        $date = date('Y-m-d H:i:s');
        $updateData = [
            'title' => $attributes['title'],
            'type' => $attributes['type'],
            'trigger' => $attributes['trigger'],
            'user_max' => $attributes['user_max'],
            'total_amount' => $attributes['total_amount'],
            'total_number' => $attributes['total_number'],
            'requirements_amount' => $attributes['requirements_amount'],
            'requirements_type' => $attributes['requirements_type'],
            'user_largest' => $attributes['user_largest'],
            'create_date' =>  $attributes['create_date'],
            'start_date' =>  $attributes['start_date'],
            'end_date' => $attributes['end_date'],
            'last_date' => $date,
        ];

        if(! RedPackets::where('id', $packet_id)->update($updateData)){
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.fails'),
                'result' => '',
            ]);
        }

        // 发送消息到游戏端更新数据
        $msg = json_encode(['cmd'=>'RedPacketChange']);
        if(! RabbitmqController::publishMsgToExchange([env('MQ_SERVER_CHANNEL'), $msg])){
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.fails'),
                'result' => '',
            ]);
        }

        @addLog(['action_name'=>'编辑红包活动','action_desc'=> \Auth::user()->user_name.' 对红包活动标题： '.$attributes['title'].'进行了编辑','action_passivity'=>'红包活列表']);

        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => '',
        ]);

    }

    /**
     * @api {delete} redPackets/{packet_id} 删除红包活动
     * @apiDescription 红包活动 删除
     * @apiGroup redPackets
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function delete(Request $request, int $packet_id)
    {
        $data = RedPackets::find($packet_id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        // 判断活动是否开始 不能删除
        if($data['start_date']  <= date('Y-m-d H:i:s')){
            return $this->response->array([
                'code' => 400,
                'text' => trans('redpacket.data_not_operate'),
                'result' => '',
            ]);
        }



        if( ! RedPackets::destroy($packet_id) ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.fails'),
                'result' => '',
            ]);
        }
        @addLog(['action_name'=>'删除红包活动','action_desc'=> \Auth::user()->user_name.'对删除红包活动进行了删除，ID为：'.$packet_id,'action_passivity'=>'红包活动列表']);

        // 发送消息到游戏端更新数据
        $msg = json_encode(['cmd'=>'RedPacketChange']);
        if(! RabbitmqController::publishMsgToExchange(['MQ_SERVER_CHANNEL', $msg])){
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.fails'),
                'result' => '',
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => '',
        ]);
    }

    /**
     * @api {get} redPackets/showDetail/{id} 查看单个红包活动领取详情
     * @apiDescription 查看单个红包活动领取详情
     * @apiGroup redPackets
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "data": [
    {
    "packets_amount": 1,// 红包金额
    "get_number": 1,//红包数量
    "hall_name": "hall_name_3"//厅主登录名
    },
    {
    "packets_amount": 1,
    "get_number": 1,
    "hall_name": "hall_name_2"
    },
    {
    "packets_amount": 11,
    "get_number": 3,
    "hall_name": "hall_name"
    }
    ]
    }
    }
     */
    public function showDetail(Request $request, int $packet_id)
    {
        $data = RedPackets::find($packet_id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }
        $page = $request->input('page',1);
        $page_num = $request->input('page_num',10);
        $skip = (int) ($page-1) * $page_num;
        $limit = (int) $page_num;

         // 查询单个红包详情
        $match['$match']['packets_id'] =  $packet_id;
        $project = ['$project'=> [ 'hall_name'=> 1,  'packets_amount'=>1, 'get_number'=>1,]];
        $sort = ['$sort'=> ['packets_amount'=>1] ];
        $group = ['$group'=> [
            '_id' => ['hall_id'=>'$hall_id'],
            'packets_amount' => ['$sum'=>'$packets_amount'],
            'get_number'=> ['$sum' => '$get_number'],
            'hall_name'=> ['$first' => '$hall_name'],
            ] ];

        $aggregate = [$match,$group, $sort, $project];
        $total_data = RedPacketsLog::raw(function($collection) use($aggregate) {
            return $collection->aggregate($aggregate);
        })->toArray();

        $data = $count_data = array_slice($total_data,$skip,$limit);

        if(empty($total_data) || empty($data)) {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'total' => count($total_data),
                'per_page' => $page_num,
                'current_page' => $page,
                'data' => $data,
                'total_score' => self::getCountScore($total_data),
            ],
        ]);
    }

    /**
     * 小计 计算
     * @param $data
     * @return array
     */
    private static function getCountScore($data)
    {
        $total = ['get_amount_total' => 0, 'get_number_total' => 0,];
        if( !empty($data) ) {
            foreach ($data as $k => $v) {
                $total['get_amount_total'] += $v['packets_amount'];
                $total['get_number_total'] += $v['get_number'];
            }
        }

        return $total;
    }

    /**
     * 红包数小计
     */
    private function perCountScore($data)
    {
        $total = ['total_amount' => 0, 'total_number' => 0, 'total_user'=>0];
        if(!empty($data) ) {
            foreach ($data as $k => $v) {
                $total['total_amount'] += $v['total_amount'];
                $total['total_number'] += $v['total_number'];
                $total['total_user'] += $v['total_user'];
            }
        }
        return $total;
    }
}