<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Community;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class checkModerator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $communityId = $request->route('community_id') ?? $request->route('id') ?? NULL;
            $community = Community::findOrFail($communityId);

            if (!$community || (!$user->is_admin && !$user->moderatedCommunities->contains($community))) {
                return response()->view('errors.403', [], 403);
            }
        }
        return $next($request);
    }
}
