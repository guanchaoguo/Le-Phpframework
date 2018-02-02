<?php
/**
 * Created by PhpStorm.
 * User: liangxz@szljfkj.com
 * Date: 2017/4/13
 * Time: 13:18
 */

namespace App\Socket;


class Byte
{
    //长度
    private $length=0;

    private $byte='';
    //操作码
    private $code;
    public function setBytePrev($content){
        $this->byte=$content.$this->byte;
    }
    public function getByte(){
        return $this->byte;
    }
    public function getLength(){
        return $this->length;
    }
    public function writeChar($string){
        $this->length+=strlen($string);
        $str=array_map('ord',str_split($string));
        foreach($str as $vo){
            $this->byte.=pack('c',$vo);
        }
        $this->byte.=pack('c','0');
        $this->length++;
    }
    public function writeInt($str){
        $this->length+=4;
        $this->byte.=pack('L',$str);
    }
    public function writeShortInt($interge){
        $this->length+=2;
        $this->byte.=pack('v',$interge);
    }
    public function writeShortInt2($interge){
        $this->length+=1;
        $this->byte.=pack('C',$interge);
    }

    /*
     *  包网通信专门打包函数
     */
    public function writeRoundot($string){
        $len = strlen($string);
        $this->length+=$len;
        $this->byte.= (pack("a{$len}",$string).pack('x'));
        $this->length++;
    }
}