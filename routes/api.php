<?php

use App\Http\Controllers\Api\AuthController; // Asegúrate de importar el controlador
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\Api\ProductController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'auth'], function () {
    // 1. Ruta de REGISTRO
    Route::post('register', [AuthController::class, 'register']);

    // 2. Ruta de LOGIN
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('/products', [ProductController::class, 'index']); 

Route::middleware('auth:sanctum')->group(function () {
    // 3. Ruta de LOGOUT
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // 4. Ruta de CREAR PRODUCTO
    Route::post('/products', [ProductController::class, 'store']);
    
    // 5. Ruta de ACTUALIZAR PRODUCTO
    Route::put('/products/{id}', [ProductController::class, 'update']);
    
    // 6. Ruta de ELIMINAR PRODUCTO
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});