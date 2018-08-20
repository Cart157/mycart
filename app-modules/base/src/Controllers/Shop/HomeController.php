<?php

namespace Apk\Base\Controllers\Shop;

use Apk\Goods\Models as GoodsModels;
use Apk\Navi\Models as NaviModels;
use Apk\Base\Services\SiteSetting;

class HomeController extends \BaseController
{
    public function getIndex()
    {
        $result['menu_index_mega']  = NaviModels\Navi::where('navi_slug', 'menu_index_mega')->first();
        $result['slider_index']     = NaviModels\Navi::where('navi_slug', 'slider_index')->first();

        $serv_setting = new SiteSetting();
        $site_setting = $serv_setting->getSiteSetting();

        $result['goods_hot'] = [];
        if (isset($site_setting['goods_hot'])) {
            $result['goods_hot']        = GoodsModels\Goods::whereIn('id', explode(',', $site_setting['goods_hot']))->get();
        }

        $result['goods_recommend'] = [];
        if (isset($site_setting['goods_recommend'])) {
            $result['goods_recommend']  = GoodsModels\Goods::whereIn('id', explode(',', $site_setting['goods_recommend']))
                                                           ->orderByRaw('instr(\',' . $site_setting['goods_recommend'] . ',\', CONCAT(\',\',id,\',\'))')->get();
        }

        return view('sec_base::shop.index')->with('result', $result);
    }
}
