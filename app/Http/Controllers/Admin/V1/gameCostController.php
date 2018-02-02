<?php
/**
 * 游戏费用&包网费用设置
 * Created by PhpStorm.
 * User: chensongjian
 * Date: 2017/3/31
 * Time: 10:01
 */

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\GameCost;
use App\Models\GameScale;
use Carbon\Carbon;
use App\Models\Agent;
use Illuminate\Support\Facades\DB;

class gameCostController extends BaseController
{

    /**
     * @api {get} /agent/{id}/gameCost 获取厅主游戏费用&包网费用数据
     * @apiDescription 获取厅主游戏费用&包网费用数据,{id}变量为厅主id
     * @apiGroup gameCost
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
        "gameScale": [//游戏分成数组
        {
        "start_profit": "0.00",//毛利润开始值
        "end_profit": "100.00",//毛利润结束值
        "scale": "30"//站成比例，单位：%
        }
        ],
        "gameCost": {//游戏包网费用对象
        "roundot": "30000.00",//包网费
        "line_map": "30000.00",//线路图
        "upkeep": "30000.00",//维护费用
        "ladle_bottom": "30000.00"//包底
        }
        }
        }
     */
    public function index(Request $request, int $id)
    {

        //厅主验证
        if($id && !self::is_hall_agent($id)) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_not_exist'),
                'result'=>'',
            ]);
        }
        $files = ['start_profit','end_profit','scale'];
        $gameScale = GameScale::select($files)->where('p_id',$id)->get();

        $filesCost = ['roundot','line_map','upkeep','ladle_bottom'];
        $gameCost = GameCost::select($filesCost)->where('p_id',$id)->first();
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => [
                'gameScale' => count($gameScale) != 0 ? $gameScale : [['start_profit'=>'','end_profit'=>'','scale'=>'']],
                'gameCost' => $gameCost ?? ['roundot'=>0,'line_map'=>0,'upkeep'=>0,'ladle_bottom'=>0],
            ],
        ]);
    }

    /**
     * @api {post} /agent/{id}/gameCost 添加保存厅主游戏费用数据&包网费用数据
     * @apiDescription 添加保存厅主游戏费用数据&包网费用数据,{id}变量为厅主id
     * @apiGroup gameCost
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} gameScale 游戏分成 ，json数组格式：[{"start_profit": "0.00","end_profit": "100.00","scale": "30"}]
     * @apiParam {String} gameCost 游戏包网费用，josn对象格式：{"roundot": "30000.00","line_map": "30000.00","upkeep": "30000.00","ladle_bottom": "30000.00"}
     * @apiSuccessExample {json} 成功返回格式
        {
        "code": 0,
        "text": "保存成功",
        "result": ""
        }
     * @apiSuccessExample {json} gameScale格式参数说明
        "gameScale": [//游戏分成数组
        {
        "start_profit": "0.00",//毛利润开始值
        "end_profit": "100.00",//毛利润结束值
        "scale": "30"//站成比例，单位：%
        }
        ]
     * @apiSuccessExample {json} gameCost格式参数说明
        "gameCost": {//游戏包网费用对象
        "roundot": "30000.00",//包网费
        "line_map": "30000.00",//线路图
        "upkeep": "30000.00",//维护费用
        "ladle_bottom": "30000.00"//包底
        }
     */
    public function store(Request $request, int $id)
    {
        $agent = Agent::where(['id'=>$id,'grade_id'=>1,'is_hall_sub' =>0])->first();
        //厅主验证
        if( ! $agent ) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.agent_not_exist'),
                'result'=>'',
            ]);
        }
        $gameScale = json_decode($request->input('gameScale'), true);
        $gameCost = json_decode($request->input('gameCost'), true);
        $time = Carbon::now()->toDateTimeString();
        if(!$gameScale && !$gameCost) {
            return $this->response->array([
                'code'=>400,
                'text'=> trans('agent.save_fails'),
                'result'=>'',
            ]);
        }
        DB::beginTransaction();
        //游戏分成设置
        if( $gameScale ) {
            $re_scale = self::setGameScale($gameScale, $id);
            if($re_scale['code'] != 1) {
                DB::rollBack();
                return $this->response->array($re_scale['data']);
            }
        }

        //游戏费用设置
        $gameCost = $request->input('gameCost');
        if( $gameCost ) {
            $gameCost = json_decode($gameCost, true);
            $re_cost = self::setGameCost($gameCost, $id);
            if($re_cost['code'] != 1) {
                DB::rollBack();
                return $this->response->array($re_cost['data']);
            }
        }

        @addLog(['action_name'=>'编辑游戏费用设置','action_desc'=>' 对厅主 '.$agent['user_name'].' 进行编辑','action_passivity'=>$agent['user_name']]);

        DB::commit();
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.save_success'),
            'result' =>'',
        ]);

    }


    /**
     * @param array $data 游戏分成 格式：
    [
    [
    "start_profit" => "0.00",//毛利润开始值
    "end_profit" => "100.00",//毛利润结束值
    "scale" => "30"//站成比例，单位：%
    ]
    ]
     * @param int $agent_id 厅主id
     * @return array
     */
    public function setGameScale( array $data, int $agent_id ) : array
    {
        if( $data ) {

            $time = date('Y-m-d H:i:s', time());
            $count = count($data) - 1;
            foreach ($data as $k => &$scale) {

                if( isset( $data[$k+1] ) ) {
                    if( $data[$k]['end_profit'] != $data[$k+1]['start_profit'] ) {
                        return [
                            'code' => -1,
                            'data' => [
                                'code' => 400,
                                'text' => trans('agent.last_max_next_min'),
                                'result' => '',
                            ]
                        ];
                    }
                }

                if( $count != $k ) {
                    if ( (double)$scale['start_profit'] >= (double)$scale['end_profit'] ) {

                        return [
                            'code' => -1,
                            'data' => [
                                'code' => 400,
                                'text' => trans('agent.min_max_error'),
                                'result' => '',
                            ]
                        ];

                    }
                } else {
                    if( (double)$scale['end_profit'] ) {
                        return [
                            'code' => -1,
                            'data' => [
                                'code' => 400,
                                'text' => trans('agent.last_max_error'),
                                'result' => '',
                            ]
                        ];
                        /*if ( (double)$scale['start_profit'] >= (double)$scale['end_profit'] ) {

                            return [
                                'code' => -1,
                                'data' => [
                                    'code' => 400,
                                    'text' => trans('agent.min_max_error'),
                                    'result' => '',
                                ]
                            ];

                        }*/
                    }
                }

                if (!is_numeric($scale['scale']) || $scale['scale'] <= 0) {
                    return [
                        'code' => -1,
                        'data' => [
                            'code' => 400,
                            'text' => trans('agent.scale_error'),
                            'result' => '',
                        ]
                    ];
                }
                $scale['add_user'] = \Auth::user()->user_name;
                $scale['add_date'] = $time;
                $scale['p_id'] = $agent_id;
            }

            GameScale::where('p_id', $agent_id)->delete();
            $re_scale = GameScale::insert($data);
            if ($re_scale) {
                return [
                    'code' => 1
                ];
            }

            return [
                'code' => 0,
                'data' => [
                    'code' => 400,
                    'text' => trans('agent.save_fails'),
                    'result' => '',
                ]
            ];
        }

        return [
            'code' => 1
        ];
    }

    /**
     * 游戏费用设置
     * @param array $data 游戏费用 格式
    [
    "roundot" => "30000.00",//包网费
    "line_map" => "30000.00",//线路图
    "upkeep" => "30000.00",//维护费用
    "ladle_bottom" => "30000.00"//包底
    ]
     * @param int $agent_id
     * @return array
     */
    protected function setGameCost( array $data, int $agent_id ) : array
    {
        if( $data ) {

            $time = date('Y-m-d H:i:s', time());
            $data['p_id'] = $agent_id;
            $data['add_user'] = \Auth::user()->user_name;
            $data['update_date'] = $time;
            GameCost::where('p_id',$agent_id)->delete();
            $re_cost = GameCost::insert($data);
            if ($re_cost) {
                return [
                    'code' => 1
                ];
            }

            return [
                'code' => 0,
                'data' => [
                    'code' => 400,
                    'text' => trans('agent.save_fails'),
                    'result' => '',
                ]
            ];
        }

        return [
            'code' => 1
        ];
    }

    /**
     * 验证厅主
     * @param int $id
     * @return mixed
     */
    private function is_hall_agent(int $id)
    {
        return Agent::where(['id'=>$id,'grade_id'=>1,'is_hall_sub' =>0])->first();

    }

}