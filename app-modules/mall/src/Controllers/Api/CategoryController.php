<?php

namespace Modules\Mall\Controllers\Api;

use Modules\Mall\Models;
use Request;

class CategoryController extends \BaseController
{
    const LIMIT_PER_PAGE = 10;

    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            $limit = (int) Request::input('limit') ?: self::LIMIT_PER_PAGE;

            if (Request::has('parent_id')) {
                $parent = Models\Category::find(Request::has('parent_id'));
                if ($parent) {
                    $path = trim($parent->path, '-');
                    $level = count(explode('-', $path)) + 1;
                } else {
                    $level = 1;
                }

                // 三级分页
                if ($level == 3) {
                    $res['data'] = Models\Category::where('parent_id', Request::input('parent_id'))
                                                  ->paginate($limit)
                                                  ->items();
                } else {
                    $res['data'] = Models\Category::where('parent_id', Request::input('parent_id'))
                                                  ->get();
                }

            } elseif (Request::has('grandfather_id')) {
                // 获取那些属于（父级是grandfather_id的二级分类）的三级分类
                $res['data'] = Models\Category::whereHas('parent', function ($query) {
                    $query->where('parent_id', Request::input('grandfather_id'));
                })->paginate($limit)->items();
            }else {
                $res['data'] = Models\Category::treeArray();
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
