<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/3/14
 * Time: 13:40
 * 框架入口文件
 */
//自动加载
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);
define('SETTING','ONLINE');//环境设定，测试环境为 TEST ， LOCAL 为开发环境 ,线上环境为 ONLINE, 外网测试环境 PUBLIC 为外网测试环境系统会根据这个常量加载对应环境的配置文件
date_default_timezone_set('Brazil/West');
header("Access-Control-Allow-Origin: *");
require __DIR__.'/../bootstrap/Le.php';
\bootstrap\Le_framework::boot();


