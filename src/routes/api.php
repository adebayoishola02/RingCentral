<?php

use App\Http\Controllers\RingCentralAccountController;
use App\Http\Controllers\RingCentralMessageController;
use Illuminate\Support\Facades\Route;

//import auth controller
use App\Http\Middleware\AuthenticateWithAuthServer;

/*
****************************************************
AUTH MIDDLEWARE
****************************************************
*/

Route::prefix('/v1')->group(function () {


    Route::middleware(AuthenticateWithAuthServer::class)->group(function () {
        //
        Route::get('/user/details', ['App\Http\Controllers\UserController', 'index']);

        Route::get('accounts', [RingCentralAccountController::class, 'index']);
        Route::put('accounts', [RingCentralAccountController::class, 'store']);
        Route::patch('accounts/{uuid}', [RingCentralAccountController::class, 'update'])->whereUuid('uuid');
        Route::delete('accounts/{uuid}', [RingCentralAccountController::class, 'destroy'])->whereUuid('uuid');


        Route::get('messages', [RingCentralMessageController::class, 'index']);
        Route::put('messages', [RingCentralMessageController::class, 'send']);

        Route::prefix('/admin')
            ->middleware(\App\Http\Middleware\CheckAdminUser::class)
            ->group(function () {});
    });
});
