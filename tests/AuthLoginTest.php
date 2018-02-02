<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * Class AuthorizationTest
 * 测试登录认证
 */
class AuthLoginTest extends TestCase
{
//    use DatabaseTransactions;
    protected $route = '/api/authorization';
    protected $header = ['Accept' => 'application/vnd.pt.v0.1.0+json'];

    /**
     *测试没有带参数
     */
    public function testLogin()
    {
        /**
         * 测试没有带参数start
         */
        $params = [

        ];
        $this->post($this->route, $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>
                        [
                            'user_name' =>
                               [
                                    0 => '用户名 不能为空。',
                                ],
                            'password' =>
                                [
                                    0 => '密码 不能为空。',
                                ],
                            'captcha' =>
                                [
                                    0 => 'captcha 不能为空。',
                                ],
                            'gid' =>
                                [
                                    0 => 'gid 不能为空。',
                                ],
                        ],
                    'result' => '',
                ]
            ) ;
        /**
         * 测试没有带参数end
         */

        /**
         * 测试验证码错误start
         */
        $params = [
            'user_name' => 'chensj',
            'password' => '111111',
            'captcha' => '7j61t1',
            'gid' => 'rajfB9WoBsGTo2eFHLlY',
        ];
        $this->post($this->route, $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' => '验证码错误',
                    'result' => '',
                ]
            ) ;
        /**
         * 测试验证码错误end
         */

        /**
         * 测试账号或密码错误start
         */
        $params = [
            'user_name' => 'chensj1',
            'password' => '111111',
            'captcha' => 'khp2i',
            'gid' => 'tFU44IgG7wLDkA3NawXC',
        ];
        $this->post($this->route, $params, $this->header)
            ->seeJsonEquals(
                [
                    'code' => 403,
                    'text' => '账号或密码错误。',
                    'result' => '',
                ]
            ) ;
        /**
         * 测试账号或密码错误end
         */

        /**
         * 测试预期结果start
         */
        $params = [
            'user_name' => 'chensj',
            'password' => '111111',
            'captcha' => 'khp2i',
            'gid' => 'tFU44IgG7wLDkA3NawXC',
        ];
        $this->post($this->route, $params, $this->header)
            ->seeJson(

            ) ;
        /**
         * 测试预期结果end
         */
    }
}
