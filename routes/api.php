<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

use App\Http\Controllers\AuthenticatedUserController;

Route::get('users', [AuthenticatedUserController::class, 'index']);
Route::post('users', [AuthenticatedUserController::class, 'store']);
Route::get('users/{id}', [AuthenticatedUserController::class, 'show']);
Route::put('users/{id}', [AuthenticatedUserController::class, 'update']);
Route::delete('users/{id}', [AuthenticatedUserController::class, 'destroy']);

Route::get('users/{id}/communities', [AuthenticatedUserController::class, 'getCommunities']);
Route::get('users/{id}/authored-posts', [AuthenticatedUserController::class, 'getAuthoredPosts']);
Route::get('users/{id}/followers', [AuthenticatedUserController::class, 'getFollowers']);
Route::get('users/{id}/follows', [AuthenticatedUserController::class, 'getFollows']);
Route::post('users/{id}/suspend', [AuthenticatedUserController::class, 'suspend']);
Route::post('users/{id}/unsuspend', [AuthenticatedUserController::class, 'unsuspend']);

