<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    public function createHub()
    {
        return view('pages.create_hub');
    }

    public function create(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:communities',
            'description' => 'required|string|max:1000',
            'privacy' => 'required|in:public,private',
            'type' => 'required|in:interest,support',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $community = Community::create([
            'name' => $request->name,
            'description' => $request->description,
            'privacy' => $request->privacy === 'private',
            'type' => $request->type,
            'image_id' => $request->image_id,
            'creation_date' => now(),
        ]);

        $authUser = Auth::user();
        $community->moderators()->attach($authUser->id);

        if ($request->type === 'interest') {
            return app(InterestCommunityController::class)->handleInterestCommunity($community);
        } elseif ($request->type === 'support') {
            return app(SupportCommunityController::class)->handleSupportCommunity($community);
        }

        return response()->json(['message' => 'Invalid community type'], 400);
    }


    public function show($id)
    {
        // Carregar comunidade com posts e autores (com o relacionamento correto)
        $community = Community::with(['posts.authors', 'posts.votes', 'posts.comments'])->find($id);

        // Verificar se a comunidade existe
        if (!$community) {
            abort(404, 'Community not found');
        }

        // Mapeando os posts da comunidade
        $posts = $community->posts->map(function ($post) {
            // Contagem de upvotes e downvotes diretamente da tabela de votos
            $upvotes = $post->votes->where('upvote', true)->count();
            $downvotes = $post->votes->where('upvote', false)->count();

            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'authors_list' => $post->authors->pluck('name')->join(', '), 
                'created_at' => $post->created_at,
                'score' => $upvotes - $downvotes, 
                'comments_count' => $post->comments->count(), 
            ];
        });

        $posts_count = $posts ->count();
        $followers_count = $community->followers()->count();

        $user = Auth::user();
        $is_following = $community->followers()
            ->where('authenticated_user_id', $user->id) 
            ->exists();

        return view('pages.hub', [
            'community' => $community,
            'posts' => $posts,
            'is_following' => $is_following,
            'posts_count' => $posts_count,
            'followers_count' => $followers_count
        ]);
    }

    public function updatePrivacy(Request $request, $id)
    {
        $community = Community::findOrFail($id);

        $this->authorize('updatePrivacy', $community);

        $privacy = $request->input('privacy');

        if ($privacy === 'private') {
            $community->privacy = true; 
        } elseif ($privacy === 'public') {
            $community->privacy = false; 
        }

        $community->save();

        return redirect()->back();
    }

    // Armazenar uma nova comunidade
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:communities',
            'description' => 'required|string|max:1000',
            'privacy' => 'required|in:public,private',
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $community = Community::create([
            'name' => $request->name,
            'description' => $request->description,
            'privacy' => $request->privacy,
            'image_id' => $request->image_id,
            'creation_date' => now(),
        ]);

        // Associar o usuário autenticado como moderador
        $authUser = Auth::user();
        $community->moderators()->attach($authUser->id);

        return response()->json([
            'message' => 'Community created successfully',
            'community' => $community,
        ], 201);
    }

    public function join($id)
    {
        $community = Community::findOrFail($id);
        if (!auth()->user()->communities()->where('community_id', $id)->exists()) {
            auth()->user()->communities()->attach($id);
            return redirect()->back()->with('success', 'Successfully joined the community!');
        }
        
        return redirect()->back()->with('error', 'You are already following this community.');
    }

    public function leave($id)
    {
        $community = Community::findOrFail($id);
        if (auth()->user()->communities()->where('community_id', $id)->exists()) {
            auth()->user()->communities()->detach($id);
            return redirect()->back()->with('success', 'Successfully left the community!');
        }
        
        return redirect()->back()->with('error', 'You are not following this community.');
    }

    public function index(Request $request) {
        $sortBy = $request->get('sort_by', 'name'); 
        $order = $request->get('order', 'asc'); 

        $communities = Community::withCount('followers')
            ->orderBy($sortBy, $order)
            ->paginate(6);

        return view('pages.hubs', compact('communities', 'sortBy', 'order'));
    }

    /*
    public function apply(Request $request, $id)
    {
        $community = Community::find($id);

        if (!$community) {
            return response()->json(['message' => 'Community not found'], 404);
        }

        if ($community->privacy === 'private') {
            return response()->json(['message' => 'You cannot directly join a private community'], 403);
        }

        $authUser = Auth::user();
        $alreadyJoined = $community->followers()->where('authenticated_user_id', $authUser->id)->exists();

        if ($alreadyJoined) {
            return response()->json(['message' => 'You are already a member of this community'], 400);
        }

        // Adicionar o usuário à lista de seguidores da comunidade
        $community->followers()->attach($authUser->id);

        return response()->json(['message' => 'Your application to join the community has been submitted']);
    }*/

}
