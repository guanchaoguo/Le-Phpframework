<?php
/**
 * 文案Logo-游戏对接.
 * User: chensongjian
 * Date: 2017/6/26
 * Time: 16:26
 */

//apidoc -i app/Http/Controllers/Game -o public/game_apidoc

namespace App\Http\Controllers\Game;

use Illuminate\Http\Request;
use App\Models\GamePlatformLogo;

class GamePlatformLogoController extends BaseController
{
    /**
     * @api {post} /lebogame/logo 游戏端获取logo
     * @apiDescription 游戏端获取logo
     * @apiGroup Logo
     * @apiPermission JWT
     * @apiVersion 2.2.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.lebogame.v2.2+json
     * @apiParam {String} token 认证token
     * @apiParam {Number} label 所属平台,0为PC，1为横版，2为竖版，默认为0
     * @apiParam {Number} type 类型 1:平台logo ,0：厅主logo ，默认为0
     * @apiSuccessExample {json} 成功返回:
    {
        "code": 0,
        "text": "操作成功",
        "result": {
            "data": [
                {
                    "title": "wwwaa",//标题名称
                    "full_logo": "http://images.dev/./upload/img/2017/06/21/2017062107172953.jpg"//Logo图片地址
                }
            ]
        }
    }
     */
    public function index(Request $request)
    {

        $label = $request->input('label', 0);
        $type = $request->input('type', 0);

        $where = [
            'label' => $label,
            'status' => 1,
            'is_use' => 1,
            'p_id' => $type ? 0 : $this->userInfo->hall_id,
        ];
        $db = GamePlatformLogo::select('title',
            \DB::raw('CONCAT("'.env('IMAGE_HOST').'", logo) AS full_logo')
            )->where($where)->orderby('sort','asc');
        $data = $db->first();

        if( ! $data && $type == 0) {

            $where['p_id'] = 0 ;
            $db = GamePlatformLogo::select('title',
                \DB::raw('CONCAT("'.env('IMAGE_HOST').'", logo) AS full_logo')
            )->where($where)->orderby('sort','asc');
            $data = $db->first();

        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' =>  $data ? $data : '',
        ]);
    }


}