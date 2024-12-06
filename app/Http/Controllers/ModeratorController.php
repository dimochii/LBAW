<?php

namespace App\Http\Controllers;


use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModeratorController extends Controller
{
    public function show(Request $request)
    { 

        $moderated_hubs= Auth::user()->moderatedCommunities;

        $selected_hub = null;

        if ($request->has('hub_id')) {
            $selected_hub = $moderated_hubs->firstWhere('id', $request->hub_id);
    
            if ($selected_hub) {
                $selected_hub->load(['posts', 'moderators', 'followers']);
            }
        }

        return view('pages.moderator', compact(
         'moderated_hubs','selected_hub'
        ));
    }
}
