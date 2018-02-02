<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * Class AgentTest
 * 测试厅主代理商列表
 */
class AgentTest extends TestCase
{
    use DatabaseTransactions;
    protected $route = '/api/agents';
    protected $header = ['Accept' => 'application/vnd.pt.v0.1.0+json'];

    /**
     *测试没有带参数
     */
    public function testIndex()
    {
        /**
         * 测试没有带参数 token start
         */

        $this->get($this->route.'/2', $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => '认证失败',
                ]
            ) ;
        /**
         * 测试没有带参数token end
         */

        /**
         * 测试有带参数 token start
         */
        $params = '?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ';
        $this->get($this->route.'/2'.$params, $this->header)
            ->seeJson(

            ) ;
        /**
         * 测试有带参数token end
         */

    }

    /**
     * 测试添加代理商
     */
    public function testAddAgent()
    {
        //测试不带参数
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];
        $this->post($this->route, $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'area' =>
                                [
                                    0 => '地区 不能为空。',
                                ],
                            'user_name' =>
                                [
                                    0 => '用户名 不能为空。',
                                ],
                            'password' =>
                                [
                                    0 => '密码 不能为空。',
                                ],
                            'password_confirmation' =>
                                [
                                    0 => '确认密码 不能为空。',
                                ],
                            'email' =>
                                [
                                    0 => '邮箱 不能为空。',
                                ],
                            'time_zone' =>
                                [
                                    0 => '时区 不能为空。',
                                ],
                            'lang_code' =>
                                [
                                    0 => '语言 不能为空。',
                                ],
                            'grade_id' =>
                                [
                                    0 => '代理商类型 不能为空。',
                                ],
                        ],
                    'result' => '',
                ]
            ) ;

        //测试添加用户名或者邮箱存在
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'grade_id'=>2,
            'area'=>'中国深圳',
            'user_name'=> 'anchen2',
            'password'=> '111111',
            'password_confirmation'=>'111111',
            'email'=> '22222@qq.com',
            'time_zone'=> '(GMT+08:00) Asia / Beijing',
            'lang_code'=> 'zh_cn',
        ];

        $this->post($this->route, $params, $this->header)
            ->seeJsonEquals(
                [
                    "code" => 400,
                    "text" => [
                        'user_name' =>
                         [
                             0 => '用户名 已经存在。',
                         ],

                        'email' =>
                         [
                            0 => '邮箱 已经存在。',
                         ],
                    ],
                    "result" => '',
                ]
            );


        //测试编辑代理商时代理id不存在
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'grade_id'=>2,
            'area'=>'中国深圳',
            'user_name'=> 'anchen2',
            'password'=> '111111',
            'password_confirmation'=>'111111',
            'email'=> '2222@qq.com',
            'time_zone'=> '(GMT+08:00) Asia / Beijing',
            'lang_code'=> 'zh_cn',
            'agent_id' => 999
        ];

        $this->post($this->route, $params, $this->header)
            ->seeJsonEquals(
                [
                    "code" => 400,
                    "text" => '代理商不存在',
                    "result" => '',
                ]
            );


        //测试添加编辑代理预期
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'grade_id'=>2,
            'area'=>'中国深圳',
            'user_name'=> 'anchen2',
            'password'=> '111111',
            'password_confirmation'=>'111111',
            'email'=> '2222@qq.com',
            'time_zone'=> '(GMT+08:00) Asia / Beijing',
            'lang_code'=> 'zh_cn',
            'agent_id' => 9
        ];

        $this->post($this->route, $params, $this->header)
            ->seeJson(

            );
    }

    /**
     * 测试获取代理商数据
     */
    public function testGetAgent()
    {

        //测试获取代理数据 grade_id 参数为空或错误
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];

        $this->post($this->route.'/9', $params, $this->header)
            ->seeJsonEquals(
                [
                    "code" => 400,
                    "text" => 'grade_id 参数值错误',
                    "result" => '',
                ]
            );

        //测试获取代理不存在
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'grade_id' => 2,
        ];

        $this->post($this->route.'/999', $params, $this->header)
            ->seeJsonEquals(
                [
                    "code" => 400,
                    "text" => '代理商不存在',
                    "result" => '',
                ]
            );

    }


    /**
     * 测试代理修改密码
     */
    public function testAgentEditPass()
    {
        //测试不带参数
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];
        $this->post($this->route.'/9/password', $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'password' =>
                                [
                                    0 => '密码 不能为空。',
                                ],
                            'password_confirmation' =>
                                [
                                    0 => '确认密码 不能为空。',
                                ],
                            'grade_id' =>
                                [
                                    0 => '代理商类型 不能为空。',
                                ],
                        ],
                    'result' => '',
                ]
            );

        //测试两次输入密码不正确
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
            'password' => '111111',
            'password_confirmation' => '1111111',
            'grade_id' => '2',
        ];
        $this->post($this->route.'/9/password', $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'password' =>
                                [
                                    0 => '密码 两次输入不一致。',
                                ],
                        ],
                    'result' => '',
                ]
            );


    }

}
