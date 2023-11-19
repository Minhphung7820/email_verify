<?php

use App\Http\Controllers\API\AuthController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth.tmp'])->group(function () {
    Route::get('/', function () {
        dd("hello");
    });
    Route::get('/logout', [AuthController::class, 'logout']);
});
