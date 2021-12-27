<?php

use App\Http\Controllers\Authentication\LoginController;
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

Route::get('/', function (\App\Services\CloudNetService $service) {
    return dd($service->getNodes());
    return view('welcome');
})->middleware('cn-auth');

Route::post('/login', [LoginController::class, 'handleLoginPost']);
Route::get('/login', fn () => view('auth.login'))->name('login');
