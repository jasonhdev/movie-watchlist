<?php

use App\Http\Controllers\AmcDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', [MovieController::class, 'index']);
Route::get('/movie', [MovieController::class, 'index']);
Route::get('/movie/search', [MovieController::class, 'testSearchMovie']);
Route::post('/movie/create', [MovieController::class, 'createMovie']);
Route::put('/movie/update/{id}', [MovieController::class, 'updateMovie']);
Route::delete('/movie/delete/{id}', [MovieController::class, 'deleteMovie']);
Route::get('/movie/refreshBatch', [MovieController::class, 'refreshBatch']);

Route::get('/amc', [AmcDataController::class, 'index']);
Route::put('/amc/create/{id}', [AmcDataController::class, 'createMovieFromData']);
Route::get('/amc/fetchData', [AmcDataController::class, 'fetchAmcData']);
