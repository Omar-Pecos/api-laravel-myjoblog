<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

// registro y login !
Route::post('/register','UserController@register')->middleware('cors');
Route::post('/login','UserController@login')->middleware('cors');

// Jornadas 
Route::resource('/journeys','JourneyController',['except' =>'create','store','edit','update','destroy']);

Route::post('/init_journey','JourneyController@init_journey')->middleware('cors');
Route::post('/end_journey','JourneyController@end_journey')->middleware('cors');
Route::get('/has_journey','JourneyController@hasactivejourney')->middleware('cors');

// Usuarios
Route::resource('/users','UserController',['except' =>'create','edit','store']);


// Image controller 
Route::get('/getimage/{name}','ImageController@getImage')->middleware('cors');