<?php

/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/13
 * Time: 13:17
 */
namespace App\Socket;

class GameSocket
{
    public $socket;
    private $port=9991;
    private $host='192.168.211.231';
    private $byte;
    public $code;
    const CODE_LENGTH=2;
    const SIZE_LENGTH=2;
    public function __set($name,$value){
        $this->$name=$value;
    }
    public function __construct($host='192.168.211.231',$port=9991){
        $this->host=$host;
        $this->port=$port;
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if(!$this->socket){
            exit('创建socket失败');
        }
        $result = socket_connect($this->socket,$this->host,$this->port);
        if(!$result){
            exit('连接不上目标主机'.$this->host);
        }
        $this->byte=new Byte();
    }
    public function getByte()
    {
        return $this->byte;
    }

    public function write($data){
        if(is_string($data)||is_int($data)||is_float($data)){
            $data[]=$data;
        }
        if(is_array($data)){
            foreach($data as $vo){
                $this->byte->writeShortInt(strlen($vo));
                $this->byte->writeChar($vo);
            }
        }
        $this->setPrev();
        $this->send();
        if (false === ($line = @socket_read($this->socket, 1024))){
            echo ("SOCKET_READ_ERROR: " . socket_strerror(socket_last_error($this->socket)));
        }
        return $line;
    }

    /*
     *设置表头部分
     *表头=length+code+flag
     *length是总长度(4字节)  code操作标志(2字节)  flag暂时无用(4字节)
     */
    private function getHeader(){
        $length=$this->byte->getLength();
        $length=intval($length)+self::CODE_LENGTH+self::SIZE_LENGTH;
        return pack('S',$length);
    }
    private function getCode(){
        return pack('S',intval($this->code));
    }
    private function getFlag(){
        return pack('S',24);
    }

    private function setPrev(){
        $this->byte->setBytePrev($this->getHeader().$this->getCode());
    }

    private function send(){
        $result=socket_write($this->socket,$this->byte->getByte());
        if(!$result){
            exit('发送信息失败');
        }
    }
    public function __desctruct(){
        socket_close($this->socket);
    }
}