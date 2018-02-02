<?php
/**
 * 游戏版本更新控制器
 * User: chensongjian
 * Date: 2017/6/16
 * Time: 11:27
 */

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\GameVersion;

class GameVersionController extends BaseController
{

    /**
     * @api {get} /gameVersion 在线更新
     * @apiDescription  版本更新列表
     * @apiGroup gameVersion
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} label 游戏平台,0为视讯PC，1为视讯H5，2为视讯APP，默认0
     * @apiParam {String} version_n 版本号
     * @apiParam {Number} forced_up 是否强制更新，0否，1是，默认0
     * @apiParam {String} start_time 开始时间
     * @apiParam {String} end_time 结束时间
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
        "id": 1,//id
        "label": 0,//游戏平台,0为视讯PC，1为视讯H5，2为视讯APP
        "version_n": "",//版本号
        "url": "",//url地址
        "forced_up": 0,//是否强制更新，0否，1是，默认0
        "content": "",//更新说明
        "add_time": "2017-04-17 11:13:41",//添加时间
        "update_time": "2017-04-17 11:13:41",//更新时间
        "user_update_time": "2017-04-17 11:13:41",//用户本地更新时间
        }
        ]
        }
        }
     */
    public function  index(Request $request)
    {
        $label = $request->input('label');
        $version_n = $request->input('version_n');
        $forced_up = $request->input('forced_up');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $is_page = (int) $request->input('is_page', 1);
        $page    = (int) $request->input('page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));

        $obj = GameVersion::select('*');

        if( isset($label) && $label !== '' ) {
            $obj->where('label', $label);
        }

        if( isset($version_n) && ! empty($version_n) ) {
            $obj->where('version_n', $version_n);
        }

        if( isset($forced_up) && $forced_up !== '' ) {
            $obj->where('forced_up', $forced_up);
        }

        if( isset($start_time) && ! empty($start_time) ) {
            $obj->where('update_time', '>=', $start_time);
        }

        if( isset($end_time) && ! empty($end_time) ) {
            $obj->where('update_time', '<=', $end_time);
        }

        $obj->orderby('label', 'asc');
        $obj->orderby('add_time', 'desc');

        if( $is_page ) {
            $data = $obj->paginate($page_num);

        } else {
            $data = $obj->get();

        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' =>  $is_page ? $data : ['data'=>$data],
        ]);
    }


    /**
     * @api {post} /gameVersion 添加版本更新
     * @apiDescription  添加版本更新
     * @apiGroup gameVersion
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} label 游戏平台,0为视讯PC，1为视讯H5，2为视讯APP ，默认0
     * @apiParam {String} update_time 更新时间
     * @apiParam {String} user_update_time 用户本地更新时间
     * @apiParam {String} content 更新内容
     * @apiParam {String} version_n 版本号
     * @apiParam {String} url url地址
     * @apiParam {Number} forced_up 是否强制更新，0否，1是，默认0
     * @apiSuccessExample {json} Success-Response:
     * {
        "code": 0,
        "text": "操作成功",
        "result": ""
        }
     */
    public function store(Request $request)
    {
        $label = $request->input('label');
        $update_time = $request->input('update_time');
        $user_update_time = $request->input('user_update_time');
        $content = $request->input('content');
        $version_n = $request->input('version_n');
        $url = $request->input('url');
        $forced_up = $request->input('forced_up');

        $message = [
            'label.required' => trans('gameversion.label.required'),
            'label.in' => trans('gameversion.label.in'),
            'update_time.required' => trans('gameversion.update_time.required'),
            'user_update_time.required' => trans('gameversion.user_update_time.required'),
            'content.required' => trans('gameversion.content'),
            'version_n.required' => trans('gameversion.version_n'),
            'url.required' => trans('gameversion.url'),
            'forced_up.required' => trans('gameversion.forced_up.required'),
            'forced_up.in' => trans('gameversion.forced_up.in'),
        ];
        $validator = \Validator::make($request->input(), [
            'label' => 'required|in:0,1,2',
            'update_time' => 'required',
            'user_update_time' => 'required',
            'content' => 'required',
            'version_n' => 'required',
            'url' => 'required',
            'forced_up' => 'required|in:0,1',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        //url验证
        if( $url && !filter_var($url,FILTER_VALIDATE_URL) ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.domain_error'),
                'result'=>'',
            ]);
        }

        $attributes = [
            'label' => $label,
            'update_time' => $update_time,
            'user_update_time' => $user_update_time,
            'content' => $content,
            'version_n' => $version_n,
            'url' => $url,
            'forced_up' => $forced_up,
            'add_time' => date('Y-m-d H:i:s', time()),
        ];

        $re = GameVersion::create($attributes);
        
        if( $re ) {
            @addLog(['action_name'=>'添加版本更新','action_desc'=> \Auth::user()->user_name.'添加了一条版本更新','action_passivity'=>'版本更新']);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
                'result' => '',
            ]);

        }

        return $this->response->array([
            'code' => 400,
            'text' =>trans('agent.fails'),
            'result' => '',
        ]);
    }

    /**
     * @api {get} /gameVersion/{id} 版本更新详情
     * @apiDescription  版本更新详情
     * @apiGroup gameVersion
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
        "id": 1,//id
        "label": 0,//游戏平台,0为视讯PC，1为视讯H5，2为视讯APP
        "version_n": "",//版本号
        "url": "",//url地址
        "forced_up": 0,//是否强制更新，0否，1是，默认0
        "content": "",//更新说明
        "add_time": "2017-04-17 11:13:41",//添加时间
        "update_time": "2017-04-17 11:13:41",//更新时间
        "user_update_time": "2017-04-17 11:13:41",//用户本地更新时间
    }
    }
    }
     */
    public function show(Request $request, int $id)
    {
        $data = GameVersion::find($id);
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' =>  $data,
        ]);
    }

    /**
     * @api {put} /gameVersion/{id} 编辑版本更新
     * @apiDescription  编辑版本更新
     * @apiGroup gameVersion
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} label 游戏平台,0为视讯PC，1为视讯H5，2为视讯APP ，默认0
     * @apiParam {String} update_time 更新时间
     * @apiParam {String} user_update_time 用户本地更新时间
     * @apiParam {String} content 更新内容
     * @apiParam {String} version_n 版本号
     * @apiParam {String} url url地址
     * @apiParam {Number} forced_up 是否强制更新，0否，1是，默认0
     * @apiSuccessExample {json} Success-Response:
     * {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function update(Request $request, int $id)
    {

        $data = GameVersion::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $label = $request->input('label');
        $update_time = $request->input('update_time');
        $user_update_time = $request->input('user_update_time');
        $content = $request->input('content');
        $version_n = $request->input('version_n');
        $url = $request->input('url');
        $forced_up = $request->input('forced_up');

        $message = [
            'label.required' => trans('gameversion.label.required'),
            'label.in' => trans('gameversion.label.in'),
            'update_time.required' => trans('gameversion.update_time.required'),
            'user_update_time.required' => trans('gameversion.user_update_time.required'),
            'content.required' => trans('gameversion.content'),
            'version_n.required' => trans('gameversion.version_n'),
            'url.required' => trans('gameversion.url'),
            'forced_up.required' => trans('gameversion.forced_up.required'),
            'forced_up.in' => trans('gameversion.forced_up.in'),
        ];
        $validator = \Validator::make($request->input(), [
            'label' => 'required|in:0,1,2',
            'update_time' => 'required',
            'user_update_time' => 'required',
            'content' => 'required',
            'version_n' => 'required',
            'url' => 'required',
            'forced_up' => 'required|in:0,1',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        //url验证
        if( $url && !filter_var($url,FILTER_VALIDATE_URL) ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.domain_error'),
                'result'=>'',
            ]);
        }

        $attributes = [
            'label' => $label,
            'update_time' => $update_time,
            'user_update_time' => $user_update_time,
            'content' => $content,
            'version_n' => $version_n,
            'url' => $url,
            'forced_up' => $forced_up,
//            'add_time' => date('Y-m-d H:i:s', time()),
        ];

        $re = GameVersion::where('id', $id)->update($attributes);
        if( $re !== false ) {
            @addLog(['action_name'=>'编辑版本更新','action_desc'=> \Auth::user()->user_name.'编辑了一条版本更新','action_passivity'=>'版本更新']);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
                'result' => '',
            ]);

        }

        return $this->response->array([
            'code' => 400,
            'text' =>trans('agent.fails'),
            'result' => '',
        ]);
    }

    /**
     * @api {delete} /gameVersion/{id} 删除版本更新
     * @apiDescription  删除版本更新
     * @apiGroup gameVersion
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     * {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function delete(Request $request, int $id)
    {
        $data = GameVersion::find($id);

        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $re = GameVersion::destroy($id);
        if( ! $re ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.fails'),
                'result' => '',
            ]);
        }
        @addLog(['action_name'=>'删除版本更新','action_desc'=>'对版本更新进行了删除，ID为：'.$id,'action_passivity'=>'版本更新']);

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => '',
        ]);
    }
}