<?php
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use  App\Models\RedPacketsLog;

class AgentRedPacketsController extends BaseController
{
    /**
     * @api {get} /agentRedPackets 查看厅主单位时间内的红包的领取记录
     * @apiDescription 查看厅主单位时间内的红包的领取记录
     * @apiGroup agentRedPackets
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {Date} start_date 开始时间
     * @apiParam {Date} end_date 结束时间
     * @apiParam {string} hall_name 厅主登录名
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 3, // 数据总数
    "per_page": "50", // 每页数量
    "current_page": "1", //当前页
    "data": [
    {
    "packets_amount": 1,// 已获取红包金额
    "get_number": 1,// 以获取个数
    "hall_name": "hall_name_3",// 厅主登录名
    "user_number": 1// 已经领取会员数
    },
    {
    "packets_amount": 1,
    "get_number": 1,
    "hall_name": "hall_name_2",
    "user_number": 1
    },
    {
    "packets_amount": 11,
    "get_number": 3,
    "hall_name": "hall_name",
    "user_number": 2
    }
    ],
    "total_page_score": { // 总计
    "get_amount_total": 13, // 总计已领取金额
    "get_number_total": 13, // 总计领取红包个数
    "get_user_total": 4 // 总计领取会员数
    },
    "total_score": { 小计
    "get_amount_total": 13,//小计领取金额
    "get_number_total": 13,//小计领取红包个数
    "get_user_total": 4// 小计领取会员数
    }
    }
    }
     *
     */
    public function index(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $page = $request->input('page',1);
        $page_num = $request->input('page_num',10);
        $hall_name = $request->input('hall_name');
        $skip = (int) ($page-1) * $page_num;
        $limit = (int) $page_num;


        $project = ['$project'=> [ 'hall_name'=> 1,  'packets_amount'=> 1, 'get_number'=> 1, 'user_number'=> '$user_id']];
        $sort = ['$sort'=> ['packets_amount'=> -1 ] ]; //金额从高到低的排序 倒序
        $group = ['$group'=> [
            '_id' => ['user_id '=> '$user_id'],
            'packets_amount' => ['$sum'=>'$packets_amount'],
            'get_number'=> ['$sum' => '$get_number'],
            'hall_name'=> ['$first' => '$hall_name'],
            'user_id'=> ['$sum' => 1],
        ] ];


        $aggregate = [$group, $sort, $project];
        if(!empty($hall_name)){
            $match['$match']['hall_name'] = $hall_name;
            $aggregate = [$match ,$group, $sort, $project];
        }

        //时间验证
        if(!empty($start_date) || !empty($end_date)){
            if(!$this->checkDate(['start_date'=>$request->input('start_date'),'end_date'=>$request->input('end_date')])) {
                return $this->response()->array([
                    'code' => 400,
                    'text' => trans('maintain.end_date.end_lt'),
                    'result' => ''
                ]);
            }

            // 单位时间内厅主获取红包详情
            $startDate_utc = strtotime($start_date) * 1000;
            $endDate_utc = strtotime($end_date) * 1000;
            $match['$match']['create_date'] = ['$gte'=> new \MongoDB\BSON\UTCDateTime($startDate_utc), '$lte'=>new \MongoDB\BSON\UTCDateTime($endDate_utc)];
            $aggregate = [$match,$group, $sort, $project];
        }

        $total_data = RedPacketsLog::raw(function($collection) use($aggregate) {
            return $collection->aggregate($aggregate);
        })->toArray();
        
        if(empty($total_data)) {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('delivery.empty_list'),
                'result'        => ''
            ]);
        }

        // 按照代理商分组
        $result = array();
        foreach ($total_data as $key => $value)
        {
            if(isset($result[$value['hall_name']])){
                $result[$value['hall_name']]['packets_amount'] +=$value['packets_amount'];
                $result[$value['hall_name']]['get_number'] +=$value['get_number'];
                $result[$value['hall_name']]['user_number'] +=1;
                $result[$value['hall_name']]['hall_name'] = $value['hall_name'];
            }else{
                $result[$value['hall_name']] = $value;
                $result[$value['hall_name']]['user_number'] = 1;
            }
        }

        $result = array_values($result);
        $data = $count_data = array_slice($result,$skip,$limit);
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'total' => count($result),
                'per_page' => $page_num,
                'current_page' => $page,
                'data' => $data,
                'total_page_score' => self::getCountScore($data),
                'total_score' => self::getCountScore($result),
            ],
        ]);

    }


    /**
     * @api {get} /agentRedPackets/show 查看单个厅主时间段红包详情
     * @apiDescription 查看单个厅主时间段红包详情
     * @apiGroup agentRedPackets
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiParam {Date} start_date 开始时间
     * @apiParam {Date} end_date 结束时间
     * @apiParam {string} hall_name 厅主登录名
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 1, //总数条数
    "per_page": "50", //每页数量
    "current_page": "1", 当前页
    "data": [
    {
    "packets_amount": 11, // 已领取红包金额
    "get_number": 3, //已经领取个数
    "agent_name": "agent_name_1",// 所属代理
    "user_name": "user_name" //玩家登陆名
    }
    ],
    "total_page_score": { //总计
    "get_amount_total": 11, //总计领取金额
    "get_number_total": 3, //总计数量
    "get_user_total": 1 //总计玩家数量
    },
    "total_score": {// 小计
    "get_amount_total": 11, //小计 红包领取金额
    "get_number_total": 3, //小计 红包领取数量
    "get_user_total": 1// 小计 领取玩家数量
    }
    }
    }
     */
    public function show(Request $request)
    {
        $page = $request->input('page',1);
        $page_num = $request->input('page_num',10);
        $hall_name = $request->input('hall_name');
        $skip = (int) ($page-1) * $page_num;
        $limit = (int) $page_num;
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $validator = \Validator::make($request->input(), ['hall_name' => 'required']);
        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors(),
                'result'=>'',
            ]);
        }

        // 单位时间内厅主获取红包详情
        $match[ '$match']['hall_name'] = $hall_name;
        $project = ['$project'=> [ 'agent_name'=> 1,  'packets_amount'=> 1, 'get_number'=> 1, 'user_name'=> 1]];
        $sort = ['$sort'=> ['packets_amount'=> 1 ] ];
        $group = ['$group'=> [
            '_id' => ['user_id '=> '$user_id'],
            'packets_amount' => ['$sum'=>'$packets_amount'],
            'get_number'=> ['$sum' => '$get_number'],
            'agent_name'=> ['$first' => '$agent_name'],
            'user_name'=> ['$first' => '$user_name'],
        ] ];

        $aggregate = [$match ,$group, $sort, $project];

        //时间验证
        if(!empty($start_date) || !empty($end_date)){
            if(!$this->checkDate(['start_date'=>$request->input('start_date'),'end_date'=>$request->input('end_date')])) {
                return $this->response()->array([
                    'code' => 400,
                    'text' => trans('maintain.end_date.end_lt'),
                    'result' => ''
                ]);
            }

            // 单位时间内厅主获取红包详情
            $startDate_utc = strtotime($start_date) * 1000;
            $endDate_utc = strtotime($end_date) * 1000;
            $match['$match']['create_date']= ['$gte'=> new \MongoDB\BSON\UTCDateTime($startDate_utc), '$lte'=>new \MongoDB\BSON\UTCDateTime($endDate_utc)];

            $aggregate = [$match,$group, $sort, $project];
        }

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
                'total_page_score' => self::getPerCountScore($count_data),
                'total_score' => self::getPerCountScore($total_data),
            ],
        ]);
    }

    /**
     * 验证查询时间
     * @param $data
     * @return bool
     */
    private function checkDate($data)
    {
        if(empty($data))
            return false;

        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        //开始时间不能大于结束时间
        if(strtotime($end_date) <= strtotime($start_date))
            return false;
        return true;
    }

    /**
     * 时段内所有厅主小计 总计 计算
     * @param $data
     * @return array
     */
    private static function getCountScore($data)
    {
        $total = [
            'get_amount_total' => 0,
            'get_number_total' => 0,
            'get_user_total' => 0,
        ];
        if( $data ) {

            foreach ($data as $k => $v) {
                $total['get_amount_total'] += $v['packets_amount'];
                $total['get_number_total'] += $v['get_number'];
                $total['get_user_total'] += $v['user_number'];
            }
        }

        return $total;
    }

    /**
     * 时段内单个厅主小计 总计 计算
     * @param $data
     * @return array
     */
    private static function getPerCountScore($data)
    {
        $total = [
            'get_amount_total' => 0,
            'get_number_total' => 0,
            'get_user_total' => count($data),
        ];
        if( $data ) {

            foreach ($data as $k => $v) {
                $total['get_amount_total'] += $v['packets_amount'];
                $total['get_number_total'] += $v['get_number'];
            }
        }

        return $total;
    }

}