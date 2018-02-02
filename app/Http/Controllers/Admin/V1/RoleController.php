<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkl.com
 * Date: 2017/2/14
 * Time: 10:41
 * 后台角色相关操作控制器
 */

namespace App\Http\Controllers\Admin\V1;

use App\Models\Menu;
use App\Models\PlatformUser;
use App\Models\RoleGroupMenu;
use App\Models\User;
use App\Models\UserMenu;
use Illuminate\Http\Request;
use App\Models\RoleGroup;
use Illuminate\Support\Facades\DB;

class RoleController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {get} /role 角色组列表
     * @apiDescription 获取后台角色组列表
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page 当前页
     * @apiParam {Number} page_num 每页条数,默认10条每页
     * @apiSampleRequest http://app-loc.dev/api/role
     * @apiSuccessExample {json} Success-Response:
     *      {
                "code": 0,
                "text": "操作成功",
                "result": {
                    "total": 6,
                    "per_page": "10",
                    "current_page": 1,
                    "last_page": 1,
                    "next_page_url": null,
                    "prev_page_url": null,
                    "from": 1,
                    "to": 6,
                    "data": [
                    {
                        "id": 1,
                        "group_name": "管理员组",
                        "desc": "管理员权限组",
                        "add_time": "2017-01-20 15:41:16",
                        "state": 1
                        },
                        {
                        "id": 2,
                        "group_name": "编辑1组",
                        "desc": "编辑1组",
                        "add_time": "2017-01-20 15:42:16",
                        "state": 1
                        },
                        {
                        "id": 3,
                        "group_name": "新的角色分组",
                        "desc": null,
                        "add_time": "2017-02-14 13:45:58",
                        "state": 1
                        },
                        {
                        "id": 4,
                        "group_name": "新的角色分组22",
                        "desc": "我是描述22",
                        "add_time": "2017-02-14 13:46:30",
                        "state": 1
                        },
                        {
                        "id": 5,
                        "group_name": "测试分组1",
                        "desc": "测试分组描述",
                        "add_time": "2017-02-15 16:58:54",
                        "state": 1
                        },
                        {
                        "id": 6,
                        "group_name": "测试分组2",
                        "desc": "测试分组描述",
                        "add_time": "2017-02-15 17:00:58",
                        "state": 1
                        }
                    ]
                }
             }
     *  @apiErrorExample  {json} Error-Response:
     * {
            code:400,
            text:'数据列表为空',
            result:''
        }
     */
    public function index(Request $request)
    {
        $page_num = $request->input('page_num',10);
        $roleList = RoleGroup::where('state','=',1)->orderBy('id','desc')->paginate($page_num);

        //数据为空时
        if(!$roleList)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.empty_list'),
                'result' => ''
            ]);
        }

        //数据请求成功返回
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => $roleList
        ]);
    }

    /**
     * @api {post} /role 新增角色组
     * @apiDescription 新增后台角色组操作
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} group_name 角色分组名称
     * @apiParam {String} desc 角色分组描述
     * @apiParam {Int} id 角色分组ID,如果是添加操作则为空，编辑操作为对应数据ID
     * @apiSampleRequest http://app-loc.dev/api/role
     * @apiSuccessExample {json} Success-Response:
     *    {
            "code": 0,
            "text": "操作成功",
            "result": {
                "id": 9
                }
            }
     * {
      * @apiErrorExample  {json} Error-Response:
        {
            "code": 400,
            "text": "操作失败",
            "result": ""
        }
     */
    public function store(Request $request)
    {
        $message = [
            'group_name.required'    => trans('role.group_name.required'),
            'group_name.max'        => trans('role.group_name.max')
        ];

        //先进行数据验证操作
        $validator = \Validator::make($request->input(),[
            'group_name'    => 'required|max:45'
        ],$message);

        //验证失败返回验证错误信息
        if ($validator->fails())
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => $validator->errors(),
                'result'    => ''
            ]);
        }

        //验证通过进行数据的添加操作
        $attributes = $request->except('token','locale');//过滤掉token 和 locale字段

        $id = $request->input('id',0);
        if($id > 0)
        {//编辑操作
            $attributes['add_time'] = date("Y-m-d H:i:s",time());
            $res = RoleGroup::where(['id'=>$id])->update($attributes);
            if($res)
                $res = $id;
        }
        else
        {
            $attributes['add_time'] = date("Y-m-d H:i:s",time());
            $res = RoleGroup::insertGetId($attributes);
        }


        //数据写入失败返回错误信息
        if(!$res)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.fails'),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'新增角色组','action_desc'=>' 新增了组账户，组账户名称为：'.$request->input('group_name'),'action_passivity'=>$request->input('group_name')]);

        //数据写入成功返回成功信息
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => [
                'id'    => $res
            ]
        ]);


    }

    /**
     * @api {post} /role/showMenus/{id} 编辑角色权限时，获取菜单数据
     * @apiDescription 编辑角色权限时，获取菜单数据
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {int}  id  编辑的角色分组id
     * @apiSampleRequest http://app-loc.dev/api/role/showMenus/{id}
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "allRole": [
    {
    "id": 1,
    "parent_id": 0,
    "title_cn": "账号管理",
    "title_en": "",
    "class": 0,
    "desc": "账号管理",
    "link_url": "/account/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "account",
    "isHaveRole": 1,
    "_child": [
    {
    "id": 5,
    "parent_id": 1,
    "title_cn": "厅主管理",
    "title_en": "",
    "class": 0,
    "desc": "厅主管理",
    "link_url": "/haller/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "haller",
    "isHaveRole": 0
    },
    {
    "id": 6,
    "parent_id": 1,
    "title_cn": "代理管理管理",
    "title_en": "",
    "class": 0,
    "desc": "代理",
    "link_url": "/agent/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "agent",
    "isHaveRole": 0
    },
    {
    "id": 7,
    "parent_id": 1,
    "title_cn": "玩家管理",
    "title_en": "",
    "class": 0,
    "desc": "玩家管理",
    "link_url": "/player/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "palyer",
    "isHaveRole": 0
    }
    ]
    },
    {
    "id": 2,
    "parent_id": 0,
    "title_cn": "游戏管理",
    "title_en": "",
    "class": 0,
    "desc": "游戏管理",
    "link_url": "/game/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "game",
    "isHaveRole": 1,
    "_child": [
    {
    "id": 8,
    "parent_id": 2,
    "title_cn": "游戏列表",
    "title_en": "",
    "class": 0,
    "desc": "游戏列表",
    "link_url": "/games/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "games",
    "isHaveRole": 0
    },
    {
    "id": 9,
    "parent_id": 2,
    "title_cn": "游戏限额",
    "title_en": "",
    "class": 0,
    "desc": "游戏限额",
    "link_url": "games/limit",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "games_limit",
    "isHaveRole": 0
    }
    ]
    },
    {
    "id": 3,
    "parent_id": 0,
    "title_cn": "报表统计",
    "title_en": "",
    "class": 0,
    "desc": "报表统计",
    "link_url": "/report/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "report",
    "isHaveRole": 1,
    "_child": [
    {
    "id": 10,
    "parent_id": 3,
    "title_cn": "游戏数据统计",
    "title_en": "",
    "class": 0,
    "desc": "游戏数据统计",
    "link_url": "game_report/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "game_report",
    "isHaveRole": 0,
    "_child": [
    {
    "id": 11,
    "parent_id": 10,
    "title_cn": "查询游戏",
    "title_en": "",
    "class": 0,
    "desc": "查询游戏",
    "link_url": "game_select/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "game_select",
    "isHaveRole": 0,
    "_child": [
    {
    "id": 26,
    "parent_id": 11,
    "title_cn": "开始了22",
    "title_en": "abc",
    "class": 1,
    "desc": null,
    "link_url": "www.baidu.com",
    "icon": "www.baidu.com",
    "state": 1,
    "sort_id": 100,
    "menu_code": null,
    "isHaveRole": 0
    },
    {
    "id": 28,
    "parent_id": 11,
    "title_cn": "开始了",
    "title_en": "abc",
    "class": 1,
    "desc": null,
    "link_url": "www.baidu.com",
    "icon": "www.baidu.com",
    "state": 1,
    "sort_id": 100,
    "menu_code": null,
    "isHaveRole": 0
    }
    ]
    }
    ]
    }
    ]
    },
    {
    "id": 4,
    "parent_id": 0,
    "title_cn": "系统管理",
    "title_en": "",
    "class": 0,
    "desc": "系统管理",
    "link_url": "/system/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "system",
    "isHaveRole": 1,
    "_child": [
    {
    "id": 12,
    "parent_id": 4,
    "title_cn": "白名单管理",
    "title_en": "",
    "class": 0,
    "desc": "白名单管理",
    "link_url": "system/allow",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "system_allow",
    "isHaveRole": 0
    },
    {
    "id": 13,
    "parent_id": 4,
    "title_cn": "菜单管理",
    "title_en": "",
    "class": 0,
    "desc": "菜单管理",
    "link_url": "system/menus",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "system_menus",
    "isHaveRole": 0
    },
    {
    "id": 14,
    "parent_id": 4,
    "title_cn": "角色管理",
    "title_en": "",
    "class": 0,
    "desc": "角色管理",
    "link_url": "system/role",
    "icon": "",
    "state": null,
    "sort_id": null,
    "menu_code": "system_role",
    "isHaveRole": 0
    }
    ]
    }
    ]
    }
    }
     * {
     * @apiErrorExample  {json} Error-Response:
    {
    "code": 400,
    "text": "操作失败",
    "result": ""
    }
     */
    public function showMenus(Request $request,$id)
    {
        //首先验证编辑的数据是否存在
        $groupFind = RoleGroup::find($id);
        if(!$groupFind)
        {
            //数据不存在返回错误提示
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.fails'),
                'result'    => ''
            ]);
        }

        //获取所有菜单信息
        $menusList = Menu::orderBy('sort_id','desc')->where('state',1)->get()->toArray();
        //获取用户已有菜单权限
        $userHaveRole = array_column(RoleGroupMenu::where('role_id',$id)->select('menu_id')->get()->toArray(),'menu_id');

        //遍历查看用户是否已经拥有该权限
        foreach ($menusList as $k=>$v)
        {
            if(in_array($v['id'],$userHaveRole))
            {
                $menusList[$k]['isHaveRole'] = 1;
            }
            else
            {
                $menusList[$k]['isHaveRole'] = 0;
            }
        }

        $menusTree = list_to_tree($menusList,'id','parent_id');

        //没有获取到菜单数据返回错误信息
        if(!$menusTree)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.empty_list'),
                'result'    => ''
            ]);
        }

        //返回菜单列表数据
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('role.success'),
            'result'    => [
                'allRole'   => $menusTree,
            ]
        ]);
    }


    /**
     * @api {patch} /role/updateRole/{id} 修改角色组权限信息
     * @apiDescription 修改角色组权限信息
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} roles 权限菜单数组，格式为 "role" => ['id-parent_id']
     * @apiSampleRequest http://app-loc.dev/api/role/updateRole/{id}
     * @apiSuccessExample {json} Success-Response:
     *    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "id": 9
    }
    }
     * {
     * @apiErrorExample  {json} Error-Response:
    {
    "code": 400,
    "text": "操作失败",
    "result": ""
    }

     */
    public function updateRole(Request $request,$id)
    {
        //获取需要编辑的数据信息
        $roleGroup  = RoleGroup::find($id);
        if(!$roleGroup)//需要编辑的数据错误
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //进行角色分组权限选择验证操作
        $messages = [
            'roles.required'    => trans('role.roles.required')
        ];

        $validator = \Validator::make($request->input(),[
            'roles'     => 'required'
        ],$messages);

        //数据格式验证错误，返回错误信息
        if($validator->fails())
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => $validator->errors(),
                'result'    => ''
            ]);
        }

       /**
        * 数据通过格式验证，开始进行角色权限字段拆解操作
        * 前后端约定格式为 roles =>[id-parent_id,id-parent_id....]
        * 而数据库中需要获取到每个权限菜单的父级ID
        * 所以需要进行数据拆解或者到父级ID
        */
        $rolesList = [];
       if(is_array($request->input('roles')))
       {
           foreach ($request->input('roles') as $v)
           {
               $menus = explode('-',$v);
                $rolesList[] = [
                    'role_id'   => $id,
                    'menu_id'   => $menus[0],
                    'parent_id' => $menus[1]
                ];
           }
       }

       //如果数据拆解错误，证明前端提交数据格式错误，返回错误信息
        if(empty($rolesList))
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.data_error'),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'修改角色组权限信息','action_desc'=>' 修改了角色组权限信息，角色组名称为：'.$roleGroup->group_name,'action_passivity'=>$roleGroup->group_name]);

        //判断是否需要进行删除旧数据操作
        $roleMenus  = RoleGroupMenu::where('role_id',$id)->get()->toArray();
        if(!$roleMenus)
        {//不存在则进行添加操作
            $res = RoleGroupMenu::insert($rolesList);
            if(!$res)
            {
                return $this->response()->array([
                    'code'  => 400,
                    'text'  => trans('role.fails'),
                    'result'   => ''
                ]);
            }

            return $this->response()->array([
                'code'  => 0,
                'text'  => trans('role.success'),
                'result'    => ''
            ]);
        }
        else
        {//数据存在，则要进行删除旧数据，然后进行写入操作，需要用到事物进行控制
            DB::beginTransaction();
            try
            {
                $de = RoleGroupMenu::where('role_id',$id)->delete();
                if(!$de)
                {
                    throw new \Exception("delete error");
                }
                $ins = RoleGroupMenu::insert($rolesList);
                if(!$ins)
                {
                    throw new \Exception('insert error');
                }
                DB::commit();//事物提交

                return $this->response()->array([
                    'code'  => 0,
                    'text'  => trans('role.success'),
                    'result'    => ''
                ]);
            }
            catch (\Exception $e)
            {
                DB::rollBack();//事物回滚

                return $this->response()->array([
                    'code'  => 400,
                    'text'  => trans('role.fails'),
                    'result'   => ''
                ]);
            }

        }
    }

    /**
     * @api {delete} /role/deleteGroup/{id} 删除角色组
     * @apiDescription 删除角色组
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSampleRequest http://app-loc.dev/api/role/deleteGroup/{id}
     * @apiSuccessExample {json} Success-Response:
     *    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "id": 9
    }
    }
     * {
     * @apiErrorExample  {json} Error-Response:
    {
    "code": 400,
    "text": "操作失败",
    "result": ""
    }
     * {
    "code": 400,
    "text": "该分组下还有子账户，不能进行删除操作，需要解除子账户关联关系后才能进行",
    "result": ""
    }
     */
    public function deleteGroup(Request $request,$id)
    {
        //获取需要删除的信息
        $findGroup = RoleGroup::where('state','=',1)->find($id);
        if(!$findGroup)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.data_error'),
                'result'   => ''
            ]);
        }

        //进行删除操作，删除动作前提是该角色分组下没有子账户
        $subAccount = PlatformUser::where('group_id',$id)->whereIn('account_state',[1,2])->select('id')->get()->toArray();
        //如果底下存在子帐号，则提示不能进行删除操作，需要把其底下的子账户删除后才能进行删除操作
        if($subAccount)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.sub_account'),
                'result'    => ''
            ]);
        }

        //如果没有子账户信息，则进行软删除操作
        $res = RoleGroup::where('id',$id)->update(['state'=>0]);
        //操作失败
        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.fails'),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'删除角色组','action_desc'=>' 删除了一个角色组，角色组名称为：'.$findGroup->group_name,'action_passivity'=>$findGroup->group_name]);

        //操作成功
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => ''
        ]);

    }

    /**
     * @api {get} /role/accountList 子账户列表
     * @apiDescription 获取后台子账户列表
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page 当前页
     * @apiParam {Number} page_num 每页条数,默认10条每页
     * @apiSampleRequest http://app-loc.dev/api/role/accountList
     * @apiSuccessExample {json} Success-Response:
     *     {
    "code": 0,
    "text": "操作成功",
    "result": {
    "total": 4,
    "per_page": 10,
    "current_page": 1,
    "last_page": 1,
    "next_page_url": null,
    "prev_page_url": null,
    "from": 1,
    "to": 4,
    "data": [
    {
    "id": 1,
    "user_name": "chensj",
    "desc": "陈",
    "group_id": 1,
    "tel": "15013777164",
    "account_state": 3,
    "add_time": "2017-01-20 14:13:09",
    "update_time": "2017-02-15 17:35:01",
    "ip_info": "127.0.0.1"
    },
    {
    "id": 2,
    "user_name": "test",
    "desc": null,
    "group_id": 0,
    "tel": null,
    "account_state": 1,
    "add_time": "2017-02-08 17:09:22",
    "update_time": "2017-02-16 13:44:25",
    "ip_info": null
    },
    {
    "id": 3,
    "user_name": "user_name-1",
    "desc": "这里是描述",
    "group_id": 0,
    "tel": null,
    "account_state": 1,
    "add_time": "2017-02-15 17:49:52",
    "update_time": "2017-02-15 17:49:52",
    "ip_info": null,
    "role_group": {
    "group_name": "管理员组"
    }
    },
    {
    "id": 4,
    "user_name": "user_name-2",
    "desc": "这里是描述-2",
    "group_id": 0,
    "tel": null,
    "account_state": 1,
    "add_time": "2017-02-15 17:50:47",
    "update_time": "2017-02-15 17:50:47",
    "ip_info": null,
    "role_group": {
    "group_name": "管理员组"
    }
    }
    ]
    }
    }
     *  @apiErrorExample  {json} Error-Response:
     * {
    code:400,
    text:'数据列表为空',
    result:''
    }
     */
    public function accountList(Request $request)
    {
        $page_num = $request->input('page_num',10);
        $platformList = PlatformUser::where('account_state','<',3)->orderBy('id','desc')->paginate($page_num);

        foreach ($platformList as $v) {
            if( ! $v->roleGroup ) {
                unset($v->roleGroup);
                $v->role_group = ['group_name' => ''];
            }
        }
        //数据为空时
        if(!$platformList)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.empty_list'),
                'result' => ''
            ]);
        }

        //数据请求成功返回
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => $platformList
        ]);
    }

    /**
     * @api {post} /role/addAccount 新增子帐号
     * @apiDescription 新增后台子帐号操作
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} user_name 子帐号名称
     * @apiParam {String} password 子帐号密码
     * @apiParam {String} password_confirmation 密码确认
     * @apiParam {Int} id id 子账号ID,如果是添加操作则为空，编辑操作为对应数据ID
     * @apiSampleRequest http://app-loc.dev/api/addAccount
     * @apiSuccessExample {json} Success-Response:
     *  {
    "code": 0,
    "text": "操作成功",
    "result": {
    "id": 5
    }
    }
     * {
     * @apiErrorExample  {json} Error-Response:
    {
    "code": 400,
    "text": "操作失败",
    "result": ""
    }
     */
    public function addAccount(Request $request)
    {
        $id = $request->input('id',0);
        //数据验证
        $message = [
            'user_name.required'    => trans('role201.user_name.required'),
            'user_name.max'         => trans('role.user_name.max'),
            'user_name.min'         => trans('role.user_name.min'),
            'user_name.regex' => trans('role201.user_name.regex'),
            'password.required'     => trans('role.password.required'),
            'password.max'          => trans('role.password.max'),
            'password.min'          => trans('role.password.min'),
            'password.confirmation' => trans('role.password.confirmation'),
            'desc.max'              => trans('role.desc.max')
        ];

        $validator = \Validator::make($request->input(),[
//            'user_name'    => 'required|max:45|min:3',
            'user_name'    => [
                'required',
                'regex:/^[a-zA-z][a-zA-Z0-9_]{2,44}$/'
            ],
            'password'  => 'required|confirmed|max:20|min:6',
            'desc'      => 'max:45'
        ],$message);

        if($validator->fails())
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => $validator->errors(),
                'result'    => ''
            ]);
        }

        //验证用户登陆名唯一性
        if($id == 0)
        {
            $find = PlatformUser::where('user_name','=',$request->input('user_name'))->where('account_state','<',3)->first();
        }
        else
        {
            $find = false;
        }
        if($find)
        {
            return $this->response->array([
                'code'  => 400,
                'text'  => trans('role.user_exists'),
                'result'    => ''
            ]);
        }

        //数据验证通过进行数据添加操作
        $attributes = $request->except('token','locale','password_confirmation');//过滤掉token 和 locale字段
        $salt = randomkeys(20);
        $attributes['salt'] = $salt;
        $attributes['password'] = app('hash')->make($request->input('password').$salt);
        $attributes['add_time'] = date("Y-m-d H:i:s",time());
        $attributes['update_time'] = date("Y-m-d H:i:s",time());
        if($id > 0)
        {//编辑操作

            $res = PlatformUser::where(['id'=>$id])->update($attributes);
            if($res)
                $res = $id;
        }
        else
        {//添加操作
            $res = PlatformUser::insertGetId($attributes);
        }

        //账号创建失败返回错误信息
        if(!$res)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.fails'),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'新增子账号','action_desc'=>' 新增了一个子账号，子账号为：'.$request->input('user_name'),'action_passivity'=>$request->input('user_name')]);
        //创建成功返回成功信息和ID
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('role.success'),
            'result'    => [
                'id'    => $res
            ]
        ]);
    }

    /**
     * @api {post} /role/accountState/{id} 删除、冻结、启用子帐号
     * @apiDescription 删除、冻结、启用子帐号
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Int} state 状态码 1为正常，2为停用，3为删除
     * @apiSampleRequest http://app-loc.dev/api/accountState/{id}
     * @apiSuccessExample {json} Success-Response:
     *  {
    "code": 0,
    "text": "操作成功",
    "result": ""
    }
     * {
     * @apiErrorExample  {json} Error-Response:
    {
    "code": 400,
    "text": "操作失败",
    "result": ""
    }
     * {
    "code": 400,
    "text": "数据错误",
    "result": ""
    }
     */
    public function accountState(Request $request,$id)
    {
        $state = $request->input('state');
        $validator = \Validator::make($request->input(),[
            'state'     => 'required|in:1,2,3'
        ]);
        if($validator->fails())
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => $validator->errors(),
                'result'    => ''
            ]);
        }
        //获取需要修改状态的数据信息
        $findAccount = PlatformUser::find($id);
        if(!$findAccount)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  =>trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //进行账号的状态修改操作
        if($state == 3)
        {
            $res = PlatformUser::where('id',$id)->delete();
        }else
        {
            $res = PlatformUser::where('id',$id)->update(['account_state'=>$state]);
        }


        if(!$res)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.fails'),
                'result'    => ''
            ]);
        }
        switch ($state)
        {
            case 1:
                @addLog(['action_name'=>'恢复子账号','action_desc'=>' 对子账号状态进行恢复操作，子账号为：'.$findAccount->user_name,'action_passivity'=>$findAccount->user_name]);
                break;
            case 2:
                @addLog(['action_name'=>'停用子账号','action_desc'=>' 停用了子账号，子账号为：'.$findAccount->user_name,'action_passivity'=>$findAccount->user_name]);
                break;
            case 3:
                @addLog(['action_name'=>'删除子账号','action_desc'=>' 删除了子账号，子账号为：'.$findAccount->user_name,'action_passivity'=>$findAccount->user_name]);
                break;
        }
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('role.success'),
            'result'   =>''
        ]);
    }

    /**
     * @api {post} /role/editPwd/{id} 子帐号修改密码操作
     * @apiDescription 后台子帐号修改密码操作
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String} password 子帐号密码
     * @apiParam {String} password_confirmation 密码确认
     * @apiSampleRequest http://app-loc.dev/api/editPwd/{id}
     * @apiSuccessExample {json} Success-Response:
     *  {
    "code": 0,
    "text": "操作成功",
    "result": {
    "id": 5
    }
    }
     * {
     * @apiErrorExample  {json} Error-Response:
    {
    "code": 400,
    "text": "操作失败",
    "result": ""
    }
     {
    "code": 400,
    "text": "数据错误",
    "result": ""
    }
     */
    public function accountEditPwd(Request $request,$id)
    {
        //获取需要编辑的子帐号信息
        $findSub = PlatformUser::find($id);
        if(!$findSub)
        {//子帐号不存在返回错误信息
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //进行提交数据验证
        $message = [
            'password.required'     => trans('role.password.required'),
            'password.max'          => trans('role.password.max'),
            'password.min'          => trans('role.password.min'),
            'password.confirmation' => trans('role.password.confirmation'),
        ];
        $validator = \Validator::make($request->input(),[
            'password'  => 'required|confirmed|max:20|min:6',
        ],$message);

        //数据验证不通过
        if($validator->fails())
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => $validator->errors(),
                'result'    => ''
            ]);
        }

        //数据验证通过进行密码修改操作
        $attributes = $request->except('token','locale','password_confirmation');//过滤掉token 和 locale字段
        $attributes['password'] = app('hash')->make($request->input('password').$findSub->salt);
        $res = PlatformUser::where('id',$id)->update($attributes);

        //密码修改操作失败
        if(!$res)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.fails'),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'修改子帐号密码','action_desc'=>' 修改了子账号密码，子账号为：'.$findSub->user_name,'action_passivity'=>$findSub->user_name]);

        //密码修改成功返回
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => ''
        ]);

    }

    /**
     * @api {post} /role/showSubMenus/{id} 编辑子帐号权限时，获取菜单数据
     * @apiDescription 编辑子帐号权限时，获取菜单数据
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {int}  id  编辑的子帐号id
     * @apiSampleRequest http://app-loc.dev/api/role/showSubMenus/{id}
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": [
    {
    "id": 1,
    "group_name": "管理员组",
    "isHaveRole": 0,
    "roles": [
    {
    "id": 22,
    "role_id": 1,
    "menu_id": 1,
    "parent_id": 0,
    "isHaveRole": 1,
    "_child": [
    {
    "id": 23,
    "role_id": 1,
    "menu_id": 2,
    "parent_id": 1,
    "isHaveRole": 1,
    "_child": [
    {
    "id": 24,
    "role_id": 1,
    "menu_id": 3,
    "parent_id": 2,
    "isHaveRole": 1,
    "_child": [
    {
    "id": 25,
    "role_id": 1,
    "menu_id": 4,
    "parent_id": 3,
    "isHaveRole": 1
    }
    ]
    }
    ]
    }
    ]
    }
    ]
    },
    {
    "id": 2,
    "group_name": "编辑1组",
    "roles": []
    },
    {
    "id": 3,
    "group_name": "新的角色分组",
    "roles": []
    },
    {
    "id": 4,
    "group_name": "新的角色分组22",
    "roles": []
    },
    {
    "id": 5,
    "group_name": "测试分组1",
    "roles": []
    },
    {
    "id": 6,
    "group_name": "测试分组2",
    "roles": []
    },
    {
    "id": 9,
    "group_name": "新的测试分组",
    "roles": []
    }
    ]
    }
     * {
     * @apiErrorExample  {json} Error-Response:
    {
    "code": 400,
    "text": "数据列表为空",
    "result": ""
    }
     */
    public function getAccountInfo(Request $request,$id)
    {
        //先判断所要编辑的子账户是否存在
        $findSub = PlatformUser::find($id);
        if(!$findSub)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //获取角色分组信息
        $roleGroupList = RoleGroup::where('state',1)->select('id','group_name')->get()->toArray();
        if(!$roleGroupList)
        {
            //角色分组不存在，返回错误信息
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.empty_list'),
                'result'    => ''
            ]);
        }
        //获取子账户的原先所属账户组
        $accountGroup = PlatformUser::where('id',$id)->select('group_id')->first()->toArray();
        //获取全部菜单信息
        $menusList = Menu::select('id','parent_id','menu_code')->get()->toArray();

        //根据分组信息获取对应的权限信息
        foreach ($roleGroupList as $k=>$v)
        {
            $subRoleMenus = RoleGroupMenu::where('role_id',$v['id'])->get()->toArray();
            $sub_menu_id = array_column($subRoleMenus,'menu_id');
            $sub_parent_id = array_column($subRoleMenus,'parent_id');
            $subRoleMenusList = [];
            foreach ($menusList as $men_k=> $menu)
            {
                if(in_array($menu['id'],$sub_menu_id) ||in_array($menu['id'],$sub_parent_id))
                {
                    $subRoleMenusList[] = $menu;
                }
            }
            foreach ($subRoleMenusList as $k1=>$v1)
            {
                $v['id'] == $accountGroup['group_id']  ? $subRoleMenusList[$k1]['isHaveRole'] = 1 : $subRoleMenusList[$k1]['isHaveRole'] = 0;
            }
//            foreach ($subRoleMenus as $kk=>$vv)
//            {
//                in_array($vv['role_id'],$accountGroupIdList) ? $roleGroupList[$kk]['isHaveRole'] = 1 : $roleGroupList[$kk]['isHaveRole'] = 0;
//            }
            $v['id'] == $accountGroup['group_id'] ? $roleGroupList[$k]['isHaveRole'] = 1 : $roleGroupList[$k]['isHaveRole'] = 0;

            $roleGroupList[$k]['roles'] = list_to_tree($subRoleMenusList,'id','parent_id');
            unset($subRoleMenusList);
        }
        if(!$roleGroupList)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.empty_list'),
                'result'    => ''
            ]);
        }
        //返回分组和对应菜单信息
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('role.success'),
            'result'    => $roleGroupList
        ]);
    }

    /**
     * @api {patch} /role/updateAccount/{id} 保存修改子账户权限菜单信息
     * @apiDescription 保存修改子账户权限菜单信息
     * @apiGroup Role
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {int}  group_id 角色分组ID
     * @apiSampleRequest http://app-loc.dev/api/role/updateRole/{id}
     * @apiSuccessExample {json} Success-Response:
     *    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "id": 9
    }
    }
     * {
     * @apiErrorExample  {json} Error-Response:
    {
    "code": 400,
    "text": "操作失败",
    "result": ""
    }

     */
    public function updateAccount(Request $request, $id)
    {
        //先判断所要编辑的子账户是否存在
        $findSub = PlatformUser::find($id);
        if(!$findSub)
        {
            return $this->response()->array([
                'code'      => 400,
                'text'      => trans('role.data_error'),
                'result'    => ''
            ]);
        }

        //进行数据提交验证
        $messages = [
            'group_id.required'    => trans('role.group.required')
        ];

        $validator = \Validator::make($request->input(),[
            'group_id' => 'required|integer'
        ],$messages);

        //提交数据格式验证错误
        if($validator->fails())
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => $validator->errors()->first(),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'修改子帐号权限','action_desc'=>' 修改了子账号权限信息，子账号为：'.$findSub->user_name,'action_passivity'=>$findSub->user_name]);

        $res = PlatformUser::where(['id'=>$id])->update(['group_id'=>$request->input('group_id')]);
        if(!$res)
        {
            return $this->response()->array([
                'code'      => 0,
                'text'      => trans('role.fails'),
                'result'    => ''
            ]);
        }
        //写入成功返回成功信息
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('role.success'),
            'result'    => ''
        ]);

    }
}