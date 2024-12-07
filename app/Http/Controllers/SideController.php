<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SideController extends Controller
{
    public static function fetchSidebarData()
    {
        $user = Auth::user();

        $userHubs = $user ? $user->communities->take(5)->map(function ($community) {
            return [
                'id' => $community->id,
                'name' => $community->name,
            ];
        }) : [];

        $recentHubs = $user ? Cache::get("recent_hubs:{$user->id}", []) : [];

        return compact('userHubs', 'recentHubs');
    }

}
