<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Community;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
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
                'authors_list' => $post->authors->pluck('name')->join(', '), // Coleta nomes dos autores
                'created_at' => $post->created_at,
                'score' => $upvotes - $downvotes, // Soma de upvotes menos downvotes
                'comments_count' => $post->comments->count(), // Contagem de comentários
            ];
        });

        // Carregar os moderadores da comunidade
        $moderators = $community->moderators()->get(['id', 'username']);

        $user = Auth::user();
        $isFollowing = $community->followers()
            ->where('authenticated_user_id', $user->id) // Corrigido o nome da chave no relacionamento
            ->exists();

        // Retornar a visão com os dados da comunidade
        return view('pages.hub', [
            'community' => $community,
            'posts' => $posts,
            'moderators' => $moderators,
            'isFollowing' => $isFollowing,
        ]);
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

    // Entrar em uma comunidade pública
    public function join(Request $request, $id)
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

        return response()->json(['message' => 'You have successfully joined the community']);
    }
}
