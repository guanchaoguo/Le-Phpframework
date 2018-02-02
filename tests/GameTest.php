<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * Class GameTest
 * 测试游戏管理类
 */
class GameTest extends TestCase
{
    use DatabaseTransactions;
    protected $route = '/api/game';
    protected $header = ['Accept' => 'application/vnd.pt.v0.1.0+json'];

    /**
     *测试添加游戏
     */
    public function testAddGame()
    {
        /**
         * 测试没有带参数start
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];
        $this->post($this->route, $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'cat_id' =>
                                [
                                    0 => '游戏分类 不能为空。',
                                ],
                            'game_name' =>
                                [
                                    0 => '游戏名称 不能为空。',
                                ],
                        ],
                    'result' => '',
                ]
            ) ;


    }

    /**
     *测试编辑游戏
     */
    public function testEditGame()
    {
        /**
         * 测试游戏不存在
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];
        $this->put($this->route.'/100', $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => '游戏不存在',
                    'result' => '',
                ]
            ) ;


        /**
         * 测试游戏名称为空
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];
        $this->put($this->route.'/110', $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => [
                        'game_name' =>
                            [
                                0 => '游戏名称 不能为空。',
                            ],
                    ],
                    'result' => '',
                ]
            ) ;
    }

    /**
     *测试删除游戏
     */
    public function testDeleteGame()
    {
        /**
         * 测试游戏不存在
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];
        $this->delete($this->route.'/100', $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => '游戏不存在',
                    'result' => '',
                ]
            ) ;

    }

    /**
     *测试获取游戏
     */
    public function testGetGame()
    {
        /**
         * 测试游戏不存在
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];
        $this->post($this->route.'/100', $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => '游戏不存在',
                    'result' => '',
                ]
            ) ;

    }
}
