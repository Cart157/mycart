<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use Request;
use Validator;
use JWTAuth;

class TalentController extends \BaseController
{
    public function show()
    {
        $res = parent::apiFetchedResponse();

        try {
            $user = JWTAuth::user();

            if ($user->talent) {
                $res['data'] = $user->talent;
                $extra['is_talent'] = true;
            } else {
                $res['data'] = (object) [];
                $extra['is_talent'] = false;
            }

            $check_num = Models\TalentApplyLog::where('user_id', $user->id)->where('check_status', 10)->count();
            $extra['can_apply'] = !(boolean) $check_num;

            $res['extra'] = $extra;

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
