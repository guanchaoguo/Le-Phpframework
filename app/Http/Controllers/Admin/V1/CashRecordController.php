<?php
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Models\CashRecord;
use App\Models\Agent;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
/**
 * Class CashRecordController
 * @package App\Http\Controllers\Admin\V1
 * @desc 现金流
 */
class CashRecordController extends BaseController
{
    public function __construct()
    {
        if( ! File::exists('excel/')) {
            File::makeDirectory('excel/');
        }
    }

    /**
     * @api {get} /cashRecord 查询现金流
     * @apiDescription 现金流列表
     * @apiGroup cashRecord
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token *
     * @apiParam {String} locale 语言 *
     * @apiParam {String} _id 单号
     * @apiParam {String} uid 玩家id
     * @apiParam {String} user_name 玩家登录名
     * @apiParam {String} time_type 时间类型 1：传时间段，2：具体时间段
     * @apiParam {String} time_area 当time_type=1时有效, 时间区 1：三天内，2：一周内，3：一个月内
     * @apiParam {String} start_time 当time_type=2时有效, 开始时间
     * @apiParam {String} end_time 当time_type=2时有效, 结束时间
     * @apiParam {String} type  操作类型, 1：api转入，2：api转出 ，3：人工操作（包括扣钱和加钱），4：下注(这个包括下注和派彩的记录）
     * @apiParam {Number} page 当前页 默认为1
     * @apiParam {Number} page_num 每页显示条数 默认 10
     * @apiParam {Number} is_export 是否导出 0不导出，1导出，默认为0不导出
     * @apiSuccessExample {json} Success-Response:现金流列表
     *      HTTP/1.1 200 OK
    {
    "code": 0,//状态码，0：成功，非0：错误
    "text": "操作成功",//文本描述
    "result": {//结果对象
    "total": 2,//总条数
    "per_page": 10,//每页显示条数
    "current_page": 1,//当前页
    "last_page": 1,//上一页
    "next_page_url": null,//下一页url
    "prev_page_url": null,//前一页url
    "data": [ //数据数组
    {
    "_id": '597ad7c8e1382314682fd841',//单号
    "cash_no": "923d3f9f07325ce4"//局ID（流水号）
    "user_name": "csj_play111",//玩家名称
    "type": 操作类型,1转帐,2打赏,3优惠退水,4线上变更,5公司入款,6优惠冲销,7视讯派彩,8系统取消出款,9系统拒绝出款,10取消派彩变更,21旗舰厅下注，22为至尊厅下注，23为金臂厅下注，24为贵宾厅下注,31视讯取消退回,32旗舰厅取消退回,33金臂厅取消退回,34至尊厅取消退回,35贵宾厅取消退回
    "amount": "-10",//加减的金额
    "user_money": 1980,//用户余额
    "add_time": "2017-03-28 08:00:00"//添加时间
    "connect_mode": 0//扣费模式：0为额度转换，1为共享钱包，默认为0
    }
    ]
    }
    }
     * @apiErrorExample {json} 现金流导出成功返回:
    HTTP/1.1 200 OK
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "url": "http://platform.va/excel/现金流_20170330.xlsx"//excel下载地址
    }
    }
     */
    public function index(Request $request)
    {

        $_id = $request->input('_id');
        $uid = $request->input('uid');
        $type = (int)$request->input('type');
        $user_name = $request->input('user_name');
        $cash_no = $request->input('cash_no');
        $time_type = $request->input('time_type');
        $time_area = $request->input('time_area');
        $start_time = $request->input('start_time');
        $end_time = $request->input('end_time');
        $page_num = $request->input('page_num', env('PAGE_NUM'));
        $is_export = $request->input('is_export');

        if($is_export && 0) {
            if( empty($uid) && empty($user_name)) {
                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.export_requisite_uid'),
                    'result'=>'',
                ]);
            }
        }
        $obj= CashRecord::select('uid','user_name','cash_no','amount','status','user_money','type','add_time','order_sn','connect_mode');
        $obj1= CashRecord::select('uid','user_name','cash_no','amount','status','user_money','type','add_time','order_sn','connect_mode');

        //获取测试，联调代理id
        $ids = Agent::where(['grade_id' => 2, 'is_hall_sub' => 0])->whereIn('account_type',[2,3])->pluck('id');
        $obj->whereNotIn('agent_id', $ids);
        $obj1->whereNotIn('agent_id', $ids);

        if(isset($_id) && !empty($_id)) {
            $obj->where('_id',  $_id);
            $obj1->where('_id',  $_id);
        }

        if(isset($uid) && !empty($uid)) {
            $obj->where('uid',  (int)$uid);
            $obj1->where('uid',  (int)$uid);
        }

        if(isset($user_name) && !empty($user_name)) {
            $obj->where('user_name',  $user_name);
            $obj1->where('user_name',  $user_name);
        }

        if(isset($cash_no) && !empty($cash_no)) {

            if(strstr($cash_no, 'LA')) {

                $obj->where('order_sn',$cash_no);
                $obj1->where('order_sn',$cash_no);

            } else {

                $obj->where('cash_no',  $cash_no);
                $obj1->where('cash_no',  $cash_no);
            }



//            $obj1->where('cash_no',  $cash_no);
//            $obj1->orWhere('order_sn',$cash_no);
        }
        if(isset($type) && !empty($type)) {
            switch ($type) {
                //api转入
                case 1:
                    $obj->where('type', 1);
                    $obj1->where('type', 1);
                    $obj->where('status', 3);
                    $obj1->where('status', 3);
                    break;
                //api转出
                case 2:
                    $obj->where('type', 1);
                    $obj1->where('type', 1);
                    $obj->where('status', 4);
                    $obj1->where('status', 4);
                    break;
                //人工操作（包括扣钱和加钱）
                case 3:
                    $obj->where('type',  4);
                    $obj1->where('type',  4);
                    break;
                //下注(这个包括下注和派彩的记录）
                case 4:
                    $obj->whereIn('type',  [7,10,21,22,23,24,31,32,33,34,35]);
                    $obj1->whereIn('type',  [7,10,21,22,23,24,31,32,33,34,35]);
                    break;
                //红包
                case 5:
                    $obj->where('type',  36);
                    $obj1->where('type',  36);
                    break;
            }

        }

        if($time_type == 1) {
            $time = '';
            switch ($time_area) {
                case 1:
                    //三天内
                    $time = self::getUTCDateTime(3);

                    break;
                case 2:
                    //一周内
                    $time = self::getUTCDateTime(7);
                    break;
                case 3:
                    //一月内
                    $time = self::getUTCDateTime(30);
                    break;
                default :
                    return $this->response->array([
                        'code'=>0,
                        'text'=> trans('agent.param_error'),
                        'result'=>'',
                    ]);
                    break;
            }

            if($time != '') {

                $obj->where('add_time',  '>=', $time[0]);
                $obj1->where('add_time',  '>=', $time[0]);
                $obj->where('add_time',  '<=', $time[1]);
                $obj1->where('add_time',  '<=', $time[1]);

            }

        }

        if($time_type == 2) {

            if(isset($start_time) && !empty($start_time)) {
                $s_time = Carbon::parse($start_time)->timestamp;
                $obj->where('add_time', '>=', new \MongoDB\BSON\UTCDateTime($s_time * 1000));
                $obj1->where('add_time', '>=', new \MongoDB\BSON\UTCDateTime($s_time * 1000));
            }

            if(isset($end_time) && !empty($end_time)) {
                $e_time = Carbon::parse($end_time)->timestamp + 1; //应对经过换算后精度损耗的问题，加多1S
                $obj->where('add_time', '<',new \MongoDB\BSON\UTCDateTime($e_time * 1000));
                $obj1->where('add_time', '<',new \MongoDB\BSON\UTCDateTime($e_time * 1000));


            }
        }

        $obj->orderby('add_time','desc');
        $obj1->orderby('add_time','desc');

        if($is_export) {
            $export_count = $obj->count();
            if($export_count) {
//                $filename = '现金流_'.date('Ymd',time());
                $filename = './excel/CashRecord_'.date('Ymd',time()).time().'.csv';
                $title = [
                    '玩家ID',
                    '登录名',
                    '单号',
                    '局ID(流水号)',
                    '加减金额',
                    '剩余额度',
                    '操作类型',
                    '操作时间(美东时间)',
                ];

                $pre_count = 30000;
                $export_count = $pre_count*10;
                set_time_limit(0);
                ini_set('memory_limit','500M');
                if ( File::exists( $filename) ){
                    File::delete($filename);
                }
                // 打开PHP文件句柄
                $fp = fopen($filename, 'a');
                // 将中文标题转换编码，否则乱码
                foreach ($title as $i => $v) {
                    $title[$i] = iconv('utf-8', 'GB18030', $v);
                }
                // 将标题名称通过fputcsv写到文件句柄
                fputcsv($fp, $title);


                //intval($export_count / $pre_count) +
                for ( $i = 0; $i < intval($export_count / $pre_count); $i++ ) {
                    $data = $obj1->offset($i*$pre_count)->limit($pre_count)->get()->toArray();

                    if($data){
                        $data = self::dataHandle($data, 1);
                        foreach ( $data as $item ) {
                            $rows = array();
                            foreach ( $item as $export_obj){
                                $rows[] = iconv('utf-8', 'GB18030', ' '.$export_obj.' ');
                            }
                            fputcsv($fp, $rows);
                        }
                        unset($data);
                        ob_flush();
                        flush();
                    } else {
                        break;
                    }

                }

                return $this->response->array([
                    'code' => 0,
                    'text' => trans('agent.success'),
                    'result' => [
                        'url' => 'http://'.$request->server("HTTP_HOST").'/'.$filename
                    ],
                ]);

            } else {

                return $this->response->array([
                    'code'=>400,
                    'text'=> trans('agent.no_data_export'),
                    'result'=>'',
                ]);

            }

        }
        $data = $obj->paginate((int)$page_num)->toArray();


        $data['data'] = self::dataHandle($data['data']);
        return $this->response->array([
            'code'=>0,
            'text'=> trans('agent.success'),
            'result'=>$data,
        ]);
    }

    /**现金流数据转换
     * @param array $data 数据
     * @param int $is_export 是否导出数据 1是，0否
     * @return array
     */
    private function dataHandle( array $data, int $is_export=0) : array
    {
        if($data) {
            $re_data = [];
            foreach ($data as $key=> &$v){
                $v['add_time'] = $v['add_time']->__tostring();
                $v['add_time'] = date('Y-m-d H:i:s',$v['add_time']/1000);
                if($v['status'] == 3) {
                    $v['amount'] = '+'.number_format($v['amount'],2);
                }
                if($v['status'] == 4) {
                    $v['amount'] = '-'.number_format($v['amount'],2);
                }
                $v['user_money'] = number_format($v['user_money'],2);

                if(in_array($v['type'],[1,4,5]))
                {
                    $v['cash_no'] = $v['order_sn'];
                }

                !isset($v['cash_no']) && $v['cash_no'] = "";

                if($is_export) {
                    switch ($v['type']) {
                        case 1:
                            $v['type'] = '转账';
                            break;
                        case 2:
                            $v['type'] = '打赏';
                            break;
                        case 3:
                            $v['type'] = '优惠退水';
                            break;
                        case 4:
                            $v['type'] = '线上变更';
                            break;
                        case 5:
                            $v['type'] = '公司入款';
                            break;
                        case 6:
                            $v['type'] = '优惠冲销';
                            break;
                        case 7:
                            $v['type'] = '视讯派彩';
                            break;
                        case 8:
                            $v['type'] = '系统取消出款';
                            break;
                        case 9:
                            $v['type'] = '系统拒绝出款';
                            break;
                        case 10:
                            $v['type'] = '取消派彩变更';
                            break;
                        case 21:
                            $v['type'] = '旗舰厅下注';
                            break;
                        case 22:
                            $v['type'] = '至尊厅下注';
                            break;
                        case 23:
                            $v['type'] = '金臂厅下注';
                            break;
                        case 24:
                            $v['type'] = '贵宾厅下注';
                            break;
                        case 31:
                            $v['type'] = '视讯取消退回';
                            break;
                        case 32:
                            $v['type'] = '旗舰厅取消退回';
                            break;
                        case 33:
                            $v['type'] = '金臂厅取消退回';
                            break;
                        case 34:
                            $v['type'] = '至尊厅取消退回';
                            break;
                        case 35:
                            $v['type'] = '贵宾厅取消退回';
                        case 36:
                            $v['type'] = '红包';
                    }
                }


                unset($v['status']);
//                unset($v['order_sn']);

                $v = [
                    'uid' => $v['uid'],
                    'user_name' => $v['user_name'],
                    '_id' => $v['_id'],
                    'cash_no' => $v['cash_no'],
                    'amount' => $v['amount'],
                    'user_money' => $v['user_money'],
                    'type' => $v['type'],
                    'add_time' => $v['add_time'],
                    'connect_mode' => isset($v['connect_mode']) ? $v['connect_mode'] : 0,//添加扣费模式字段

                ];
            }
            unset($v);

            return $data;
        }
        return [];
    }

    /**
     * 返回mongo的UTC时间
     * @param int $day 几天内
     */
    private function getUTCDateTime(int $day)
    {
        if($day) {
            $start_d = (new Carbon('-'.$day.' day'))->startOfDay()->timestamp;
            $end_d = (new Carbon())->timestamp;
            $s_time = new \MongoDB\BSON\UTCDateTime($start_d * 1000);
            $e_time = new \MongoDB\BSON\UTCDateTime($end_d * 1000);
            return [
                $s_time,
                $e_time
            ];
        } else {
            return '';
        }


    }
}