<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\TransactionController;

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

Route::group(['middleware' => ['cors', 'isAdmin']], function () {
    Route::get('/', [TemplateController::class, 'templateFunction']);
    Route::get('/users', [UserController::class, 'filter']);
    Route::get('users-metric', [UserController::class, 'metric']);
    Route::put('/update/{userId}', [UserController::class, 'updateUserProfile']);
    Route::put('/make-admin/{userId}', [UserController::class, 'makeAdmin']);
    Route::get('/user/{userId}', [UserController::class, 'getUser']);
    Route::get('/properties-metric', [PropertyController::class, 'metric']);
    Route::get('/requests-metric', [RequestController::class, 'metric']);
    Route::get('/transactions', [TransactionController::class, 'transactions']);
    Route::get('/transaction/{transactionId}', [TransactionController::class, 'transaction']);
});