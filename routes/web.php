<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthenticatedUserController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home

Route::redirect('/', '/login');



// Cards


// API



// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});

//Authenticated User
    //profile
Route::get('/users/{id}/profile', [AuthenticatedUserController::class, 'show'])->name('user.profile');
    //edit profile
Route::get('/users/{id}/edit', [AuthenticatedUserController::class, 'edit'])->name('user.edit');
Route::post('/users/{id}', [AuthenticatedUserController::class, 'update'])->name('user.update');   
    //followers & following
Route::get('/users/{id}/followers', [AuthenticatedUserController::class, 'getFollowers'])->name('user.followers');
Route::get('/users/{id}/following', [AuthenticatedUserController::class, 'getFollows'])->name('user.following');
    //articles
    
//News
Route::get('/news', [NewsController::class, 'list'])->name('news');
Route::get('/news/{post_id}', [NewsController::class, 'show'])->name('news.show');
Route::get('/news/{post_id}/comments', [CommentController::class, 'getComments'])->name('post.comments');
// Exemplo de definição de rota
Route::post('/news/{post_id}/comment', [CommentController::class, 'store'])->name('comments.store');
Route::put('/comments/{id}', [CommentController::class, 'update'])->middleware('auth')->name('comments.update');



    //upvote & downvote
Route::post('/news/{post_id}/upvote', [NewsController::class, 'upvote'])->name('news.upvote');
Route::post('/news/{post_id}/downvote', [NewsController::class, 'downvote'])->name('news.downvote');
Route::post('/news/{post_id}/voteupdate', [NewsController::class, 'voteUpdate'])->name('news.voteupdate');

    //editing
Route::get('/news/{post_id}/edit', [NewsController::class, 'edit'])->middleware('auth')->name('news.edit');
Route::put('/news/{post_id}', [NewsController::class, 'update'])->middleware('auth')->name('news.update');


//Posts
    //creation
Route::get('/posts/create', [PostController::class, 'createPost'])->middleware('auth')->name('post.create');
Route::post('/posts', [PostController::class, 'create'])->middleware('auth')->name('post.store');
Route::delete('/posts/delete/{id}', [PostController::class, 'delete'])->middleware('auth')->name('post.delete');


Route::middleware('auth')->group(function () {
    Route::controller(FeedController::class)->group(function () {
        Route::get('/home', 'home')->name('home'); 
        Route::get('/global', 'global')->name('global');
        Route::get('/recent', 'recent')->name('recent');
    });

    Route::get('/messages', [MessageController::class, 'index'])->name('messages');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');

    // Search
    Route::controller(SearchController::class)->group(function () {
        Route::get('/search', 'search')->name('search');
    });
});


//Hub
Route::get('/hub/{id}', [CommunityController::class, 'show'])->name('communities.show');
Route::get('/hubs/create', [CommunityController::class, 'create'])->middleware('auth')->name('communities.create');
Route::post('/hubs', [CommunityController::class, 'store'])->middleware('auth')->name('communities.store');
Route::post('/hub/{id}/join', [CommunityController::class, 'join'])->middleware('auth')->name('communities.join');
Route::delete('/hub/{id}/leave', [CommunityController::class, 'leave'])->middleware('auth')->name('communities.leave');
//Route::post('/communities/{id}/apply', [CommunityController::class, 'apply'])->middleware('auth')->name('communities.apply');
