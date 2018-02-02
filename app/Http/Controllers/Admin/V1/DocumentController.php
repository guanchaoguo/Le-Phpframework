<?php
/**
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/6/19
 * Time: 14:23
 */

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\GamePlatformDocument;

class DocumentController extends BaseController
{

    /**
     * @api {get} /document 文档管理列表
     * @apiDescription  文档管理列表
     * @apiGroup document
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
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
            "title": '',//文档名称
            "size": "",//文档大小
            "path": "",//文档保存相对路径
            "desc": "",//文档备注描述
            "add_time": "2017-04-17 11:13:41",//添加时间
            "full_path":""//文档全路径
        }
    ]
    }
    }
     */
    public function index(Request $request)
    {

        $is_page = (int) $request->input('is_page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));

        $obj = GamePlatformDocument::select('*', \DB::raw('CONCAT("'.env('IMAGE_HOST').'", path) AS full_path'));
        $obj->orderby('add_time', 'desc');

        if( $is_page ) {
            $data = $obj->paginate($page_num);
        } else {
            $data = $obj->get();
        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' =>  $is_page ? $data : ['data' => $data],
        ]);
    }

    /**
     * @api {post} /document 添加文档管理
     * @apiDescription  添加文档管理
     * @apiGroup document
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token *
     * @apiParam {String} locale 语言 *
     * @apiParam {String} title 文档名称 *
     * @apiParam {String} size 文档大小 *
     * @apiParam {String} path 文档路径 *
     * @apiParam {String} desc 文档备注描述
     * @apiSuccessExample {json} 成功返回：
     * {
        "code": 0,
        "text": "操作成功",
        "result": ""
        }
     */
    public function store(Request $request)
    {
        $title = $request->input('title');
        $size = $request->input('size');
        $path = $request->input('path');
        $desc = $request->input('desc');

        $message = [
            'title.required' => trans('document.title.required'),
            'size.required' => trans('document.size.required'),
            'path.required' => trans('document.path.required'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required',
            'size' => 'required',
            'path' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $attributes = [
            'title' => $title,
            'size' => $size,
            'path' => $path,
            'desc' => $desc,
            'add_time' => date('Y-m-d H:i:s', time()),
        ];

        $re = GamePlatformDocument::create($attributes);

        if( $re ) {
            @addLog(['action_name'=>'添加文档','action_desc'=> \Auth::user()->user_name.'添加了一个文档','action_passivity'=>'文档管理']);

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
     * @api {get} /document/{id} 获取文档详情
     * @apiDescription  获取文档详情
     * @apiGroup document
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} Success-Response:
     * {
        "code": 0,
        "text": "操作成功",
        "result": {
            "id": 1,//id
            "title": '',//文档名称
            "size": "",//文档大小
            "path": "",//文档保存相对路径
            "desc": "",//文档备注描述
            "add_time": "2017-04-17 11:13:41",//添加时间
        }
      }
     */
    public function show(Request $request, int $id)
    {
        $data = GamePlatformDocument::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * @api {put} /document/{id} 编辑文档管理
     * @apiDescription  编辑文档管理
     * @apiGroup document
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token *
     * @apiParam {String} locale 语言 *
     * @apiParam {String} title 文档名称 *
     * @apiParam {String} size 文档大小 *
     * @apiParam {String} path 文档路径 *
     * @apiParam {String} desc 文档备注描述
     * @apiSuccessExample {json} 成功返回：
     * {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function update(Request $request, int $id)
    {
        $title = $request->input('title');
        $size = $request->input('size');
        $path = $request->input('path');
        $desc = $request->input('desc');

        $data = GamePlatformDocument::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $message = [
            'title.required' => trans('document.title.required'),
            'size.required' => trans('document.size.required'),
            'path.required' => trans('document.path.required'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required',
            'size' => 'required',
            'path' => 'required',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $attributes = [
            'title' => $title,
            'size' => $size,
            'path' => $path,
            'desc' => $desc,
            'add_time' => date('Y-m-d H:i:s', time()),
        ];

        $re = GamePlatformDocument::where('id', $id)->update($attributes);

        if( $re !== false ) {

            @addLog(['action_name'=>'编辑文档','action_desc'=> \Auth::user()->user_name.'编辑了一个文档，文档ID：'.$id,'action_passivity'=>'文档管理']);

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
     * @api {delete} /document/{id} 删除文档
     * @apiDescription 删除文档
     * @apiGroup document
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
        $data = GamePlatformDocument::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $re = GamePlatformDocument::destroy($id);
        if( !$re ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.fails'),
                'result' => '',
            ]);
        }
        @addLog(['action_name'=>'删除文档','action_desc'=> \Auth::user()->user_name.'对文档进行了删除，ID为：'.$id,'action_passivity'=>'文档管理']);

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => '',
        ]);
    }
}