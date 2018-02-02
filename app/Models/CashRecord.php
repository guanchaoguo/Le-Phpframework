<?php

namespace App\Models;

/**
现金流模型
*/
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
class CashRecord extends Eloquent
{
    use  Authenticatable;
    protected $table = 'cash_record';
    protected $connection = 'mongodb';
    protected $hidden = ['pkey'];
    public $timestamps = false; //关闭创建时间、更新时间

}
