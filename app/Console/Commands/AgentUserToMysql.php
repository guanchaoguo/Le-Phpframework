<?php

/**
 * 外部厅主、代理商导入（密码为明文，没有盐值），以厅主为单位，一个厅主一张表
 * User: chensongjian
 * Date: 2017/7/21
 * Time: 10:00
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\AgentMenuList;
use App\Models\AgentMenus;
use App\Models\GameHall;
use App\Models\AgentGame;
use App\Models\HallLimitGroup;
use App\Models\HallLimitItem;
use App\Models\Whitelist;
use Carbon\Carbon;

class AgentUserToMysql extends Command
{
    /**
     * 命令名称标识
     * protected $commands = [
    \App\Console\Commands\AgentUserToMysql::class
     * ]
     * @var string
     */
    protected $signature = 'AgentUserImportToMysql {agentImport=lb_agent_user_import}';

    /**
     * 命令描述
     * @var string
     */
    protected $description = 'agent user import to mysql';

    //代理商外部表
    const AGENT_IMPORT = 'lb_agent_user_import';
    //代理商原表
    const AGENT = 'lb_agent_user';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $t1 = microtime(true);

        DB::beginTransaction();
        $hall_agent = self::getAgent(1);//获取厅主信息

        //导入厅主
        $hall_id = self::importAgent($hall_agent);

        if( count($hall_id) ) {
            //设置厅主权限
            if ( ! self::setDefalutRole($hall_id[0], 1) ){
                $this->info('hall_id'.$hall_id[0].'The hall agent set defalut role failure!!!');
                DB::rollBack();
                return;
            }
            //开通游戏种类
            if ( ! self::setGameHall($hall_id[0]) ){
                $this->info('hall_id'.$hall_id[0].'The hall agent set game hall failure!!!');
                DB::rollBack();
                return;
            }

            //设置游戏限额
            /*if( ! self::setGameScore($hall_id[0])) {
                $this->info('hall_id'.$hall_id[0].'The hall agent set game score failure!!!');
                DB::rollBack();
                return;
            }*/

            //设置白名单
            if( ! self::setWhiteList($hall_id[0]) ) {
                $this->info('hall_id'.$hall_id[0].'The hall agent set whitelist failure!!!');
                DB::rollBack();
                return;
            }
            //获取厅主下面的多个代理商
            $agent = self::getAgent(2, $hall_id[0]);//获取代理商信息+厅主id
            //导入代理商
            $agent_ids = self::importAgent($agent);
            if( ! count($agent_ids) ) {
                $this->info('The agent insert failure!!!');
                DB::rollBack();
                return;
            }

            foreach ($agent_ids as $agent_id) {
                //设置代理商权限
                if ( ! self::setDefalutRole($agent_id, 2) ){
                    $this->info('agent_id'.$agent_id.'The agent set defalut role failure!!!');
                    DB::rollBack();
                    return;
                }
            }

            //更新厅主表的代理商数
            DB::table(self::AGENT)->where('id',$hall_id[0])->update(['sub_count'=> count($agent)]);

        } else {
            $this->info('The hall agent insert failure!!!');
            DB::rollBack();
            return;
        }

        DB::commit();
        $t2 = microtime(true);
        $this->info('Took '.round($t2-$t1,2).' seconds');
        $this->info('Import is complete!');


    }

    /**获取代外部理商数据
     * @param int $grade_id 代理级别，总代则为1，2为二级代理
     * @param int $parent_id 上级代理ID
     * @return array
     */
    protected function getAgent(int $grade_id = 1, int $parent_id = 0) : array
    {
        //外部输入导入表的参数
        $agentImport = $this->argument('agentImport');

        $db = DB::table($agentImport);//要导入的外部代理商表

        $db->where('grade_id',$grade_id);
        if($grade_id == 1) {
            $db->limit(1);
        }
        $agent = $db->get()->toArray();
        foreach ($agent as $k => &$item){
            $item = (array)$item;
            $item['salt'] = randomkeys(20);//创建盐值
            $item['password'] = app('hash')->make($item['password'] . $item['salt']);//将文明密码+盐值加密
            $parent_id && $item['parent_id'] = $parent_id;
            $item['area'] = '香港';//默认地区
            $item['time_zone'] = '(GMT+08:00) Beijing';//默认时区
            if($grade_id == 2 && $k != 0) {
                $item['agent_code'] = $item['agent_code'] . '_' . $k;
            }
            unset($item['id']);

        };
        unset($item);
        return $agent;
    }

    /**导入代理商数据
     * @param array $agent_data 代理商数据 二维数组
     * @return array []：表示插入失败, 返回插入成功的id
     */
    protected function importAgent(array $agent_data) : array
    {
        $insert_ids = [];
        foreach ($agent_data as $v) {

            $res = DB::table(self::AGENT)->where('user_name', $v['user_name'])->first();
            if( ! $res ) {
                $re = DB::table(self::AGENT)->insert($v);//插入数据库

                if($re) {
                    $id = DB::table(self::AGENT)->where('user_name', $v['user_name'])->pluck('id')[0];
                    //存放插入的id
                    $insert_ids[] = $id;
                } else {
                    return [];
                }
            } else {
                return [];
            }
        }
        return $insert_ids;
    }


    /**
     * 为厅主、代理商开通菜单权限
     * @param int $agent_id 厅主、代理商id
     * @param int $grade_id 代理商级别 1厅主 2代理商
     * @return mixed
     */
    protected function setDefalutRole(int $agent_id, int $grade_id)
    {
        //获取当前代理商类型所属的菜单列表[menu_id，parent_id]
        $agent_menu = AgentMenuList::select(
            'menu_id',
            'parent_id',
            DB::raw('CONCAT('.$agent_id.') as user_id')
        )->where('grade_id',$grade_id)->where('state',1)->get()->toArray();
        AgentMenus::where('user_id',$agent_id)->delete();
        $re = AgentMenus::insert($agent_menu);
        return $re;
    }

    /**
     * 为厅主开通游戏种类
     * @param int $agent_id 厅主id
     * @return bool
     */
    protected function setGameHall(int $agent_id=0)
    {
        if($agent_id == 0) {
            return false;
        }
        $data = GameHall::select('id')->orderBy('id')->get();
        $agent_menu = [];
        foreach ($data as $v){
            $v->games;
            foreach ($v->games as $vv) {
                $tmp = [
                    'agent_id' => $agent_id,
                    'game_id' => $vv->id,
                    'hall_id' => $v->id,
                    'status' => 1,//默认显示游戏
                ];
                $agent_menu[] = $tmp;
            }
        }
        AgentGame::where('agent_id',$agent_id)->delete();
        $res = AgentGame::insert($agent_menu);
       return $res;
    }

    /**
     * 为厅主开通默认游戏限额 该方法暂时不处理
     * @param int $agent_id 厅主id
     * @return int
     */
    protected function setGameScore(int $agent_id)
    {
        $time = date('Y-m-d H:i:s',time());
        //游戏厅循环
        foreach ([0,1,2,3] as $cat) {
            foreach (['defaultA','defaultB','defaultC'] AS $default) {
                $data_insert1 = $data_insert2 = [
                    'agent_id' => $agent_id,
                    'hall_type' => $cat,
                    'title' => $default,
                ];
                $re = HallLimitGroup::where($data_insert1)->first();
                if( ! $re ) {
                    $data_insert1['status'] = 1;
                    $data_insert1['uptime'] = $time;
                    $res = HallLimitGroup::create($data_insert1);
                    if( $res ) {
                        $data_insert2['agent_id'] = 0;
                        $limitItem = HallLimitGroup::where($data_insert2)->first()->limitItem->toArray();
                        foreach ($limitItem as &$item) {
                            $item['group_id'] = $res['id'];
                        }
                        unset($item);
                        $re = HallLimitItem::insert($limitItem);
                        if( ! $re ) return 0;
                    } else {
                        return 0;
                    }
                }
            }
        }
        return 1;
    }


    /**为厅主添加默认白名单
     * @param int $agent_id 厅主id
     * @return mixed
     */
    protected function setWhiteList(int $agent_id)
    {
        $agent = DB::table(self::AGENT)->select('id','user_name')->find($agent_id);
        $str = str_shuffle($agent->user_name.mt_rand(10,100000));
        $securityKey = createSecurityKey(env('SECURITY_KEY_ENCRYPT'),$str);
        $data = [
            'ip_info' => '*',
            'agent_id' => $agent_id,
            'agent_name' => $agent->user_name,
            'state' => 1,
        ];
        $data['agent_seckey'] = $securityKey;
        $data['seckey_exp_date'] = Carbon::parse('+'.env('KEY_MAX_VALID_TIME').' days')->toDateTimeString();
        $re = Whitelist::create($data);
        return $re;
    }
}
