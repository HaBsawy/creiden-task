<?php

use App\Helpers\ResponseHelper;
use App\Http\Controllers\API\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\StorageController;
use App\Http\Controllers\API\User\AuthController as UserAuthController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

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

Route::get('notAuth', function () {
    return ResponseHelper::notAuthenticated();
})->name('notAuth');

Route::group(['prefix' => 'admins/auth', 'as' => 'admins.'], function () {
    Route::post('register', [AdminAuthController::class, 'register'])->name('register');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout')
        ->middleware('auth:admin');
});

Route::group(['prefix' => 'users/auth', 'as' => 'users.'], function () {
    Route::post('register', [UserAuthController::class, 'register'])->name('register');
    Route::post('login', [UserAuthController::class, 'login'])->name('login');
    Route::post('logout', [UserAuthController::class, 'logout'])->name('logout')
        ->middleware('auth:user');
});


Route::post('users/items', [ItemController::class, 'storeUserItem'])
    ->name('users.items.store')->middleware('auth:user');

Route::group(['middleware' => 'auth:admin'], function () {
    Route::apiResource('users', UserController::class)
        ->missing(function () {
            return ResponseHelper::notFound();
        });

    Route::apiResource('storages', StorageController::class)
        ->missing(function () {
            return ResponseHelper::notFound();
        });

    Route::apiResource('items', ItemController::class)
        ->missing(function () {
            return ResponseHelper::notFound();
        });
});
