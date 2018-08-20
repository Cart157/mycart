<?php

namespace Modules\Base\Libraries\Image;

class Crop
{
    public function __construct()
    {
        $this->tmp_dir = public_path() . '/data/tmp';
    }

    public function crop($post, $size)
    {
        // 处理上传的图片
        $tmp_name   = $this->tmp_dir . '/' . $post['tmpfile'];

        $image_info = getimagesize($tmp_name);
        switch($image_info['mime']) {
            case "image/gif":
                $source = imagecreatefromgif($tmp_name);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source = imagecreatefromjpeg($tmp_name);
                break;
            case "image/png":
            case "image/x-png":
                $source = imagecreatefrompng($tmp_name);
                break;
        }

//        // 头像大中小尺寸设置
//        $size = array(
//            'big'   => '120_120',
//            'middle'=> '80_80'),
//            'small' => '40_40',
//        );

        // 通过$config的size，生成头像图片
        $img_array = array();

        if (!is_array($size)) {
            $size = array('thumb' => $size);
        }

        foreach ($size as $type => $value) {
            // 取得宽高
            list($width, $height) = explode('_', $value);

            // 生成空白图片
            $new_image = imagecreatetruecolor($width, $height);

            // 填充白色作为背景色
            $white = imagecolorallocate($new_image, 255, 255, 255);
            imagefill($new_image, 0, 0, $white);

            // 制作剪切后的图像
            imagecopyresampled($new_image, $source, 0, 0, $post["x1"], $post["y1"], $width, $height, $post["w"], $post["h"]);

            // 输出图像
            $path_parts = pathinfo($post['tmpfile']);
            $new_path   = sprintf('%s/%s_%d_%s.jpg', $this->tmp_dir, $path_parts['filename'], rand( 1 , 10000 ), $type);
            imagejpeg($new_image, $new_path, 100);

            $img_array[$type] = $new_path;

            // 释放内存
            imagedestroy($new_image);
        }

        return $img_array;
    }
}