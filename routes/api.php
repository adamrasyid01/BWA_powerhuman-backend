<?php

use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
 

Route::get('/company', [CompanyController::class, 'all']);
Route::get('all', [UserController::class, 'fetchAll']);


Route::post('/login', [UserController::class, 'login']);

Route::post('register', [UserController::class, 'register']);

Route::post("logout", [UserController::class, 'logout'])-> middleware('auth:sanctum');

Route::get('user', [UserController::class, 'fetch']) -> middleware('auth:sanctum');