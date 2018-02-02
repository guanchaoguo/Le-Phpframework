<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/11
 * Time: 10:11
 * 系统维护控制器
 */

namespace App\Http\Controllers\Admin\V1;


use App\Models\GameHall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MaintainController extends BaseController
{
    /**
     * @api {post} /sysmaintain 提交平台维护
     * @apiDescription 提交平台维护
     * @apiGroup maintain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} state 状态 系统是否开启维护，0为未开启，1为开启，默认为0
     * @apiParam {String} comtent 系统维护内容
     * @apiParam {String} start_date 开始时间
     * @apiParam {String} end_date 结束时间
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function sysmaintain(Request $request)
    {
        //验证错误信息自定义，提升验证信息友好度
        $message = [
            'comtent.required'     => trans('maintain.comtent.required'),
            'start_date.required'         => trans('maintain.start_date.required'),
            'end_date.required'          => trans('maintain.end_date.required'),
        ];
        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
            'comtent'     => 'required',
            'start_date'     => 'required|date',
            'end_date'      => 'required|date'
        ],$message);
        //数据格式验证不通过
        if($validate->fails())
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors()->first(),
                'result'        => ''
            ]);
        }

        //时间验证
        if(!$this->checkDate(['start_date'=>$request->input('start_date'),'end_date'=>$request->input('end_date')]))
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('maintain.end_date.end_lt'),
                'result'        => ''
            ]);
        }
        $user = Auth::user();
        $data['start_date'] = $request->input('start_date');
        $data['end_date'] = $request->input('end_date');
        $data['state'] = $request->input('state',0);
        $data['comtent'] = $request->input('comtent');
        $data['sys_type'] = 0;
        $data['hall_id'] = '';
        $data['add_user'] =$user['user_name'];
        $data['add_date'] = date("Y-m-d H:i:s",time());
        $localDate = $request->input('localTime');
        $data['user_start_date'] = $localDate[0];
        $data['user_end_date'] = $localDate[1];

        $find = DB::table('system_maintain')->where(['sys_type'=>$data['sys_type']])->first();
        if(!$find)
        {
           $res =  DB::table('system_maintain')->insert($data);
        }
        else
        {
            $res = DB::table('system_maintain')->where(['sys_type'=>$data['sys_type']])->update($data);
        }

        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.fails'),
                'result'    => ''
            ]);
        }

        $redis = Redis::connection("default");

        if($request->input('state') == 1)
        {
            //同步维护信息到redis
            $redisData = ['content'=>$data['comtent'],'start_date'=>$data['start_date'],'end_date'=>$data['end_date']];

            $redis->set(env('GAME_MT_ING'),json_encode($redisData));
            $redis->expire(env('GAME_MT_ING'),strtotime($data['end_date'])-time());//设置过期时间

            @addLog(['action_name'=>'开启系统维护','action_desc'=>' 开启了系统维护，维护时间为：'. $data['start_date'].' 到 '. $data['end_date'],'action_passivity'=>'系统维护']);
        }else{
            //清空redis维护信息
            $redis->del(env('GAME_MT_ING'));
            @addLog(['action_name'=>'关闭系统维护','action_desc'=>' 关闭了系统维护','action_passivity'=>'系统维护']);
        }

        //和游戏服务端进行消息通信，发送维护通知
//        $gameServer = new GameServerController();
//        $gameServer->sysMaintain();
        //写入队列中
        $msg = json_encode(['cmd'=>'PlatMainChange']);
        $re = RabbitmqController::publishMsg([env('MQ_SERVER_CHANNEL'),env('MQ_SERVER_QUEUE'),env('MQ_SERVER_KEY'),$msg]);




        //success
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('delivery.success'),
            'result'    => ''
        ]);

    }

    /**
     * @api {post} /hallmaintain 提交游戏厅维护
     * @apiDescription 提交游戏厅维护
     * @apiGroup maintain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} state 状态 系统是否开启维护，0为未开启，1为开启，默认为0
     * @apiParam {String} comtent 系统维护内容
     * @apiParam {String} games 维护的游戏数组,格式为：厅ID-游戏ID,例如：[0-91,0-92]
     * @apiParam {String} start_date 开始时间
     * @apiParam {String} end_date 结束时间
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function hallmaintain(Request $request)
    {
        //验证错误信息自定义，提升验证信息友好度
        $message = [
            'comtent.required'     => trans('maintain.comtent.required'),
            'start_date.required'         => trans('maintain.start_date.required'),
            'end_date.required'          => trans('maintain.end_date.required'),
            'games.required'            => trans('maintain.games.required'),
        ];
        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
            'comtent'     => 'required',
            'start_date'     => 'required|date',
            'end_date'      => 'required|date',
            'games'         => 'required'
        ],$message);
        //数据格式验证不通过
        if($validate->fails())
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors()->first(),
                'result'        => ''
            ]);
        }

        //时间验证
        if(!$this->checkDate(['start_date'=>$request->input('start_date'),'end_date'=>$request->input('end_date')]))
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => trans('maintain.end_date.end_lt'),
                'result'        => ''
            ]);
        }

        $user = Auth::user();
        $data['start_date'] = $request->input('start_date');
        $data['end_date'] = $request->input('end_date');
        $data['state'] = $request->input('state',0);
        $data['comtent'] = $request->input('comtent');
        $data['sys_type'] = 1;
        $data['add_user'] =$user['user_name'];
        $data['add_date'] = date("Y-m-d H:i:s",time());
        $games = $request->input('games');
        $data['hall_id'] = implode(',',$games);
        $localDate = $request->input('localTime');
        $data['user_start_date'] = $localDate[0];
        $data['user_end_date'] = $localDate[1];

        $find = DB::table('system_maintain')->where(['sys_type'=>$data['sys_type']])->first();
        if(!$find)
        {
            $res =  DB::table('system_maintain')->insert($data);
        }
        else
        {
            $res = DB::table('system_maintain')->where(['sys_type'=>$data['sys_type']])->update($data);
        }

        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('delivery.fails'),
                'result'    => ''
            ]);
        }
        if($request->input('state') == 1)
        {
            @addLog(['action_name'=>'开启游戏厅维护','action_desc'=>' 开启了游戏厅维护，维护时间为：'. $data['start_date'].' 到 '. $data['end_date'],'action_passivity'=>'游戏厅维护']);
        }else{
            @addLog(['action_name'=>'关闭游戏厅维护','action_desc'=>' 关闭了游戏厅维护','action_passivity'=>'游戏厅维护']);
        }

        //和游戏服务端进行消息通信，发送维护通知
//        $gameServer = new GameServerController();
//        $gameServer->hallMaintain();
        //写入队列中
        $msg = json_encode(['cmd'=>'HallMainChange']);
        $re = RabbitmqController::publishMsg([env('MQ_SERVER_CHANNEL'),env('MQ_SERVER_QUEUE'),env('MQ_SERVER_KEY'),$msg]);


        //success
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('delivery.success'),
            'result'    => ''
        ]);
    }

    /**
     * @api {get} /getmaintain 获取系统维护信息
     * @apiDescription 获取系统维护信息
     * @apiGroup maintain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} sys_type 类型：0为平台维护，1为厅维护，默认为平台
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "id": 0,
    "sys_type": "0",    //0为平台维护，1为厅维护，默认为平台
    "state": 0,         //系统是否开启维护，0为未开启，1为开启，默认为0
    "hall_id": "",      //
    "start_date": "",   //系统维护开始时间
    "end_date": "",     //系统维护结束时间
    "comtent": "",      //系统维护的公告内容
    "add_user": "",     //开启系统维护的操作角色账号
    "add_date": ""      //添加时间
    }
    }
     */
    public function getmaintain(Request $request)
    {
        $sys_type = $request->input('sys_type',0);
        $find = DB::table('system_maintain')->where(['sys_type'=>$sys_type])->first();
        if(!$find) //平台维护
        {
            $find = [
                'id'    => 0,
                'sys_type' => $sys_type,
                'state' => 0,
                'hall_id' => '',
                'start_date' => '',
                'end_date'  => '',
                'comtent'   => '',
                'add_user'  => '',
                'add_date'  => '',
                'user_start_date'   => '',
                'user_end_date' => ''
            ];
            $find = json_decode(json_encode($find));

        }

        //获取游戏厅数据
        if($sys_type == 1)
        {
            $data = GameHall::select('*')->orderBy('id')->get();
            $only_hall = (int)$request->input('only_hall',0);

            if(!$only_hall) {
                foreach ($data as  $v){
                    $data->games = $v->games;
                }
            }
            $data = $data->toArray();

            if(!empty($find->hall_id))
            {
                $hall_game = explode(',',$find->hall_id);
                if($hall_game)
                {
                    foreach ($data as $key=>$val)
                    {
                        foreach ($val['games'] as $k1=>$v1)
                        {
                            foreach ($hall_game as $k=>$v2)
                            {
                                if(!$v2)
                                {
                                    break;
                                }
                                $gameList = explode('-',$v2);
                                if ($v1['pivot']['hall_id'] == $gameList[0] && $v1['id'] == $gameList[1]) {
                                    $data[$key]['games'][$k1]['is_have'] = 1;
                                }
                            }
                        }
                    }
                }
                foreach ($data as $key=>$val)
                {
                    foreach ($val['games'] as $k1=>$v1)
                    {
                        if(!isset($v1['is_have']))
                        {
                            $data[$key]['games'][$k1]['is_have'] = 0;
                        }
                    }
                }
            }
            else
            {
                foreach ($data as $key=>$val)
                {
                    foreach ($val['games'] as $k1=>$v1)
                    {
                        $data[$key]['games'][$k1]['is_have'] = 0;
                    }
                }
            }
            $find->result = $data;
        }
        $find->state = (int)$find->state;
        $find->start_date = $find->user_start_date;
        $find->end_date = $find->user_end_date;
        return $this->response->array([
            'code'  => 0,
            'text'  => trans('delivery.success'),
            'result'   => $find
        ]);
    }

    //验证系统维护时间
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
     * @api {post} /outAllUser 一键登出所有玩家
     * @apiDescription 一键登出所有玩家
     * @apiGroup maintain
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {Number} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function signOutAllUser(Request $request)
    {
        //写入队列中
        $msg = json_encode(['cmd'=>'KickAllPlayer']);
        $re = RabbitmqController::publishMsg([env('MQ_SERVER_CHANNEL'),env('MQ_SERVER_QUEUE'),env('MQ_SERVER_KEY'),$msg]);
        if( $re ) {

            //Player::where($where)->update(['on_line' => 'N']);

            @addLog(['action_name'=>'登出玩家','action_desc'=>'登出所有玩家','action_passivity'=>'所有玩家']);

            return $this->response->array([
                'code'=>0,
                'text'=>trans('agent.success'),
                'result'=>'',
            ]);

        }

        return $this->response->array([
            'code'=>400,
            'text'=>trans('agent.fails'),
            'result'=>'',
        ]);
    }
}