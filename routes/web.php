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



// Authentication

Route::controller(LoginController::class)->group(function () {
  Route::get('/login', 'showLoginForm')->name('login');
  Route::post('/login', 'authenticate');
  Route::get('/logout', 'logout')->name('logout');
});

Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [PasswordResetController::class, 'updatePassword'])->name('password.updates');
Route::post('/password/update', [PasswordResetController::class, 'updatePassword'])->name('password.update1');


Route::controller(RegisterController::class)->group(function () {
  Route::get('/register', 'showRegistrationForm')->name('register');
  Route::post('/register', 'register');
});



//AuthenticatedUsers

Route::middleware(['auth', 'check.suspension'])->group(function () {
  // Authenticated User Profile and Actions
  Route::controller(AuthenticatedUserController::class)->group(function () {
    // Profile Management
    Route::get('/users/{id}/profile', 'show')->name('user.profile');
    Route::get('/users/{id}/edit', 'edit')->name('user.edit');
    Route::post('/users/{id}/update', 'update')->name('user.update');
    Route::delete('/deletemyaccount', 'deletemyaccount')->name('user.delete');
    Route::post('/users/{id}', 'destroy')->name('user.destroy');

    // Followers & Following
    Route::get('/users/{id}/followers', 'getFollowers')->name('user.followers');
    Route::get('/users/{id}/following', 'getFollows')->name('user.following');
    Route::post('/user/{id}/follow', 'follow')->name('user.follow');

    // Favorites Management
    Route::get('/users/{user}/profile/favorites', 'favorites');
    Route::get('/favorites', 'favorites');
    Route::post('/favorite/{id}/add', 'addfavorite');
    Route::post('/favorite/{id}/remove', 'remfavorite');
    Route::delete('/unfavorites/{id}', 'remfavorite');
  });
});



//Posts

Route::controller(PostController::class)->group(function () {
  // Post Viewing
  Route::get('/post/{post_id}', 'show')->name('post.show');

  // Post Creation and Management
  Route::middleware(['auth', 'check.suspension'])->group(function () {
    Route::get('/posts/create', 'createPost')->name('post.create');
    Route::post('/posts', 'create')->name('post.store');
    Route::delete('/posts/delete/{id}', 'destroy')->name('post.delete');

    // Post Voting
    Route::post('/news/{post_id}/upvote', 'upvote')->name('news.upvote');
    Route::post('/news/{post_id}/downvote', 'downvote')->name('news.downvote');
    Route::post('/news/{post_id}/voteupdate', 'voteUpdate')->name('news.voteupdate');

    // Post Author Management
    Route::post('/news/{post}/remove-authors', 'removeAuthors')->name('news.remove-authors');
    Route::post('/topic/{post_id}/remove-authors', 'removeAuthors')->name('topics.remove-authors');
  });
});



//News

Route::controller(NewsController::class)->group(function () {
  // News Viewing
  Route::get('/news', 'list')->middleware('check.suspension')->name('news');
  Route::get('/news/{post_id}', 'show')->middleware('check.suspension')->name('news.show');

  // News Editing and Updating
  Route::middleware(['auth', 'check.suspension'])->group(function () {
    Route::get('/news/{post_id}/edit', 'edit')->name('news.edit');
    Route::put('/news/{post_id}', 'update')->name('news.update');
  });
});



//Topics

Route::controller(TopicController::class)->group(function () {
  // Topic Viewing
  Route::get('/topic/{post_id}', 'show')->middleware('check.suspension')->name('topic.show');

  // Topic Editing and Updating
  Route::middleware(['auth', 'check.suspension'])->group(function () {
    Route::get('/topic/{post_id}/edit', 'edit')->name('topics.edit');
    Route::put('/topic/{post_id}', 'update')->name('topics.update');

    // Topic Author Management

    // Topic Approval/Rejection
    Route::post('/topic/{post_id}/accept', 'accept')->name('topics.accept');
    Route::post('/topic/{post_id}/reject', 'reject')->name('topics.reject');
  });
});



//Comments

Route::controller(CommentController::class)->group(function () {
  // Comment Management
  Route::middleware(['auth', 'check.suspension'])->group(function () {
    Route::post('/news/{post_id}/comment', 'store')->name('comments.store');
    Route::put('/comments/{id}', 'update')->name('comments.update');
  });

  // Comment Voting and Deletion
  Route::middleware('auth')->group(function () {
    Route::post('/comment/{comment_id}/voteupdate', 'voteUpdate')->name('comments.voteupdate');
    Route::put('/comment/{comment_id}/delete', 'delete')->name('comments.delete');
  });
});



//Administrators

Route::middleware(['auth', 'check.suspension', 'check.admin'])->group(function () {
  // Admin routes handled by AdminController
  Route::controller(AdminController::class)->group(function () {
    Route::get('/admin', 'overview')->name('admin.overview');
    Route::match(['get', 'post'], '/admin/users', 'users')->name('admin.users');
    Route::get('/admin/hubs', 'hubs')->name('admin.hubs');
    Route::get('/admin/posts', 'posts')->name('admin.posts');
    Route::get('/admin/reports', 'reports')->name('admin.reports');

    // User management routes
    Route::post('/users/{id}/suspend', 'suspend')->name('users.suspend');
    Route::post('/users/{id}/unsuspend', 'unsuspend')->name('users.unsuspend');
    Route::post('/users/{id}/make_admin', 'makeAdmin')->name('users.make_admin');
    Route::post('/users/{id}/remove_admin', 'removeAdmin')->name('users.remove_admin');
    Route::delete('/deleteaccount/{id}', 'deleteUserAccount')->name('admin.delete');
  });
});



//Feed

Route::get('/global', [FeedController::class, 'global'])->middleware('check.suspension')->name('global');

Route::middleware(['auth', 'check.suspension'])->group(function () {
  Route::controller(FeedController::class)->group(function () {
    Route::get('/home', 'home')->name('home');
    Route::get('/recent', 'recent')->name('recent');
    Route::get('/about-us', 'aboutUs')->name('about-us');
    Route::get('/bestof', 'bestof')->name('bestof');
  });

  Route::get('/notifications', function () {
    return view('pages.admin');
  })->name('notifications');

  // Search
  Route::controller(SearchController::class)->group(function () {
    Route::get('/search', 'search')->name('search');
  });
});



//Hub /Community

Route::middleware('check.suspension')->group(function () {
  // Viewing hub (community)
  Route::get('/hub/{id}', [CommunityController::class, 'show'])->name('communities.show');
  Route::get('/hub/{id}/followers', [CommunityController::class, 'getFollowers'])->name('community.followers');
});

Route::middleware(['auth', 'check.suspension'])->group(function () {
  // Community creation and management
  Route::get('/hubs/create', [CommunityController::class, 'createHub']);
  Route::post('/hubs', [CommunityController::class, 'store'])->name('communities.store');
  Route::post('/hub/{id}/join', [CommunityController::class, 'join'])->name('communities.join');
  Route::delete('/hub/{id}/leave', [CommunityController::class, 'leave'])->name('communities.leave');
  Route::post('/hub/{id}/privacy', [CommunityController::class, 'updatePrivacy'])->name('communities.update.privacy');

  // Follow request handling
  Route::post('/notifications/accept-follow-request/{id}', [CommunityController::class, 'acceptFollowRequest'])->name('community.acceptRequest');
  Route::post('/notifications/reject-follow-request/{id}', [CommunityController::class, 'rejectFollowRequest'])->name('community.rejectRequest');
});

// Hub index and destruction
Route::middleware(['auth', 'check.suspension'])->group(function () {
  Route::get('/all-hubs', [CommunityController::class, 'index'])->name('communities.index');
  Route::post('/hubs/destroy', [CommunityController::class, 'destroy'])->name('communities.destroy');
  Route::delete('deletecommunity/{id}', [CommunityController::class, 'deleteCommunity'])->name('admin.community.delete');
});



//(Hub/Community) Moderator

Route::middleware(['auth', 'check.suspension', 'check.moderator'])->group(function () {
  // Moderator-specific routes
  Route::controller(ModeratorController::class)->group(function () {
    // Moderation overview and sections
    Route::get('/hub/{id}/moderation', 'overview')->name('moderation.overview');
    Route::get('/hub/{id}/moderation/users', 'users')->name('moderation.users');
    Route::get('/hub/{id}/moderation/posts', 'posts')->name('moderation.posts');
    Route::get('/hub/{id}/moderation/reports', 'reports')->name('moderation.reports');

    // User management within communities
    Route::post('/users/{user_id}/{community_id}/make_moderator', 'makeModerator')->name('users.make_moderator');
    Route::post('/users/{user_id}/{community_id}/remove_moderator', 'removeModerator')->name('users.remove_moderator');
    Route::delete('/users/{user_id}/{community_id}/remove_follower', 'removeFollower')->name('community.remove_follower');
  });
});



//Hub Join Requests

Route::get('/request/{request_id}', [CommunityFollowRequest::class, 'show'])->middleware(['auth', 'check.suspension'])->name('request.show');



//Reports

Route::middleware(['auth', 'check.suspension'])->group(function () {
  // Viewing reports
  // Creating a new report
  Route::post('/report', [ReportController::class, 'report'])->name('report');
  // Resolving a report
  Route::patch('/report/{id}/resolve', [ReportController::class, 'resolve'])->middleware('check.moderator');
});



//Left side bar

Route::get('/side', [SideController::class, 'show'])->middleware(['auth', 'check.suspension'])->name('side.show');



// Notifications

Route::middleware(['auth', 'check.suspension'])->group(function () {
  Route::get('/notifications', [NotificationController::class, 'show'])->name('notifications.show');
  Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
});



// Recover password

Route::post('/send', [MailController::class, 'send']);

// OAuth API

Route::controller(GoogleController::class)->group(function () {
  Route::get('auth/google', 'redirect')->name('google-auth');
  Route::get('auth/google/call-back', 'callbackGoogle')->name('google-call-back');
});


//Images

Route::get('/images/{filename}', function ($filename) {
  $path = base_path('images/' . $filename);

  $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
  $extension = pathinfo($filename, PATHINFO_EXTENSION);

  if (!File::exists($path) || !in_array($extension, $allowedExtensions)) {
    abort(404);
  }

  return response()->file($path);
})->name('images.serve');
