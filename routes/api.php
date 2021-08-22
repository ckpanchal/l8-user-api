<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

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

Route::post('login', [AuthController::class, 'login'])->name('api.auth.login');
Route::post('register', [AuthController::class, 'register'])->name('api.auth.register');

Route::group(['middleware' => 'jwt.verify'], function ($router) {
	Route::post('invite-user-for-registration', [UserController::class, 'inviteUserForRegistration'])->name('api.user.inviteUserForRegistration');
	Route::post('user-verification-code', [UserController::class, 'verifyUser'])->name('api.user.verifyUser');
	Route::post('update-profile', [UserController::class, 'updateProfile'])->name('api.user.updateProfile');
});