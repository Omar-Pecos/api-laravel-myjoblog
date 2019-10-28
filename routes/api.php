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
Route::resource('/journeys','JourneyController',['except' =>'create','store','edit','update','destroy'])->middleware('cors');

Route::post('/init_journey','JourneyController@init_journey')->middleware('cors');
Route::get('/pause_journey','JourneyController@pause_journey')->middleware('cors');
Route::get('/continue_journey','JourneyController@continue_journey')->middleware('cors');
Route::post('/end_journey','JourneyController@end_journey')->middleware('cors');
Route::get('/has_journey','JourneyController@hasactivejourney')->middleware('cors');


// Usuarios
Route::resource('/users','UserController',['except' =>'create','edit','store'])->middleware('cors');
// me da los 2 primeros users ;;;;
Route::get('/get_first_users', 'UserController@get2first')->middleware('cors');


// Pdf Controller

Route::get('/get_trigger', 'PdfController@getTrigger')->middleware('cors');
Route::post('/set_trigger', 'PdfController@setTrigger')->middleware('cors');

Route::get('/get_files', 'PdfController@getMyFiles')->middleware('cors');
Route::get('/see_file', 'PdfController@seeFile')->middleware('cors');
Route::get('/down_file', 'PdfController@downloadFile')->middleware('cors');
Route::get('/delete_file', 'PdfController@deleteFile')->middleware('cors');

// Job Controller
Route::get('/pdf', 'JobController@processQueue')->middleware('cors');

// Journey ( ChartJs data )
//Route::get('/get_chartdata','JourneyController@chart_data')->middleware('cors');
Route::get('/data_line/{id}','JourneyController@chart_line_pormes')->middleware('cors');
Route::get('/data_donut_porcentage/{id}','JourneyController@chart_donut_porcentaje')->middleware('cors');
Route::get('/data_donut_today/{id}','JourneyController@chart_donut_dia')->middleware('cors');
Route::get('/data_donut_month/{id}','JourneyController@chart_donut_mes')->middleware('cors');
Route::get('/data_donut_year/{id}','JourneyController@chart_donut_anio')->middleware('cors');


// Image controller 
Route::get('/getimage/{name}','ImageController@getImage')->middleware('cors');

