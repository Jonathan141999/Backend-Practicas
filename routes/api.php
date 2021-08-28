<?php

use App\Models\Publication;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Rutas publicas
Route::post('register', 'App\Http\Controllers\UserController@register');
Route::post('login', 'App\Http\Controllers\UserController@authenticate');
//verificar correo
Route::name('verify')->get('users/verify/{code}', 'App\Http\Controllers\UserController@verify');
Route::name('resent')->get('users/{user}/resend', 'App\Http\Controllers\UserController@resend');
//Route::post('users/create', 'App\Http\Controllers\UserController@create');
Route::group(['middleware' => ['jwt.verify']], function() {
    //Logout
    Route::get('user', 'App\Http\Controllers\UserController@getAuthenticatedUser');
    Route::post('logout', 'App\Http\Controllers\UserController@logout');
    Route::put('users/{user}', 'App\Http\Controllers\UserController@update');

    //Rutas para Publicaciones
    Route::get('publications', 'App\Http\Controllers\PublicationController@index');
    Route::get('publications/forstudents', 'App\Http\Controllers\PublicationController@forstudents');
    Route::get('publication/{category}','App\Http\Controllers\PublicationController@searchPublication');
    Route::get('publications/{publication}', 'App\Http\Controllers\PublicationController@show');
    Route::post('publications', 'App\Http\Controllers\PublicationController@store');
    Route::put('publications/{publication}', 'App\Http\Controllers\PublicationController@update');
    Route::delete('publications/{publication}', 'App\Http\Controllers\PublicationController@delete');

    //Postulation
    Route::get('postulations', 'App\Http\Controllers\PostulationController@index');
    Route::get('postulations/{apostulation}', 'App\Http\Controllers\PostulationController@show');
    Route::get('postulations/detail/{publication}', 'App\Http\Controllers\PostulationController@detail');
    Route::get('postulation/user', 'App\Http\Controllers\PostulationController@requestsByUser');

    //verificar este mañana
    Route::post('postulations', 'App\Http\Controllers\PostulationController@store');
    Route::put('postulations/{apostulation}', 'App\Http\Controllers\PostulationController@update');
    Route::delete('postulations/{request}', 'App\Http\Controllers\PostulationController@delete');
    Route::put('postulations/status/{arequest}', 'App\Http\Controllers\PostulationController@updatestatus');

    //Rutas para Categorias

    Route::get('categories', 'App\Http\Controllers\CategoryController@index');
    Route::get('categories/{category}', 'App\Http\Controllers\CategoryController@show');
    Route::post('categories', 'App\Http\Controllers\CategoryController@store');
    Route::put('categories/{category}', 'App\Http\Controllers\CategoryController@update');
    Route::delete('categories/{category}', 'App\Http\Controllers\CategoryController@delete');

    //Rutas para la postulacion Postulación
    //Route::get('postulations', 'App\Http\Controllers\PostulationController@index');
    //Route::get('postulations/{postulation}', 'App\Http\Controllers\PostulationController@show');
    //Route::post('postulations', 'App\Http\Controllers\PostulationController@store');
    //Route::put('postulations/{postulation}', 'App\Http\Controllers\PostulationController@update');
    //Route::delete('postulations/{postulation}', 'App\Http\Controllers\PostulationController@delete');
});


//Route::middleware('auth:api')->get('/user', function (Request $request) {
//return $request->user();
//});
