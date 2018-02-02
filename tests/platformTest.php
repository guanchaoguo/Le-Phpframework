<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/3/2
 * Time: 13:01
 * 子账户相关单元测试
 */
class PlatForm extends TestCase
{
    private $header = ['Accept' => 'application/vnd.pt.v0.1.0+json'];//头部参数

    //测试添加子账户时没有填写完整参数场景
    public function testAddAccount()
    {
        $route = "api/role/addAccount";
        $param=['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw'];
        $this->post($route,$param,$this->header)
            ->seeJsonEquals([
                'code'      => 400,
                'text'      => [
                    'user_name' => ['账号名称不能为空'],
                    'password'  => ['密码不能为空']
                ],
                'result'    => ''
        ]);
    }

    //测试编辑子账户权限时子账户ID错误场景
    public function testUpdateIdIsEmpty()
    {
        $route = "api/role/showSubMenus/100";
        $param=['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw'];
        $this->post($route,$param,$this->header)
            ->seeJsonEquals([
                'code'  => 400,
                'text'  => '数据错误',
                'result'    => ''
            ]);
    }

    //测试保存子账户权限信息时权限为空场景
    public function testSaveSubRoles()
    {
        $route = "api/role/updateAccount/1";
        $param=['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw',
            'roles'=>'','group_id'=>1];
        $this->patch($route,$param,$this->header)
            ->seeJsonEquals([
                'code'      => 400,
                'text'      => [
                    'roles' => ['角色权限不能为空']
                ],
                'result'    => ''
            ]);
    }

    //测试修改子账户状态，状态参数为错误场景
    public function testEditStateIsError()
    {
        $route = "/api/role/accountState/1";
        $param=['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw',
            'state'=>4];
        $this->patch($route,$param,$this->header)
            ->seeJsonEquals([
                'code'      => 400,
                'text'      => [
                    'state' => ['已选的属性 state 非法。']
                ],
                'result'    => ''
            ]);
    }

    //测试修改子账户状态，ID参数为错误时场景
    public function testEditStateIdEmpty()
    {
        $route = "/api/role/accountState/100";
        $param=['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw',
            'state'=>3];
        $this->patch($route,$param,$this->header)
            ->seeJsonEquals([
                'code'      => 400,
                'text'      => '数据错误',
                'result'    => ''
            ]);
    }


    //测试修改子账户密码时，ID参数错误时场景
    public function testSaveAccountPwdIdError()
    {
        $route ="api/role/editPwd/100";
        $param=['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw',
            'password'=>'123456'];
        $this->patch($route,$param,$this->header)
            ->seeJsonEquals([
                'code'  => 400,
                'text'  => '数据错误',
                'result'    => ''
            ]);
    }


    //测试修改子账户密码时，密码参数为空时场景
    public function testSaveAccountPwdEmpty()
    {
        $route ="api/role/editPwd/1";
        $param=['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw',
            'password'=>''];
        $this->patch($route,$param,$this->header)
            ->seeJsonEquals([
                'code'  => 400,
                'text'  => [
                    'password'  => ['密码不能为空']
                ],
                'result'    => ''
            ]);
    }

    //测试修改子账户密码时，两次密码不一致时场景
    public function testSaveAccountPwdNot()
    {
        $route ="api/role/editPwd/1";
        $param=['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4NDIwODQzLCJleHAiOjE0ODg2MzY4NDMsIm5iZiI6MTQ4ODQyMDg0MywianRpIjoieGpHejJ2dmlBZ2IzeWRtWiIsInN1YiI6MX0.oXSW5Kh8-xmIgIk94vwkpIQxhgiwqDKHOFvT_RwXxcw',
            'password'=>'123456','password_confirmation'=>'654321'];
        $this->patch($route,$param,$this->header)
            ->seeJsonEquals([
                'code'  => 400,
                'text'  => [
                    'password'  => ['密码 两次输入不一致。']
                ],
                'result'    => ''
            ]);
    }
}