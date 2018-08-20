<?php

namespace Modules\Base\Controllers\Api;

use Modules\Base\Models\Material;
use JWTAuth;
use Request;
use Auth;
use Validator;
use DB;

class MaterialController extends \BaseController
{

    /**
     *  素材列表
     *
     */
    public function index()
    {
        $res = parent::apiFetchedResponse();
        try {

            $user = JWTAuth::User();
            $res['data'] = Material::search(Request::all(),$user->id);
        } catch(\Exception $e) {
            $res = parent::apiException($e, $res);
        }
        return $res;
    }

    /**
     * 上传素材
     */
    public function store()
    {
        $res = parent::apiCreatedResponse();
        $validator = Validator::make(Request::all(),[
            'material'       => 'required|array',
        ]);
        try {

            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $user = JWTAuth::User();
            $images = request('material');

            foreach ($images as $code => $path) {
                $source = get_qiniu_key($path);
                $path_parts = pathinfo($source);
                $target = sprintf('/uploads/user/material/%d/%s', $code, $path_parts['basename']);
                move_qiniu_uploads($source, $target);
                //$custom_image_all[] = $target;
                Material::create([
                    'user_id'   =>  $user->id,
                    'material'  =>  $target,
                    'type'      =>  request()->has('type')?request('type'):'image',
                    'name'      =>  $path_parts['basename']
                ]);
            }
        }catch (\Exception $e) {

            $res = parent::apiException($e, $res);

        }
        return $res;
    }

    /**
     * 删除
     * @return mixed
     */
    public function destroy()
    {
        $res = parent::apiDeletedResponse();
        $validator = Validator::make(Request::all(),[
            'ids'       => 'required|array',
        ]);
        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }
            $user = JWTAuth::User();
            $items = Material::whereIn('id',request('ids'))->where('user_id',$user->id)->delete();

        } catch(\Exception $e) {
            $res = parent::apiException($e, $res);
        }
        return $res;
    }




}