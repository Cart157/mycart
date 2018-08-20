<?php

namespace Modules\User\Controllers\User;

use App\Http\Controllers\Controller;
use App;
use Request;

class HomeController extends Controller
{
    /*
     * Get Method
     */
    public function index()
    {
        return redirect(route('user::srch.se.index'));
//        return view('user::user.home.index');
    }

    public function getBook()
    {
        return view('front.home.book');
    }

    public function getSearch()
    {
        $query = App\Event::query();

        $accept_type = config('const.search_type');
        if (isset($accept_type[Request::input('type')])) {
            $col = 'rp_' . Request::input('type');

            $query->whereNotNull($col);

            if (Request::has('wd')) {
                $query->where($col, Request::input('wd'));
            }
        }

        $result['events'] = $query->orderBy('id', 'desc')
                                  ->paginate(15);

        return view('front.home.search', $result);
    }
}
