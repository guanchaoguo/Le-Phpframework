<?php

namespace App\Http\Controllers\Admin\V1;

/**
 * 游戏厅控制器
 */
use Illuminate\Http\Request;
use App\Models\GameHall;
use App\Models\AgentGame;
class GameHallController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {get} /hall/games 游戏种类&游戏厅
     * @apiDescription 获取游戏厅游戏种类&游戏厅
     * @apiGroup account
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {Number} only_hall 1：只获取厅数据，0：获取游戏种类和厅，默认为0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *      {
                "code": 0,
                "text": "ok",
                "result": {
                    "data": [
                        {
                            "id": 1,
                            "title": "旗舰厅",
                            "desc": "旗舰厅",
                            "games": [
                                {
                                    "id": 88,
                                    "cat_id": 1,
                                    "game_name": "龙虎百家乐",
                                    "sort_id": 1,
                                    "status": 1,
                                    "pivot": {
                                    "hall_id": 1,
                                    "game_id": 88
                                }
                            ]
                        }
                    ]
                }
           }
     */
    public function games(Request $request)
    {


        $data = GameHall::select('*')->orderBy('id')->get();
        $only_hall = (int)$request->input('only_hall',0);
        $hall_id  = $request->input('hall_id');
        $agentGame = '';
        if( !$only_hall ) {

            if( $hall_id ) {

                $agentGame = AgentGame::where('agent_id',$hall_id)->get();
            }
            foreach ($data as  &$v){
                foreach ($v->games as &$vv) {
                    if($agentGame) {
                        foreach ($agentGame as $k => $a){

                            if($vv['id'] == $a['game_id'] && $v['id'] == $a['hall_id']){
                                $vv['is_have'] = 1;
                                $vv['status_a'] = $a['status'];
                                break;
                            }

                        }
                        if(!isset($vv['is_have'])) {
                            $vv['is_have'] = 0;
                            $vv['status_a'] = 0;
                        }
                    }


                }
                $data->games = $v->games;
//                var_export($data);die;
            }

        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' => [
                'data' =>$data->toArray(),
            ],
        ]);
    }

}
