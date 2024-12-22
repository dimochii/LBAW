<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SideController extends Controller
{

    /*
        GET side bar, uses laravel cache storage to retrieve the hubs accessed recently  
    */
    public static function fetchSidebarData()
    {
        $user = Auth::user();
    
        $userHubs = $user ? $user->communities->take(5)->map(function ($community) {
            return [
                'id' => $community->id,
                'name' => $community->name,
                'image' => $community->image ? $community->image->path : '/images/groupdefault.jpg', 
            ];
        }) : [];
    
        $recentHubs = $user ? Cache::get("recent_hubs:{$user->id}", []) : [];
    
        $recentHubs = array_map(function ($hub) {
            $hub['image'] = $hub['image'] ?? '/images/groupdefault.jpg'; 
            return $hub;
        }, $recentHubs);
    
        return compact('userHubs', 'recentHubs');
    }
    

}
