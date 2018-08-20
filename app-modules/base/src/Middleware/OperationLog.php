<?php

namespace Modules\Base\Middleware;

use Closure;
use Request;
use Auth;

class OperationLog
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if ($request->method() != 'GET' && $request->method() != 'OPTIONS'
          && !isset($response->exception)) {
            $input = array_except($request->all(), ['_token', '_method', 'save_action']);

            $log = new \Modules\Base\Models\OperationLog();
            $log->user_id = Auth::user()->id;
            $log->path = $request->path();
            $log->method = $request->method();
            $log->ip = $request->ip();
            $log->input = json_encode($input, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
            @$log->save();
        }

        return $response;
    }
}
