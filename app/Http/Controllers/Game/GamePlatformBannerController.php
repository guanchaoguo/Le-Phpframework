<?php
/**
 * 文案Banner-游戏对接.
 * User: chensongjian
 * Date: 2017/6/23
 * Time: 13:30
 */

//apidoc -i app/Http/Controllers/Game -o public/game_apidoc

namespace App\Http\Controllers\Game;

use Illuminate\Http\Request;
use App\Models\GamePlatformBanner;

class GamePlatformBannerController extends BaseController
{
    /**
     * @api {post} /lebogame/banner 游戏端获取banner
     * @apiDescription 游戏端获取banner
     * @apiGroup Banner
     * @apiPermission JWT
     * @apiVersion 2.2.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.lebogame.v2.2+json
     * @apiParam {String} token 认证token
     * @apiParam {Number} label 所属平台,0为PC，1为横版，2为竖版，默认为0
     * @apiSuccessExample {json} 成功返回:
    {
        "code": 0,
        "text": "操作成功",
        "result": {
            "data": [
                {
                    "title": "wwwaa",//标题名称
                    "url": "http://www.1631.com",//url地址
                    "full_banner": "http://images.dev/./upload/img/2017/06/21/2017062107172953.jpg"//banner图片地址
                }
            ]
        }
    }
     */
    public function index(Request $request)
    {

        $label = $request->input('label', 0);

        $where = [
            'label' => $label,
            'status' => 1,
            'is_use' => 1,
            'p_id' => $this->userInfo->hall_id,
        ];
        $db = GamePlatformBanner::select('title', 'url',
            \DB::raw('CONCAT("'.env('IMAGE_HOST').'", banner) AS full_banner')
            )->where($where)->orderby('sort','asc');
        $data = $db->get();

        if( ! count($data)  ) {

            $where['p_id'] = 0 ;
            $db = GamePlatformBanner::select('title', 'label', 'url',
                \DB::raw('CONCAT("'.env('IMAGE_HOST').'", banner) AS full_banner')
            )->where($where)->orderby('sort','asc');
            $data = $db->get();

        }

        return $this->response->array([
            'code' => 0,
            'text' => trans('agent.success'),
            'result' =>  ['data' => $data],
        ]);
    }


}