<?php

namespace Modules\Base\Controllers\Common;

use Modules\Base\Libraries\File\Storage;
use Modules\Base\Logics\Common\Image;

class ImageController extends \BaseController
{
    public function __construct()
    {
    }

    public function getTest()
    {
        $storage = new Storage();
        dd($storage->lists('data'));
    }

    public function postUpload()
    {
        $logic = new Image\PostUpload();
        $result = $logic->run();

        return $result;
    }

    public function postEleditorUpload()
    {
        try {
            $logic = new Image\PostUpload();
            $result = $logic->run();

            $res = [
                'status' => 1,
                'url'    => $result['imgInfo']['url'],
            ];
        } catch (\Exception $e) {
            $res = [
                'status' => 0,
                'msg'    => $e->getMessage(),
            ];
        }

        return $res;
    }

    public function postDelete()
    {
        $logic = new Image\PostDelete();
        $result = $logic->run();

        return $result;
    }
}
