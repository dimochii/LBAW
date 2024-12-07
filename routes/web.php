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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ModeratorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\LeftController;

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






// API





// Authentication
Route::controller(LoginController::class)->group(function () {
  Route::get('/login', 'showLoginForm')->name('login');
  Route::post('/login', 'authenticate');
  Route::get('/logout', 'logout')->name('logout');
});

Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [PasswordResetController::class, 'updatePassword'])->name('password.update');
Route::post('/password/update', [PasswordResetController::class, 'updatePassword'])->name('password.update');


Route::controller(RegisterController::class)->group(function () {
  Route::get('/register', 'showRegistrationForm')->name('register');
  Route::post('/register', 'register');
});

//admin


//Authenticated User
//profile
Route::get('/users/{id}/profile', [AuthenticatedUserController::class, 'show'])->name('user.profile');
//edit profile
Route::get('/users/{id}/edit', [AuthenticatedUserController::class, 'edit'])->name('user.edit');
Route::post('/users/{id}/update', [AuthenticatedUserController::class, 'update'])->name('user.update');
Route::post('/users/{id}', [AuthenticatedUserController::class, 'destroy'])->name('user.destroy');
Route::get('/users/{id}', [AuthenticatedUserController::class, 'show'])->name('user.profile');
Route::get('/users/{user}/profile', [AuthenticatedUserController::class, 'show'])->name('user.profile');
Route::get('/users/{user}/profile/favorites', [AuthenticatedUserController::class, 'favorites']);



//followers & following
Route::get('/users/{id}/followers', [AuthenticatedUserController::class, 'getFollowers'])->name('user.followers');
Route::get('/users/{id}/following', [AuthenticatedUserController::class, 'getFollows'])->name('user.following');
Route::post('/user/{id}/follow', [AuthenticatedUserController::class, 'follow'])->name('user.follow');
//articles

Route::get('/favorites', [AuthenticatedUserController::class, 'favorites'])->middleware('auth');
Route::delete('/unfavorites/{id}', [AuthenticatedUserController::class, 'remfavorite'])->middleware('auth');
Route::delete('/users/{id}', [AuthenticatedUserController::class, 'deletemyaccount'])->middleware('auth')->name('user.delete');


//admin
Route::post('/users/{id}/suspend',[AuthenticatedUserController::class,'suspend'])->middleware('auth');
Route::post('/users/{id}/suspend',[AuthenticatedUserController::class,'unsuspend'])->middleware('auth');
Route::post('/favorite/{id}/add', [AuthenticatedUserController::class, 'addfavorite']);
Route::post('/favorite/{id}/remove', [AuthenticatedUserController::class, 'remfavorite']);



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
    Route::get('/bestof', 'bestof')->name('bestof');
    Route::get('/admin', [AdminController::class, 'show'])->name('user.admin');
    Route::get('/moderator', [ModeratorController::class, 'show'])->name('user.moderator');
    Route::post('/users/{id}/suspend', [AuthenticatedUserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{id}/unsuspend', [AuthenticatedUserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::post('/users/{id}/make_admin', [AuthenticatedUserController::class, 'makeAdmin'])->name('users.make_admin');
    Route::post('/users/{id}/remove_admin', [AuthenticatedUserController::class, 'removeAdmin'])->name('users.remove_admin');
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

Route::middleware('auth')->group(function () {
  Route::get('/hubs/create', [CommunityController::class, 'createHub']);
});
Route::post('/hubs/destroy', [CommunityController::class, 'destroy'])->middleware('auth')->name('communities.destroy');

Route::post('/hubs', [CommunityController::class, 'store'])->middleware('auth')->name('communities.store');
Route::get('/all-hubs', [CommunityController::class, 'index'])->name('communities.index');

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

// Recover password

Route::post('/send', [MailController::class, 'send']);

// OAuth API

Route::controller(GoogleController::class)->group(function () {
  Route::get('auth/google', 'redirect')->name('google-auth');
  Route::get('auth/google/call-back', 'callbackGoogle')->name('google-call-back');
});


Route::get('/images/{filename}', function ($filename) {
  $path = base_path('images/' . $filename);

  $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
  $extension = pathinfo($filename, PATHINFO_EXTENSION);

  if (!File::exists($path) || !in_array($extension, $allowedExtensions)) {
      abort(404);
  }

  return response()->file($path);
})->name('images.serve');

// Left side bar

