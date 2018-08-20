<?php

namespace Modules\Base\Libraries\File;

use Overtrue\Qiniu\Qiniu;
use Symfony\Component\Finder\Finder;

/**
 * 所有的操作都是相对public文件路径的
 * copy 和 move 又有云间操作之分
 * 初始化时，指定是否使用
 */
class Storage
{
    public $useCloud = false;

    public function __construct()
    {
    }

//    public static function __callStatic($method, $parameters)
//    {
//        // 要调的伪静态方法不能是public
//        $instance = new static;
//        return call_user_func_array(array($instance, $method), $parameters);
//    }

    public function useLocal()
    {
        $this->useCloud = false;
        return $this;
    }

    public function useCloud()
    {
        $this->useCloud = true;

        // 初始化云
        $options =  [
            'access_key' => 'mXlml86-DPWwFPE9dL8S2YrWczWwHlI9m4jYDgQ0',
            'secret_key' => 'vhBGorRaJuPBYakZrUZvmiPusHp81AcGDM0BcarL',
            'bucket'     => 'www-54mz-com', // bucket名称
            'domain'     => 'www-54mz-com.qiniudn.com', //bucket域名
            'timeout'    => 3600,
            'is_private' => false, //是否为私有
        ];

        $this->cloud = Qiniu::make($options);

        return $this;
    }

    /**
     * see:http://developer.qiniu.com/docs/v6/api/reference/rs/stat.html
     */
    public function exists($filePath)
    {
        $filePath = ltrim($filePath, '/');

        if ($this->useCloud) {
            $result = $this->cloud->info($filePath);

            if (isset($result['error'])) {
                return false;
            }

            return $result;
        }

        $filePath = public_path() . '/' . $filePath;
        return file_exists($filePath);
    }

    /**
     * see:http://developer.qiniu.com/docs/v6/api/reference/rs/copy.html
     */
    public function copy($filePath, $newPath, $betweenCloud = false)
    {
        $filePath = ltrim($filePath, '/');
        $newPath  = ltrim($newPath, '/');
        // todo: mkdir($newDirectory, 0777, true);

        // 云间复制
        if ($betweenCloud) {
            $result = $this->cloud->copy($filePath, $newPath);

            if (isset($result['error'])) {
                return false;
            }

            return true;
        }

        $filePath = public_path() . '/' . $filePath;

        // 本地复制到云端
        if ($this->useCloud) {
            $result = $this->cloud->upload($filePath, $newPath);

            if (isset($result['error'])) {
                return false;
            }

            return $result;
        }

        // 本地复制
        $newPath  = public_path() . '/' . $newPath;
        $directory = dirname($newPath);
        if (!file_exists ($directory)) {
            mkdir($directory, 0777, true);
        }

        return copy($filePath, $newPath);
    }

    /**
     * see:http://developer.qiniu.com/docs/v6/api/reference/rs/move.html
     */
    public function move($filePath, $newPath, $betweenCloud = false)
    {
        $filePath = ltrim($filePath, '/');
        $newPath  = ltrim($newPath, '/');

        // 云间移动
        if ($betweenCloud) {
            $result = $this->cloud->move($filePath, $newPath);

            if (isset($result['error'])) {
                return false;
            }

            return true;
        }

        $filePath = public_path() . '/' . $filePath;

        // 本地移动到云端
        if ($this->useCloud) {
            $result = $this->cloud->upload($filePath, $newPath);

            if (isset($result['error'])) {
                return false;
            }

            unlink($filePath);
            return $result;
        }

        // 本地移动
        $newPath  = public_path() . '/' . $newPath;
        $directory = dirname($newPath);
        if (!file_exists ($directory)) {
            mkdir($directory, 0777, true);
        }

        return rename($filePath, $newPath);
    }

    /**
     * see:http://developer.qiniu.com/docs/v6/api/reference/rs/list.html
     */
    public function lists($prefix)
    {
        $prefix = trim($prefix, '/');

        // 列举云端文件
        if ($this->useCloud) {
            $result = $this->cloud->lists([
                'prefix' => $prefix . '/',
            ]);

            if (isset($result['error'])) {
                return [];
            }

            return $result['items'];
        }

        // 列举本地文件
        $directory = public_path() . '/' . $prefix;
        if (!file_exists ($directory)) {
            mkdir($directory, 0777, true);
        }
        return iterator_to_array(Finder::create()->files()->in($directory));

//        $glob = glob(public_path() . '/' . $prefix . '/*');
//
//        if ($glob === false) return [];
//
//        return array_filter($glob, function($file)
//        {
//            return filetype($file) == 'file';
//        });
    }

    /**
     * see:http://developer.qiniu.com/docs/v6/api/reference/rs/delete.html
     */
    public function delete($filePath)
    {
        $filePath = ltrim($filePath, '/');

        // 本地移动到云端
        if ($this->useCloud) {
            $result = $this->cloud->delete($filePath);

            if (isset($result['error'])) {
                return false;
            }

            return true;
        }

        // 本地删除
        $filePath = public_path() . '/' . $filePath;

        return unlink($filePath);
    }
}
