<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/6/26
 * Time: 13:11
 *  rabbitmq 消息队列
 */
namespace App\Http\Controllers\Admin\V1;

class RabbitmqController
{
    private static $mqobject = null;

    /**
     * @param $channelName string 交换机名称
     * @param $queueName  string 使用的队列名称
     */
    private static function connect($channelName, $queueName)
    {
        $connect_list = ['host'=>env('MQ_HOST'), 'prot'=>env('MQ_PORT'), 'login'=>env('MQ_USER'), 'password'=>env('MQ_PWD'),'vhost'=>env('MQ_VHOST')];
        $conn = new \AMQPConnection($connect_list);
        if (!$conn->connect()) {
            die("Cannot connect to the broker \n ");
        }
        $channel = new \AMQPChannel($conn);

        //创建交换机
        $ex = new \AMQPExchange($channel);
        $ex->setName($channelName);//交换机名
        $ex->setType(AMQP_EX_TYPE_FANOUT); //direct类型
        $ex->setFlags(AMQP_DURABLE); //持久化

        //创建队列
//        $queue = new \AMQPQueue($channel);
//        $queue->setName($queueName);
//        $queue->setFlags(AMQP_DURABLE); //持久化
        //echo "Queue Status:".$queue->declare()."\n";

        self::$mqobject = $ex;
        return $ex;
    }


    /**
     * @param $data 参数数组 [交换机名，队列名称，队列路由KEY，发送的消息（json）]
     */
    public static function publishMsg($data)
    {
        list($channelName,$queueName, $routeKey,$msg)  = $data;
        $ex = self::$mqobject ? self::$mqobject : self::connect($channelName,$queueName);
        $res = $ex->publish($msg,$routeKey);//进行消息推送操作
        return $res;
    }


    /**
     * @param $channelName string 交换机名称
     */
    private static function connectToExchange($channelName)
    {
        $connect_list = ['host'=>env('MQ_HOST'), 'prot'=>env('MQ_PORT'), 'login'=>env('MQ_USER'), 'password'=>env('MQ_PWD'),'vhost'=>env('MQ_VHOST')];
        $conn = new \AMQPConnection($connect_list);
        if (!$conn->connect()) {
            die("Cannot connect to the broker \n ");
        }
        $channel = new \AMQPChannel($conn);

        //创建交换机
        $ex = new \AMQPExchange($channel);
        $ex->setName($channelName);//交换机名
        $ex->setType(AMQP_EX_TYPE_FANOUT); //fanout类型
        $ex->setFlags(AMQP_DURABLE); //持久化

        self::$mqobject = $ex;
        return $ex;
    }


    /**
     * @param $data 参数数组 [交换机名,发送的消息（json）]
     */
    public static function publishMsgToExchange($data)
    {
        list($channelName, $msg)  = $data;
        $ex = self::$mqobject ? self::$mqobject : self::connectToExchange($channelName);
        $res = $ex->publish($msg);//进行消息推送操作
        return $res;
    }



}