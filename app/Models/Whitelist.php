<?php

namespace App\Models;

/**
白名单模型
*/
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class Whitelist extends BaseModel
{
    use  Authenticatable;
    protected $table = 'white_list';
    public $timestamps = false; //关闭创建时间、更新时间

}
