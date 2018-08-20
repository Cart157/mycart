<?php

namespace Modules\Base\Libraries\File;

class Upload
{
    public function __construct()
    {
        $this->tmp_path = '/uploads/_tmp';
        $this->tmp_dir  = public_path() . $this->tmp_path;
        $this->del_min  = 60;

        // 临时文件夹不存在时创建
        if(!is_dir($this->tmp_dir)){
            mkdir($this->tmp_dir, 0755, true);
        }
    }

    public function upload($upfile)
    {
        // 通过时间戳进行垃圾处理
        $this->_deleteTmpFile();
        $this->checkType($upfile['type']);

        // 上传文件信息
        $upfile_name      = $upfile['name'];        // 上传文件的原始文件名
        $upfile_tmp_name  = $upfile['tmp_name'];    // 上传文件的临时文件名（全路径）
        $upfile_type      = $upfile['type'];        // 上传文件的类型（image/jpeg）
        $upfile_size      = $upfile['size'];        // 上传文件的大小
                                                    // 还有一个error，上传成功时为0

        $path_parts       = pathinfo($upfile['name']);
        $tmp_name         = time() .'_'. $path_parts["filename"] . strtolower(strrchr($upfile_name, '.'));
        // $tmp_name         = time() .'_'. rand(100000, 999999) . strtolower(strrchr($upfile_name, '.'));
        $tmp_path         = $this->tmp_dir .DIRECTORY_SEPARATOR. $tmp_name;

        // 移动文件
        // $tmp_path_ts = iconv('UTF-8', 'GBK//IGNORE', $tmp_path);
        // move_uploaded_file($upfile_tmp_name, $tmp_path_ts);
        move_uploaded_file($upfile_tmp_name, $tmp_path);
        $uri  = $this->tmp_path .'/'. $tmp_name;

        $result = [
            'name'      => $upfile_name,
            'type'      => $upfile_type,
            'size'      => $upfile_size,
            'uri'       => $uri,
            'url'       => url('/').$uri,
            'tmp_name'  => $tmp_name,
            'tmp_path'  => $tmp_path,
        ];

        return $this->result($result);
    }

    protected function checkType($upfile_type)
    {
        //
    }

    protected function result($result)
    {
        unset($result['tmp_path']);

        return $result;
    }

    /**
     * 上传云存储
     */
    public function uploadBucket($cloudpath, $localpath)
    {
        $bucket = 'www-54mz-com';
        $accessKey = 'mXlml86-DPWwFPE9dL8S2YrWczWwHlI9m4jYDgQ0';
        $secretKey = 'vhBGorRaJuPBYakZrUZvmiPusHp81AcGDM0BcarL';

        Qiniu_SetKeys($accessKey, $secretKey);
        $putPolicy = new \Qiniu_RS_PutPolicy($bucket);
        $upToken = $putPolicy->Token(null);
        $putExtra = new \Qiniu_PutExtra();
        $putExtra->Crc32 = 1;

        return Qiniu_PutFile($upToken, $cloudpath, $localpath, $putExtra);
    }

    /**
     * 垃圾文件处理
     */
    private function _deleteTmpFile()
    {
        // 取得临时文件
        $tmp_files = $this->_files($this->tmp_dir);

        // 垃圾处理
        foreach ($tmp_files as $file) {
            $file_info = pathinfo($file);
            if ($file_info['basename'] == 'Thumbs.db') {
                unlink($file);
            } else {
                $past_time = time() - substr($file_info['filename'], 0, 10);
                if ($past_time > 60 * $this->del_min) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * 取得文件夹下所有文件
     */
    private function _files($directory)
    {
        $glob = glob($directory.'/*');

        if ($glob === false) return [];

        return array_filter($glob, function($file)
        {
            return filetype($file) == 'file';
        });
    }
}
