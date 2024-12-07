<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
            'image_id' => 'nullable|integer|exists:images,id',
        ]);

        $community = Community::create([
            'name' => $request->name,
            'description' => $request->description,
            'privacy' => $request->privacy === 'private', 
            'image_id' => $request->image_id,
            'creation_date' => now(), 
        ]);

        $authUser = Auth::user();
        $community->moderators()->attach($authUser->id);

        return response()->json([
            'message' => 'Community created successfully',
            'community' => $community,
        ], 201);
    }

    public function destroy($id)
    {
        // Find the community by ID
        $community = Community::findOrFail($id);
        if (!($this->authorize('isAdmin') || $this->authorize('isCommunityAdmin', $community))) {
            abort(403, 'Unauthorized action.');
        }

        // Check if the community has any posts
        if ($community->posts()->exists()) {
            // If the community has posts, prevent deletion
            return redirect()->back()->with('error', 'Cannot delete a community that has posts.');
        }

        // If no posts exist, delete the community
        $community->delete();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'deleted community.');
    }



    public function show($id)
    {
    
        // Carregar comunidade com posts e autores (com o relacionamento correto)
        $community = Community::with(['posts.authors', 'posts.votes', 'posts.comments'])->find($id);

        // Verificar se a comunidade existe
        if (!$community) {
            abort(404, 'Community not found');
        }

        //dar cache à comunidade ao id the cache
        $this->cacheRecentHub($community->id,$community->name);

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
                'news' => $post->news,  // Add the related news
                'topic' => $post->topic,
            ];
        });

        $newsPosts = $community->posts->filter(function ($post) {
            return !is_null($post['news']);
        });

        $topicPosts = $community->posts->filter(function ($post) {
            return !is_null($post['topic']);
        });
        $posts_count = $posts ->count();
        $followers_count = $community->followers()->count();

        $user = Auth::user();
        $is_following = $community->followers()
            ->where('authenticated_user_id', $user->id) 
            ->exists();

        return view('pages.hub', [
            'community' => $community,
           // 'posts' => $posts,
            'newsPosts'=> $newsPosts,
            'topicPosts'=> $topicPosts,
            'is_following' => $is_following,
            'posts_count' => $posts_count,
            'followers_count' => $followers_count
        ]);
    }


    private function cacheRecentHub($communityId, $communityName)
    {
        $userId = Auth::user()->id;
        
        //cache key
        $cacheKey = "recent_hubs:{$userId}";

        $hubData = ['id' => $communityId, 'name' => $communityName];

        // Dar fetch aos hubs mais recentes na cache
        $recentHubs = Cache::get($cacheKey, []);

        // Remover o hub se esse já estiver na cache
        $recentHubs = array_filter($recentHubs, fn($hub) => $hub['id'] !== $communityId);

        // Addicionar o hub no inicio
        array_unshift($recentHubs, $hubData);

        // Manter só os 4 primeiros
        $recentHubs = array_slice($recentHubs, 0, 4);

        // Guardar na cache por 12 horas:
        Cache::put($cacheKey, $recentHubs, now()->addHours(12));


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
            'privacy' => $request->privacy === 'private',
            'image_id' => $request->image_id,
            'creation_date' => now(),
        ]);

        // Associar o usuário autenticado como moderador
        $authUser = Auth::user(); 
        $community->moderators()->attach($authUser->id);

        return redirect()->route('news')->with('success', 'Community created successfully!');
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
