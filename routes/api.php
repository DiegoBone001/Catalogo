<?php

use App\Http\Controllers\Api\AuthController; // Asegúrate de importar el controlador
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Route; 

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'auth'], function () {
    // 1. Ruta de REGISTRO
    Route::post('register', [AuthController::class, 'register']);

    // 2. Ruta de LOGIN
    Route::post('login', [AuthController::class, 'login']);
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    // 1. Ruta de LOGOUT
    Route::post('logout', [AuthController::class, 'logout']);
});