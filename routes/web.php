<?php

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
})->name('welcome');

Route::post('/login', 'App\Http\Controllers\Auth\LoginController@login')->name('login');
Route::post('/logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::post('/register', 'App\Http\Controllers\Auth\RegisterController@register')->name('register');

Route::get('/dashboard', 'App\Http\Controllers\DashboardController@index')->name('dashboard');

//borrow route
Route::get('/borrow', 'App\Http\Controllers\DashboardController@borrow')->name('borrow');
Route::post('/borrow-book', 'App\Http\Controllers\DashboardController@borrow_book')->name('borrow-book');

//return route
Route::get('/return', 'App\Http\Controllers\DashboardController@return')->name('return');
Route::post('/return-book', 'App\Http\Controllers\DashboardController@return_book')->name('return-book');

//myaccount route
Route::get('/myaccount', 'App\Http\Controllers\DashboardController@myaccount')->name('myaccount');