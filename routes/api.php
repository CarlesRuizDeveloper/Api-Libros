<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/usuarios', [UserController::class, 'crearUsuario']);
    Route::put('/usuarios/{id}', [UserController::class, 'editarUsuario']);
    Route::delete('/usuarios/{id}', [UserController::class, 'borrarUsuario']);
    Route::post('/usuarios/recuperar-password', [UserController::class, 'recuperarPassword']);
    Route::post('/usuarios/reset-password', [UserController::class, 'resetPassword']);
});
