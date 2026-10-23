<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () { return redirect('/items'); });
Route::get('/login', 'StaffSessionController@create')->name('login');
Route::post('/login', 'StaffSessionController@store');
Route::post('/logout', 'StaffSessionController@destroy')->middleware('auth');
Route::group(['middleware' => 'auth'], function () {
    Route::resource('categories', 'CategoryController', ['except' => ['show']]);
    Route::resource('items', 'ItemController');
    Route::post('/items/{id}/adjust', 'ItemController@adjust');
});
