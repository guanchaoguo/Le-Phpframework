<?php
/**
 * 游戏厅主文案LOGO.
 * User: chensongjian
 * Date: 2017/4/13
 * Time: 10:03
 */

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\GamePlatformLogo;


class GamePlatformLogoController extends BaseController
{

    /**
     * @api {get} /copywriter/logo 文案-logo 列表
     * @apiDescription 文案-logo 列表
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} p_id 主厅id
     * @apiParam {Number} p_name 主厅登录名
     * @apiParam {Number} type 类型 1：总平台，2厅主
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
        "id": 6,//logo id
        "p_id": 1,//厅主id
        "p_name": '',//厅主名称
        "title": "test_title",//LOGO标题
        "label": 1,//所属平台,0为视讯PC，1为视讯H5，2为视讯APP
        "logo": "images/12121.jpg",//logo图片地址
        "add_date": "2017-04-13 15:16:19",//添加时间
        "update_date": "2017-04-13 15:16:19",//修改时间
        "status": 0,//审核状态，0：未审核，1：已审核，2：审核不通过
        "is_use": 0,//启用状态：0 未使用，1已使用
        "full_logo": "http://192.168.31.230:8000/images/12121.jpg",//全路径图片地址
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

        $p_id = (int)$request->input('p_id');
        $p_name = $request->input('p_name');
        $type = (int) $request->input('type');
        $is_page = (int)$request->input('is_page', 1);
        $page    = (int)$request->input('page', 1);
        $page_num = (int)$request->input('page_num', env('PAGE_NUM', 10));

        $db = GamePlatformLogo::select('*',\DB::raw('CONCAT("'.env('IMAGE_HOST').'", logo) AS full_logo'));

        switch ($type) {
            case 1:

                $db->where('p_id', 0);
                $db->orderby('is_use', 'desc');
                $db->orderby('label', 'asc');
                $db->orderby('add_date', 'desc');
                break;

            case 2:
                if( isset( $p_id ) && ! empty( $p_id )) {

                    $db->where('p_id', $p_id);

                } else {

                    $db->where('p_id', '!=', 0);

                }

                if( isset( $p_name ) && ! empty( $p_name ) ) {
                    $db->where('p_name', $p_name);
                }
                $db->orderby('status', 'asc');
                $db->orderby('add_date', 'desc');
                break;

            default :
                return $this->response->array([
                    'code' => 400,
                    'text' => trans('agent.param_error'),
                    'result' => '',
                ]);
        }



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
     * @api {post} /copywriter/logo 文案-logo 添加
     * @apiDescription 文案-logo 添加
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} title LOGO描述信息 *
     * @apiParam {Number} label 所属平台,0为视讯PC，1为视讯H5，2为视讯APP *
     * @apiParam {Number} logo logo图片地址 格式：images/12121.jpg *
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function store(Request $request)
    {
        $title = $request->input('title');
        $label = (int)$request->input('label');
        $logo = $request->input('logo');

        $message = [
            'p_id.unique' => trans('agent.hall_has_data'),
            'logo.required' => trans('copywriter.logo_required'),
            'label.required' => trans('copywriter.label.required'),
            'label.in' => trans('copywriter.label.in'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required',
            'label' => 'required|in:0,1,2',
            'logo' => 'required',
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
            'label' => $label,
            'logo' => $logo,
            'add_date' => date('Y-m-d H:i:s', time()),
            'status' => 1,//总平台添加logo是不需要审核，状态置为1
        ];
        $re = GamePlatformLogo::create($attributes);

        if( $re ) {
            @addLog(['action_name'=>'总平台添加文案Logo','action_desc'=> \Auth::user()->user_name.'添加了一张文案Logo','action_passivity'=>'文案']);

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
     * @api {get} /copywriter/logo/{id} 文案-logo 详情
     * @apiDescription 文案-logo 详情
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
            "id": 6,//logo id
            "p_id": 1,//厅主id
            "p_name":''//厅主登录名
            "title": "test_title",//LOGO标题
            "label": 1,//所属平台,0为视讯PC，1为视讯H5，2为视讯APP
            "logo": "images/12121.jpg",//logo图片地址
            "add_date": "2017-04-13 15:16:19",//添加时间
            "update_date": "2017-04-13 15:16:19",//修改时间
            "status": 0,//审核状态，0：未审核，1：已审核，2：审核不通过
            "is_use": 0,//启用状态：0 未使用，1已使用
            "full_logo": "http://192.168.31.230:8000/images/12121.jpg",//图片全路径
            "agent": {//厅主信息
            "user_name": "csj"//厅主名称
            }
        }
        }
     */
    public function show(Request $request, int $id)
    {
        $data = GamePlatformLogo::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }
        $data->full_logo = env('IMAGE_HOST').$data->logo;
        $data->agent;
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * @api {put} /copywriter/logo/{id} 文案-logo 编辑
     * @apiDescription 文案-logo 编辑
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} title LOGO描述信息 *
     * @apiParam {Number} label 所属平台,0为视讯PC，1为视讯H5，2为视讯APP *
     * @apiParam {Number} logo logo图片地址 格式：images/12121.jpg *
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function update(Request $request, int $id)
    {
        $title =$request->input('title');
        $label = (int)$request->input('label');
        $logo = $request->input('logo');

        $data = GamePlatformLogo::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }


        $message = [
            'p_id.unique' => trans('agent.hall_has_data'),
            'logo.required' => trans('copywriter.logo_required'),
            'label.required' => trans('copywriter.label.required'),
            'label.in' => trans('copywriter.label.in'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required',
            'label' => 'required|in:0,1,2',
            'logo' => 'required',
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
            'label' => $label,
            'logo' => $logo,
            'update_date' => date('Y-m-d H:i:s', time()),
        ];
        $re = GamePlatformLogo::where('id', $id)->update($attributes);
        if( $re !== false ) {

            @addLog(['action_name'=>'总平台编辑文案Logo','action_desc'=> \Auth::user()->user_name.'文案Banner进行了编辑','action_passivity'=>'文案']);

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
     * @api {patch} /copywriter/logo/{id}/isUse 文案-logo 启用&禁用
     * @apiDescription 文案-banner 启用&禁用
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} is_use 是否启用 1：启用 ，0：禁用
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function isUse(Request $request, int $id) {
        $is_use = $request->input('is_use');

        if( ! in_array($is_use, [0,1]) ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.param_error'),
                'result' => '',
            ]);
        }

        $data = GamePlatformLogo::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }
        //logo只能有一条数据为启用，若要启用，先把之前的已启用状态置为0
        $is_use && GamePlatformLogo::where('p_id', 0)->where('is_use', 1)->where('label', $data['label'])->update(['is_use' => 0]);

        $re = GamePlatformLogo::where('id', $id)->update(['is_use' => $is_use]);
        $use_title = $is_use == 1 ? '启用' : '禁用';

        if( $re !== false ) {
            @addLog(['action_name'=>'总平台'.$use_title.'了文案Logo','action_desc'=> \Auth::user()->user_name.$use_title.'了文案Logo，ID为：'.$id,'action_passivity'=>'文案']);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
                'result' => '',
            ]);

        }

        return $this->response->array([
            'code' => 400,
            'text' =>trans('agent.save_fails'),
            'result' => '',
        ]);


    }

    /**
     * @api {patch} /copywriter/logo/{id}/review 文案-logo 审核
     * @apiDescription 文案-logo 审核
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} status 审核状态 1：通过，2不通过
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function review(Request $request, int $id)
    {
        $status = $request->input('status');

        if( ! in_array($status, [1,2]) ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.param_error'),
                'result' => '',
            ]);
        }
        $data = GamePlatformLogo::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $up_data = ['status' => $status];
        $status != 1 && $up_data['is_use'] = 0;

        $re = GamePlatformLogo::where('id',$id)->update($up_data);
        if($re !== false) {
            $status_title = $status == 1 ? '审核通过' : '审核不通过';
            @addLog(['action_name'=>'总平台'.$status_title.'文案Logo','action_desc'=> \Auth::user()->user_name.$status_title.'文案Logo，ID为：'.$id,'action_passivity'=>'文案']);

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
     * @api {delete} /copywriter/logo/{id} 文案-logo 删除
     * @apiDescription 文案-logo 删除
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
        $data = GamePlatformLogo::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $re = GamePlatformLogo::destroy($id);
        if( !$re ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.fails'),
                'result' => '',
            ]);
        }
        @addLog(['action_name'=>'编辑文案Logo','action_desc'=>'对文案Logo进行了删除，ID为：'.$id,'action_passivity'=>'文案']);

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => '',
        ]);
    }
}