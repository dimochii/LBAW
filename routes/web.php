<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthenticatedUserController;
use App\Http\Controllers\SearchController;

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


//News
Route::get('/news', [NewsController::class, 'list'])->name('news');
Route::get('/news/{post_id}', [NewsController::class, 'show'])->name('news.show');
    //upvote & downvote
Route::post('/news/{post_id}/upvote', [NewsController::class, 'upvote'])->name('news.upvote');
Route::post('/news/{post_id}/downvote', [NewsController::class, 'downvote'])->name('news.downvote');
    //editing
Route::get('/news/{post_id}/edit', [NewsController::class, 'edit'])->middleware('auth')->name('news.edit');
Route::put('/news/{post_id}', [NewsController::class, 'update'])->middleware('auth')->name('news.update');


//Posts
    //creation
Route::get('/posts/create', [PostController::class, 'createPost'])->middleware('auth')->name('post.create');
Route::post('/posts', [PostController::class, 'create'])->middleware('auth')->name('post.store');

// To do
Route::get('/messages', [MessageController::class, 'index'])->name('messages');
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

//Search
Route::controller(SearchController::class)->group(function () {
    Route::get('/search', 'search')->name('search');
});
