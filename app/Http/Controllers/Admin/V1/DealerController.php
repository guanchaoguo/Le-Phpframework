<?php
/**
 * 荷官管理控制器
 * User: chensongjian
 * Date: 2017/10/13
 * Time: 11:23
 */

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\Dealer;

class DealerController extends BaseController
{

    /**
     * @api {get} /dealer 荷官列表
     * @apiDescription 荷官列表
     * @apiGroup dealer
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} dealer 荷官ID
     * @apiParam {String} dealer_name 荷官名称
     * @apiParam {String} page_num 每页显示条数 默认10
     * @apiParam {String} page 当前第几页 默认1
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 2,
    "per_page": "10",
    "current_page": 1,
    "last_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 2,
    "data": [
    {
    "id": 2,
    "dealer": "333333333",//荷官ID
    "dealer_name": "w",//荷官名称
    "dealer_img": "upload/img/dealer/222323233434.jpg",//荷官图片（相对路径）
    "last_update": "2017-10-17 01:24:52",//最后更新时间
    "full_dealer_img": "http://images.dev/upload/img/dealer/222323233434.jpg"//荷官图片（完整路径）
    }
    ]
    }
    }
     */
    public function index(Request $request)
    {
        $dealer = $request->input('dealer','');
        $dealer_name = $request->input('dealer_name','');
        $page_num = $request->input('page_num',10);
        $db = Dealer::select('*',\DB::raw('CONCAT("'.env('IMAGE_HOST').'", dealer_img) AS full_dealer_img'));

        if( isset($dealer) && $dealer !== '' ) {
            $db->where('dealer', $dealer);
        }

        if( isset($dealer_name) && $dealer_name !== '' ) {
            $db->where('dealer_name', $dealer_name);
        }

        $db->orderby('last_update', 'desc');

        $data = $db->paginate($page_num);

        return $this->response->array([
            'code'=>0,
            'text'=>trans('agent.success'),
            'result'=> $data,
        ]);
    }

    /**
     * @api {post} /dealer 添加荷官
     * @apiDescription 添加荷官
     * @apiGroup dealer
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} dealer 荷官ID
     * @apiParam {String} dealer_name 荷官名称
     * @apiParam {String} dealer_img 荷官图片
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function store(Request $request)
    {
        $dealer = $request->input('dealer');
        $dealer_name = $request->input('dealer_name');
        $dealer_img = $request->input('dealer_img');
        $now_date = date('Y-m-d H:i:s');

        $msg = [
            'dealer.required' => trans('dealer.dealer_id.required'),
            'dealer.unique' => trans('dealer.dealer_id.unique'),
            'dealer_name.required' => trans('dealer.dealer_name.required'),
            'dealer_img.required' => trans('dealer.dealer_img.required'),
        ];
        $validator = \Validator::make($request->input(), [
            'dealer' => 'required|unique:dealer_info',
            'dealer_name' => 'required',
            'dealer_img' => 'required',
        ],$msg);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=> 400,
                'text'=> $validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $insert_data = [
            'dealer' => $dealer,
            'dealer_name' => $dealer_name,
            'dealer_img' => $dealer_img,
            'last_update' => $now_date
        ];

        $res = Dealer::create($insert_data);

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
     * @api {get} /dealer/{id} 获取荷官详情
     * @apiDescription 编辑时获取荷官详情
     * @apiGroup dealer
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
    "dealer": "222323233434",//荷官ID
    "dealer_name": "w",//荷官名称
    "dealer_img": "upload/img/dealer/222323233434.jpg",//荷官图片（相对路径）
    "last_update": "2017-10-16 23:29:24",//最后更新时间
    "full_dealer_img": "http://images.dev/upload/img/dealer/222323233434.jpg"//荷官图片（完整路径）
    }
    }
     */
    public function show($id)
    {
        $res = Dealer::select('*', \DB::raw('CONCAT("'.env('IMAGE_HOST').'", dealer_img) AS full_dealer_img'))->find($id);

        return $this->response->array([
            'code'=> 0,
            'text'=> trans('agent.success'),
            'result'=> $res,
        ]);
    }

    /**
     * @api {put} /dealer/{id} 保存荷官
     * @apiDescription 保存荷官
     * @apiGroup dealer
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} dealer 荷官ID
     * @apiParam {String} dealer_name 荷官名称
     * @apiParam {String} dealer_img 荷官图片
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function update(Request $request, $id)
    {
        $info = Dealer::find($id);
        if(  ! $info ) {
            return $this->response->array([
                'code'=> 400,
                'text'=> trans('hall_message.data_not_exist'),
                'result'=> '',
            ]);
        }

//        $dealer = $request->input('dealer');
        $dealer_name = $request->input('dealer_name');
        $dealer_img = $request->input('dealer_img');
        $now_date = date('Y-m-d H:i:s');

        $msg = [
            'dealer.required' => trans('dealer.dealer_id.required'),
            'dealer.unique' => trans('dealer.dealer_id.unique'),
            'dealer_name.required' => trans('dealer.dealer_name.required'),
            'dealer_img.required' => trans('dealer.dealer_img.required'),
        ];
        $validator = \Validator::make($request->input(), [
//            'dealer' => 'required|unique:dealer_info,dealer,'.$id,
            'dealer_name' => 'required',
            'dealer_img' => 'required',
        ],$msg);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=> 400,
                'text'=> $validator->errors()->first(),
                'result'=> '',
            ]);
        }

        $update_data = [
//            'dealer' => $dealer,
            'dealer_name' => $dealer_name,
            'dealer_img' => $dealer_img,
            'last_update' => $now_date
        ];

        $res = Dealer::where('id', $id)->update($update_data);

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
     * @api {delete} /dealer/{id} 删除荷官
     * @apiDescription 删除荷官
     * @apiGroup dealer
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
    public function delete($id)
    {
        $info = Dealer::find($id);
        if(  ! $info ) {
            return $this->response->array([
                'code'=> 400,
                'text'=> trans('hall_message.data_not_exist'),
                'result'=> '',
            ]);
        }

        $res = Dealer::where('id', $id)->delete();

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