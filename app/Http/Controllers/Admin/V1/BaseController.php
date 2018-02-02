<?php

namespace App\Http\Controllers\Admin\V1;

use App\Socket\Byte;
use App\Socket\GameSocket;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    // 接口帮助调用
    use Helpers;

    // 返回错误的请求
    protected function errorBadRequest($message = '')
    {
        return $this->response->array($message)->setStatusCode(400);
    }


    //给游戏服务端发送信号处理
    public static function putSocketServer($data = [],$action_anme = '')
    {
        $res = new GameServerController();
        $aa = $res->loginSever();
        var_export($aa);die;
        $gameSocket=new GameSocket(env('GAME_SERVER'),env('GAME_SERVER_PORT'));
        $gameSocket->getByte()->writeChar('chensj');
        $gameSocket->getByte()->writeChar('$2y$10$8QOHnH87akFaXHRBw/Ae.uS3rGlqkPiAmlKp4Fyw/sEKMgAk9a.k.');
        $gameSocket->code=7;
        $res = $gameSocket->write($data);
        var_export(unpack("Sl1/Sl2/cl3",$res));die;
    }

}
