<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::middleware('auth')->group(function(){

    Route::get('/dashboard', [DashboardController::class, 'show']);

    Route::get('/admin', [DashboardController::class, 'show']);

    Route::get('/admin/user/list', [AdminUserController::class, 'list']);

    Route::get('/admin/user/add', [AdminUserController::class, 'add']);

    Route::post('/admin/user/store', [AdminUserController::class, 'store']);

    Route::get('/admin/user/delete/{id}', [AdminUserController::class, 'delete'])->name('delete_user');

    Route::get('/admin/user/action', [AdminUserController::class, 'action']);

    Route::get('/admin/user/edit/{id}', [AdminUserController::class, 'edit'])->name('user.edit');

    Route::post('/admin/user/update/{id}', [AdminUserController::class, 'update'])->name('user.update');

});


