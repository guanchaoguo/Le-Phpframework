<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/6
 * Time: 15:26
 * 系统公告相关控制器
 */
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends BaseController
{
    /**
     * @api {get} /message 系统公告列表
     * @apiDescription 系统公告列表
     * @apiGroup message
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} type 公告发布的平台，0为所有平台，1为视讯PC，2为视讯H5，3为视讯APP
     * @apiParam {Int} status 使用状态 1：启用，0禁用
     * @apiParam {Number} page 当前页 默认1
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "per_page": 10,
    "current_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 1,
    "data": [
    {
    "id": 1,
    "coment_cn": "中文标题1",
    "coment_en": "英文标题2",
    "start_date": "2017-02-03 00:00:00",//开始时间
    "end_date": "2017-03-05 00:00:00",//结束时间
    "user_start_date": "2017-03-05 00:00:00",//用户本地开始时间
    "user_end_date": "2017-03-05 00:00:00",//用户本地结束时间
    "state": 0, //0为未启用，1为启用，2为删除（该字段没有用到）
    "type": 1, //公告发布的平台，0为所有平台，1为视讯PC，2为视讯H5，3为视讯APP。默认为0
    "add_date": "2017-04-06 17:02:31",
    "update_date": "2017-04-06 17:02:52"
    }
    ]
    }
    }
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $status = $request->input('status');
        $is_page = (int) $request->input('is_page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));

        $db = DB::table('system_message');

        $db->where('state', 1);
        if( isset($type) && $type !== '' ) {

            $db->where('type', $type);
        }

        if(isset($status) && $status !== '') {
            $now_time = date('Y-m-d H:i:s');
            if( $status  == 1) {
                $db->where('start_date', '<=', $now_time);
                $db->where('end_date', '>=', $now_time);
            } else if($status == 0){

//                $db->where('start_date', '>', $now_time);
//                $db->orWhere('end_date', '<', $now_time);

                $db->where(function ($query) use ($now_time) {
                    $query->where('start_date', '>', $now_time) ->orWhere('end_date', '<', $now_time);
                });
            } else {
                return $this->response->array([
                    'code'=>400,
                    'text'=>trans('agent.param_error'),
                    'result'=>'',
                ]);
            }
        }



        $db->orderby('add_date', 'desc');

        if( $is_page ) {
            $data = $db->paginate($page_num);
        } else {

            $data = $db->get();
        }
        //success
        return  $this->response()->array([
            'code'          => 0,
            'text'          => trans('delivery.success'),
            'result'        => $is_page ? $data : ['data'=>$data],
        ]);
    }

    /**
     * @api {post} /message 添加系统公告
     * @apiDescription 添加系统公告
     * @apiGroup message
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} coment_cn 中文公告
     * @apiParam {String} start_date 开始时间
     * @apiParam {String} end_date 结束时间
     * @apiParam {Array} local_time 本地时间 ['2017-06-19 09:50:00','2017-06-19 20:30:00']
     * @apiParam {Int} type 公告发布的平台，0为所有平台，1为视讯PC，2为视讯H5，3为视讯APP。默认为0
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function store(Request $request)
    {
        $title = $request->input('title','');
        $messge_cn = $request->input('coment_cn');
//        $messge_en = $request->input('message_en');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $local_time = $request->input('local_time');
        $type = $request->input('type',1);

        //验证错误信息自定义，提升验证信息友好度
        $message = [
            'coment_cn.required'     => trans('message.message_cn.required'),
            'message_en.required'     => trans('message.message_en.required'),
            'start_date.required'         => trans('message.start_date.required'),
            'end_date.required'          => trans('message.end_date.required'),
        ];
        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
//            'title'     => 'required',
            'coment_cn'     => 'required',
//            'coment_en'     => 'required',
            'start_date'     => 'required|date',
            'end_date'      => 'required|date'
        ],$message);
        //数据格式验证不通过
        if($validate->fails())
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors(),
                'result'        => ''
            ]);
        }

        //数据校验成功进行添加操作
        $res = DB::table('system_message')->insert([
            'coment_cn' => $messge_cn,
//            'title' => $title,
            'start_date'    => $start_date,
            'end_date'  => $end_date,
            'user_start_date' => $local_time[0],
            'user_end_date' => $local_time[1],
            'state' => 1,
            'type' => $type,
            'add_date'  => date("Y-m-d H:i:s",time())
        ]);

        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('message.fails'),
                'result'    => ''
            ]);
        }

        //写入队列中
        $msg = json_encode(['cmd'=>'NoticeChange']);
        $re = RabbitmqController::publishMsg([env('MQ_SERVER_CHANNEL'),env('MQ_SERVER_QUEUE'),env('MQ_SERVER_KEY'),$msg]);

        @addLog(['action_name'=>'添加系统公告','action_desc'=>' 添加了系统公告,公告内容为:'.$messge_cn,'action_passivity'=>'系统公告']);

        //success
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('message.success'),
            'result'    => ''
        ]);

    }

    /**
     * @api {post} /message/{id} 修改系统公告时获取数据状态
     * @apiDescription 修改系统公告时获取数据状态
     * @apiGroup message
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
    "id": 1,
    "title: "标题",
    "coment_cn": "内容",
    "coment_en": "英文内容（暂时不用）",
    "start_date": "2017-04-06 17:06:15",
    "end_date": "2017-04-06 17:06:15",
    "user_start_date": "2017-04-06 17:06:15",
    "user_end_date": "2017-04-06 17:06:15",
    "state": 1, //状态：0为未启用，1为启用，2为删除
    "type": 1, 公告发布的平台，0为所有平台，1为视讯PC，2为视讯H5，3为视讯APP。默认为0
    "add_date": "2017-04-06 17:06:15",
    "update_date": "2017-04-06 17:06:15",
     "local_time":["2017-04-06 17:06:15","2017-04-06 17:06:15"]
    }
    }
     */
    public function getMessage(Request $request,$id)
    {
        //获取数据信息
        $find = DB::table('system_message')->where(['id'=>$id])->first();
        if(!$find)
        {
            return $this->response->array([
                'code'  => 400,
                'text'  => trans('message.message_not_exist'),
                'result'    => ''
            ]);
        }
        $find = (array)$find;
        $find['local_time'] = array_values([$find['user_start_date'], $find['user_end_date']]);
        //success
        return $this->response->array([
            'code'  => 0,
            'text'  => trans('message.success'),
            'result'    => $find
        ]);
    }

    /**
     * @api {patch} /message/{id} 编辑系统公告
     * @apiDescription 编辑系统公告
     * @apiGroup message
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} title 标题
     * @apiParam {String} coment_cn 中文公告
     * @apiParam {String} start_date 开始时间
     * @apiParam {String} end_date 结束时间
     * @apiParam {Array} local_time 本地时间 ['2017-06-19 09:50:00','2017-06-19 20:30:00']
     * @apiParam {Int} type 公告发布的平台，0为所有平台，1为视讯PC，2为视讯H5，3为视讯APP。默认为0
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function update(Request $request,$id)
    {
        $messge_cn = $request->input('coment_cn');
        $title = $request->input('title','');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $local_time = $request->input('local_time');
        $type = $request->input('type',1);

        //验证错误信息自定义，提升验证信息友好度
        $message = [
            'coment_cn.required'     => trans('message.message_cn.required'),
            'message_en.required'     => trans('message.message_en.numeric'),
            'start_date.required'         => trans('message.start_date.required'),
            'end_date.required'          => trans('message.end_date.required'),
        ];
        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
//            'title'     => 'required',
            'coment_cn'     => 'required',
            'start_date'     => 'required|date',
            'end_date'      => 'required|date'
        ],$message);
        //数据格式验证不通过
        if($validate->fails())
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors(),
                'result'        => ''
            ]);
        }

        //验证数据是否存在
        $find = DB::table('system_message')->where(['id'=>$id])->first();
        if(!$find)
        {
            return $this->response->array([
                'code'  => 400,
                'text'  => trans('message.message_not_exist'),
                'result'    => ''
            ]);
        }

       $res = DB::table('system_message')->where(['id'=>$id])->update([
           'coment_cn' => $messge_cn,
//           'title' => $title,
           'start_date'    => $start_date,
           'end_date'  => $end_date,
           'user_start_date'  => $local_time[0],
           'user_end_date'  => $local_time[1],
           'type' => $type,
           'update_date'  => date("Y-m-d H:i:s",time())
       ]);

        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('message.fails'),
                'result'    => ''
            ]);
        }
        //写入队列中
        $msg = json_encode(['cmd'=>'NoticeChange']);
        $re = RabbitmqController::publishMsg([env('MQ_SERVER_CHANNEL'),env('MQ_SERVER_QUEUE'),env('MQ_SERVER_KEY'),$msg]);

        @addLog(['action_name'=>'修改系统公告','action_desc'=>' 修改了系统公告，公共标题为：'.$title.',公告内容为:'.$messge_cn,'action_passivity'=>'系统公告']);

        //success
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('message.success'),
            'result'    => ''
        ]);
    }

    /**
     * @api {patch} /message/state/{id} 修改系统公告状态
     * @apiDescription 修改系统公告状态
     * @apiGroup message
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} state 状态：0为未启用，1为启用，2为删除
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function editState(Request $request,$id)
    {
        //验证数据是否存在
        $find = DB::table('system_message')->where(['id'=>$id])->first();
        if(!$find)
        {
            return $this->response->array([
                'code'  => 400,
                'text'  => trans('message.message_not_exist'),
                'result'    => ''
            ]);
        }

        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
            'state'     => 'required|in:0,1,2',
        ]);
        //数据格式验证不通过
        if($validate->fails())
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors(),
                'result'        => ''
            ]);
        }

        //进行状态修改操作
        $res = DB::table('system_message')->where(['id'=>$id])->update(['state'=>$request->input('state')]);
        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('message.fails'),
                'result'    => ''
            ]);
        }
        switch ($request->input('state'))
        {
            case 0:
                @addLog(['action_name'=>'关闭系统公告','action_desc'=>' 关闭了系统公告，公告内容为:'.$find->coment_cn,'action_passivity'=>'系统公告']);
                break;
            case 1:
                @addLog(['action_name'=>'开启系统公告','action_desc'=>' 开启了系统公告，公告内容为:'.$find->coment_cn,'action_passivity'=>'系统公告']);
                break;
            case 2:
                @addLog(['action_name'=>'删除系统公告','action_desc'=>' 删除了系统公告，公告内容为:'.$find->coment_cn,'action_passivity'=>'系统公告']);
                break;
        }

        //写入队列中
        $msg = json_encode(['cmd'=>'NoticeChange']);
        $re = RabbitmqController::publishMsg([env('MQ_SERVER_CHANNEL'),env('MQ_SERVER_QUEUE'),env('MQ_SERVER_KEY'),$msg]);

        //success
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('message.success'),
            'result'    => ''
        ]);
    }
}