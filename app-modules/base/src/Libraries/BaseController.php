<?php

namespace Modules\Base\Libraries;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use AliyuncsValidate;
use BaiduImage;

abstract class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // public function getAuthUser()
    // {
    //     if (!$user = \JWTAuth::parseToken()->authenticate()) {
    //         return response()->json(['user_not_found'], 404);
    //     }

    //     return $user;
    // }


    public function getAuthUser(Request $request)
    {
        \JWTAuth::setToken($request->input('token'));
        $user = \JWTAuth::toUser();

        return $user;
    }

    public static function apiFetchedResponse()
    {
        return [
            'status' => 200,
            'message' => 'done',
        ];
    }

    public static function apiCreatedResponse()
    {
        return [
            'status' => 201,
            'message' => 'done',
        ];
    }

    public static function apiQueuedResponse()
    {
        return [
            'status' => 202,
            'message' => 'done',
        ];
    }

    public static function apiDeletedResponse()
    {
        return [
            'status' => 204,
            'message' => 'done',
        ];
    }

    public static function apiException($e, $res)
    {
        if (method_exists($e, 'getStatusCode')) {
            $res['status']  = $e->getStatusCode();
//        } elseif (method_exists($e, 'getCode')) {
//            $res['status']  = $e->getCode() ?: 500;
        } else {
            $res['status']  = $e->getCode() ? $e->getCode() : 500;
        }

        $res['message']     = $e->getMessage();

        return $res;
    }
    /**
     * 删除相似图片
     * @param $contSign
     * @return mixed
     */
    public static function baiduResembleDelete($contSign)
    {
        $image = BaiduImage::resembleDelete($contSign);
        if($image['status'] != 204)
        {
            abort($image['status'], $image['error_msg']);
        }
        return $image;
    }

    /**
     * 百度搜索相似图片
     * @param $url
     * @param $options['tag_logic'] $options['pn'] $options['rn']
     * @return mixed
     */
    public static function baiduResembleSearch($url,$options)
    {
        $image = BaiduImage::resembleSearch($url,$options);
        if($image['status'] != 200)
        {
            abort($image['status'], $image['error_msg']);
        }
        return $image;
    }

    /**
     * 百度添加相似图片
     * @param $url
     * @param $options['brief'] $options['tags']
     * @return mixed
     */
    public static function baiduResembleAdd($url,$options)
    {
        $image = BaiduImage::resembleAdd($url,$options);
        if($image['status'] != 201)
        {
            abort($image['status'], $image['error_msg']);
        }
        return $image;
    }

    /**阿里云 内容验证
     * @param $content (string|array)
     */
    public static function validateText($content)
    {

        $ali = AliyuncsValidate::text($content);

        if($ali['status'] != 200)
        {
            abort($ali['status'], $ali['message']);
        }
        if($ali['verify']==0)
        {
            abort(403, $ali['message'].'请重新填写');
        }
    }
    /**阿里云 图片同步验证
     * @param $image (string|array)
     */
    public static function validateImageSync($image)
    {

        $ali = AliyuncsValidate::imageSync($image);

        if($ali['status'] != 200)
        {
            abort($ali['status'], $ali['message']);
        }
        if($ali['verify']==0)
        {
            abort(403, $ali['message'].'请重新上传');
        }
    }

    /**
     * 阿里云 图片异步检测
     * @param $image (string|array)
     * @return mixed array
     */
    public static function validateImageAsync($image)
    {

        $ali = AliyuncsValidate::imageAsync($image);

        if($ali['status'] != 200)
        {
            abort($ali['status'], $ali['message']);
        }else{
            return $ali['data'];
        }

    }

    /**
     * 阿里云 图片异步检测结果
     * @param $taskId (string|array)
     */
    public static function validateImageAsyncResults($taskId)
    {
        $ali = AliyuncsValidate::imageAsyncResults($taskId);

        if($ali['status'] != 200)
        {
            if($ali['status'] == 280)
            {
                $ali['message'] = '图片合法性检测任务正在执行中，请稍后';
            }
            abort($ali['status'], $ali['message']);
        }
        if($ali['verify']==0)
        {
            abort(403, $ali['message'].'请重新上传');
        }
    }
    /**
     * 阿里云 视频异步检测
     * @param $video (string)
     * @return mixed array
     */
    public static function validateVideoAsync($video)
    {
        $ali = AliyuncsValidate::videoAsync($video);

        if($ali['status'] != 200)
        {
            abort($ali['status'], $ali['message']);
        }else{
            return $ali['data'];
        }
    }

    /**
     * 阿里云 视频异步检测结果
     * @param $taskId (string)
     */
    public static function validateVideoAsyncResults($taskId)
    {
        $ali = AliyuncsValidate::videoAsyncResult($taskId);

        if($ali['status'] != 200)
        {
            if($ali['status'] == 280)
            {
                $ali['message'] = '视频合法性检测任务正在执行中，请稍后';
            }
            abort($ali['status'], $ali['message']);
        }

        if($ali['verify']==0)
        {
            abort(403, $ali['message'].'请重新上传');
        }
    }
}
