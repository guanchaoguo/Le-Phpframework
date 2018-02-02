<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/3/1
 * Time: 11:14
 */
class MenusTest extends TestCase
{
    private $route = "/api/menus";
    private $header = ['Accept' => 'application/vnd.pt.v0.1.0+json'];//头部参数

    //测试所有参数都为空时场景
    public function testAllEmtpy()
    {
        $this->get($this->route)
            ->seeJsonEquals([
                'message' => 'Accept header could not be properly parsed because of a strict matching process.',
                'status_code'=> 400
            ]);
    }

    //测试添加菜单所有参数都为空时情况
    public function testAddMenusEmpty()
    {
        $this->post($this->route)
            ->seeJsonEquals([
                'message' => 'Accept header could not be properly parsed because of a strict matching process.',
                'status_code'=> 400
            ]);
    }

    //测试token为空时场景
    public function testTokenEmpty()
    {
        $param = ['title_cn'=> 'aa','token'=>''];
        $this->post($this->route,$param,$this->header)
            ->seeJsonEquals([
                'code'  => 400,
                'text'   => '认证失败'
            ]);

    }

    //测试添加菜单某一个参数为空时场景
    public function testAddMenusTitleEmpty()
    {
        $param = ['parent_id'=>'1','class'=>'a',
            'token'=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzM2MzMxLCJleHAiOjE0ODg1NTIzMzEsIm5iZiI6MTQ4ODMzNjMzMSwianRpIjoiZGZkMWI3ODUyZDRlYmUzYjAzNDM5YmI4ZmY4OGZhNmEiLCJzdWIiOjF9.CrkhMIVs7FxjDh2pUxaRGV1EPIV85kvuC-JCdgnYGXY"];
        $this->post($this->route,$param,$this->header)
            ->seeJsonEquals([
                'code'  => 400,
               'text'=> [
                   'class'  => ['菜单类型字段类型只能为整数类型'],
                   'title_cn'   => ['菜单中文名称不能为空'],
                   'title_en'   => ['菜单英文名称不能为空'],
                   'icon'   => ['菜单图标不能为空'],
                   'link_url'   => ['菜单链接地址不能为空'],
                   'sort_id'    => ['菜单排序不能为空'],
                   'state'  => ['state 不能为空。'],
               ],
                'result'   => ''
            ]);
    }

    //测试修改菜单时没有传入菜单ID场景
    public function testUpdateMenus()
    {
        $param = [];
        $this->patch($this->route,$param,$this->header)
            ->seeJsonEquals([
               'status_code'    => 405,
                'message'       => '405 Method Not Allowed'
            ]);
    }

    //测试编辑菜单时获取数据接口传入ID不存在场景
    public function testUpdateGetInfo()
    {
        $param = ['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzM2MzMxLCJleHAiOjE0ODg1NTIzMzEsIm5iZiI6MTQ4ODMzNjMzMSwianRpIjoiZGZkMWI3ODUyZDRlYmUzYjAzNDM5YmI4ZmY4OGZhNmEiLCJzdWIiOjF9.CrkhMIVs7FxjDh2pUxaRGV1EPIV85kvuC-JCdgnYGXY'];
        $this->post($this->route.'/100',$param,$this->header)
            ->seeJsonEquals([
               'code'   => 400,
                'text'  => '菜单不存在',
                'result'    => ''
            ]);
    }

    //测试删除菜单时获取数据接口传入ID不存在场景
    public function testDeleteGetInfo()
    {
        $param = ['token'=>'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzM2MzMxLCJleHAiOjE0ODg1NTIzMzEsIm5iZiI6MTQ4ODMzNjMzMSwianRpIjoiZGZkMWI3ODUyZDRlYmUzYjAzNDM5YmI4ZmY4OGZhNmEiLCJzdWIiOjF9.CrkhMIVs7FxjDh2pUxaRGV1EPIV85kvuC-JCdgnYGXY'];
        $this->delete($this->route.'/100',$param,$this->header)
            ->seeJsonEquals([
                'code'   => 400,
                'text'  => '数据错误',
                'result'    => ''
            ]);
    }

}