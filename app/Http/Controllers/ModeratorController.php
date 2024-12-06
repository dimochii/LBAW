<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModeratorController extends Controller
{
    public function show($id)
    {
        $user = AuthenticatedUser::findOrFail($id);  
        $moderated_hubs = $user->moderatedCommunities();


        return view('pages.moderator', compact(
            'users', 'moderated_hubs'
        ));
    }
}
