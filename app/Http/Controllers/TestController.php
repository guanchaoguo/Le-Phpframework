<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Angent;
use Illuminate\Support\Facades\DB;
use App\Models\CashRecord;
use Maatwebsite\Excel\Facades\Excel;
class TestController
{
    public function __construct()
    {
        //$this->client = new \swoole_client(SWOOLE_SOCK_TCP);
    }
    public  function index()
    {

        $data = array(
            array('id'=>1,'name'=>'csj'),
            array('id'=>2,'name'=>'csj2'),
        );

        Excel::create('Filename', function($excel) use($data) {

            $excel->sheet('First sheet', function($sheet) use($data) {
                $sheet->fromArray($data);

            });

        })->export('xls');
        die;
       /* $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $con=socket_connect($socket,'127.0.0.1',11109);
        if(!$con){socket_close($socket);exit;}
        echo "Link\n";
        while($con){
            $hear=socket_read($socket,1024);
            echo $hear;
            $words=fgets(STDIN);
            socket_write($socket,$words);
            if($words=="bye\r\n"){break;}
        }
        socket_shutdown($socket);
        socket_close($socket);*/

        //$data = Angent::get()->toArray();
        //$data = list_to_tree($data);

//        $re = CashRecord::all();

        $user = new CashRecord;
        $user->uid = 222;
        $user->save();

//        CashRecord::create($attributes);
//        dd($re);
    }
}
