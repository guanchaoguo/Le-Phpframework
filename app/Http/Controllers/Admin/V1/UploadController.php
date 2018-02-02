<?php
/**
 * Created by PhpStorm.
 * User: chengkang
 * Date: 2017/2/8
 * Time: 17:38
 */
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class UploadController extends BaseController
{
    public function __construct()
    {
    }

    /**
     * @api {post} /upload.php 文件上传
     * @apiDescription 文件上传 使用文件服务器域名： http://images.dev/
     * 将 “http://platform.dev/api” 替换成 http://images.dev/
     * @apiGroup files
     * @apiParam {string} file 上传控件名 *
     * @apiParam {Numer} type 上传类型 ，非必须，当type=1 ：上传文档 ，type=2：上传荷官图片，默认type为空：默认上传普通图片
     * @apiParam {string} dealer_ID  荷官ID（编号） 当type=2时，必须传荷官ID
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} 成功返回格式:
        {
        "code":0,
        "text":"success",
        "result":
        [
            {
                "host":"http://images.dev/",//域名
                "save_path":"./upload/2017/05/2017052505071153.jpg",//保存数据库的路径
                "size":'31.14KB'//文件大小
            }
        ]
        }
     */
    public function index(Request $request)
    {
        return $this->response->array([
            'code' => 400,
            'text' =>trans('agent.fails'),
            'result' => '请访问图片服务器：'.env('IMAGE_HOST')

        ]);
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();     // 扩展名
        $pathname = 'images/';
        $filename = date('Y-m-d-H-i-s') . '-' . uniqid() . '.' . $ext;
        $file->move($pathname, $filename);
        return $this->response->array([
            'code' => 0,
            'text' =>trans('agent.success'),
            'result' => [
                'filename' => $pathname.$filename,
                'host' => env('APP_HOST'),
            ],
        ]);
    }


    /**
     * @api {post} /getImages.php 获取图片缩略图
     * @apiUrl http://192.168.31.230:8000
     * @apiDescription 获取图片缩略图 使用图片服务器域名： http://images.dev/
     * 将 “http://platform.dev/api” 替换成 http://images.dev/
     * @apiGroup files
     * @apiParam {String} file 原图片路径
     * @apiParam {Array} size 要生成的图片尺寸 数组 格式['500_200','800_500']
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} 成功返回格式:
        {
        "code": 0,
        "text": "success",
        "result": [
            {
                "500_200": "./upload/2017/05/thumb_500_200_2017052505071153.jpg"
            },
            {
                "800_500": "./upload/2017/05/thumb_800_500_2017052505071153.jpg"
            }
        ]
        }
     */

    //删除方法没有
    public function getImages(Request $request) {

        /*return $this->response->array([
            'code' => 400,
            'text' =>trans('agent.fails'),
            'result' => '请访问图片服务器：'.env('IMAGE_HOST')

        ]);

        $path = $request->input('path');
        if( ! File::exists( $path)) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.file_not_eixt'),
                'result' => $path

            ]);

        }

        if( ! File::delete($path) ) {

            return $this->response->array([
                'code' => 400,
                'text' =>trans('agent.fails'),
                'result' => $path

            ]);

        } else {
            return $this->response->array([
                'code' => 0,
                'text' =>trans('agent.success'),
                'result' =>''

            ]);
        }*/

    }

    /**
     * @api {post} /removeFile.php 删除文件
     * @apiUrl http://192.168.31.230:8000
     * @apiDescription 删除文件 使用文件域名： http://images.dev/
     * 将 “http://platform.dev/api” 替换成 http://images.dev/
     * @apiGroup files
     * @apiParam {String} file 文件路径（相对路径，数据库保存的路径）
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} 成功返回格式:
    {
    "code": 0,
    "text": "success",
    "result": ''
    }
     */
    public function removeFile(Request $request) {

    }
}