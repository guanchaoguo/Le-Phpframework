<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/5
 * Time: 18:00
 * Desc: 用户下注明细
 */
namespace App\Models;

use App\Http\Controllers\Admin\V1\DeliveryController;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
class PlayerOrder extends Eloquent
{
    use  Authenticatable;
    public $timestamps = false; //关闭创建时间、更新时间
    protected $connection = 'mongodb';
    protected $table = 'user_order';

    /**
     * 取消派彩操作抽象针对单个用户+单局派彩
     * @param $data  单局派彩数据信息
     * @param $desc  取消备注
     * @return bool
     */
    public static function cancelOrder($data,$desc='',$nowTime)
    {
        /**
         * 进行正常取消操作
         * 取消注单核心逻辑：
         *      如果用户是赢钱，则取消注单时，只需要进行扣除赢钱金额；
         *      如果用户是输钱，则取消注单时，只需要回退有效下注金额；
         */
        try
        {
            DB::beginTransaction();
            //获取注单对应用户信息
            $userInfo = DB::table('lb_user')->where(['uid'=>$data->user_id])->first();
            if(!$userInfo)
            {
                return false;
            }
            //进行用户余额的修改操作
//            $nowUserMoney = $data->total_win_score > 0 ? $userInfo->money - $data->total_win_score : $userInfo->money + $data->valid_bet_score_total;
            $nowUserMoney = $userInfo->money - $data->total_win_score;
            DB::table('lb_user')->where(['uid'=>$userInfo->uid])->update([
                'money' => $nowUserMoney
            ]);
            //修改该局下的注单状态为“已取消”状态
            $cancelCount = PlayerOrder::where(['round_no'=>$data->round_no,'user_name'=>$data->account])->update(['is_cancel'=>1]);

            //添加一条现金记录信息(以局+用户为单位生成现金记录)
            $inserData= [];
            $inserData['user_chart_id'] = $data->_id;
            $inserData['order_sn'] = $data->_id;
            $inserData['uid'] = (int)$data->user_id;
            $inserData['agent_id'] = (int)$data->agent_id;
            $inserData['hall_id'] = (int)$data->hall_id;
            $inserData['user_name'] = $data->account;
            $inserData['type'] = self::getHallTypeById($data->game_hall_id);
            $inserData['amount'] = (float)abs($data->total_win_score);
            $inserData['status'] = (int)$data->total_win_score > 0 ? 4 : 3;//赢钱为扣除，输钱为加钱
            $inserData['user_money'] = (float)$nowUserMoney;
            $inserData['desc'] = "局".$data->round_no."取消";
            $inserData['admin_user'] = $data->account;
            $inserData['admin_user_id'] = $data->user_id;
            $inserData['cash_no'] = $data->round_no;
//            $inserData['add_time'] = new UTCDateTime(time() * 1000);
            $inserData['add_time'] = new UTCDateTime($nowTime * 1000);
            $inserData['pkey'] = md5($data->user_id.$data->round_no.$data->total_win_score);
            $inserData['connect_mode'] = ($data->connect_mode == 1) ? 1 : 0;
            CashRecord::insert($inserData);

            //添加取消日志记录
            $logData['user_chart_id'] = $data->_id;
            $logData['order_sn'] = $data->_id;
            $logData['uid'] = $userInfo->uid;
            $logData['user_name'] = $data->account;
            $logData['agent_id'] = $userInfo->agent_id;
            $logData['agent_name'] = $userInfo->agent_name;
            $logData['hall_id'] = $userInfo->hall_id;
            $logData['hall_name'] = $userInfo->hall_name;
            $logData['round_no'] = $data->round_no;
            $logData['payout_win'] = (float)$data->total_win_score;
            $logData['before_user_money'] = (float)$userInfo->money;
            $logData['user_money'] = (float)$nowUserMoney;
            $logData['bet_time'] = $data->start_time;
            $logData['desc'] = $desc."(取消)";
            $user = \Illuminate\Support\Facades\Auth::user();
            $logData['action_user'] = $user['user_name'];
            $logData['action_user_id'] = $user['id'];
            $logData['action_passivity'] = $data->round_no;
            $logData['add_time'] = new UTCDateTime($nowTime * 1000);
            DB::connection('mongodb')->collection('exception_cash_log')->insert($logData);

            //取消派彩后派彩统计数据进行相对应的修改
            $nowDay = date("Y-m-d",$data->start_time->__toString()/1000);
            $statis_cash = DB::table("statis_cash")->where('add_date',$nowDay)->first();

            //投注派彩统计表修改
            DB::table("statis_cash")->where('add_date',$nowDay)->update([
                'total_win_score'   => $statis_cash->total_win_score - $data->total_win_score,
                'total_bet_score'   => $statis_cash->total_bet_score - $data->total_bet_score,
                'total_bet_count'   => $statis_cash->total_bet_count - $cancelCount
            ]);

            //玩家扣钱，玩家该注单为输钱时，进行取消操作，负负得正，实际上的加钱回来；如果是赢钱，取消时就是扣钱操作
            $statis_cash_agent = DB::table("statis_cash_agent")->where('add_date',$nowDay)->where('agent_id',$data->agent_id)->first();
            DB::table("statis_cash_agent")->where('add_date',$nowDay)->where('agent_id',$data->agent_id)->update([
                'total_bet_score'   => $statis_cash_agent->total_bet_score - $data->total_bet_score,
                'total_bet_count'   => $statis_cash_agent->total_bet_count - $cancelCount,
                'operator_win_score'    => $statis_cash_agent->operator_win_score + $data->total_win_score,
                'total_win_score'   => $statis_cash_agent->total_win_score - $data->total_win_score,
            ]);
            //商家扣钱，玩家该注单为输钱时，进行相加操作，正加负，实为扣钱操作；如果玩家是赢钱，取消时，正正相加，为加钱操作。
            $statis_cash_player = DB::table("statis_cash_player")->where('add_date',$nowDay)->where('agent_id',$data->agent_id)->where("user_id",$data->user_id)->first();
            DB::table("statis_cash_player")->where('add_date',$nowDay)->where('agent_id',$data->agent_id)->where("user_id",$data->user_id)->update([
                'total_bet_score'   => $statis_cash_player->total_bet_score - $data->total_bet_score,
                'total_bet_count'   => $statis_cash_player->total_bet_count - $cancelCount,
                'total_win_score'   => $statis_cash_player->total_win_score - $data->total_win_score
            ]);
            //修改当前局的状态为已取消状态
            UserChartInfo::where(['_id'=>$data->_id])->update(['is_cancel'=>1]);

            @addLog(['action_name'=>'注单取消','action_desc'=>' 进行注单取消，用户为：'.$data->account.',局号为：'.$data->round_no,'action_passivity'=>'注单取消']);

            DB::commit();//事物提交
            return true;
        }catch (\Exception $e)
        {
            echo $e->getMessage();
            //mongodb数据回滚操作
            UserChartInfo::where(['_id'=>$data->_id])->update(['is_cancel'=>0]);
            PlayerOrder::where(['round_no'=>$data->round_no,'user_name'=>$data->account])->update(['is_cancel'=>0]);
            CashRecord::where(['user_chart_id'=>$data->_id])->delete();
            DB::connection('mongodb')->collection('exception_cash_log')->where(['user_chart_id'=>$data->_id])->delete();
            DB::rollBack();//事物回滚
            return false;
        }
    }

    /**
     * 回滚派彩操作抽象，针对单局派彩+单个用户
     * @param $data 单局派彩数据信息
     * @param $desc  取消备注
     * @return bool
     */
    public static function rollbackOrder($data,$desc="",$nowTime)
    {
        /**
         * 注单回滚核心业务逻辑:
         *      取消注单时只需要回滚用户的有效下注金额即可
         */
        try
        {
            DB::beginTransaction();
            //获取用户的信息
            $userInfo = DB::table('lb_user')->where(['uid'=>(int)$data->user_id])->first();
            $nowDay = date("Y-m-d",$data->start_time->__toString()/1000);
            //进行数据回滚操作（增加多一条现金记录表数据，同时修改当前局和当前局和用户下的注单信息都为取消状态）
            $nowUserMoney = sprintf('%.2f',($data->total_bet_score + $userInfo->money));
            $inserData= [];
            $inserData['user_chart_id'] = $data->_id;
            $inserData['order_sn'] = $data->_id;
            $inserData['uid'] = $data->user_id;
            $inserData['agent_id'] = $data->agent_id;
            $inserData['hall_id'] = $data->hall_id;
            $inserData['user_name'] = $data->account;
            $inserData['type'] = self::getHallTypeById($data->game_hall_id);
            $inserData['amount'] = (float)$data->total_bet_score;
            $inserData['status'] = 3;
            $inserData['user_money'] = (float)$nowUserMoney;
            $inserData['desc'] = "局".$data->round_no."回滚";
            $inserData['admin_user'] = $data->account;
            $inserData['admin_user_id'] = $data->user_id;
            $inserData['cash_no'] = $data->round_no;
//            $inserData['add_time'] = new UTCDateTime(time() * 1000);
            $inserData['add_time'] = new UTCDateTime($nowTime * 1000);
            $inserData['pkey'] = md5($data->user_id.$data->round_no.$data->total_bet_score);
            $inserData["connect_mode"] = ($data->connect_mode == 1) ? 1 : 0;
            $res = CashRecord::insert($inserData);//新增一条现金记录

            //添加取消日志记录
            $logData['user_chart_id'] = $data->_id;
            $logData['order_sn'] = $data->_id;
            $logData['uid'] = $userInfo->uid;
            $logData['user_name'] = $data->account;
            $logData['agent_id'] = $userInfo->agent_id;
            $logData['agent_name'] = $userInfo->agent_name;
            $logData['hall_id'] = $userInfo->hall_id;
            $logData['hall_name'] = $userInfo->hall_name;
            $logData['round_no'] = $data->round_no;
            $logData['payout_win'] = (float)$data->total_win_score;
            $logData['before_user_money'] = (float)$userInfo->money;
            $logData['user_money'] = (float)$nowUserMoney;
            $logData['bet_time'] = $data->start_time;
            $logData['desc'] = $desc."(回滚)";
            $user = \Illuminate\Support\Facades\Auth::user();
            $logData['action_user'] = $user['user_name'];
            $logData['action_user_id'] = $user['id'];
            $logData['action_passivity'] = $data->round_no;
//            $logData['add_time'] = new UTCDateTime(time() * 1000);
            $inserData['add_time'] = new UTCDateTime($nowTime * 1000);
            DB::connection('mongodb')->collection('exception_cash_log')->insert($logData);

            //该局下的所有注单信息都为取消状态
            $cancelCount = PlayerOrder::where(['round_no'=>$data->round_no,'user_name'=>$data->account])->update(['is_cancel'=>1]);
            DB::table('lb_user')->where(['uid'=>(int)$data->user_id])->update(['money'=>$nowUserMoney]);//修改玩家余额

            $nowDay = date("Y-m-d",$data->start_time->__toString()/1000);

//            $statis_cash = DB::table("statis_cash")->where('add_date',$nowDay)->first();
//            //投注派彩统计表修改
//            if(isset($statis_cash->total_bet_score))
//            {
//                DB::table("statis_cash")->where('add_date',$nowDay)->update([
//                    'total_bet_score'   => $statis_cash->total_bet_score - $data->total_bet_score,
//                    'total_bet_count'   => $statis_cash->total_bet_count - $cancelCount
//                ]);
//            }
//
//            $statis_cash_agent = DB::table('statis_cash_agent')->where('add_date',$nowDay)->where('agent_id',$data->agent_id)->first();
//           if(isset($statis_cash_agent->total_bet_score))
//           {
//               DB::table('statis_cash_agent')->where('add_date',$nowDay)->where('agent_id',$data->agent_id)->update([
//                   'total_bet_score'   => $statis_cash_agent->total_bet_score - $data->total_bet_score,
//                   'total_bet_count'   => $statis_cash_agent->total_bet_count - $cancelCount
//               ]);
//           }
//
//            $statis_cash_player = DB::table("statis_cash_player")->where('add_date',$nowDay)->where('agent_id',$data->agent_id)->where("user_id",$data->user_id)->first();
//            if(isset($statis_cash_player->total_bet_score))
//            {
//                DB::table("statis_cash_player")->where('add_date',$nowDay)->where('agent_id',$data->agent_id)->where("user_id",$data->user_id)->update([
//                    'total_bet_score'   => $statis_cash_player->total_bet_score - $data->total_bet_score,
//                    'total_bet_count'   => $statis_cash_player->total_bet_count - $cancelCount,
//                ]);
//            }
            //修改当前局的状态为已取消状态
            UserChartInfo::where(['_id'=>$data->_id])->update(['is_cancel'=>1]);

            @addLog(['action_name'=>'注单取消','action_desc'=>' 进行注单取消(回滚)，用户为：'.$data->account.',局号为：'.$data->round_no,'action_passivity'=>'注单取消']);

            DB::commit();//事物提交
            return true;
        }catch (\Exception $e)
        {
            echo $e->getMessage();
            //mongodb数据回滚操作
            UserChartInfo::where(['_id'=>$data->_id])->update(['is_cancel'=>0]);
            PlayerOrder::where(['round_no'=>$data->round_no,'user_name'=>$data->account])->update(['is_cancel'=>0]);
            CashRecord::where(['user_chart_id'=>$data->_id])->delete();
            DB::connection('mongodb')->collection('exception_cash_log')->where(['user_chart_id'=>$data->_id])->delete();
            DB::rollBack();//事物回滚
            return false;
        }
    }

    //根据游戏厅ID获取到对应的厅级别类型
    public static function getHallTypeById($game_hall_id)
    {
        $type = 0;
        switch ($game_hall_id)
        {
            case 0:
                $type = 32; //旗舰厅取消退回
                break;
            case 1:
                $type = 35; //贵宾厅取消退回
                break;
            case 2:
                $type = 33; //金臂厅取消退回
                break;
            case 3:
                $type = 34; //至尊厅取消退回
                break;
            default :
                $type = 21;
        }

        return $type;
    }


}