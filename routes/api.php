<?php

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
Route::post('login', 'AuthController@login')->middleware('request.snake.case.transform');
Route::post('register', 'MobileUserController@store')->middleware('request.snake.case.transform');
Route::post('registerClub', 'ClubController@store')->middleware('request.snake.case.transform');
Route::get('clubs', 'ClubController@find');

Route::group(['middleware' => ['auth', 'request.snake.case.transform']], function(){

    Route::patch('users/device-identifier', 'UserController@updateDeviceIdentifier');
    Route::get('users/find-teacher', 'UserController@findTeacher');
    Route::get('users/myCamps', 'UserController@findMyCamps');
    Route::get('camps', 'UserController@findCamps');
    Route::get('users', 'UserController@findUsers');
    Route::post('medias', 'UserController@addPicture');
    Route::post('users/{id}/subscribe', 'CampeonatoController@subscribe');

    Route::get('club', 'ClubController@index');
    Route::put('club', 'ClubController@update');
    
    Route::get('me', 'MeController@index');
    Route::get('meTeacher', 'MeController@indexTeacher');
    Route::get('meAccount', 'MeController@indexAccount');
    Route::put('meAccount', 'MeController@update');
    Route::put('meTeacher', 'MeController@update');
    Route::get('myClubs', 'MeController@indexMyClub');
    
    Route::post('game', 'JogoController@store');
    Route::get('game/myGames', 'JogoController@getMyGames');
    Route::get('game/pendingGames', 'JogoController@getPendingGames');
    Route::patch('game/{id}/publish', 'JogoController@accept');
    Route::patch('game/{id}/reject', 'JogoController@reject');
    Route::patch('game/{id}/win', 'JogoController@setWin');
    Route::patch('game/{id}/lose', 'JogoController@setLose');
    Route::get('game/statistic', 'StatisticController@index');

    Route::post('users/{id}/club', 'MeController@addClub');

    Route::post('camp', 'CampeonatoController@store');
    Route::get('myProgressCamp', 'CampeonatoController@progressCamp');
    Route::get('doneCamp', 'CampeonatoController@doneCamp');
    Route::patch('camp/{id}/done', 'CampeonatoController@done');

    Route::post('messages', 'MessageController@store');
    Route::post('messages/{id}', 'MessageController@update');
    Route::get('messages/{id}', 'MessageController@getMessageView');
    Route::patch('messages/{id}/update-status', 'MessageController@updateStatus');
    Route::get('me/messages', 'MeController@messages');

});
