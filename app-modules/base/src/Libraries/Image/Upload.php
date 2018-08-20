<?php

namespace Modules\Base\Libraries\Image;

use Modules\Base\Libraries\File;

class Upload extends File\Upload
{
    public function checkType($upfile_type)
    {
        switch($upfile_type) {
            case "image/gif":
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                break;
            case "image/png":
            case "image/x-png":
                break;
            default:
                throw new \Exception('文件类型错误');
        }
    }

    protected function result($result)
    {
        // $tmp_path_ts = iconv('UTF-8', 'GBK//IGNORE', $result['tmp_path']);
        // $size = getimagesize($tmp_path_ts);
        $size = getimagesize($result['tmp_path']);
        unset($result['tmp_path']);

        $result['width']  = $size[0];
        $result['height'] = $size[1];

        return $result;
    }

//    public function byRemote($picUrl)
//    {
//        $getFile = @file_get_contents($picUrl);
//        $filePath = '/data/tmp/123.jpg';
//
//        if ($getFile) {
//            $fp = @fopen($filePath, 'w');
//            @fwrite($fp, $getFile);
//            @fclose($fp);
//        }
//    }
//
//    public function byBase64($picBase64Data)
//    {
//        $getFile = base64_decode($picBase64Data);
//        $filePath = '/data/tmp/123.jpg';
//
//        if ($getFile) {
//            $fp = @fopen($filePath, 'w');
//            @fwrite($fp, $getFile);
//            @fclose($fp);
//        }
//    }
}
