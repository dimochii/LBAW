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

Route::redirect('/', '/global');


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
Route::get('/users/{id}/profile', [AuthenticatedUserController::class, 'show'])->middleware(['auth', 'check.suspension'])->name('user.profile');
//edit profile
Route::get('/users/{id}/edit', [AuthenticatedUserController::class, 'edit'])->middleware(['auth', 'check.suspension'])->name('user.edit');
Route::post('/users/{id}/update', [AuthenticatedUserController::class, 'update'])->middleware(['auth', 'check.suspension'])->name('user.update');
Route::post('/users/{id}', [AuthenticatedUserController::class, 'destroy'])->middleware(['auth', 'check.suspension'])->name('user.destroy');
Route::get('/users/{id}', [AuthenticatedUserController::class, 'show'])->middleware(['auth', 'check.suspension'])->name('user.profile');
Route::get('/users/{user}/profile', [AuthenticatedUserController::class, 'show'])->middleware(['auth', 'check.suspension'])->name('user.profile');
Route::get('/users/{user}/profile/favorites', [AuthenticatedUserController::class, 'favorites'])->middleware(['auth', 'check.suspension']);



//followers & following
Route::get('/users/{id}/followers', [AuthenticatedUserController::class, 'getFollowers'])->middleware('check.suspension')->name('user.followers');
Route::get('/users/{id}/following', [AuthenticatedUserController::class, 'getFollows'])->middleware('check.suspension')->name('user.following');
Route::post('/user/{id}/follow', [AuthenticatedUserController::class, 'follow'])->middleware('check.suspension')->name('user.follow');
//articles

Route::get('/favorites', [AuthenticatedUserController::class, 'favorites'])->middleware(['auth', 'check.suspension']);
Route::delete('/unfavorites/{id}', [AuthenticatedUserController::class, 'remfavorite'])->middleware(['auth', 'check.suspension']);
Route::delete('/deletemyaccount', [AuthenticatedUserController::class, 'deletemyaccount'])->middleware(['auth', 'check.suspension'])->name('user.delete');
Route::delete('deleteaccount/{id}',[AuthenticatedUserController::class,'deleteUserAccount'])->middleware(['auth', 'check.suspension'])->name('admin.delete');

//admin
Route::post('/users/{id}/suspend',[AuthenticatedUserController::class,'suspend'])->middleware(['auth', 'check.suspension']);
Route::post('/users/{id}/unsuspend',[AuthenticatedUserController::class,'unsuspend'])->middleware(['auth', 'check.suspension']);
Route::post('/favorite/{id}/add', [AuthenticatedUserController::class, 'addfavorite'])->middleware(['auth', 'check.suspension']);
Route::post('/favorite/{id}/remove', [AuthenticatedUserController::class, 'remfavorite'])->middleware(['auth', 'check.suspension']);



//Post
Route::get('/post/{post_id}', [PostController::class, 'show'])->name('post.show');

//News
Route::get('/news', [NewsController::class, 'list'])->middleware('check.suspension')->name('news');
Route::get('/news/{post_id}', [NewsController::class, 'show'])->middleware('check.suspension')->name('news.show');
Route::get('/news/{post_id}/comments', [CommentController::class, 'getComments'])->middleware('check.suspension')->name('post.comments');

Route::post('/news/{post_id}/comment', [CommentController::class, 'store'])->middleware(['auth', 'check.suspension'])->name('comments.store');
Route::put('/comments/{id}', [CommentController::class, 'update'])->middleware(['auth', 'check.suspension'])->name('comments.update');

//upvote & downvote
Route::post('/news/{post_id}/upvote', [PostController::class, 'upvote'])->middleware(['auth', 'check.suspension'])->name('news.upvote');
Route::post('/news/{post_id}/downvote', [PostController::class, 'downvote'])->middleware(['auth', 'check.suspension'])->name('news.downvote');
Route::post('/news/{post_id}/voteupdate', [PostController::class, 'voteUpdate'])->middleware(['auth', 'check.suspension'])->name('news.voteupdate');

//editing
Route::get('/news/{post_id}/edit', [NewsController::class, 'edit'])->middleware(['auth', 'check.suspension'])->name('news.edit');
Route::put('/news/{post_id}', [NewsController::class, 'update'])->middleware(['auth', 'check.suspension'])->name('news.update');
Route::post('/news/{post}/remove-authors', [PostController::class, 'removeAuthors'])
    ->name('news.remove-authors');

//Topic
Route::get('/topic/{post_id}', [TopicController::class, 'show'])->middleware('check.suspension')->name('topic.show');
//editing
Route::get('/topic/{post_id}/edit', [TopicController::class, 'edit'])->middleware(['auth', 'check.suspension'])->name('topics.edit');
Route::put('/topic/{post_id}', [TopicController::class, 'update'])->middleware(['auth', 'check.suspension'])->name('topics.update');
Route::post('/topic/{post_id}/remove-authors', [TopicController::class, 'removeAuthors'])
    ->name('topics.remove-authors');
Route::post('/topic/{post_id}/accept', [TopicController::class, 'accept'])->middleware(['auth', 'check.suspension'])->name('topics.accept');
Route::post('/topic/{post_id}/reject', [TopicController::class, 'reject'])->middleware(['auth', 'check.suspension'])->name('topics.reject');

Route::middleware(['auth', 'check.suspension'])->group(function () {
  Route::controller(AdminController::class)->group(function () {
    Route::get('/admin', 'overview')->name('admin.overview');
    Route::get('/admin/users', 'users')->name('admin.users');
    Route::get('/admin/hubs', 'hubs')->name('admin.hubs');
    Route::get('/admin/posts', 'posts')->name('admin.posts');
    Route::get('/admin/reports', 'reports')->name('admin.reports');
  });
});


//Posts
//creation
Route::get('/posts/create', [PostController::class, 'createPost'])->middleware(['auth', 'check.suspension'])->name('post.create');
Route::post('/posts', [PostController::class, 'create'])->middleware(['auth', 'check.suspension'])->name('post.store');
Route::delete('/posts/delete/{id}', [PostController::class, 'delete'])->middleware(['auth', 'check.suspension'])->name('post.delete');

Route::get('/global', [FeedController::class, 'global'])->middleware('check.suspension')->name('global');

Route::middleware(['auth', 'check.suspension'])->group(function () {
  Route::controller(FeedController::class)->group(function () {
    Route::get('/home', 'home')->name('home');
    Route::get('/recent', 'recent')->name('recent');
    Route::get('/about-us', 'aboutUs')->name('about-us');
    Route::get('/bestof', 'bestof')->name('bestof');
    Route::post('/users/{id}/suspend', [AuthenticatedUserController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{id}/unsuspend', [AuthenticatedUserController::class, 'unsuspend'])->name('users.unsuspend');
    Route::post('/users/{id}/make_admin', [AuthenticatedUserController::class, 'makeAdmin'])->name('users.make_admin');
    Route::post('/users/{id}/remove_admin', [AuthenticatedUserController::class, 'removeAdmin'])->name('users.remove_admin');
  });

  Route::get('/notifications', function () {
    return view('pages.admin');
  })->name('notifications');

  // Search
  Route::controller(SearchController::class)->group(function () {
    Route::get('/search', 'search')->name('search');
  });
});


//Hub
Route::get('/hub/{id}', [CommunityController::class, 'show'])->middleware('check.suspension')->name('communities.show');

Route::middleware(['auth', 'check.suspension'])->group(function () {
  Route::get('/hubs/create', [CommunityController::class, 'createHub']);
});
Route::post('/hubs/destroy', [CommunityController::class, 'destroy'])->middleware(['auth', 'check.suspension'])->name('communities.destroy');

Route::post('/hubs', [CommunityController::class, 'store'])->middleware(['auth', 'check.suspension'])->name('communities.store');
Route::get('/all-hubs', [CommunityController::class, 'index'])->middleware(['auth', 'check.suspension'])->name('communities.index');

Route::post('/hub/{id}/join', [CommunityController::class, 'join'])->middleware(['auth', 'check.suspension'])->name('communities.join');
Route::delete('/hub/{id}/leave', [CommunityController::class, 'leave'])->middleware(['auth', 'check.suspension'])->name('communities.leave');
Route::post('/hub/{id}/privacy', [CommunityController::class, 'updatePrivacy'])->middleware(['auth', 'check.suspension'])->name('communities.update.privacy');

Route::post('/users/{user_id}/{community_id}/make_moderator', [ModeratorController::class, 'makeModerator'])->middleware(['auth', 'check.suspension'])->name('users.make_moderator');
Route::post('/users/{user_id}/{community_id}/remove_moderator', [ModeratorController::class, 'removeModerator'])->middleware(['auth', 'check.suspension'])->name('users.remove_moderator');
Route::delete('/users/{user_id}/{community_id}/remove_follower', [ModeratorController::class, 'removeFollower'])->name('community.remove_follower');
//Route::post('/communities/{id}/apply', [CommunityController::class, 'apply'])->middleware(['auth', 'check.suspension'])->name('communities.apply');

Route::middleware(['auth', 'check.suspension'])->group(function () {
  Route::controller(ModeratorController::class)->group(function () {
    Route::get('/hub/{id}/moderation', 'overview')->name('moderation.overview');
    Route::get('/hub/{id}/moderation/users', 'users')->name('moderation.users');
    Route::get('/hub/{id}/moderation/posts', 'posts')->name('moderation.posts');
    Route::get('/hub/{id}/moderation/reports', 'reports')->name('moderation.reports');
  });
});


Route::get('/reports', [ReportController::class, 'show'])->middleware(['auth', 'check.suspension']);
Route::post('/report', [ReportController::class, 'report'])->middleware(['auth', 'check.suspension'])->name('report');
Route::patch('/report/{id}/resolve', [ReportController::class, 'resolve'])->middleware(['auth', 'check.suspension']);
Route::get('/side', [SideController::class, 'show'])->middleware(['auth', 'check.suspension'])->name('side.show');

//Followers
Route::get('/hub/{id}/followers', [CommunityController::class, 'getFollowers'])->middleware('check.suspension')->name('community.followers');

//Notifications
Route::get('/notifications', [NotificationController::class, 'show'])
    ->middleware(['auth', 'check.suspension'])
    ->name('notifications.show');
    //mark as read
Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
Route::patch('/notifications/accept-follow-request/{id}', [CommunityController::class, 'acceptFollowRequest'])->middleware(['auth', 'check.suspension'])->name('communities.acceptFollowRequest');
Route::patch('/notifications/reject-follow-request/{id}', [CommunityController::class, 'rejectFollowRequest'])->middleware(['auth', 'check.suspension'])->name('communities.rejectFollowRequest');


//Hub Join Requests
Route::get('/request/{request_id}', [CommunityFollowRequest::class, 'show'])->middleware(['auth', 'check.suspension'])->name('request.show');

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
