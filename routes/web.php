<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
/*
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
*/

Route::get('migrate-warranty', 'App\Http\Controllers\WarrantyMigrateController@index');

Route::post('password/email', 'App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail');
Route::get('password/reset/{token}', function () {
    return view('app');
})->name('password.reset');
Route::post('password/reset', 'App\Http\Controllers\Auth\ResetPasswordController@reset');

Route::get('/singpass', 'App\Http\Controllers\SingpassController@login');
Route::get('/singpass/success', 'App\Http\Controllers\SingpassController@success');
Route::get('/singpass/callback', 'App\Http\Controllers\SingpassController@callback');
Route::get('/singpass/jwks.json', 'App\Http\Controllers\SingpassController@jwks');

Route::get('/myinfo', 'App\Http\Controllers\MyInfoController@index');
Route::get('/myinfo/callback', 'App\Http\Controllers\MyInfoController@callback');

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
