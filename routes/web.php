<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PostController;

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
//Route::get('/news', [NewsController::class, 'list'])->middleware('auth')->name('news.list');
Route::get('/news', [NewsController::class, 'list'])->name('news');

//Posts
Route::post('/posts', [PostController::class, 'create'])->middleware('auth');
// Post creation
Route::get('/posts/create', [PostController::class, 'createPost'])->middleware('auth')->name('post.create');
Route::post('/posts', [PostController::class, 'create'])->middleware('auth')->name('post.store');

// To do
Route::get('/messages', [MessageController::class, 'index'])->name('messages');
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');