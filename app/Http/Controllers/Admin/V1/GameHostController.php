<?php
/**
 * Created by PhpStorm.
 * User: guanc
 * Date: 2017/11/21
 * Time: 14:20
 */
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use App\Models\GameHost;

class GameHostController extends BaseController
{
    /**
     * @api {get} /gamehost  修改游戏入口域名列表
     * @apiDescription  域名列表
     * @apiGroup gameHost
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": [
    {
    "id": 1,
    "host_type": "1",
    "host_url": "http://lebogame-pc-22.dev/game.php",
    "status": 1,
    "add_time": "-0001-11-30 00:00:00",
    "update_time": "-0001-11-30 00:00:00"
    },
    {
    "id": 2,
    "host_type": "2", // 域名类型 1 pc 2 h5
    "host_url": "http://lebogame-22.dev",// 域名地址
    "status": 1,// 状态 1 启用 0 禁用
    "add_time": "-0001-11-30 00:00:00", //创建时间
    "update_time": "-0001-11-30 00:00:00" //开始时间
    },
    {
    "id": 3,
    "host_type": "1",
    "host_url": "http://platform-fore-22.dev/?",
    "status": 1,
    "add_time": "-0001-11-30 00:00:00",
    "update_time": "2017-11-21 05:05:13"
    }
    ]
    }
     */
    public function index(Request $request)
    {
        $page_num = (int)$request->input('page_num',10);

        $db = GameHost::select();
        $data = $db->paginate($page_num);

        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('message.success'),
            'result'    => $data
        ]);
    }

    /**
     * @api {get} /gamehost/{id} 查看单个域名信息
     * @apiDescription   查看单个域名信息
     * @apiGroup gameHost
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": [
    {
    "id": 1,
    "host_type": "1",
    "host_url": "http://lebogame-pc-22.dev/game.php",
    "status": 1,
    "add_time": "-0001-11-30 00:00:00",
    "update_time": "-0001-11-30 00:00:00"`
    },
    ]
    }
     */
    public function show(Request $request,$id)
    {
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('message.success'),
            'result'    =>   GameHost::find($id)
        ]);
    }

    /**
     * @api {put} /gamehost/{$id}  修改游戏入域名
     * @apiDescription  修改游戏入口域名
     * @apiGroup gameHost
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} host_url  域名地址
     * @apiParam {numeric} host_type 域名类型 1 pc 2 h5
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function update(Request $request,$id)
    {
        if( !$gameHostFind = GameHost::find($id)) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);

        }

        $message = ['host_url.active_url' => trans('gamehost.host'),];
        $validator = \Validator::make($request->input(), [
            'host_url' => 'required|active_url',
            'host_type' => 'required|numeric|min:0',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $type = $request->input('host_type');
        $host = $request->input('host_url');
        $setData = ['host_url'=>$host,'host_type'=> $type];
        $res = $gameHostFind->where(['id'=>$id])->update($setData);
        if(!$res) {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('message.fails'),
                'result'    => ''
            ]);
        }

        //修改启动中的域名则更新缓存
        if( 1 ==  $gameHostFind->status)
        {
            $redis = Redis::connection("default");
            $redis->hset('GAMEHOST:URL',$type ,$host);
        }


        //success
        @addLog(['action_name'=>'修改游戏入口域名','action_desc'=>' 对游戏入口域名 '.$gameHostFind->host_url.' 进行了修改','action_passivity'=>'游戏域名列表']);
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('message.success'),
            'result'    => ''
        ]);

    }

    /**
     * @api {put} /gamehost/status/{id}  修改游戏入口域名状态
     * @apiDescription  修改游戏入口域名
     * @apiGroup gameHost
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {numeric} status  状态 1 启用 0 禁用
     * @apiParam {numeric} host_type 域名类型 1 pc 2 h5
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function status(Request $request,$id)
    {
        if( !$gameHostFind = GameHost::find($id)) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.agent_not_exist'),
                'result' => '',
            ]);

        }

        // 校验是否已经有同类型的启动状态只能启用一个
        $type = $request->input('host_type');
        $status = $request->input('status');
        if($status == 1){
            $where = ['host_type'=>$type ,'status'=>$status];
            if( $res = GameHost::where($where)->where('id','<>',$id)->get()->toArray()){
                return $this->response->array([
                    'code' => 400,
                    'text' => trans('gamehost.status_is_up'),
                    'result' => '',
                ]);
            }
        }

        $res = $gameHostFind->where(['id'=>$id])->update(['status'=>$request->input('status')]);
        if(!$res) {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('message.fails'),
                'result'    => ''
            ]);
        }

        //success
        @addLog(['action_name'=>'修改游戏入口域名状态','action_desc'=>' 对游戏入口域名 '.$gameHostFind->host_url.' 进行了状态修改','action_passivity'=>'游戏域名列表']);
        $redis = Redis::connection("default");


        if($status) {
            $redis->hset('GAMEHOST:URL',$type ,$gameHostFind->host_url);
        } else {
            $redis->hdel('GAMEHOST:URL',$type);
        }
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('message.success'),
            'result'    => ''
        ]);
    }

    /**
     * @api {post} /gamehost  添加游戏入口域名
     * @apiDescription  添加游戏入口域名
     * @apiGroup gameHost
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} host_url  域名地址
     * @apiParam {numeric} host_type 域名类型 1 pc 2 h5
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function store(Request $request)
    {
        $message = ['host_url.active_url' => trans('gamehost.host'),];
        $validator = \Validator::make($request->input(), [
            'host_url' => 'required|active_url',
            'host_type' => 'required|numeric|min:0',
        ],$message);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $setData = ['host_url'=>$request->input('host_url'),'host_type'=>$request->input('host_type')];
        $res = GameHost::insert($setData);
        if(!$res) {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('message.fails'),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'添加游戏入口域名','action_desc'=>' 对游戏入口域名 '.$request->input('host_url').' 进行了添加','action_passivity'=>'游戏域名列表']);
        //success
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('message.success'),
            'result'    => ''
        ]);

    }


    /**
     * @api {delete} /gamehost/{id}  删除游戏入口域名
     * @apiDescription 删除游戏入口域名
     * @apiGroup gameHost
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSuccessExample {json} 成功返回格式
    {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     */
    public function destroy(Request $request,$id)
    {
        $gameHostFind  = GameHost::find($id);
        if(!$gameHostFind)//验证需要删除的数据是否存在
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('message.data_error'),
                'result'    => ''
            ]);
        }

        // 启用中不允许删除
        if( 1 ==  $gameHostFind->status)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('gamehost.data_is_up'),
                'result'    => ''
            ]);
        }

        //进行删除操作
        $res = $gameHostFind->where('id',$id)->delete();
        if(!$res) {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('message.fails'),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'删除游戏入口域名','action_desc'=>' 对游戏入口域名 '.$gameHostFind->host_url.' 进行了删除','action_passivity'=>'游戏域名列表']);

        $redis = Redis::connection("default");

        //$gameHostFind->status && $redis->hdel('GAMEHOST:URL',$gameHostFind->host_type);
        //删除成功
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('message.success'),
            'result'    => ''
        ]);
    }

}