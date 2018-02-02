<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * Class HallQuotaTest
 * 测试厅限额类
 */
class HallQuotaTest extends TestCase
{
    use DatabaseTransactions;
    protected $route = '/api/hall/quota';
    protected $header = ['Accept' => 'application/vnd.pt.v0.1.0+json'];

    /**
     *测试获取厅的限额数据
     */
    public function testIndex()
    {
        /**
         * 测试没带参数
         */
        $params = '?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ';

        $this->get($this->route.$params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'title' =>
                                [
                                    0 => '标题 不能为空。',
                                ],
                            'hall_type' =>
                                [
                                    0 => '厅 不能为空。',
                                ],
                        ],
                    'result' => '',
                ]
            ) ;

    }

    /**
     *测试添加厅限额
     */
    public function testAdd()
    {
        /**
         * 测试没带参数
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
                            'title' =>
                                [
                                    0 => '标题 不能为空。',
                                ],
                            'hall_type' =>
                                [
                                    0 => '厅 不能为空。',
                                ],
                            'items' =>
                                [
                                    0 => '下注区域值 不能为空。',
                                ],

                        ],
                    'result' => '',
                ]
            ) ;

        /**
         * 测试默认限额标题错误
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'items' => '[{"game_id": 1,"area": [{"bet_area": 1,"max_money": 1000,"min_money": 10},{"bet_area": 2,"max_money": 1000,"min_money": 10}]}]',
            'hall_type' => '2',
            'title' => '1212',
        ];

        $this->post($this->route, $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'title' =>
                                [
                                    0 => '已选的属性 标题 非法。',
                                ],
                        ],
                    'result' => '',
                ]
            ) ;

    }


    /**
     *测试保存厅限额
     */
    public function testEdit()
    {
        /**
         * 测试没带参数
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];

        $this->put($this->route.'/33333', $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => '限额分组不存在',
                    'result' => '',
                ]
            ) ;


    }

    /**
     *测试快捷设置厅限额
     */
    public function testAddShortcut()
    {
        /**
         * 测试没带参数
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];

        $this->post($this->route.'/shortcut', $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'title' =>
                                [
                                    0 => '标题 不能为空。',
                                ],
                            'game_id' =>
                                [
                                    0 => '游戏 不能为空。',
                                ],
                            'hall_type' =>
                                [
                                    0 => '厅 不能为空。',
                                ],
                            'max_money' =>
                                [
                                    0 => '最高限额 不能为空。',
                                ],
                            'min_money' =>
                                [
                                    0 => '最低限额 不能为空。',
                                ],
                        ],
                    'result' => '',
                ]
            ) ;

        /**
         * 测试最高限额低于最低限额
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'game_id' => 1,
            'title' => 'defaultA',
            'hall_type' => 2,
            'max_money' => 1000,
            'min_money' => 2000,
        ];

        $this->post($this->route.'/shortcut', $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => '最高限额不能小于最低限额',
                    'result' => '',
                ]
            ) ;
    }

    /**
     *测试快捷设置厅限额 编辑
     */
    public function testEditShortcut()
    {
        /**
         * 测试限额分组不存在
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];

        $this->put($this->route.'/shortcut/284312', $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => '限额分组不存在',
                    'result' => '',
                ]
            ) ;

        /**
         * 测试最高限额低于最低限额
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'game_id' => 1,
            'title' => 'defaultA',
            'hall_type' => 2,
            'max_money' => 1000,
            'min_money' => 2000,
        ];

        $this->put($this->route.'/shortcut/2843', $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => '最高限额不能小于最低限额',
                    'result' => '',
                ]
            ) ;
    }
}
