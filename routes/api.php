<?php

use App\Http\Controllers\Api\TinodeRestAuthenticatorController;
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

Route::apiResource('test', 'SampleResourceApiController');


Route::namespace('Api')->group(function () {
    Route::namespace('Auth')->group(function () {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');
        Route::post('verify', 'AuthController@verify');
        Route::post('requestVerificationCode', 'AuthController@requestVerificationCode');
        Route::post('postRegisterVerify', 'AuthController@postRegisterVerify');
        Route::middleware('auth:api')->group(function(){
            Route::post('setFcmToken', 'AuthController@setFcmToken');
            Route::post('unsetFcmToken', 'AuthController@unsetFcmToken');
        });
    });
    Route::resource('categories', 'ProviderCategoriesController');
    Route::prefix('providers')->group(function () {
        Route::get('getByCategoryId/{categoryId}', 'ProvidersController@getByCategoryId');
        Route::get('getByUid/{uid}', 'ProvidersController@getByUid');
        Route::get('getById/{id}', 'ProvidersController@getById');
        Route::middleware('auth:api')->group(function(){
            Route::post('addFavorite/{providerId}', 'FavoriteProvidersController@add');
            Route::post('deleteFavorite/{providerId}', 'FavoriteProvidersController@delete');
            Route::get('favorites', 'FavoriteProvidersController@index');
        });
    });
    Route::prefix('notify')->group(function () {
        
        Route::middleware('auth:api')->group(function(){
            Route::post('sentMessage/{recipeintUserId}', 'UsersController@notifySentMessage');
        });
    });
    Route::prefix('users')->group(function () {
        
        Route::middleware('auth:api')->group(function(){
            Route::get('getById/{id}', 'UsersController@getById');
            Route::get('getByUid/{uid}', 'UsersController@getByUid');
        });
    });
    Route::prefix('files')->group(function () {
        
        Route::middleware('auth:api')->group(function(){
            Route::post('uploadMessageFile', 'FileController@uploadMessageFile');
            Route::get('downloadMessageFile/{name}', 'FileController@downloadMessageFile');
        });
    });
    Route::prefix('sessions')->group(function () {
        
        Route::middleware('auth:api')->group(function(){
            Route::post('request/{providerId}/{chatTopicName}', 'SessionsController@request');
            Route::post('start/{sessionId}', 'SessionsController@start');
            Route::post('end/{sessionId}', 'SessionsController@end');
            Route::get('userActiveSessions', 'SessionsController@userActiveSessions');
            Route::get('userEndedSessions', 'SessionsController@userEndedSessions');
            Route::get('userRequestedSessions', 'SessionsController@userRequestedSessions');
            Route::get('providerActiveSessions', 'SessionsController@providerActiveSessions');
            Route::get('providerEndedSessions', 'SessionsController@providerEndedSessions');
            Route::get('providerRequestedSessions', 'SessionsController@providerRequestedSessions');
        });
    });
    Route::prefix('tinodeAuthenticator')->group(function () {
        Route::post('add', 'TinodeRestAuthenticatorController@add');
        Route::post('auth', 'TinodeRestAuthenticatorController@auth');
        Route::post('checkunique', 'TinodeRestAuthenticatorController@checkUnique');
        Route::post('del', 'TinodeRestAuthenticatorController@delete');
        Route::post('gen', 'TinodeRestAuthenticatorController@generate');
        Route::post('link', 'TinodeRestAuthenticatorController@link');
        Route::post('upd', 'TinodeRestAuthenticatorController@update');
        Route::post('rtagns', 'TinodeRestAuthenticatorController@restrictedTagNamespaces');
    });




    Route::middleware('auth:api')->get('user', function (Request $request) {
        if (Gate::denies('see-user')) {
            abort(403);
        }
        return auth()->user();
    });
});
