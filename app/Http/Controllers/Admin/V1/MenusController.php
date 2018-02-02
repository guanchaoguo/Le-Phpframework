<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkl.com
 * Date: 2017/2/13
 * Time: 9:52
 * 后台系统菜单相关控制器
 */
namespace App\Http\Controllers\Admin\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Menu;

class MenusController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {get} /menus 菜单列表
     * @apiDescription 获取后台菜单组列表
     * @apiGroup Menus
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {Number} page 当前页
     * @apiSampleRequest http://app-loc.dev/api/menus
     * @apiSuccessExample {json} Success-Response:
     *      {
    "code": 0,
    "text": "操作成功",
    "result": [
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
    "_child": [
    {
    "id": 5,
    "parent_id": 1,
    "title_cn": "|--厅主管理",
    "title_en": "",
    "class": 0,
    "desc": "厅主管理",
    "link_url": "/haller/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "haller"
    },
    {
    "id": 6,
    "parent_id": 1,
    "title_cn": "|--代理管理管理",
    "title_en": "",
    "class": 0,
    "desc": "代理",
    "link_url": "/agent/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "agent"
    },
    {
    "id": 7,
    "parent_id": 1,
    "title_cn": "|--玩家管理",
    "title_en": "",
    "class": 0,
    "desc": "玩家管理",
    "link_url": "/player/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "palyer"
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
    "_child": [
    {
    "id": 8,
    "parent_id": 2,
    "title_cn": "|--游戏列表",
    "title_en": "",
    "class": 0,
    "desc": "游戏列表",
    "link_url": "/games/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "games"
    },
    {
    "id": 9,
    "parent_id": 2,
    "title_cn": "|--游戏限额",
    "title_en": "",
    "class": 0,
    "desc": "游戏限额",
    "link_url": "games/limit",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "games_limit"
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
    "_child": [
    {
    "id": 10,
    "parent_id": 3,
    "title_cn": "|--游戏数据统计",
    "title_en": "",
    "class": 0,
    "desc": "游戏数据统计",
    "link_url": "game_report/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "game_report",
    "_child": [
    {
    "id": 11,
    "parent_id": 10,
    "title_cn": "|--查询游戏",
    "title_en": "",
    "class": 0,
    "desc": "查询游戏",
    "link_url": "game_select/list",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "game_select",
    "_child": [
    {
    "id": 26,
    "parent_id": 11,
    "title_cn": "|--开始了22",
    "title_en": "|--abc",
    "class": 1,
    "desc": null,
    "link_url": "www.baidu.com",
    "icon": "www.baidu.com",
    "state": 1,
    "sort_id": 100,
    "menu_code": null
    },
    {
    "id": 28,
    "parent_id": 11,
    "title_cn": "|--开始了",
    "title_en": "|--abc",
    "class": 1,
    "desc": null,
    "link_url": "www.baidu.com",
    "icon": "www.baidu.com",
    "state": 1,
    "sort_id": 100,
    "menu_code": null
    },
    {
    "id": 29,
    "parent_id": 11,
    "title_cn": "|--开始了1",
    "title_en": "|--abc——1",
    "class": 1,
    "desc": null,
    "link_url": "www.albaba.com",
    "icon": "www.albaba.com",
    "state": 1,
    "sort_id": 100,
    "menu_code": null
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
    "_child": [
    {
    "id": 12,
    "parent_id": 4,
    "title_cn": "|--白名单管理",
    "title_en": "",
    "class": 0,
    "desc": "白名单管理",
    "link_url": "system/allow",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "system_allow"
    },
    {
    "id": 13,
    "parent_id": 4,
    "title_cn": "|--菜单管理",
    "title_en": "",
    "class": 0,
    "desc": "菜单管理",
    "link_url": "system/menus",
    "icon": "",
    "state": 1,
    "sort_id": 1,
    "menu_code": "system_menus"
    },
    {
    "id": 14,
    "parent_id": 4,
    "title_cn": "|--角色管理",
    "title_en": "",
    "class": 0,
    "desc": "角色管理",
    "link_url": "system/role",
    "icon": "",
    "state": null,
    "sort_id": null,
    "menu_code": "system_role"
    }
    ]
    }
    ]
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
        $menus = Menu::orderBy('id')->get()->toArray();
        if(!$menus)
        {
            return $this->response()->array([
                'code'  => '400',
                'text'  => trans('menus.empty_list'),
                'result'    => ''
            ]);
        }
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('menus.success'),
            'result'    => list_to_tree(menusPrefix($menus),'id','parent_id')
        ]);
    }

    /**
     * @api {post} /role/menus/{id} 编辑菜单时获取数据
     * @apiDescription 编辑菜单时获取数据
     * @apiGroup Menus
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {int}  id  编辑的菜单id
     * @apiSampleRequest http://app-loc.dev/api/role/menu/{id}
     * @apiSuccessExample {json} Success-Response:
    {
    "code": 0,
    "text": "操作成功",
    "result": {
    "id": 11,
    "parent_id": 10,
    "title_cn": "查询游戏",
    "title_en": "",
    "class": 0,
    "desc": "查询游戏",
    "link_url": "game_select/list",
    "icon": "http://app-loc.dev/",
    "state": 1,
    "sort_id": 1,
    "menu_code": "game_select"
    }
    }
     * {
     * @apiErrorExample  {json} Error-Response:
    {
    "code": 400,
    "text": "菜单不存在",
    "result": ""
    }
     */
    public function show(Request $request,$id)
    {
        $menu = Menu::find($id);

        //菜单不存在返回错误
        if(!$menu)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('menus.menu_not_exist'),
                'result'    => ""
            ]);
        }

        //返回菜单信息
        $menu->icon = $menu->icon;//拼装图标URL
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('menus.success'),
            'result'    => $menu
        ]);

     }

    /**
     * @api {post} /role/menus 添加菜单
     * @apiDescription 添加菜单
     * @apiGroup Menus
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String}  title_cn  菜单的中文名称
     * @apiParam {String}  title_en  菜单的英文名称
     * @apiParam {String}   icon    菜单的icon图标地址
     * @apiParam {String}   link_url    菜单的url地址
     * @apiParam {int}  sort_id     菜单的排序
     * @apiParam {int}  parent_id   菜单所属父级ID
     * @apiParam {int}  class       菜单类型分类，0为分类菜单，1为页面菜单，默认为分类菜单
     * @apiParam {String} desc   菜单的描述信息
     * @apiParam {String} menu_code 菜单的标识符
     * @apiSampleRequest http://app-loc.dev/api/role/menus
     * @apiSuccessExample {json} Success-Response:
    {
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
     */
    public function store(Request $request)
    {
        //验证错误信息自定义，提升验证信息友好度
        $message = [
            'parent_id.required'     => trans('menus.parent_id.required'),
            'class.required'         => trans('menus.class.required'),
            'class.numeric'          => trans('menus.class.integer'),
            'class.max'              => trans('menus.class.max'),
            'title_cn.required'      => trans('menus.title_cn.required'),
            'title_cn.max'           => trans('menus.title_cn.max'),
            'title_en.required'      => trans('menus.title_en.required'),
            'title_en.max'           => trans('menus.title_en.max'),
            'icon.required'          => trans('menus.icon.required'),
            'link_url.required'      => trans('menus.link_url.required'),
            'link_url.max'           => trans('menus.link_url.max'),
            'sort_id.required'       => trans('menus.sort.required'),
            'sort_id.integer'        => trans('menus.sort.integer'),
            'state.require'          => trans('menus.state.required'),
            'state.integer'          => trans('menus.state.integer'),
            'state.max'              => trans('menus.state.max'),
            'menu_code.required'     => trans('menus.menu_code.required'),
        ];

        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
            //'parent_id'     => 'required',
            //'class'         => 'required|numeric|max:1',
            'title_cn'      => 'required|max:45',
           // 'title_en'      => 'required|max:45',
            'icon'          => 'required',
            'link_url'      => 'required|max:255',
            'sort_id'       => 'required|integer',
            //'state'         => 'required|integer|max:1',
            'menu_code'     => 'required'
        ],$message);
        //数据格式验证不通过
        if($validate->fails())
        {
           return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors(),
                'result'        => ''
            ]);
        }

        //数据格式验证通过进行创建操作
        $attributes = $request->except('token','locale','state');//排除token,locale两个字段
        $attributes['parent_id'] = $request->input('parent_id',0);
        $res = Menu::insert($attributes);

        if(!$res)//创建失败，返回失败信息
        {
            return $this->response()->array([
                'code'        => 400,
                'text'        => trans('menus.fails'),
                'result'      => ''
            ]);
        }
        @addLog(['action_name'=>'添加系统菜单','action_desc'=>' 添加了一个系统菜单，菜单名称为：'.$request->input('title_cn'),'action_passivity'=>'系统菜单']);

        //成功返回成功信息
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('menus.success'),
            'result'    => ""
        ]);
    }


    /**
     * @api {patch} /role/menus{id} 保存修改菜单
     * @apiDescription 保存修改菜单
     * @apiGroup Menus
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiParam {String}  title_cn  菜单的中文名称
     * @apiParam {String}  title_en  菜单的英文名称
     * @apiParam {String}   icon    菜单的icon图标地址
     * @apiParam {String}   link_url    菜单的url地址
     * @apiParam {int}  sort_id     菜单的排序
     * @apiParam {int}  parent_id   菜单所属父级ID
     * @apiParam {int}  class       菜单类型分类，0为分类菜单，1为页面菜单，默认为分类菜单
     * @apiParam {String} desc   菜单的描述信息
     * @apiSampleRequest http://app-loc.dev/api/role/menus/{id}
     * @apiSuccessExample {json} Success-Response:
    {
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
    {
    "code": 400,
    "text": "数据错误",
    "result": ""
    }
     */
    public function update(Request $request,$id)
    {
        //首先获取被修改的数据ID
        $menuFind = Menu::find($id);
        if(!$menuFind)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('menus.data_error'),
                'result'    => ''
            ]);
        }

        //验证错误信息自定义，提升验证信息友好度
        $message = [
            'parent_id.required'     => trans('menus.parent_id.required'),
            'class.required'         => trans('menus.class.required'),
            'class.integer'          => trans('menus.class.integer'),
            'class.max'              => trans('menus.class.max'),
            'title_cn.required'      => trans('menus.title_cn.required'),
            'title_cn.max'           => trans('menus.title_cn.max'),
            'title_en.required'      => trans('menus.title_en.required'),
            'title_en.max'           => trans('menus.title_en.max'),
            'icon.required'          => trans('menus.icon.required'),
            'link_url.required'      => trans('menus.link_url.required'),
            'link_url.max'           => trans('menus.link_url.max'),
            'sort_id.required'       => trans('menus.sort.required'),
            'sort_id.integer'        => trans('menus.sort.integer'),
            'state.require'          => trans('menus.state.required'),
            'state.integer'          => trans('menus.state.integer'),
            'state.max'              => trans('menus.state.max'),
            'menu_code.required'     => trans('menu_code.required'),

        ];

        //进行提交数据验证操作
        $validate = \Validator::make($request->input(),[
           // 'parent_id'     => 'required',
           // 'class'         => 'required|integer|max:1',
            'title_cn'      => 'required|max:45',
            //'title_en'      => 'required|max:45',
            'icon'          => 'required',
            'link_url'      => 'required|max:255',
            'sort_id'       => 'required|integer',
            'state'         => 'required|integer|max:1',
            'menu_code'     => 'required'
        ],$message);

        //数据格式验证不通过
        if($validate->fails())
        {
            return  $this->response()->array([
                'code'          => 400,
                'text'          => $validate->errors(),
                'result'        => ''
            ]);
        }

        //数据格式验证通过进行修改操作
        $attributes = $request->except('token','locale');//排除token,locale两个字段
        $attributes['parent_id'] = $request->input('parent_id',0);
        $res = $menuFind->where('id',$id)->update($attributes);

        if(!$res)//修改失败，返回失败信息
        {
            return $this->response()->array([
                'code'        => 400,
                'text'        => trans('menus.fails'),
                'result'      => ''
            ]);
        }
        @addLog(['action_name'=>'编辑系统菜单','action_desc'=>' 编辑了一个系统菜单，菜单名称为：'.$request->input('title_cn'),'action_passivity'=>'系统菜单']);

        //修改成功返回成功信息
        return $this->response()->array([
            'code'      => 0,
            'text'      => trans('menus.success'),
            'result'    => ""
        ]);
    }

    /**
     * @api {delete} /role/menus/{id} 删除菜单
     * @apiDescription 删除菜单操作
     * @apiGroup Menus
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Accept http头协议 application/vnd.pt.v0.1.0+json
     * @apiParam {String} token 认证token
     * @apiParam {String} locale 语言
     * @apiSampleRequest http://app-loc.dev/api/role/menus/{id}
     * @apiSuccessExample {json} Success-Response:
    {
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
    {
    "code": 400,
    "text": "数据错误",
    "result": ""
    }
     */
    public function destroy(Request $request,$id)
    {
        $menuFind  = Menu::find($id);
        if(!$menuFind)//验证需要删除的数据是否存在
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('menus.data_error'),
                'result'    => ''
            ]);
        }

        //进行删除操作
        $res = $menuFind->where('id',$id)->delete();
        if(!$res)
        {
            return $this->response()->array([
                'code'  => 400,
                'text'  => trans('menus.fails'),
                'result'    => ''
            ]);
        }
        @addLog(['action_name'=>'删除系统菜单','action_desc'=>' 删除了一个系统菜单，菜单名称为：'.$res->title_cn,'action_passivity'=>'系统菜单']);

        //删除成功
        return $this->response()->array([
            'code'  => 0,
            'text'  => trans('menus.success'),
            'result'    => ''
        ]);


    }
}