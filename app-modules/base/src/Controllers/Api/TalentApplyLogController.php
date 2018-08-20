<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models;
use Request;
use Validator;
use JWTAuth;

class TalentApplyLogController extends \BaseController
{
    public function store()
    {
        $res = parent::apiCreatedResponse();

        $validator = Validator::make(Request::all(), [
            'name'                  => 'required|min:1|max:8',
            'description'           => 'required|min:20|max:200',
            'certificate_image'     => 'array',
            'certificate_image.*'   => 'url',
        ]);

        $validator->after(function ($validator) {
            if (count(Request::input('certificate_image')) > 9) {
                $validator->errors()->add('certificate_image', '最多上传9张图片');
            }
        });

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $user = JWTAuth::user();
            $check_num = Models\TalentApplyLog::where('user_id', $user->id)->where('check_status', 10)->count();
            if ($check_num) {
                abort(403, '不能重复提交申请！');
            }

            $apply = Models\TalentApplyLog::create([
                'user_id'       => $user->id,
                'name'          => Request::input('name'),
                'description'   => Request::input('description'),
            ]);

            if (Request::has('certificate_image') && count(Request::input('certificate_image')) > 0) {
                // 移动七牛图片，然后把input的数据剔除域名
                $idx = 1;
                $image = [];
                foreach (Request::input('certificate_image') as $image_url) {
                    $source = get_qiniu_key($image_url);

                    $path_parts = pathinfo($source);
                    $target = sprintf('/uploads/base/talent_log/%d/%d.%s', $apply->id, $idx, $path_parts['extension']);

                    move_qiniu_uploads($source, $target);

                    $image[] = $target;
                    $idx++;
                }

                $apply->certificate_image = json_encode($image, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                $apply->save();
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
