<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/7/15
 * Time: 13:56
 * 判断用户是否拥有某个菜单ID中间件
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuMiddleware
{
    public function handle($request, Closure $next)
    {

       $menuId = $request['menu_id'];
       $user_id =  Auth::user()->id;
        //判断用户是否拥有该权限
        $groupMenuList = DB::table("role_group_menus")->where(['role_id'=>Auth::user()->group_id])->select('menu_id')->get()->toArray();

//        $userMenus = DB::table('user_menus')->where('user_id',$user_id)->where('menu_id',$menuId)->first();
        $userMenus = array_column($groupMenuList,'menu_id');
        if(!in_array($menuId,$userMenus))
        {
            return response()->json([
                'code' => 400,
                'text' => trans('auth203.no_permission')
            ]);
        }
        return $next($request);
    }
}