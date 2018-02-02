<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/20
 * Time: 17:24
 * 和游戏服务端操作控制器
 */
namespace App\Http\Controllers\Admin\V1;

use App\Models\PlatformUser;
use App\Socket\GameSocket;
use Illuminate\Support\Facades\DB;

class GameServerController
{
    public $socket = null;


    //登录游戏服务端
    public function loginSever()
    {
        $data = [];
        $gameSocket=new GameSocket(env('GAME_SERVER'),env('GAME_SERVER_PORT'));
        $find = PlatformUser::where(['account_state'=>1])->first();
        if(!$find)
            return false;
        $gameSocket->getByte()->writeChar($find->user_name);
        $gameSocket->getByte()->writeChar($find->password);
        $gameSocket->code=7;
        $res = $gameSocket->write($data);
        $res = unpack("Sl1/Sl2/cl3",$res);
        if($res['l3'] === 0)
        {
            $this->socket = $gameSocket->socket;
            return true;
        }
        return false;
    }

    //系统维护操作
    public function sysMaintain()
    {
        //先登录，然后进行服务器推送操作
        if($socket = $this->loginSever() === false)
            return false;

        //进行系统维护消息推送
        $find = DB::table('system_maintain')->where(['sys_type'=>0])->first();
        if(!$find)
            return false;
        $data = [];
        $gameSocket=new GameSocket(env('GAME_SERVER'),env('GAME_SERVER_PORT'));
        $gameSocket->__desctruct();
        $gameSocket->socket = $this->socket;
        $gameSocket->getByte()->writeShortInt2($find->state);
        $gameSocket->getByte()->writeChar($find->comtent);
        $gameSocket->getByte()->writeInt(strtotime($find->start_date));
        $gameSocket->getByte()->writeInt(strtotime($find->end_date));
        $gameSocket->code=5;
        $res = $gameSocket->write($data);
        $res = unpack("Sl1/Sl2/cl3",$res);
        socket_close($this->socket);//关闭socket
        if($res['l3'] === 0)
        {
            return true;
        }
        return false;
    }

    //设置厅维护
    public function hallMaintain()
    {
        //先登录，然后进行服务器推送操作
        if($socket = $this->loginSever() === false)
            return false;

        //进行系统维护消息推送
        $find = DB::table('system_maintain')->where(['sys_type'=>1])->first();
        if(!$find)
            return false;
        if($find->state == 0)
        {
            $find->hall_id = ',';
        }
        $data = [];
        $gameSocket=new GameSocket(env('GAME_SERVER'),env('GAME_SERVER_PORT'));
        $gameSocket->__desctruct();
        $gameSocket->socket = $this->socket;
        $gameSocket->getByte()->writeChar($find->hall_id);
        $gameSocket->getByte()->writeChar($find->comtent);
        $gameSocket->getByte()->writeInt(strtotime($find->start_date));
        $gameSocket->getByte()->writeInt(strtotime($find->end_date));
        $gameSocket->code=9;
        $res = $gameSocket->write($data);
        $res = unpack("Sl1/Sl2/cl3",$res);
        socket_close($this->socket);//关闭socket
        if($res['l3'] === 0)
        {
            return true;
        }
        return false;
    }

    //设置系统公告
    public function sysNotice()
    {
        //先登录，然后进行服务器推送操作
        if($socket = $this->loginSever() === false)
            return false;

        //进行系统维护消息推送
        $find = DB::table('system_message')->first();
        if(!$find)
            return false;
        $data = [];
        $gameSocket=new GameSocket(env('GAME_SERVER'),env('GAME_SERVER_PORT'));
        $gameSocket->__desctruct();
        $gameSocket->socket = $this->socket;
        $gameSocket->getByte()->writeChar($find->coment_cn);
        $gameSocket->getByte()->writeChar($find->coment_en);
        $gameSocket->getByte()->writeInt(strtotime($find->start_date));
        $gameSocket->getByte()->writeInt(strtotime($find->end_date));
        $gameSocket->code=11;
        $res = $gameSocket->write($data);
        $res = unpack("Sl1/Sl2/cl3",$res);
        socket_close($this->socket);//关闭socket
        if($res['l3'] === 0)
        {
            return true;
        }
        return false;
    }

    //玩家登出操作
    public function userLoginOut($user_id)
    {
        if(!$user_id)
            return false;
        //先登录，然后进行服务器推送操作
        if($socket = $this->loginSever() === false)
            return false;
        $data = [];
        $gameSocket=new GameSocket(env('GAME_SERVER'),env('GAME_SERVER_PORT'));
        $gameSocket->__desctruct();
        $gameSocket->socket = $this->socket;
        $gameSocket->getByte()->writeInt((int)$user_id);
        $gameSocket->code=1;
        $res = $gameSocket->write($data);
        $res = unpack("Sl1/Sl2/cl3",$res);
        socket_close($this->socket);//关闭socket
        if($res['l3'] === 0)
        {
            return true;
        }
        return false;
    }

    //一键登出所有玩家操作
    public function allUserLoginOut()
    {
        //先登录，然后进行服务器推送操作
        if($socket = $this->loginSever() === false)
            return false;
        $data = [];
        $gameSocket=new GameSocket(env('GAME_SERVER'),env('GAME_SERVER_PORT'));
        $gameSocket->__desctruct();
        $gameSocket->socket = $this->socket;
//        $gameSocket->getByte()->writeInt((int)$user_id);
        $gameSocket->code=15;
        $res = $gameSocket->write($data);
        $res = unpack("Sl1/Sl2/cl3",$res);
        socket_close($this->socket);//关闭socket
        if($res['l3'] === 0)
        {
            return true;
        }
        return false;
    }

    /**
     *  用户充值、扣款通知包网平台操作
     */
    public function roundotUserBalanceMessage($data,$code)
    {
        //链接包网socket

        $roundotServer = new GameSocket(env("BW_SERVER"),env("BW_PORT"));
        $roundotServer->getByte()->writeRoundot(json_encode($data,JSON_UNESCAPED_UNICODE));
        $roundotServer->code=$code;
        $res = $roundotServer->write([]);//进行数据发送
        $res = unpack("vsize/vcmd/A*res",$res);
        $result = json_decode($res['res'],true);
        socket_close($roundotServer->socket);//关闭socket
        if(isset($result['ret_code']) && $result['ret_code'] === 1)
        {
            return $result;
        }else{
            return false;
        }
    }
}