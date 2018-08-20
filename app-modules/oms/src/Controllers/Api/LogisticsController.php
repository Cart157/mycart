<?php

namespace Modules\Oms\Controllers\Api;

use Modules\Oms\Models;
use Request;
use Validator;
use Cache;
use GuzzleHttp;

class LogisticsController extends \BaseController
{
    // const LIMIT_PER_PAGE = 10;

    // public function fee()
    // {
    //     $res = parent::apiFetchedResponse();

    //     try {
    //         $res['data']['logistics'] = '123';
    //         $res['data']['coin'] = '123';

    //     } catch (\Exception $e) {
    //         $res = parent::apiException($e, $res);
    //     }

    //     return $res;
    // }

    public function query()
    {
        $res = parent::apiFetchedResponse();

        $validator = Validator::make(Request::all(), [
            'waybill_no' => 'required|string',
        ]);

        try {
            if ($validator->fails()) {
                abort(400, $validator->errors()->first());
            }

            $waybill_no = Request::input('waybill_no');

            $logistics_info =  Cache::get("logistics_info:waybill_no_{$waybill_no}");
            if ($logistics_info) {
                $res['data'] = $logistics_info;
            } else {
                $guzzle = new GuzzleHttp\Client();
                $response = $guzzle->get('http://kdwlcxf.market.alicloudapi.com/kdwlcx', [
                    'query'    => http_build_query([
                        'no' => $waybill_no,
                    ]),
                    'headers' => [
                        'Authorization'  => 'APPCODE f35b01c1a7e640fca241c9e22541f6db',
                    ],
                ]);

                $query_res = json_decode((string) $response->getBody(), true);
                if ($query_res['status'] != 0) {
                    abort(403, $query_res['msg']);
                }

                $query_data = [
                    'waybill_no'    => $query_res['result']['number'],
                    'waybill_trace' => $query_res['result']['list'],
                    'delivery_status' => $query_res['result']['deliverystatus'],
                    'is_sign'       => $query_res['result']['issign'],
                    'exp_type'      => $query_res['result']['type'],
                    'exp_name'      => $query_res['result']['expName'],
                    'exp_site'      => $query_res['result']['expSite'],
                    'exp_phone'     => $query_res['result']['expPhone'],
                ];

                $res['data'] = $query_data;
                Cache::put("logistics_info:waybill_no_{$waybill_no}", $query_data, 10);
            }
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}
