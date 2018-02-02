<?php
/**
 * 厅主公告
 * User: chensongjian
 * Date: 2017/10/13
 * Time: 11:14
 */

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AgentMessage;

class AgentMessageController extends BaseController
{

    /**
     * @api {get} /agent/message 厅主公告列表
     * @apiDescription 厅主公告列表
     * @apiGroup agentMessage
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} status 状态 '':全部，0：禁用，1：启用
     * @apiParam {String} page_num 每页显示条数 默认10
     * @apiParam {String} page 当前第几页 默认1
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 3,
    "per_page": "10",
    "current_page": 1,
    "last_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 3,
    "data": [
    {
    "id": 3,
    "message": "content test ",//公告内容
    "start_date": "2017-10-13 03:48:56",//开始时间
    "end_date": "2017-10-13 05:55:55",//结束时间
    "create_date": "2017-10-13 04:48:08",//创建时间
    "status": 1//状态 1：启用中 ，0：未启用
    }
    ]
    }
    }
     */
    public function index(Request $request)
    {
        $status = $request->input('status','');
        $page_num = $request->input('page_num',10);
        $now_time = date('Y-m-d H:i:s');

        $db = AgentMessage::select('*');

        if(isset($status) && $status !== '') {
            if( $status  == 1) {
                $db->where('start_date', '<=', $now_time);
                $db->where('end_date', '>=', $now_time);
            } else if($status == 0){
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
        $db->orderby('create_date', 'desc');

        $data = $db->paginate($page_num);

        foreach ($data as $item) {
            $item->status = 0;
            if( $now_time >= $item->start_date &&  $now_time <= $item->end_date ) {
                $item->status = 1;
            }
        }
        return $this->response->array([
            'code'=>0,
            'text'=>trans('agent.success'),
            'result'=> $data,
        ]);
    }

    /**
     * @api {post} /agent/message 添加厅主公告
     * @apiDescription 添加厅主公告
     * @apiGroup agentMessage
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} message 公告内容
     * @apiParam {String} start_date 开始时间
     * @apiParam {String} end_date 结束时间
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function store(Request $request)
    {
        $message = $request->input('message');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $now_date = date('Y-m-d H:i:s');
        $msg = [
            'message.required' => trans('hall_message.message.required'),
            'start_date.required' => trans('hall_message.start_date.required'),
            'start_date.date_format' => trans('hall_message.start_date.date_format'),
            'start_date.after' => trans('hall_message.start_date.after'),
            'end_date.required' => trans('hall_message.end_date.required'),
            'end_date.date_format' => trans('hall_message.end_date.date_format'),
            'end_date.after' => trans('hall_message.end_date.after'),
        ];
        $validator = \Validator::make($request->input(), [
            'message' => 'required',
            'start_date' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:'.$now_date
            ],
            'end_date' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:start_date'
            ],
        ],$msg);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=> 400,
                'text'=> $validator->errors()->first(),
                'result'=>'',
            ]);
        }


        $insert_data = [
            'message' => $message,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'create_date' => date('Y-m-d H:i:s')
        ];

        $res = AgentMessage::create($insert_data);

        if( ! $res->id ) {
            return $this->response->array([
                'code'=> 400,
                'text'=> trans('agent.fails'),
                'result'=> '',
            ]);
        }
        return $this->response->array([
            'code'=> 0,
            'text'=> trans('agent.success'),
            'result'=> '',
        ]);
    }

    /**
     * @api {get} /agent/message/{id} 获取厅主公告内容
     * @apiDescription 编辑时获取厅主公告内容
     * @apiGroup agentMessage
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
    "message": "edit content test",
    "start_date": "2017-10-13 04:48:56",
    "end_date": "2017-10-14 04:48:56",
    "create_date": "2017-10-13 04:36:06"
    }
    }
     */
    public function show(int $id)
    {
        $res = AgentMessage::find($id);

        return $this->response->array([
            'code'=> 0,
            'text'=> trans('agent.success'),
            'result'=> $res,
        ]);
    }

    /**
     * @api {put} /agent/message/{id} 保存厅主公告
     * @apiDescription 保存厅主公告
     * @apiGroup agentMessage
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} message 公告内容
     * @apiParam {String} start_date 开始时间
     * @apiParam {String} end_date 结束时间
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function update(Request $request, int $id)
    {

        $info = AgentMessage::find($id);
        if(  ! $info ) {
            return $this->response->array([
                'code'=> 400,
                'text'=> trans('hall_message.data_not_exist'),
                'result'=> '',
            ]);
        }
        $now_date = date('Y-m-d H:i:s');
        $message = $request->input('message');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $msg = [
            'message.required' => trans('hall_message.message.required'),
            'start_date.required' => trans('hall_message.start_date.required'),
            'start_date.date_format' => trans('hall_message.start_date.date_format'),
            'start_date.after' => trans('hall_message.start_date.after'),
            'end_date.required' => trans('hall_message.end_date.required'),
            'end_date.date_format' => trans('hall_message.end_date.date_format'),
            'end_date.after' => trans('hall_message.end_date.after'),
        ];
        $validator = \Validator::make($request->input(), [
            'message' => 'required',
            'start_date' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:'.$now_date
            ],
            'end_date' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:start_date'
            ],
        ], $msg);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=> 400,
                'text'=> $validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $update_data = [
            'message' => $message,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];

        $res = AgentMessage::where('id', $id)->update($update_data);

        if( $res === false ) {
            return $this->response->array([
                'code'=> 400,
                'text'=> trans('agent.fails'),
                'result'=> '',
            ]);
        }
        return $this->response->array([
            'code'=> 0,
            'text'=> trans('agent.success'),
            'result'=> '',
        ]);

    }

    /**
     * @api {delete} /agent/message/{id} 删除厅主公告
     * @apiDescription 删除厅主公告
     * @apiGroup agentMessage
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function delete(int $id)
    {
        $info = AgentMessage::find($id);
        if(  ! $info ) {
            return $this->response->array([
                'code'=> 400,
                'text'=> trans('hall_message.data_not_exist'),
                'result'=> '',
            ]);
        }

        $res = AgentMessage::where('id', $id)->delete();

        if( ! $res ){
            return $this->response->array([
                'code'=> 400,
                'text'=> trans('agent.fails'),
                'result'=> '',
            ]);
        }

        return $this->response->array([
            'code'=> 0,
            'text'=> trans('agent.success'),
            'result'=> '',
        ]);
    }
}