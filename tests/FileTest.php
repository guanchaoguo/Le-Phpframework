<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * Class FileTest
 * 测试文件类
 */
class FileTest extends TestCase
{
    use DatabaseTransactions;
    protected $route = '/api/';
    protected $header = ['Accept' => 'application/vnd.pt.v0.1.0+json'];

    /**
     *测试删除文件
     */
    public function testAddGame()
    {
        /**
         * 测试没带参数
         */
        $params = [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ',
        ];
        $this->delete($this->route.'file', $params,  $this->header)
            ->seeJsonEquals(
                [
                    'code' => 400,
                    'text' =>'文件不存在',
                    'result' => null,
                ]
            ) ;


    }

}
