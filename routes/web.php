<?php

use Illuminate\Support\Facades\Route;
use App\Tweet;

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
	//$tweets = App\Tweet::orderBy('created_at','desc')->take(5)->get();
    return view('welcome');
});
