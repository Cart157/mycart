<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;

class AppUpdateController extends \BaseController
{
    public function current($device_type)
    {
        $res = parent::apiFetchedResponse();

        try {
            $current = Models\AppUpdateLog::where('device_type', $device_type)->orderBy('version', 'desc')->first();

            $res['data'] = $current ?: [];
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
