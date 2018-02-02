<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/8
 * Time: 17:38
 * Desc 游戏厅限额控制器
 */
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\HallLimitGroup;
use App\Models\HallLimitItem;
use App\Models\GameCat;
use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HallQuotaController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {get} /hall/quota 游戏厅限额查询
     * @apiDescription 游戏厅限额查询
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} hall_name 厅主名称
     * @apiParam {String} title 标题（设定限额标题）
     * @apiParam {Number} hall_type 厅类型，厅id
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
        {
            "code": 0,
            "text": "操作成功",
            "result": {
                "id": 15,
                "title": "默认限额A",
                "agent_id": 0,
                "status": 1,
                "uptime": "2015-05-06 04:06:23",
                "hall_type": 1,
                "item_type": 2,
                "limit_items": [
                    {
                        "game_name": "百家乐test ",
                        "game_cat_id": 4,
                        "bet_areas": [
                            {
                                "id": 433,
                                "group_id": 15,
                                "game_cat_id": 4,
                                "max_money": 4000,
                                "min_money": 4,
                                "bet_area": 4
                            }
                        ]
                    }
                ]
            }
        }
     *@apiErrorExample {json} Error-Response:
     *     HTTP/1.1 200 OK
         {
            "code": 400,
            "text": {
                "hall_type": [
                    "hall type 不能为空。"
                ]
            },
            "result": ""
        }
     */
    public function index(Request $request)
    {

        $validator = \Validator::make($request->input(), [
            'title' => 'required|in:defaultA,defaultB,defaultC',
            'hall_type' => 'required|integer|exists:game_hall,id',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $title = $request->input('title', 'defaultA');
        $hall_type = $request->input('hall_type', 0);
        $hall_name = $request->input('hall_name', '');
        $agent_id = 0;
        if( $hall_name ) {
            $agent = Agent::select('id')->where(['user_name' => $hall_name,'grade_id' => 1,'is_hall_sub'=> 0])->first('id');

            if( ! $agent ) {
                return $this->response->array([
                    'code' => 400,
                    'text' => trans('agent.agent_not_exist'),
                    'result' => '',
                ]);
            }
            $agent_id = $agent['id'];
        }


        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => self::getHallLimit($agent_id, $hall_type, $title),
        ]);


    }

    /**
     * 获取游戏限额数据
     * @param int $agent_id 厅主id
     * @param int $hall_type 厅类型
     * @param string $title 限额组
     * @return array
     */
    private static function getHallLimit(int $agent_id = 0, int $hall_type = 0, string $title="defaultA") {
        //获取默认限额和厅主的限额
        $data = DB::table("hall_limit_group as g")->join("hall_limit_item as i", "g.id", "=" ,"i.group_id")
            ->select('g.id','g.agent_id','i.game_cat_id','i.max_money','i.min_money','i.bet_area')
            ->where('g.title', $title)
            ->where('g.hall_type', $hall_type)
            ->whereIn('g.agent_id', [$agent_id, 0])
            ->orderby('g.agent_id', 'asc')
            ->get();


        //合并去重（厅主有的限额覆盖平台的默认限额）确保限额完整
        $list = [];
        $id = 0;
        foreach ($data as $k => &$v)
        {
            if($agent_id != 0 && $v->agent_id != 0) {
                $id = $v->id;
            } elseif ($agent_id == 0 && $v->agent_id == 0) {
                $id = $v->id;
            }
            $tmp_key = $title . '-' . $hall_type . '-' . $v->game_cat_id . '-' . $v->bet_area;//以这个key作为键名，去重
            $v->betarea_code = config('betarea.'.$v->game_cat_id.'.'.$v->bet_area)['betarea_code'];//获取下去区域标识码
            unset($v->id, $v->agent_id);
            $list[$tmp_key] = (array)$v;
        }
        unset($v);
        //以游戏分类id分组，由于游戏限额是按游戏分类进行设置的
        $items = [];
        foreach ($list as $item) {
            $items[$item['game_cat_id']]['bet_areas'][] = $item;
        }

        //获取游戏分类默认限额数据格式
        $cat_data = self::getGameCat();

        foreach ($cat_data as $key => &$cat) {
            foreach ($cat['bet_areas'] as &$vv) {
                if (isset($items[$cat['game_cat_id']])) {
                    foreach ($items[$cat['game_cat_id']]['bet_areas'] as &$v3) {
                        if ($vv['bet_area'] == $v3['bet_area']) {
                            $vv = $v3;
                            break;
                        }
                    }
                    unset($v3);
                }else {
                    break;
                }
            }
            unset($vv);
        }
        unset($cat);

        $data = [
            'title' => $title,
            'hall_type' => $hall_type,
            'agent_id' => $agent_id,
            'limit_items' => $cat_data
        ];

        $id && $data['id'] = $id;
       return $data;

    }


    /**
     * 获取游戏分类默认限额数据格式
     * @return array
     */
    private static function getGameCat() : array
    {
        $data = [];
        //游戏分类
        $cat_data = GameCat::where('game_type',0)->select('cat_name','game_cat_code', 'id as game_cat_id')->get()->toArray();
        if($cat_data) {

            foreach ($cat_data as $k => &$cat) {

                $bet_area = config('betarea.'.$cat['game_cat_id']);
                if(isset($bet_area)) {
                    $cat['bet_areas'] = array_values($bet_area);
                    foreach ($cat['bet_areas'] as &$v) {
                        $v['max_money'] = '';
                        $v['min_money'] = '';
                    }
                    unset($v);
                } else {
                    unset($cat_data[$k]);
                }

            }
            unset($cat);
            $data = $cat_data;
        }
        return $data;
    }

    /**
     * @api {post} /hall/quota 游戏厅限额添加
     * @apiDescription 游戏厅限额添加
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} title 标题（设定限额标题 defaultA defaultB defaultC）
     * @apiParam {String} hall_name 厅主名称
     * @apiParam {Number} hall_type 厅类型，厅id
     * @apiParam {Json} items 下注区域值
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
        {
            "code": 0,
            "text": "保存成功",
            "result": ""
        }
     *@apiErrorExample {json} Error-Response:
     *     HTTP/1.1 200 OK
            {
                "code": 400,
                "text": "限额分组已存在",
                "result": ""
            }
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'title' => 'required|in:defaultA,defaultB,defaultC',
            'hall_type' => 'required|integer|exists:game_hall,id',
            'items' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $items = json_decode($request->input('items'), true);
        if( !$items ) {
            return $this->response->array([
                'code'=>400,
                'text'=>trans('agent.param_error'),
                'result'=>'',
            ]);
        }
        $title = $request->input('title');
        $hall_type = $request->input('hall_type');
        $hall_name = $request->input('hall_name');

        $agent_id = 0;
        if( $hall_name ) {
            $agent = Agent::select('id')->where(['user_name' => $hall_name,'grade_id' => 1,'is_hall_sub'=> 0])->first('id');

            if( ! $agent ) {
                return $this->response->array([
                    'code' => 400,
                    'text' => trans('agent.agent_not_exist'),
                    'result' => '',
                ]);
            }
            $agent_id = $agent['id'];
        }

        $attributes = [
            'title' => $title,
            'hall_type' => $hall_type,
            'item_type' => $agent_id ? 1 : 2,
            'agent_id' => $agent_id
        ];
        //默认分组是否存在
        $info = HallLimitGroup::where($attributes)->first();

        if($info != null) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.limit_group_exist'),
                'result' => '',
            ]);

        }

        $attributes['status'] = 1;
        $attributes['uptime'] = Carbon::now()->toDateTimeString();
        $attributes['status'] = 1;

        DB::beginTransaction();

        $re = HallLimitGroup::create($attributes);

        if($re){

            if($items) {
                $item_arr = [];

                foreach ($items as $item) {

                    foreach ($item['bet_areas'] as $v) {

                        if (!is_numeric($v['min_money']) || !is_numeric($v['max_money'])) {

                            DB::rollBack();
                            return $this->response->array([
                                'code' => 400,
                                'text' =>trans('agent.balance_str_error'),
                                'result' => '',
                            ]);

                        }
                        if ($v['min_money'] > $v['max_money']) {
                            DB::rollBack();
                            return $this->response->array([
                                'code' => 400,
                                'text' =>trans('agent.min_max_error'),
                                'result' => '',
                            ]);
                        }

                        $item_arr[] = [
                            'group_id' => $re['id'],
                            'game_cat_id' => $item['game_cat_id'],
                            'max_money' => $v['max_money'],
                            'min_money' => $v['min_money'],
                            'bet_area' => $v['bet_area'],
                        ];
                    }

                }

                $r = HallLimitItem::insert($item_arr);
                if($r) {
                    DB::commit();
                    //将修改后更新缓存
                    $this->setCacaheQuota($agent_id);
                    return $this->response->array([
                        'code' => 0,
                        'text' =>trans('agent.save_success'),
                        'result' => '',
                    ]);

                } else {

                    DB::rollBack();

                    return $this->response->array([
                        'code' => 400,
                        'text' =>trans('agent.add_fails'),
                        'result' => '',
                    ]);

                }
            }
            DB::commit();
            @addLog(['action_name'=>'游戏厅限额添加','action_desc'=>' 给厅主：'.$request->input('hall_name').'设定了游戏限额 ','action_passivity'=>$request->input('hall_name')]);

            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.save_success'),
                'result' => '',
            ]);

        } else {

            DB::rollBack();

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.add_fails'),
                'result' => '',
            ]);
        }

    }


    /**
     * @api {put} /hall/quota/{id} 保存游戏厅限额
     * @apiDescription 保存游戏厅限额
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Json} items 下注区域值
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "保存成功",
    "result": ""
    }
     *@apiErrorExample {json} Error-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 400,
    "text": "限额分组不存在",
    "result": ""
    }
     */
    public function update(Request $request, $id)
    {


        $id = (int)$id;
        $info = HallLimitGroup::find($id);
        if($info == null) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.limit_group_not_exist'),
                'result' => '',
            ]);
        }
        $items = json_decode($request->input('items'), true);
        if($items) {

            $item_arr = [];
            foreach ($items as $item) {

                foreach ($item['bet_areas'] as $v) {

                    if (!is_numeric($v['min_money']) || !is_numeric($v['max_money'])) {

                        return $this->response->array([
                            'code' => 400,
                            'text' =>trans('agent.balance_str_error'),
                            'result' => '',
                        ]);

                    }
                    if ($v['min_money'] > $v['max_money']) {
                        return $this->response->array([
                            'code' => 400,
                            'text' =>trans('agent.min_max_error'),
                            'result' => '',
                        ]);
                    }

                    $item_arr[] = [
                        'group_id' => $id,
                        'game_cat_id' => $item['game_cat_id'],
                        'max_money' => $v['max_money'],
                        'min_money' => $v['min_money'],
                        'bet_area' => $v['bet_area'],
                    ];
                }

            }
            DB::beginTransaction();
            //delete old data
            HallLimitItem::where('group_id', $id)->delete();
            // add data
            $r = HallLimitItem::insert($item_arr);
            if($r) {
                DB::commit();
                @addLog(['action_name'=>'游戏限额修改','action_desc'=>' 修改了游戏限额','action_passivity'=>'游戏限额']);

                //将修改后更新缓存

                $this->setCacaheQuota( $info['agent_id'] );

                return $this->response->array([
                    'code' => 0,
                    'text' =>trans('agent.save_success'),
                    'result' => '',
                ]);

            } else {
                DB::rollBack();
                return $this->response->array([
                    'code' => 400,
                    'text' =>trans('agent.save_fails'),
                    'result' => '',
                ]);

            }

        } else {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.save_fails'),
                'result' => '',
            ]);
        }

    }

    /**
     * @api {post} /hall/quota/shortcut 快捷设定限额（添加）
     * @apiDescription 快捷设定限额（添加）
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} title 标题（设定限额标题 默认限额A，默认限额B,默认限额C）
     * @apiParam {Number} hall_type 厅类型，厅id
     * @apiParam {String} hall_name 厅主名称
     * @apiParam {String} game_cat_id 游戏分类的id（百家乐，轮盘，骰宝，龙虎）例如（[1,2,3,4]
    ）
     * @apiParam {Number} max_money 最大值
     * @apiParam {Number} min_money 最小值
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "保存成功",
    "result": ""
    }
     *@apiErrorExample {json} Error-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 400,
    "text": "限额分组已存在",
    "result": ""
    }
     */
    public function shortcutStore(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'title' => 'required|in:defaultA,defaultB,defaultC',
            'game_cat_id' => 'required',
            'hall_type' => 'required|integer|exists:game_hall,id',
            'max_money' => 'required|numeric',
            'min_money' => 'required|numeric',
        ]);


        if ($validator->fails()) {
            return $this->response->array([
                'code'=>400,
                'text'=>$validator->errors()->first(),
                'result'=>'',
            ]);
        }

        $title = $request->input('title');
        $hall_type = $request->input('hall_type');
        $hall_name = $request->input('hall_name');
//        $game_id = explode(',',$request->input('game_id'));
        $game_cat_id = json_decode($request->input('game_cat_id'),true);
        $max_money = $request->input('max_money');
        $min_money = $request->input('min_money');

        $agent_id = 0;
        if( $hall_name ) {
            $agent = Agent::select('id')->where(['user_name' => $hall_name,'grade_id' => 1,'is_hall_sub'=> 0])->first('id');

            if( ! $agent ) {
                return $this->response->array([
                    'code' => 400,
                    'text' => trans('agent.agent_not_exist'),
                    'result' => '',
                ]);
            }
            $agent_id = $agent['id'];
        }

        if ( $min_money === '' ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_is_null'),
                'result' => '',
            ]);
        }

        if ( $max_money === '' ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.max_balance_is_null'),
                'result' => '',
            ]);
        }

        if (!is_numeric($min_money)) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_str_error'),
                'result' => '',
            ]);
        }

        if (!is_numeric($max_money)) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.max_balance_str_error'),
                'result' => '',
            ]);
        }

        if($min_money <= 0) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_is_not_0'),
                'result' => '',
            ]);
        }
        if($min_money > $max_money) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.min_max_error'),
                'result'=>'',
            ]);
        }

        if($min_money > $max_money) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.min_max_error'),
                'result'=>'',
            ]);
        }

        $attributes = [
            'title' => $title,
            'hall_type' => $hall_type,
            'item_type' => $agent_id ? 1: 2,
            'agent_id' => $agent_id
        ];

        //默认分组是否存在
        $info = HallLimitGroup::where($attributes)->first();

        if($info != null) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.limit_group_exist'),
                'result' => '',
            ]);

        }

        $attributes['status'] = 1;
        $attributes['uptime'] = Carbon::now()->toDateTimeString();
        DB::beginTransaction();

        $re = HallLimitGroup::create($attributes);
        if($re){

            if($game_cat_id && is_array($game_cat_id)) {
                $item_arr = [];

                foreach ($game_cat_id as $v) {
                    $betarea = config('betarea.'.$v);

                    if($betarea) {
                        foreach ($betarea as $vv){
                            $item_arr[] = [
                                'group_id' => $re['id'],
                                'game_cat_id' => $v,
                                'max_money' => $max_money,
                                'min_money' => $min_money,
                                'bet_area' => $vv['bet_area'],
                            ];
                        }
                    }
                }
                $r = HallLimitItem::insert($item_arr);
                if($r) {

                    DB::commit();
                    //将修改后更新缓存
                    $this->setCacaheQuota($agent_id);
                    return $this->response->array([
                        'code' => 0,
                        'text' =>trans('agent.save_success'),
                        'result' => '',
                    ]);

                } else {

                    DB::rollBack();

                    return $this->response->array([
                        'code' => 400,
                        'text' =>trans('agent.add_fails'),
                        'result' => '',
                    ]);

                }
            }
            DB::commit();
            @addLog(['action_name'=>'快捷添加游戏限额','action_desc'=>' 给厅主'.$request->input('hall_name').'快捷设定了游戏限额','action_passivity'=>$request->input('hall_name')]);

        } else {

            DB::rollBack();

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.add_fails'),
                'result' => '',
            ]);
        }

    }

    /**
     * @api {post} /hall/quota/shortcut/{id} 快捷设定限额（保存）
     * @apiDescription 快捷设定限额（保存）
     * @apiGroup game
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} game_cat_id 游戏分类的id（百家乐，轮盘，骰宝，龙虎）例如（[1,2,3,4]）
     * @apiParam {Number} max_money 最大值
     * @apiParam {Number} min_money 最小值
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "保存成功",
    "result": ""
    }
     *@apiErrorExample {json} Error-Response:
     *     HTTP/1.1 200 OK
    {
    "code": 400,
    "text": "限额分组不存在",
    "result": ""
    }
     */
    public function shortcutUpdate(Request $request, $id)
    {
        $id = (int)$id;
        $info = HallLimitGroup::find($id);
        if($info == null) {
            return $this->response->array([
                'code' => 400,
                'text' => trans('agent.limit_group_not_exist'),
                'result' => '',
            ]);
        }

//        $game_id = explode(',',$request->input('game_id'));
        $game_cat_id = json_decode($request->input('game_cat_id'),true);
        $max_money = $request->input('max_money');
        $min_money = $request->input('min_money');

        if ( $min_money === '' ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_is_null'),
                'result' => '',
            ]);
        }

        if ( $max_money === '' ) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.max_balance_is_null'),
                'result' => '',
            ]);
        }

        if (!is_numeric($min_money)) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_str_error'),
                'result' => '',
            ]);
        }

        if (!is_numeric($max_money)) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.max_balance_str_error'),
                'result' => '',
            ]);
        }

        if($min_money <= 0) {
            return $this->response->array([
                'code' => 400,
                'text' =>trans('gamebalance.min_balance_is_not_0'),
                'result' => '',
            ]);
        }

        if($min_money > $max_money) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.min_max_error'),
                'result'=>'',
            ]);
        }

        if($game_cat_id && is_array($game_cat_id)) {
            //delete old datas

            HallLimitItem::where('group_id', $id)->whereIn('game_cat_id', $game_cat_id)->delete();

            $item_arr = [];
            foreach ($game_cat_id as $v) {
                $betarea = config('betarea.'.$v);

                if($betarea) {
                    foreach ($betarea as $vv){
                        $item_arr[] = [
                            'group_id' => $id,
                            'game_cat_id' => $v,
                            'max_money' => $max_money,
                            'min_money' => $min_money,
                            'bet_area' => $vv['bet_area'],
                        ];
                    }
                }
            }
            // add datas
            $r = HallLimitItem::insert($item_arr);
            if($r) {
                @addLog(['action_name'=>'编辑快捷游戏限额','action_desc'=>' 编辑了快捷游戏限额','action_passivity'=>'游戏限额']);

                //将修改后更新缓存
                $this->setCacaheQuota($info['agent_id']);
                return $this->response->array([
                    'code' => 0,
                    'text' =>trans('agent.save_success'),
                    'result' => '',
                ]);

            } else {

                return $this->response->array([
                    'code' => 400,
                    'text' =>trans('agent.save_fails'),
                    'result' => '',
                ]);

            }

        } else {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.save_fails'),
                'result' => '',
            ]);
        }
    }

    /**
     * 修改厅限额缓存
     * @param array $item
     */
   private  function setCacaheQuota($agent_id = 0)
   {
       $keyName = 'hall_limit:' . $agent_id;
       //获取默认限额和厅主的限额
       $data = DB::table("hall_limit_group as g")->join("hall_limit_item as i", "g.id", "=" ,"i.group_id")
           ->select('g.title','g.hall_type','i.group_id', 'i.game_cat_id','i.max_money','i.min_money','i.bet_area')
           ->whereIn('g.agent_id', [$agent_id, 0])
           ->orderby('g.agent_id','asc')
           ->get();

       //合并去重（厅主有的限额覆盖平台的默认限额）确保限额完整
       $list = [];
       foreach ($data as $k => &$v)
       {
           $tmp_key = $v->title . '-' . $v->hall_type . '-' . $v->game_cat_id . '-' . $v->bet_area;//以这个key作为键名，去重
           unset($v->title);
           $list[$tmp_key] = (array)$v;
       }

       if(count($list)) {
           $redis = Redis::connection("default");
           $redis->del($keyName);
           foreach (StringShiftToInt($list, ['hall_type','group_id','game_cat_id','max_money','min_money','bet_area']) as $item){
               $redis->rpush($keyName,  json_encode($item));
           }
       }

   }
}