<?php

namespace App\Http\Controllers\Admin\V1;

use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CaptchaController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {post} /captcha 验证码
     * @apiDescription 验证码
     * @apiParam {String} gid 验证码GID
     * @apiGroup Auth
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *      {
        "code": 0,
        "text": "ok",
        "result": {
        "captcha_img": "data:image/jpeg;base64,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",
        "captcha_value": "l9ww2"
        }
        }  
     */
    public function index(Request $request)
    {

        /*$tomorrow = Carbon::now()->addDay();
        $lastWeek = Carbon::now()->subWeek();
        $nextSummerOlympics = Carbon::createFromDate(2012)->addYears(4);
        $officialDate = Carbon::now()->toRfc2822String();
        $howOldAmI = Carbon::createFromDate(1975, 5, 21)->age;
        $noonTodayLondonTime = Carbon::createFromTime(12, 0, 0, 'Europe/London');
        $worldWillEnd = Carbon::createFromDate(2012, 12, 21, 'GMT');
        echo $worldWillEnd;die;*/

        $redis = Redis::connection("default");

        //因为需要使用redis进行存储验证码字符，防止数据过大，每次请求生成验证码时都需要删除上次的验证码数据
        if($request->input('gid'))
        {
            $redis->del($request->input('gid'));
        }

        $builder = new CaptchaBuilder();
//        $builder = new CaptchaBuilder('20 + 6');
//        $builder->setMaxBehindLines(0);
//        $builder->setMaxFrontLines(0);
        $builder->build();

//        $builder->setMaxFrontLines(0);
//        $builder->save('out.jpg');
        //把内容存入Redis
        $key = str_random(20);
        $redis->set($key,$builder->getPhrase());
        $redis->expire($key,360);//半个小时过期

        return $this->response->array([
            'code'=>0,
            'text'=>'ok',
            'result'=>[
                'captcha_img' => $builder->inline(),
                'captcha_value' => $builder->getPhrase(),
                'gid'           => $key,
            ],
        ]);
    }

}
