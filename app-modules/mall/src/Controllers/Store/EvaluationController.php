<?php

namespace Modules\Mall\Controllers\Store;

use Modules\Mall\Models;
use Request;

class EvaluationController extends \BaseController
{
    public function index()
    {
        $week_ago = date('Y-m-d', strtotime('-1 week'));
        $month_ago = date('Y-m-d', strtotime('-1 month'));
        $halfyear_ago = date('Y-m-d', strtotime('-6 month'));

        // count
        $count['avg_score'] = round(Models\Evaluation::withTrashed()->avg('score'), 1);
        $evaluation = Models\Evaluation::withTrashed();
        $count['evaluation'] = $evaluation->count();
        $count['evaluation_halfyear'] = $evaluation->where('created_at', '>=', $halfyear_ago)->count();
        $count['evaluation_month'] = $evaluation->where('created_at', '>=', $month_ago)->count();
        $count['evaluation_week'] = $evaluation->where('created_at', '>=', $week_ago)->count();
        $count['evaluation_before'] = Models\Evaluation::withTrashed()->where('created_at', '<', $halfyear_ago)->count();

            // 1分
        $score_1 = Models\Evaluation::withTrashed()->where('score', 1);
        $count['score_1'] = $score_1->count();
        $count['score_1_halfyear'] = $score_1->where('created_at', '>=', $halfyear_ago)->count();
        $count['score_1_month'] = $score_1->where('created_at', '>=', $month_ago)->count();
        $count['score_1_week'] = $score_1->where('created_at', '>=', $week_ago)->count();
        $count['score_1_before'] = Models\Evaluation::withTrashed()->where('score', 1)->where('created_at', '<', $halfyear_ago)->count();
            // 2分
        $score_2 = Models\Evaluation::withTrashed()->where('score', 2);
        $count['score_2'] = $score_2->count();
        $count['score_2_halfyear'] = $score_2->where('created_at', '>=', $halfyear_ago)->count();
        $count['score_2_month'] = $score_2->where('created_at', '>=', $month_ago)->count();
        $count['score_2_week'] = $score_2->where('created_at', '>=', $week_ago)->count();
        $count['score_2_before'] = Models\Evaluation::withTrashed()->where('score', 2)->where('created_at', '<', $halfyear_ago)->count();
            // 3分
        $score_3 = Models\Evaluation::withTrashed()->where('score', 3);
        $count['score_3'] = $score_3->count();
        $count['score_3_halfyear'] = $score_3->where('created_at', '>=', $halfyear_ago)->count();
        $count['score_3_month'] = $score_3->where('created_at', '>=', $month_ago)->count();
        $count['score_3_week'] = $score_3->where('created_at', '>=', $week_ago)->count();
        $count['score_3_before'] = Models\Evaluation::withTrashed()->where('score', 3)->where('created_at', '<', $halfyear_ago)->count();
            // 4分
        $score_4 = Models\Evaluation::withTrashed()->where('score', 4);
        $count['score_4'] = $score_4->count();
        $count['score_4_halfyear'] = $score_4->where('created_at', '>=', $halfyear_ago)->count();
        $count['score_4_month'] = $score_4->where('created_at', '>=', $month_ago)->count();
        $count['score_4_week'] = $score_4->where('created_at', '>=', $week_ago)->count();
        $count['score_4_before'] = Models\Evaluation::withTrashed()->where('score', 4)->where('created_at', '<', $halfyear_ago)->count();
            // 5分
        $score_5 = Models\Evaluation::withTrashed()->where('score', 5);
        $count['score_5'] = $score_5->count();
        $count['score_5_halfyear'] = $score_5->where('created_at', '>=', $halfyear_ago)->count();
        $count['score_5_month'] = $score_5->where('created_at', '>=', $month_ago)->count();
        $count['score_5_week'] = $score_5->where('created_at', '>=', $week_ago)->count();
        $count['score_5_before'] = Models\Evaluation::withTrashed()->where('score', 5)->where('created_at', '<', $halfyear_ago)->count();

        $res['count'] = $count;

        // data
        if (Request::input('is_delete') == 1) {
            $q = Models\Evaluation::onlyTrashed();
        } else {
            $q = Models\Evaluation::whereNull('deleted_at');
        }

        if (Request::has('score')) {
            $q->where('score', Request::input('score'));
        }

        $res['data'] = $q->paginate(10);

        return view('mall::store.evaluate.index', $res);
    }

    public function delete($id)
    {
        $res = parent::apiDeletedResponse();

        try {
            $evaluation = Models\Evaluation::find($id)->delete();
        } catch (\Exception $e) {
            $res = parent::apiException($e, $res);
        }

        return $res;
    }
}