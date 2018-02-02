<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * Class AuthorizationTest
 * 测试玩家类
 */
class PlayerTest extends TestCase
{
//    use DatabaseTransactions;
    protected $route = '/api/player';
    protected $header = ['Accept' => 'application/vnd.pt.v0.1.0+json'];

    /**
     *测试游戏种类列
     */
    public function testPlayerList()
    {
        /**
         * 测试没有带参数返回游戏列表
         */
        $params = '?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ';
        $this->get($this->route.$params,  $this->header)
            ->seeJson(
            ) ;
    }

    /**
     * 测试添加编辑玩家
     */
    public function testPlayerStore()
    {
        //测试添加玩家没有带参数
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];
        $this->post($this->route, $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'username_md' =>
                                [
                                    0 => '玩家 不能为空。',
                                ],
                            'password_md' =>
                                [
                                    0 => '密码 不能为空。',
                                ],
                            'password_md_confirmation' =>
                                [
                                    0 => '确认密码 不能为空。',
                                ],
                            'agent_id' =>
                                [
                                    0 => '代理商 不能为空。',
                                ],
                            'language' =>
                                [
                                    0 => '语言 不能为空。',
                                ],
                            'account_state' =>
                                [
                                    0 => '状态 不能为空。',
                                ],
                        ],
                    'result' => '',
                ]
            );

        //测试编辑玩家不存在
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'username_md' => '111111',
            'password_md' => '111111',
            'password_md_confirmation' => '111111',
            'agent_id' => 2,
            'language' => 'zh-cn',
            'account_state' => 1,
            'user_id' => 191111,
        ];
        $this->post($this->route, $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => '玩家不存在',
                    'result' => '',
                ]
            );

        //测试添加玩家 用户名或者邮箱存在
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'username_md' => '111111',
            'password_md' => '111111',
            'password_md_confirmation' => '111111',
            'agent_id' => 2,
            'language' => 'zh-cn',
            'account_state' => 1,
        ];

        $this->post($this->route, $params, $this->header)
            ->seeJsonEquals(
                [
                    "code" => 400,
                    "text" => [
                        'username_md' =>
                            [
                                0 => '玩家 已经存在。',
                            ],
                    ],
                    "result" => '',
                ]
            );
    }

    /**
     * 测试编辑玩家时获取数据
     */
    public function testGetData()
    {
        //测试玩家id参数错误 获取空数据
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];

        $this->post($this->route.'/19111', $params, $this->header)
            ->seeJsonEquals(
                [
                    "code" => 0,
                    "text" => '操作成功',
                    "result" => [
                        "data" => null,
                    ],
                ]
            );
    }

    /**
     * 测试修改玩家密码
     */
    public function testPassword()
    {
        //测试不带参数
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];

        $this->post($this->route.'/191/password', $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'password_md' =>
                                [
                                    0 => '密码 不能为空。',
                                ],
                            'password_md_confirmation' =>
                                [
                                    0 => '确认密码 不能为空。',
                                ],
                        ],
                    'result' => '',
                ]
            );

        //测试两次密码不正确
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'password_md' => '111111',
            'password_md_confirmation' => '222222',
        ];

        $this->post($this->route.'/191/password', $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'password_md' =>
                                [
                                    0 => '密码 两次输入不一致。',
                                ],
                        ],
                    'result' => '',
                ]
            );
    }

    /**
     * 测试余额扣取
     */
    public function testBalanceHandle()
    {
        //测试不带参数
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];

        $this->post($this->route.'/191/balance', $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'money' =>
                                [
                                    0 => '金额 不能为空。',
                                ],
                            'status' =>
                                [
                                    0 => '类型 不能为空。',
                                ],
                        ],
                    'result' => '',
                ]
            );


        //测试 金额 类型参数错误
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'money' => 'e',
            'status' => '2',
        ];

        $this->post($this->route.'/191/balance', $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'money' =>
                                [
                                    0 => '金额 必须是一个数字。',
                                ],
                            'status' =>
                                [
                                    0 => '已选的属性 类型 非法。',
                                ],
                        ],
                    'result' => '',
                ]
            );
    }
}
