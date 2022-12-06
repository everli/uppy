<?php

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

use App\Http\Controllers\Web\ApplicationController;
use Illuminate\Support\Facades\Route;

Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout');

Route::prefix('/applications/{application:slug}')->group(function () {
    Route::get('/', [ApplicationController::class, 'platformRedirect'])->name('applications.redirect');
    Route::get('/icon', [ApplicationController::class, 'icon'])->name('applications.icon');
    Route::get('/{platform}/install/{build?}', [ApplicationController::class, 'install'])->name('applications.install');
    Route::get('/{platform}/raw/{build?}', [ApplicationController::class, 'raw'])->name('applications.raw');
    Route::get('/{platform:iOS}/plist/{build?}', [ApplicationController::class, 'plist'])->name('applications.plist');
});

Route::view('/{catch?}', 'app')->where('catch', '(.*)')->name('app');

