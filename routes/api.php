<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ExternalApiController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ProductController;
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

Route::get('/products', [ProductController::class, 'index']);

// Ruta pública para obtener comentarios de un producto
Route::get('/comments/{product_id}', [CommentController::class, 'getByProduct']);

// Ruta pública para API externa (clima)
Route::get('/external', [ExternalApiController::class, 'getWeather']); 

Route::middleware('auth:sanctum')->group(function () {
    // 3. Ruta de LOGOUT
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // 4. Ruta de CREAR PRODUCTO (solo admin)
    Route::post('/products', [ProductController::class, 'store'])->middleware('admin');

    // 4.1 Ruta de MOSTRAR PRODUCTO
    Route::get('/products/{id}', [ProductController::class, 'show']);
    
    // 5. Ruta de ACTUALIZAR PRODUCTO (solo admin)
    Route::put('/products/{id}', [ProductController::class, 'update'])->middleware('admin');
    
    // 5.1 Ruta alternativa POST para actualizar (con _method=PUT, necesaria para form-data con archivos, solo admin)
    Route::post('/products/{id}', [ProductController::class, 'update'])->middleware('admin');
    
    // 6. Ruta de ELIMINAR PRODUCTO (solo admin)
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->middleware('admin');
    
    // 7. Ruta de LIMPIEZA MASIVA (solo para desarrollo, solo admin)
    Route::delete('/products-cleanup/all-except-latest', [ProductController::class, 'cleanupOldProducts'])->middleware('admin');
    
    // 8. Rutas de FAVORITOS (requieren autenticación)
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{product_id}', [FavoriteController::class, 'destroy']);
    
    // 9. Rutas de COMENTARIOS (requieren autenticación para crear)
    Route::post('/comments', [CommentController::class, 'store']);
});