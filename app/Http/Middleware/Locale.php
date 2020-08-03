<?php

namespace App\Http\Middleware;

use Closure;
use Config;
use Session;
use Language;
use App;
use Carbon\Carbon;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = Config::get('app.locale');
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        }
        App::setLocale($locale);
        Carbon::setLocale($locale);
        return $next($request);
    }
}
