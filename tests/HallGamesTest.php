<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * Class HallGamesTest
 * 测试游戏种类列表&游戏厅
 */
class HallGamesTest extends TestCase
{
//    use DatabaseTransactions;
    protected $route = '/api/hall/games';
    protected $header = ['Accept' => 'application/vnd.pt.v0.1.0+json'];

    /**
     *测试游戏种类列
     */
    public function testGetHallGames()
    {
        /**
         * 测试没有带参数start
         */
        $params = '?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vcGxhdGZvcm0uZGV2L2FwaS9hdXRob3JpemF0aW9uIiwiaWF0IjoxNDg4MzU1MjMwLCJleHAiOjE0ODg1NzEyMzAsIm5iZiI6MTQ4ODM1NTIzMCwianRpIjoiM2UwMzI4ZDZjMzhjYTJkMWQ0Nzc1NGQzNzMzMzg4MzkiLCJzdWIiOjZ9.TnOb0FBRtssguxQncwuY7wnA9QZp8jpSw3LImMZMrBQ';
        $this->get($this->route.$params,  $this->header)
            ->seeJson(

            ) ;
    }
}
