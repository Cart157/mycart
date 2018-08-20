<?php

namespace Modules\Base\Facades;

use Illuminate\Support\Facades\Facade;

class AliyunRealname extends Facade
{
    /**
     * Return the facade accessor.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'aliyunrealname';
    }
}