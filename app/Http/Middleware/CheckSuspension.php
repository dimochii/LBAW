<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Para lidar com datas

class CheckSuspension
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  
     * @param  \Closure
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            $suspensions = $user->suspensions()
                ->where(function ($query) {
                    $query->where('start', '<=', Carbon::now()) 
                          ->whereRaw('DATE_ADD(start, INTERVAL duration DAY) >= ?', [Carbon::now()]); 
                })
                ->get();

            if ($suspensions->isNotEmpty()) {
                return response()->view('pages.suspension', ['suspensions' => $suspensions]);
            }
        }

        return $next($request);
    }
}


