<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\BookController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('api')->controller(\App\Http\Controllers\API\V1\AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
    Route::post('/register', 'register');
});
Route::get('books', [BookController::class, 'index']);
Route::get('books/{id}', [BookController::class, 'show']);

Route::middleware(['auth:api'])->group(function () {
    //Books
    Route::apiResource('books', BookController::class)->except(['index','show']);
});




