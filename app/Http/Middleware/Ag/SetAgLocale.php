<?php


namespace App\Http\Middleware\Ag;


use Closure;
use Illuminate\Support\Facades\App;

class SetAgLocale
{

    public function handle($request, Closure $next)
    {
        App::setLocale(env('AG_LANG','en'));
        return $next($request);
    }

}
