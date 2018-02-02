<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/3/2
 * Time: 10:22
 * 权限相关单元测试
 */
class Role extends TestCase
{
    private $route = "/api/role";
    private $header = ['Accept' => 'application/vnd.pt.v0.1.0+json'];//头部参数

    //测试添加角色分组时参数没有添加场景
    public function testAddRoleEmpty()
    {
        $param = ['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw','group_name'=>'','desc'=>'aa'];

        $this->post($this->route,$param,$this->header)
            ->seeJsonEquals([
                'code'      => 400,
                'text'      => [
                    'group_name'=>['请输入角色名称'],
                ],
                'result'    => ''
            ]);
    }

    //测试编辑角色时没有角色组ID为错误场景
    public function testUpdateRoleIdIsError()
    {
        $param = ['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw'];
        $route = "api/role/showMenus/100";
        $this->post($route,$param,$this->header)
            ->seeJsonEquals([
                'code'  => 400,
                'text'  => '操作失败',
                'result'    => ''
            ]);
    }

    //测试保存修改角色信息时角色组权限为空时场景
    public function testSaveRoleNameEmpty()
    {
        $param = ['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw','roles'=>''];

        $route = "/api/role/updateRole/1";
        $this->patch($route,$param,$this->header)
            ->seeJsonEquals([
                'code'      => 400,
                'text'      => [
                    'roles' => ['角色权限不能为空'],
                ],
                'result'    => ''
            ]);
    }

    //测试删除角色分组时没有传入角色分组ID或ID错误场景
    public function testDeleteRoleIdEmpty()
    {
        $param = ['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw'];

        $route = 'api/role/deleteGroup/100';
        $this->delete($route,$param,$this->header)
            ->seeJsonEquals([
                'code'  => 400,
                'text'  => '数据错误',
                'result'    => ''
            ]);
    }
}