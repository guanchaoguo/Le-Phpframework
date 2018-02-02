<?php
/**
 * Created by PhpStorm.
 * User: Sanji
 * Date: 2018/1/8
 * Time: 10:15
 * 派彩异常处理
 */
namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\V1\GameServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutController extends BaseController
{

    /**
     * @api {get} /abnormalList 重新派彩数据列表
     * @apiDescription 获取重新派彩数据列表
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} round_no 局ID
     * @apiParam {Number} page 当前页
     * @apiParam {Number} page_num 每页条数
     * @apiParam {Number} is_page 是否分页 1：是，0：否 ，默认1
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *    {
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
    "round_no": "d79c74f64a408118", //局ID
    "table_no": "B01", //桌号
    "game_name": "百家乐", //游戏名称
    "game_hall_code": "GH0001"
    "game_result": "5;1", //游戏结果
    "is_responsed": 0 //是否派彩
    }
    ]
    }
    }
     */
    public function getList(Request $request)
    {
        $round_no = $request->input("round_no");
        $page_num = $request->input('page_num', env('PAGE_NUM'));
        $is_page = $request->input('is_page', 1);

        $db = DB::connection("mongodb")->table("manual_payout_record")->select('round_no', 'table_no', 'game_name', 'game_result', 'is_responsed','game_hall_code');
        $db->where(['is_responsed'=>0]);
        if ($round_no) {
            $where["round_no"] = $round_no;
            $db->where($where);
        }

        if ($is_page) {
            $list = $db->paginate((int)$page_num)->toArray();
        } else {
            $list = $db->get()->toArray();
        }
        if ($list && isset($list['data']))
        {
            foreach ($list['data'] as $key=>&$val)
            {
                if(!isset($val['is_responsed']))
                {
                    unset($val['_id']);
                    $val['is_responsed'] = 0;
                }
            }
        }
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => $is_page ? $list : ['data' => $list],
        ]);
    }


    /**
     * @api {post} /refresh 重新派彩操作
     * @apiDescription 重新派彩操作
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} round_no 局ID
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function refresh(Request $request)
    {
        $round_no = $request->input('round_no');
        //判断数据是否存在
        $find = DB::connection("mongodb")->table("manual_payout_record")->where(['round_no'=>$round_no])->first();

        if(!$find)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.param_error'),
                'result'=>'',
            ]);
        }

        //重新组装数据推送到包网
        unset($find['_id']);
        unset($find['game_hall_code']);
        $user_payout_detail_info = json_decode($find['user_payout_detail_info'],true);
        $find['user_payout_detail_info'] = $user_payout_detail_info['user_payout_detail_info'];
        $server = new GameServerController();
        $res = $server->roundotUserBalanceMessage($find,1006);
        if($res && isset($res['ret_code']) && $res['ret_code'] == 1)
        {
            //包网返回成功进行修改数据的状态操作
            $res = DB::connection("mongodb")->table("manual_payout_record")->where(['round_no'=>$round_no])->update(['is_responsed'=>1]);
            return $this->response->array([
                'code'=>0,
                'text'=> trans('agent.success'),
                'result'=>'',
            ]);
        }else{
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.fails'),
                'result'=>'',
            ]);
        }
    }

    /**
     * @api {get} /getRollbackList 回退下注金额列表数据
     * @apiDescription 回退下注金额列表数据
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page 当前页
     * @apiParam {Number} page_num 每页条数
     * @apiParam {Number} is_page 是否分页 1：是，0：否 ，默认1
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *    {
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
    "_id": {
    "$oid": "5a55cfaee138232b3c1dbdd3"
    },
    "user_id": 1761863,
    "user_name": "114game662lebo",  //登录名
    "agent_id": 600,
    "agent_name": "lebo98095",
    "money": 100,  //下注金额
    "remark": "backspace_addgold",
    "insert_id": "5a55cf95e138232b3c1dbdd2",
    "oper_code": 3,
    "game_hall_code": "GH0001",  //游戏厅CODE
    "game_name": "百家乐",  //游戏名称
    "table_no": "B02",  //桌号
    "is_responsed": 0   //状态 0为异常
    },
    ]
    }
    }
     */
    public function getRollbackList(Request $request)
    {
        $page_num = $request->input('page_num', env('PAGE_NUM'));
        $is_page = $request->input('is_page', 1);

        $db = DB::connection("mongodb")->table("manual_backspace_addgold_record");
        $db->where(['is_responsed'=>0]);
        if ($is_page) {
            $list = $db->paginate((int)$page_num)->toArray();
        } else {
            $list = $db->get()->toArray();
        }
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => $is_page ? $list : ['data' => $list],
        ]);
    }

    /**
     * @api {post} /rollbackMoney 重新回退下注金额操作
     * @apiDescription 重新回退下注金额操作
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} insert_id insert_id数组，例如['5a55d068e138232b3c1dbdda','55a55d36ae138233eb63a6906']
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function rollbackMoney(Request $request)
    {
        $insertIdList = $request->input('insert_id');
        //参数验证
        if(!is_array($insertIdList))
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.param_error'),
                'result'=>'',
            ]);
        }

        //获取需要回退的数据
        $list = DB::connection("mongodb")->table("manual_backspace_addgold_record")->where(['is_responsed'=>0])->whereIn('insert_id',$insertIdList)->get()->toArray();

        if(!$list)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.param_error'),
                'result'=>'',
            ]);
        }

        //和包网进行通信，进行用户余额回退操作。批量时进行循环发包操作
        $updateList = [];
        $errorList = [];
        $server = new \App\Http\Controllers\Admin\V1\GameServerController();
        $mdb = DB::connection("mongodb")->table("manual_backspace_addgold_record");
        foreach ($list as $key=>$val)
        {
            //进行数据组装，发包操作
            $sendData['agent_id'] = $val['agent_id'];
            $sendData['agent_name'] = $val['agent_name'];
            $sendData['user_id'] = $val['user_id'];
            $sendData['user_name'] = $val['user_name'];
            $sendData['money'] = $val['money'];
            $sendData['remark'] = $val['remark'];
            $sendData['oper_code'] = $val['oper_code'];
            $sendData['insert_id'] = $val['insert_id'];
            $res = $server->roundotUserBalanceMessage($sendData,1012);
            if($res['ret_code'] == 1)
            {
                //包网返回成功则进行本地数据状态修改
                $updateList[] = $val['insert_id'];
            }else{
                $errorList[] = $val['insert_id'];
            }

        }
        //批量修改状态
        $stats = $mdb->whereIn('insert_id',$updateList)->update(['is_responsed'=>1]);

        if(!$stats || count($errorList) > 0)
        {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.fails'),
                'result'=>'',
            ]);
        }

        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>'',
        ]);
    }
}
