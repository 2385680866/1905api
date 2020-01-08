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

Route::get('/', function () {
    return view('welcome');
});
Route::get("test/pay","TestController@alipay"); //去支付
Route::get("test/alipay/return","Alpay\PayController@aliReturn");
Route::get("test/alipay/notify","Alpay\PayController@notify");
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
