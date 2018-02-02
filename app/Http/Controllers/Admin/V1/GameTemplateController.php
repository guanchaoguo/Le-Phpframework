<?php
/**
 * 游戏风格模板控制器
 * User: chensongjian
 * Date: 2017/4/11
 * Time: 10:31
 */

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\GameTemplate;
use App\Models\GameTemplateImages;
class GameTemplateController extends BaseController
{

    /**
     * @api {get} /gameTemplate 风格模板列表
     * @apiDescription 风格模板列表
     * @apiGroup Template
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} title 模板标题
     * @apiParam {String} state 模板启用状态，0为未启用，1为启用，默认为0
     * @apiParam {Number} page 当前页 默认1
     * @apiParam {Number} page_num 每页条数 默认10
     * @apiParam {Number} is_page 是否分页 1是 0否，默认为1
     * @apiSuccessExample {json} Success-Response:
     * {
        "code": 0,
        "text": "操作成功",
        "result": {
        "total": 1,
        "per_page": "10",
        "current_page": 1,
        "last_page": 1,
        "next_page_url": null,
        "prev_page_url": null,
        "from": 1,
        "to": 1,
        "data": [
        {
        "id": 2,//模板id
        "title": "模板test",//模板标题
        "desc": "这是描述",//模板说明
        "label": 0,//所属平台：0为PC，1为手机横版，2为手机竖版
        "code": "bbb",//模板代码
        "state": 0,'//模板启用状态，0为未启用，1为启用，默认为0'
        "add_date": "2017-04-11 15:33:04"//添加时间
        }
        ]
        }
        }
     */
    public function index(Request $request)
    {

        $title = $request->input('title');
        $state = $request->input('state');
        $page_num = $request->input('page_num', env('PAGE_NUM'));
        $page = $request->input('page', 1);
        $is_page = $request->input('is_page', 1);

        $db = GameTemplate::orderby('add_date', 'desc');

        if(isset($state) && $state !== '') {
            $db->where('state', $state);
        }

        if(isset($title) && !empty($title)) {
            $db->where('title', 'like', '%'.$title.'%');
        }
        if($is_page) {
            $data = $db->paginate($page_num);
        } else {
            $data['data'] = $db->get();
        }
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * @api {post} /gameTemplate 添加风格模板
     * @apiDescription 添加风格模板列表
     * @apiGroup Template
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} title 模板标题
     * @apiParam {String} code 模板风格代码
     * @apiParam {String} desc 模板说明
     * @apiParam {Number} label 所属平台,0为PC，1为手机横版，2为手机竖版
     * @apiParam {String} images 模板图片 数组格式 ['','']
     * @apiSuccessExample {json} Success-Response:
        {
        "code": 0,
        "text": "操作成功",
        "result": ""
        }
     */
    public function store(Request $request)
    {
        $message = [
            'title.required' => trans('template.title.required'),
            'title.unique' => trans('template.title.unique'),
            'code.required' => trans('template.code.required'),
            'code.unique' => trans('template.code.unique'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required|unique:game_template',
            'code' => 'required|unique:game_template',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }
        $time = date('Y-m-d H:i:s');
        $attributes = $request->except('token','locale','images');
        $attributes['add_date'] = $time;
        $attributes['state'] = 1;
        $re = GameTemplate::create($attributes);
        $images = $request->input('images');
        if($re) {

            if($images) {
                $img = [];
                foreach ($images as $v) {
                    $img[] = ['img' => $v, 't_id' => $re->id,'add_date'=>$time];
                }

                $img && GameTemplateImages::insert($img);
            }
            @addLog(['action_name'=>'添加风格模板','action_desc'=>'添加了一套新的风格模板，ID为：'.$re->id,'action_passivity'=>'模板列表']);



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
     * @api {get} /gameTemplate/{id} 风格模板详情
     * @apiDescription 风格模板详情
     * @apiGroup Template
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
        "id": 2,//模板id
        "title": "模板test",//模板标题
        "desc": "这是描述",//模板描述说明
        "label": 0,//所属平台,0为PC，1为手机横版，2为手机竖版
        "code": "bbb",//模板代码
        "state": 0,//模板启用状态，0为未启用，1为启用，默认为0
        "add_date": "2017-04-11 15:33:04",//添加时间
        "images": [//模板图片
        {
        "img": "images/2017-03-01-14-21-02-58b6684ecc36e.jpg"//相对路径
        "full_img": "http://platform.dev/images/2017-03-01-14-21-02-58b6684ecc36e.jpg"//全路径
        }
        ]
        }
        }
     */
    public function show(Request $request, int $id)
    {
        $data = GameTemplate::find($id);
        if( !$data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('template.not_exist'),
                'result' => '',
            ]);
        }
        $data->images;
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * @api {put} /gameTemplate/{id} 编辑风格模板
     * @apiDescription 编辑风格模板
     * @apiGroup Template
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} title 模板标题
     * @apiParam {String} code 模板风格代码
     * @apiParam {String} desc 模板说明
     * @apiParam {Number} label 所属平台,0为PC，1为手机横版，2为手机竖版
     * @apiParam {String} images 模板图片 数组格式 ['','']
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function update(Request $request, int $id)
    {
        $data = GameTemplate::find($id);
        if( !$data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('template.not_exist'),
                'result' => 'show'.$id,
            ]);
        }

        $message = [
            'title.required' => trans('template.title.required'),
            'title.unique' => trans('template.title.unique'),
            'code.required' => trans('template.code.required'),
            'code.unique' => trans('template.code.unique'),
        ];

        $validator = \Validator::make($request->input(), [
            'title' => 'required|unique:game_template,title,'.$id,
            'code' => 'required|unique:game_template,code,'.$id,
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $images = $request->input('images');
        $time = date('Y-m-d H:i:s',time());

        $attributes = $request->except('token','locale','images');
        $re = GameTemplate::where('id', $id)->update($attributes);
        if($re !== false) {
            GameTemplateImages::where('t_id', $id)->delete();
            //图片处理
            if($images) {
                $img = [];
                foreach ($images as $v) {
                    $img[] = ['img' => $v, 't_id' => $id,'add_date'=>$time];
                }
                $img && GameTemplateImages::insert($img);
            }
            @addLog(['action_name'=>'编辑风格模板','action_desc'=>'对风格模板进行了编辑，ID为：'.$re->id,'action_passivity'=>'模板列表']);


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
     * @api {delete} /gameTemplate/{id} 删除风格模板
     * @apiDescription 删除风格模板
     * @apiGroup Template
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
    public function delete(Request $request, $id)
    {
        $data = GameTemplate::find($id);
        if( !$data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('template.not_exist'),
                'result' => '',
            ]);
        }
        $re = GameTemplate::destroy($id);
        if( !$re ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.fails'),
                'result' => '',
            ]);
        }

        GameTemplateImages::where('t_id',$id)->delete();
        @addLog(['action_name'=>'删除风格模板','action_desc'=>'对风格模板进行了删除，ID为：'.$re->id,'action_passivity'=>'模板列表']);

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => '',
        ]);
    }

    /**
     * @api {patch} gameTemplate/{id}/agent/{a_id} 厅主选模板
     * @apiDescription 厅主选模板保存
     * @apiGroup Template
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
     */
    public function saveAgentTemplate(Request $request, int $id, int $a_id) {

        $where = ['id' => $a_id, 'grade_id' => 1,'is_hall_sub' => 0];
        $agent = Agent::where($where)->first();

        if( ! $agent ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);
        }
        $temp = GameTemplate::find($id);

        if( !$temp ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('template.not_exist'),
                'result' => '',
            ]);
        }

        $re = Agent::where($where)->update(['t_id'=>$a_id]);

        if( $re !== false ) {
            @addLog(['action_name'=>'厅主模板选择','action_desc'=>'给厅主：'.$agent->user_name.'选择了一套 '.$temp->title.'模板','action_passivity'=>$agent->user_name]);

            return $this->response->array([
                'code' => 0,
                'text' => trans('agent.save_success'),
                'result' => '',
            ]);
        }
        return $this->response->array([
            'code' => 400,
            'text' => trans('agent.save_fails'),
            'result' => '',
        ]);
    }
}