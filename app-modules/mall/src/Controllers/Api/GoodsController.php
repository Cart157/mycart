<?php

namespace Modules\Mall\Controllers\Api;

use Modules\Mall\Models;
use Request;
use DB;
use JWTAuth;

class GoodsController extends \BaseController
{
    public function index()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $res['data'] = Models\Goods::search(Request::all())->each(function($item) {
                $sku_list = $item->spu->sku()->where('color_id', $item->color_id)->get();
                unset($item->spu);

                if (($sku_list->min('goods_price') != $sku_list->max('goods_price')) && Request::input('sort_by') != 'add_desc') {
                    $item->price_range = $sku_list->min('goods_price') .' - '. $sku_list->max('goods_price');
                } else {
                    $item->price_range = $sku_list->min('goods_price');
                }
            });

            if (Request::has('topic_id')) {
                $res['extra']['parent_name'] = Models\Topic::find(Request::input('topic_id'))->name;
            } elseif (Request::has('spu_id')) {
                $res['extra']['parent_name'] = Models\GoodsSpu::find(Request::input('spu_id'))->name;
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function show($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $goods = Models\Goods::findOrFail($id);
            $goods->goods_images = $goods->images->pluck('path');

            // 整理返回数据
            $goods->addHidden([
                'sku_spec', 'sku_attr',
                'sort_order', 'created_at', 'updated_at', 'deleted_at', 'spu', 'images'
            ]);

            $goods->attr_info = json_decode($goods->sku_attr, true);
            $service_info = $goods->spu
                                  ->skuService($goods->color_id)
                                  ->get()
                                  ->each(function ($item) {
                                      unset($item->sort_order, $item->created_at, $item->updated_at, $item->deleted_at, $item->pivot);
                                  });
            $goods->service_info = $service_info;
            $res['data']  = $goods;

            // 整理 extra 信息
            $sku_list = $goods->spu->sku;

            $spec_arr = [];
            $stock_arr = [];
            // foreach ($sku_list as $sku) {
            //     $spec_tmp = json_decode($sku->sku_spec, true);
            //     $stock_key = '';
            //     foreach ($spec_tmp as $spec) {
            //         if (!isset($spec_arr[$spec['spec_name']][$spec['value']])) {
            //             $spec_value = Models\SpecValue::find($spec['value']);
            //             $spec_arr[$spec['spec_name']][$spec['value']] = $spec_value->name;
            //         }

            //         $stock_key .= empty($stock_key) ? $spec['value'] : '-'. $spec['value'];
            //     }

            //     $stock_arr[$stock_key] = $sku->stock;
            // }

            // 循环商品下的所有的sku
            foreach ($sku_list as $sku) {
                $spec_tmp = json_decode($sku->sku_spec, true);
                $spec_key = '';

                // 循环某个sku的规格
                foreach ($spec_tmp as $spec) {
                    if (!array_get($spec_arr, "{$spec['spec_name']}.{$spec['value']}")) {
                        $spec_value = Models\SpecValue::find($spec['value']);
                        $spec_arr[$spec['spec_name']][$spec['value']] = [
                            'spec_value_id' => $spec_value->id,
                            'spec_value_name' => $spec_value->name,
                        ];
                    }
                    $spec_key .= empty($spec_key) ? $spec['value'] : '-'. $spec['value'];
                }

                $stock_arr[$spec_key] = $sku;
            }
            ksort($spec_arr[$spec['spec_name']]);

            $new_spec_arr = [];
            foreach ($spec_arr as $spec_name => $spec) {
                $new_spec_arr[] = [
                    'spec_name' => $spec_name,
                    'spec_value' => array_values($spec),
                ];
            }

            $new_stock_arr = [];
            $stock_total   = 0;
            foreach ($stock_arr as $spec_key => $sku) {
                $new_stock_arr[] = [
                    'spec_key'      => $spec_key,
                    'spec_stock'    => $sku->stock,
                    'goods_id'      => $sku->id,
                    'goods_price'   => $sku->goods_price,
                    'goods_images'  => $sku->images->pluck('path'),
                ];

                $stock_total    += $sku->stock;
            }

            $res['extra']['spec']  = $new_spec_arr;
            $res['extra']['stock'] = $new_stock_arr;

            // 计算总库存和价格区间
            $res['extra']['stock_total'] = $stock_total;
            if ($sku_list->min('goods_price') != $sku_list->max('goods_price')) {
                $res['extra']['price_range'] = $sku_list->min('goods_price') .' - '. $sku_list->max('goods_price');
            } else {
                $res['extra']['price_range'] = $sku_list->min('goods_price');
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function images($id)
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $sku = Models\ItemSku::findOrFail($id);
            $res['data'] = $sku->images()
                               ->select(['position_code', 'path'])
                               ->orderBy('sort_order', 'asc')
                               ->get();

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function calendar()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $res['data'] = Models\ItemSku::calendar(Request::all());

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    public function recommend()
    {
        $res = parent::apiFetchedResponse();

        try {
            // 业务逻辑
            $rec_ids = DB::table('product_recommend_item')->orderBy('sort_order')->get()
                         ->pluck('sku_id')->toArray();

            if (empty($rec_ids)) {
                $res['data'] = [];
            } else {
                $ids_ordered = implode(',', $rec_ids);
                $res['data'] = Models\ItemSku::select(['id', 'name', 'cover_image'])
                                             ->whereIn('id', $rec_ids)
                                             ->orderByRaw(DB::raw("FIELD(id, $ids_ordered)"))
                                             ->get();
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }

    /**
     * 分享
     */
    public function share($id)
    {
        $res = parent::apiCreatedResponse();

        try {
            $rs = Models\Goods::where('id', $id)->increment('share_cnt');

            if ($rs) {
                if (Request::has('token')) {
                    JWTAuth::setToken(Request::input('token'));
                    $user = JWTAuth::toUser();
                    $user_id = $user->id;

                    // 分享加金币，打金币变化log
                    finish_task_add_coin($user_id, 5, 20);
                }

                $res['message'] = '分享成功';
            } else {
                abort(404, '记录不存在');
            }

        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}