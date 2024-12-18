<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckSuspension
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            $suspensions = $user->suspensions()
            ->where('start', '<=', Carbon::now()) 
            ->where('duration', '>=', Carbon::now()) 
            ->get();


            if ($user->is_suspended || $suspensions->count() > 0) {
                return response()->view('pages.suspension', ['suspensions' => $suspensions]);
            }
        }

        return $next($request);
    }
}




