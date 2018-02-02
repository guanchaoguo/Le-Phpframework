<?php
/**
 * 文案Banner.
 * User: chensongjian
 * Date: 2017/4/17
 * Time: 10:19
 */

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\Agent;
use App\Models\GamePlatformBanner;

class GamePlatformBannerController extends BaseController
{
    /**
     * @api {get} /copywriter/banner 文案-banner列表
     * @apiDescription 文案-banner列表
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} p_id 厅主id
     * @apiParam {String} p_name 厅主登录名
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
    "id": 1,//banner id
    "p_id": 1,//厅主id
    "p_name": '',//厅主名称
    "title": "test_title",//标题
    "play_type": 0,//banner的轮播方式，0为从左到右，1为从右到左，默认为0左到右
    "label": 1,//所属平台,0为视讯PC，1为视讯H5，2为视讯APP
    "banner": "images/12121.jpg",//banner图片地址
    "add_date": "2017-04-17 11:13:41",//添加时间
    "update_date": "2017-04-17 11:13:41",//修改时间
    "status": 0,//审核状态，0：未审核，1：已审核，2：审核不通过
    "is_use": 0,//启用状态：0 未使用，1已使用
    "sort": 1,//排序：数字越小越靠前
    "full_banner": "http://192.168.31.230:8000/images/12121.jpg",//全路径图片地址
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

        $type = (int) $request->input('type');
        $p_id = (int) $request->input('p_id');
        $p_name =  $request->input('p_name');
        $is_page = (int) $request->input('is_page', 1);
        $page    = (int) $request->input('page', 1);
        $page_num = (int) $request->input('page_num', env('PAGE_NUM', 10));

        $db = GamePlatformBanner::select('*',\DB::raw('CONCAT("'.env('IMAGE_HOST').'", banner) AS full_banner'));

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
     * @api {post} /copywriter/banner 文案-banner 添加
     * @apiDescription 文案-banner 添加
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} title 标题 *
     * @apiParam {Number} play_type 轮播方式，0为从左到右，1为从右到左，默认为0左到右 *
     * @apiParam {Number} label 所属平台,0为视讯PC，1为视讯H5，2为视讯APP*
     * @apiParam {String} banner banner图片地址 格式：images/12121.jpg *
     * @apiParam {String} url banner跳转地址 格式：http://baidu.com *
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
        $play_type = (int) $request->input('play_type');
        $label = (int) $request->input('label');
        $banner = $request->input('banner');
        $url = $request->input('url');



        $message = [
            'banner.required' => trans('copywriter.logo_required'),
            'label.required' => trans('copywriter.label.required'),
            'label.in' => trans('copywriter.label.in'),
            'play_type.required' => trans('copywriter.play_type.required'),
            'play_type.in' => trans('copywriter.play_type.in'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required',
//            'play_type' => 'required|in:0,1',
            'label' => 'required|in:0,1,2',
            'banner' => 'required',
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
            'title' => $title,
            'play_type' => $play_type,
            'label' => $label,
            'banner' => $banner,
            'url' => $url,
            'add_date' => date('Y-m-d H:i:s', time()),
            'status' => 1,//总平台添加banner是不需要审核，状态置为1
        ];

        $re = GamePlatformBanner::create($attributes);

        if( $re ) {
            @addLog(['action_name'=>'总平台添加文案Banner','action_desc'=> \Auth::user()->user_name.'添加了一张文案Banner','action_passivity'=>'文案']);

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
     * @api {get} /copywriter/banner/{id} 文案-banner 详情
     * @apiDescription 文案-banner 详情
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
        "id": 1,//banner id
        "p_id": 1,//厅主id
        "p_name": '',//厅主登录名
        "title": "test_title",//标题
        "play_type": 0,//banner的轮播方式，0为从左到右，1为从右到左，默认为0左到右
        "label": 1,//所属平台,0为视讯PC，1为视讯H5，2为视讯APP
        "banner": "images/12121.jpg",//banner图片地址
        "add_date": "2017-04-17 11:13:41",//添加时间
        "update_date": "2017-04-17 11:13:41",//修改时间
        "status": 0,//审核状态，0：未审核，1：已审核，2：审核不通过
        "is_use": 0,//启用状态：0 未使用，1已使用
        "sort": 1,//排序：数字越小越靠前
        "full_banner": "http://192.168.31.230:8000/images/12121.jpg",//全路径图片地址
        "agent": {//厅主信息
        "user_name": "csj"//厅主名称
        }
        }
        }
     */
    public function show(Request $request, int $id)
    {
        $data = GamePlatformBanner::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }
        $data->full_banner = env('IMAGE_HOST').$data->banner;
        $data->agent;
        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => $data,
        ]);
    }

    /**
     * @api {put} /copywriter/banner/{id} 文案-banner 编辑
     * @apiDescription 文案-banner 编辑
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} title 标题 *
     * @apiParam {Number} play_type banner的轮播方式，0为从左到右，1为从右到左，默认为0左到右 *
     * @apiParam {Number} label 所属平台,0为视讯PC，1为视讯H5，2为视讯APP *
     * @apiParam {String} banner banner图片地址 格式：images/12121.jpg *
     * @apiParam {String} url banner跳转地址 格式：http://baidu.com *
     * @apiSuccessExample {json} Success-Response:
        {
            "code": 0,
            "text": "操作成功",
            "result": ""
        }
     */
    public function update(Request $request, int $id)
    {
        $title = $request->input('title');
        $play_type = (int) $request->input('play_type');
        $label = (int) $request->input('label');
        $banner = $request->input('banner');
        $url = $request->input('url');
        $data = GamePlatformBanner::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $message = [
            'banner.required' => trans('copywriter.logo_required'),
            'label.required' => trans('copywriter.label.required'),
            'label.in' => trans('copywriter.label.in'),
            'play_type.required' => trans('copywriter.play_type.required'),
            'play_type.in' => trans('copywriter.play_type.in'),
        ];
        $validator = \Validator::make($request->input(), [
            'title' => 'required',
//            'play_type' => 'required|in:0,1',
            'label' => 'required|in:0,1,2',
            'banner' => 'required',
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
            'title' => $title,
            'play_type' => $play_type,
            'label' => $label,
            'banner' => $banner,
            'url' => $url,
            'update_date' => date('Y-m-d H:i:s', time()),
        ];

        $re = GamePlatformBanner::where('id', $id)->update($attributes);

        if( $re !== false ) {
            @addLog(['action_name'=>'总平台编辑文案Banner','action_desc'=> \Auth::user()->user_name.'文案Banner进行了编辑','action_passivity'=>'文案']);

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
     * @api {delete} /copywriter/banner/{id} 文案-banner 删除
     * @apiDescription 文案-banner 删除
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
        $data = GamePlatformBanner::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $re = GamePlatformBanner::destroy($id);
        if( !$re ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.fails'),
                'result' => '',
            ]);
        }
        @addLog(['action_name'=>'删除文案Banner','action_desc'=> \Auth::user()->user_name.'对文案Banner进行了删除，ID为：'.$id,'action_passivity'=>'文案']);

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => '',
        ]);
    }

    /**
     * @api {patch} /copywriter/banner/{id}/isUse 文案-banner 启用&禁用
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

        $data = GamePlatformBanner::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }
        $use_num = GamePlatformBanner::where('p_id', 0)->where('is_use', 1)->where('label',$data['label'])->count();
        
        if( $is_use && $use_num >= 5 ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.banner_use_num'),
                'result' => '',
            ]);
        }

        $re = GamePlatformBanner::where('id', $id)->update(['is_use' => $is_use]);
        $use_title = $is_use == 1 ? '启用' : '禁用';
        if( $re !== false ) {
            @addLog(['action_name'=>'总平台'.$use_title.'了文案Banner','action_desc'=> \Auth::user()->user_name.$use_title.'了文案Banner，ID为：'.$id,'action_passivity'=>'文案']);

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
     * @api {patch} /copywriter/banner/{id}/review 文案-banner 审核
     * @apiDescription 文案-banner 审核
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
        $data = GamePlatformBanner::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }

        $up_data = ['status' => $status];
        $status != 1 && $up_data['is_use'] = 0;
        $re = GamePlatformBanner::where('id',$id)->update($up_data);
        if($re !== false) {
            $status_title = $status == 1 ? '审核通过' : '审核不通过';
            @addLog(['action_name'=>'总平台'.$status_title.'文案Banner','action_desc'=> \Auth::user()->user_name.$status_title.'文案Banner，ID为：'.$id,'action_passivity'=>'文案']);

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
     * @api {patch} /copywriter/banner/{id}/sort 文案-banner 排序
     * @apiDescription 文案-banner 排序
     * @apiGroup copywriter
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} sort 排序 数字越小越靠前
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function sort(Request $request, int $id)
    {
        $sort = $request->input('sort');

        $data = GamePlatformBanner::find($id);
        if( ! $data ) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('copywriter.data_not_exist'),
                'result' => '',
            ]);
        }


        $re = GamePlatformBanner::where('id',$id)->update(['sort' => $sort]);
        if($re !== false) {

            @addLog(['action_name'=>'文案Banner排序','action_desc'=> \Auth::user()->user_name.'对文案Banner排序，ID为：'.$id,'action_passivity'=>'文案']);

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
}