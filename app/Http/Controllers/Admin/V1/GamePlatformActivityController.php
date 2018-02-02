<?php
/**
 * 文案活动
 * User: chensongjian
 * Date: 2017/4/17
 * Time: 13:23
 */

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\GamePlatformActivity;

class GamePlatformActivityController extends BaseController
{
    /**
     * @api {get} /copywriter/activity 文案-活动 列表
     * @apiDescription 文案-活动 列表
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} p_id 主厅id
     * @apiParam {Number} page 当前页 默认1
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiSuccessExample {json} Success-Response:
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
    "id": 2,//活动id
    "p_id": 1,//厅主id
    "title": "test_title",//活动标题
    "play_type": 0,//展现方式，0为弹框形式，1为其他
    "label": 1,//所属平台,0为PC，1为手机横版，2为手机竖版
    "play_place": 1,//展示位置，0为页面居中方式，1为其他
    "start_date": "2017-04-13 15:16:18",//活动开始时间
    "end_date": "2017-04-13 15:16:19",//活动结束时间
    "img": "images/12121.jpg",//活动图片地址
    "status": 0,//审核状态，0：未审核，1：已审核，2：审核不通过
    "add_date": "2017-04-17 14:44:39",//添加时间
    "update_date": "2017-04-17 14:44:39",//修改时间
    "full_img": "http://192.168.31.230:8000/images/12121.jpg",//全路径活动图片地址
    "agent": {//厅主信息
    "user_name": "csj"//厅主名称
    }
    }
    ]
    }
    }
     */
    public function index(Request $request)
    {

        $p_id = (int) $request->input('p_id');
        $is_page = (int) $request->input('is_page', 1);
        $page    = (int) $request->input('page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));
        $where = [];
        if( isset( $p_id ) && ! empty( $p_id ) ) {
            $where['p_id'] = $p_id;
        }
        $db = GamePlatformActivity::select('*',\DB::raw('CONCAT("'.env('IMAGE_HOST').'", img) AS full_img'))->where($where);
        if( $is_page ) {
            $data = $db->paginate($page_num);
        } else {
            $data = $db->get();
        }

        foreach ($data as $v) {
            $v->agent;
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' =>  $is_page ? $data : ['data'=>$data],
        ]);
    }

    /**
     * @api {post} /copywriter/activity 文案-活动 添加
     * @apiDescription 文案-活动 添加
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} p_id 厅主ID *
     * @apiParam {String} title LOGO描述信息 *
     * @apiParam {Number} play_type 轮播方式，0为从左到右，1为从右到左 *
     * @apiParam {Number} play_place 展示位置，0为页面居中方式，1为其他 *
     * @apiParam {Number} label 所属平台,0为PC，1为手机横版，2为手机竖版 *
     * @apiParam {Number} start_date 活动开始时间 2017-04-13 15:16:19*
     * @apiParam {Number} end_date 活动结束时间 2017-04-13 15:16:19*
     * @apiParam {String} img  图片地址 格式：images/12121.jpg *
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function store(Request $request)
    {
        $p_id = (int) $request->input('p_id');
        $title = $request->input('title');
        $play_type = (int) $request->input('play_type');
        $play_place = (int) $request->input('play_place');
        $label = (int) $request->input('label');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $img = $request->input('img');

        if( ! $agent = Agent::where(['id'=>$p_id,'grade_id' =>1,'is_hall_sub' => 0])->first() ){
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);
        }

        $message = [
            'p_id.unique' => trans('agent.hall_has_data'),
            'img.required' => trans('copywriter.logo_required'),
            'label.required' => trans('copywriter.label.required'),
            'label.in' => trans('copywriter.label.in'),
            'play_type.required' => trans('copywriter.play_type.required'),
            'play_type.in' => trans('copywriter.play_type.in'),
            'play_place.required' => trans('copywriter.play_place.required'),
            'play_place.in' => trans('copywriter.play_place.in'),
            'start_date.required' => trans('copywriter.start_date.required'),
            'end_date.required' => trans('copywriter.end_date.required'),
        ];
        $validator = \Validator::make($request->input(), [
            'p_id' =>'required|unique:game_platform_activity',
            'title' => 'required',
            'play_type' => 'required|in:0,1',
            'label' => 'required|in:0,1,2',
            'play_place' => 'required|in:0,1',
            'start_date' => 'required',
            'end_date' => 'required',
            'img' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        if($start_date >= $end_date) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('copywriter.start_date.gt'),
                'result'=>'',
            ]);
        }

        $attributes = [
            'p_id' => $p_id,
            'title' => $title,
            'play_type' => $play_type,
            'play_place' => $play_place,
            'label' => $label,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'img' => $img,
            'add_date' => date('Y-m-d H:i:s', time()),
        ];

        $re = GamePlatformActivity::create($attributes);

        if( $re ) {
            @addLog(['action_name'=>'添加文案','action_desc'=>' 添加了一个新的文案，文案ID为：'.$re->id,'action_passivity'=>'文案列表']);

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
     * @api {get} /copywriter/activity/{id} 文案-活动 详情
     * @apiDescription 文案-活动 详情
     * @apiGroup copywriter
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
    "id": 2,//活动id
    "p_id": 1,//厅主id
    "title": "test_title",//活动标题
    "play_type": 0,//展现方式，0为弹框形式，1为其他
    "label": 1,//所属平台,0为PC，1为手机横版，2为手机竖版
    "play_place": 1,//展示位置，0为页面居中方式，1为其他
    "start_date": "2017-04-13 15:16:18",//活动开始时间
    "end_date": "2017-04-13 15:16:19",//活动结束时间
    "img": "images/12121.jpg",//活动图片地址
    "status": 0,//审核状态，0：未审核，1：已审核，2：审核不通过
    "add_date": "2017-04-17 14:44:39",//添加时间
    "update_date": "2017-04-17 14:44:39",//修改时间
    "full_img": "http://192.168.31.230:8000/images/12121.jpg",//全路径活动图片地址
    "agent": {//厅主信息
    "user_name": "csj"//厅主名称
    }
    }
    }
     */
    public function show(Request $request, int $id)
    {
        $data = GamePlatformActivity::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }
        $data->full_img = env('IMAGE_HOST').$data->img;
        $data->agent;
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * @api {put} /copywriter/activity/{id} 文案-活动 编辑
     * @apiDescription 文案-活动 编辑
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} p_id 厅主ID *
     * @apiParam {String} title LOGO描述信息 *
     * @apiParam {Number} play_type 轮播方式，0为从左到右，1为从右到左 *
     * @apiParam {Number} play_place 展示位置，0为页面居中方式，1为其他 *
     * @apiParam {Number} label 所属平台,0为PC，1为手机横版，2为手机竖版 *
     * @apiParam {Number} start_date 活动开始时间 2017-04-13 15:16:19*
     * @apiParam {Number} end_date 活动结束时间 2017-04-13 15:16:19*
     * @apiParam {String} img  图片地址 格式：images/12121.jpg *
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function update(Request $request, int $id)
    {
        $p_id = (int) $request->input('p_id');
        $title = $request->input('title');
        $play_type = (int) $request->input('play_type');
        $label = (int) $request->input('label');
        $play_place = (int) $request->input('play_place');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $img = $request->input('img');

        $data = GamePlatformActivity::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        if( ! $agent = Agent::where(['id'=>$p_id,'grade_id' =>1,'is_hall_sub' => 0])->first() ){
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);
        }

        $message = [
            'p_id.unique' => trans('agent.hall_has_data'),
            'img.required' => trans('copywriter.logo_required'),
            'label.required' => trans('copywriter.label.required'),
            'label.in' => trans('copywriter.label.in'),
            'play_type.required' => trans('copywriter.play_type.required'),
            'play_type.in' => trans('copywriter.play_type.in'),
            'play_place.required' => trans('copywriter.play_place.required'),
            'play_place.in' => trans('copywriter.play_place.in'),
            'start_date.required' => trans('copywriter.start_date.required'),
            'end_date.required' => trans('copywriter.end_date.required'),
        ];
        $validator = \Validator::make($request->input(), [
            'p_id' =>'required|unique:game_platform_activity,p_id,'.$p_id,
            'title' => 'required',
            'play_type' => 'required|in:0,1',
            'label' => 'required|in:0,1,2',
            'play_place' => 'required|in:0,1',
            'start_date' => 'required',
            'end_date' => 'required',
            'img' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        if($start_date >= $end_date) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('copywriter.start_date.gt'),
                'result'=>'',
            ]);
        }
        $attributes = [
            'p_id' => $p_id,
            'title' => $title,
            'play_type' => $play_type,
            'play_place' => $play_place,
            'label' => $label,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'img' => $img,
            'update_date' => date('Y-m-d H:i:s', time()),
        ];

        $re = GamePlatformActivity::where('id', $id)->update($attributes);

        if( $re !== false ) {
            @addLog(['action_name'=>'编辑文案','action_desc'=>' 对文案进行了编辑，文案ID为：'.$id,'action_passivity'=>'文案列表']);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
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
     * @api {patch} /copywriter/activity/{id} 文案-活动 审核
     * @apiDescription 文案-活动 审核
     * @apiGroup copywriter
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
    public function review(Request $request, int $id)
    {
        $data = GamePlatformActivity::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        if( $data->status == 1 ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.has_review'),
                'result' => '',
            ]);
        }

        $re = GamePlatformActivity::where('id',$id)->update(['status' => 1]);
        if($re !== false) {
            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.success'),
                'result' => '',
            ]);
        }
        return $this->response->array([
            'code' => 400,
            'text' => trans('agent.fails'),
            'result' => '',
        ]);
    }

    /**
     * @api {delete} /copywriter/activity/{id} 文案-活动 删除
     * @apiDescription 文案-活动 删除
     * @apiGroup copywriter
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
    public function delete(Request $request, int $id)
    {
        $data = GamePlatformActivity::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $re = GamePlatformActivity::destroy($id);
        if( !$re ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.fails'),
                'result' => '',
            ]);
        }
        @addLog(['action_name'=>'删除文案','action_desc'=>' 对文案进行了删除，文案ID为：'.$id,'action_passivity'=>'文案列表']);

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => '',
        ]);
    }
}