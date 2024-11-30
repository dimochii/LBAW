<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SideController extends Controller
{
    public function show()
{
    $user = auth()->user();

    $userHubs = $user->communities->take(5)->map(function ($community) {
            return [
                'id' => $community->id,
                'name' => $community->name,
            ];
        });
    
    $recentHubs = Cache::get("recent_hubs:{$user->id}", []);
    Log::info('Recent Hubs Cache:', ['cache' => Cache::get("recent_hubs:{$user->id}")]);

    if (!$recentHubs) {
        $recentHubs = [];
    }
    
    return response()->json([
        'userHubs' => $userHubs,
        'recentHubs' => $recentHubs,
    ]);
}

}
