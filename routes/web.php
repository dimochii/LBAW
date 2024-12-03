<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthenticatedUserController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SideController;
use App\Http\Controllers\NotificationController;

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

Route::redirect('/', '/news');



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
Route::get('/users/{id}', [AuthenticatedUserController::class, 'show'])->name('user.profile');

//followers & following
Route::get('/users/{id}/followers', [AuthenticatedUserController::class, 'getFollowers'])->name('user.followers');
Route::get('/users/{id}/following', [AuthenticatedUserController::class, 'getFollows'])->name('user.following');
Route::post('/user/{id}/follow', [AuthenticatedUserController::class, 'follow'])->name('user.follow');
//articles

Route::get('/favorites', [AuthenticatedUserController::class, 'favorites'])->middleware('auth');
Route::post('/favorites/{id}', [AuthenticatedUserController::class, 'addfavorite'])->middleware('auth');
Route::delete('/unfavorites/{id}', [AuthenticatedUserController::class, 'remfavorite'])->middleware('auth');


//articles

//News
Route::get('/news', [NewsController::class, 'list'])->name('news');
Route::get('/news/{post_id}', [NewsController::class, 'show'])->name('news.show');
Route::get('/news/{post_id}/comments', [CommentController::class, 'getComments'])->name('post.comments');

Route::post('/news/{post_id}/comment', [CommentController::class, 'store'])->name('comments.store');
Route::put('/comments/{id}', [CommentController::class, 'update'])->middleware('auth')->name('comments.update');

//upvote & downvote
Route::post('/news/{post_id}/upvote', [PostController::class, 'upvote'])->name('news.upvote');
Route::post('/news/{post_id}/downvote', [PostController::class, 'downvote'])->name('news.downvote');
Route::post('/news/{post_id}/voteupdate', [PostController::class, 'voteUpdate'])->name('news.voteupdate');

//editing
Route::get('/news/{post_id}/edit', [NewsController::class, 'edit'])->middleware('auth')->name('news.edit');
Route::put('/news/{post_id}', [NewsController::class, 'update'])->middleware('auth')->name('news.update');

//Topic
Route::get('/topic/{post_id}', [TopicController::class, 'show'])->name('topic.show');
//editing
Route::get('/topic/{post_id}/edit', [TopicController::class, 'edit'])->middleware('auth')->name('topics.edit');
Route::put('/topic/{post_id}', [TopicController::class, 'update'])->middleware('auth')->name('topics.update');



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
    Route::get('/about-us', 'aboutUs')->name('about-us');
    Route::get('/admin', 'admin')->name('admin');
  });

  // 'Route::get('/messages', [MessageController::class, 'index'])->name('messages');
  Route::get('/notifications', function() {
    return view('pages.admin');
  })->name('notifications');

  // Search
  Route::controller(SearchController::class)->group(function () {
    Route::get('/search', 'search')->name('search');
  });
});


//Hub
Route::get('/hub/{id}', [CommunityController::class, 'show'])->name('communities.show');
Route::get('/hubs/create', [CommunityController::class, 'create'])->middleware('auth')->name('communities.create');
Route::middleware('auth')->group(function () {
  Route::get('/hubs/create', [CommunityController::class, 'createHub']);
});

Route::get('/hubs', [CommunityController::class, 'store'])->middleware('auth')->name('communities.store');
Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');

Route::post('/hub/{id}/join', [CommunityController::class, 'join'])->middleware('auth')->name('communities.join');
Route::delete('/hub/{id}/leave', [CommunityController::class, 'leave'])->middleware('auth')->name('communities.leave');
Route::post('/hub/{id}/privacy', [CommunityController::class, 'updatePrivacy'])->middleware('auth')->name('communities.update.privacy');
//Route::post('/communities/{id}/apply', [CommunityController::class, 'apply'])->middleware('auth')->name('communities.apply');

Route::get('/reports', [ReportController::class, 'show'])->middleware('auth');
Route::post('/report/{id}', [ReportController::class, 'report'])->middleware('auth');
Route::put('/report/{id}/resolve', [ReportController::class, 'resolve'])->middleware('auth');
Route::get('/side', [SideController::class, 'show'])->middleware('auth')->name('side.show');

//Notifications
Route::get('/notifications', [NotificationController::class, 'show'])
    ->middleware('auth')
    ->name('notifications.show');
    //mark as read
Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');

