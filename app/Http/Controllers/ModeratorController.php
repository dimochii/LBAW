<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModeratorController extends Controller
{
    public function show()
    { 
        $moderated_hubs = Auth::user()->moderatedCommunities();


        return view('pages.moderator', compact(
         'moderated_hubs'
        ));
    }
}
